<?php

namespace console\workers;

use common\components\MainFunctions;
use common\models\Device;
use common\models\DeviceStatus;
use common\models\DeviceType;
use common\models\Node;
use inpassor\daemon\Worker;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Exception\AMQPTimeoutException;
use PhpAmqpLib\Message\AMQPMessage;
use Yii;
use ErrorException;
use Exception;

class MtmServerAmqpWorker extends Worker
{
    const ROUTE_TO_LSERVER = 'routeLServer';
    const EXCHANGE = 'light';
    const QUERY_LSERVER = 'queryLServer';

    public $active = true;
    public $maxProcesses = 1;
    public $delay = 60;
    public $run = true;


    /** @var AMQPStreamConnection */
    private $connection;
    /** @var AMQPChannel $channel */
    private $channel;

    public function handler($signo)
    {
        $this->log('call handler... ' . $signo);
        switch ($signo) {
            case SIGTERM:
            case SIGINT:
                $this->run = false;
                break;
        }
    }

    public function init()
    {
        $this->logFile = '@console/runtime/daemon/logs/mtm_server_amqp_worker.log';
        parent::init();

        $params = Yii::$app->params;
        if (!isset($params['amqpServer']['host']) ||
            !isset($params['amqpServer']['port']) ||
            !isset($params['amqpServer']['user']) ||
            !isset($params['amqpServer']['password'])) {
            $this->log('Не задана конфигурация сервера сообщений и шкафа.');
            $this->run = false;
            return;
        }

        try {
            $this->connection = new AMQPStreamConnection($params['amqpServer']['host'],
                $params['amqpServer']['port'],
                $params['amqpServer']['user'],
                $params['amqpServer']['password']);

            $this->channel = $this->connection->channel();
            $this->channel->exchange_declare(self::EXCHANGE, 'direct', false, true, false);
            $this->channel->queue_declare(self::QUERY_LSERVER, false, true, false, false);
            $this->channel->queue_bind(self::QUERY_LSERVER, self::EXCHANGE, self::ROUTE_TO_LSERVER);
            $this->channel->basic_consume(self::QUERY_LSERVER, '', false, false, false, false, [&$this, 'callback']);
        } catch (Exception $e) {
            $this->log($e->getMessage());
            $this->log('init not complete');
            $this->run = false;
            return;
        }

        pcntl_signal(SIGTERM, [&$this, 'handler']);
        pcntl_signal(SIGINT, [&$this, 'handler']);

        $this->log('init complete');
    }


    /**
     * @throws Exception
     */
    public function run()
    {
        $checkNodes = 0;
        $checkNodesRate = 30;

        $this->log('run...');
        while ($this->run) {
//            $this->log('tick...');
            // TODO: придумать механизм который позволит выбирать все сообщения в очереди, а не по одному с задержкой в секунду
            try {
                if (count($this->channel->callbacks)) {
//                    $this->log('wait for message...');
                    $this->channel->wait(null, true);
//                    $this->log('end wait...');
                }
            } catch (ErrorException $e) {
                $this->log($e->getMessage());
            } catch (AMQPTimeoutException $e) {
                $this->log($e->getMessage());
            } catch (Exception $e) {
                $this->log($e->getMessage());
                return;
            }

            // изменяем статус шкафа если от координатора давно не поступали данные
            // это не верно, т.к. шкаф может быть доступен, но все потоки в том числе и координатора на нём остановлены
            // пока сделаю так
            $linkTimeOut = 60;
            $currentTime = time();
            if ($checkNodes + $checkNodesRate < $currentTime) {
                $checkNodes = $currentTime;
                // для всех шкафов от которых не было пакетов состояния координатора более $timeOut секунд,
                // а статус был "В порядке", устанавливаем статус "Нет связи"
                $db = Yii::$app->db;
                // выбираем все шкафы которые будут менять статус с WORK на NOT_LINK
                $params = [
                    ':deviceType' => DeviceType::DEVICE_ZB_COORDINATOR,
                    ':timeOut' => $linkTimeOut,
                    ':workUuid' => DeviceStatus::WORK
                ];
                $command = $db->createCommand("
SELECT nt.uuid as nodeUuid, dt.uuid as deviceUuid, dt.address, nt.oid
FROM node AS nt
LEFT JOIN device AS dt ON dt.nodeUuid=nt.uuid
LEFT JOIN sensor_channel AS sct ON sct.deviceUuid=dt.uuid
LEFT JOIN measure AS mt ON mt.sensorChannelUuid=sct.uuid
WHERE dt.deviceTypeUuid=:deviceType
AND nt.deviceStatusUuid=:workUuid
AND (timestampdiff(second,  mt.changedAt, current_timestamp()) > :timeOut OR mt.changedAt IS NULL)
GROUP BY dt.uuid", $params);
//ORDER BY mt.changedAt DESC", $params);
                $result = $command->query()->readAll();
//                $this->log('sel query: ' . $command->rawSql);

                // создаём записи в логах о смене статуса, составляем список для изменения статуса
                $uuid2Update = [];
                foreach ($result as $device) {
                    $uuid2Update[] = $device['nodeUuid'];
                    $rc = MainFunctions::deviceRegister($device['deviceUuid'], "Устройство изменило статус на 'Нет связи' (" . $device['address'] . ")", $device['oid']);
                    $this->log('MainFunctions::deviceRegister: ' . $rc);
                }

                // изменяем статус
                $params = [
                    ':noLinkUuid' => DeviceStatus::NOT_LINK,
                ];
                $inParam = [];
                $inParamSql = $db->getQueryBuilder()->buildCondition(['IN', 'nt.uuid', $uuid2Update], $inParam);
                $params = array_merge($params, $inParam);
                $command = $db->createCommand("
UPDATE node AS nt SET nt.deviceStatusUuid=:noLinkUuid, changedAt=current_timestamp()
WHERE $inParamSql", $params);
//                $this->log('upd query: ' . $command->rawSql);
                $command->execute();

                // для всех шкафов от которых были получены пакеты со статусом координатора менее 30 секунд назад,
                // а статус был "Нет связи", устанавливаем статус "В порядке"
                $params = [
                    ':timeOut' => $linkTimeOut,
                    ':noLinkUuid' => DeviceStatus::NOT_LINK,
                    ':deviceType' => DeviceType::DEVICE_ZB_COORDINATOR,
                ];

                $command = $db->createCommand("
SELECT nt.uuid as nodeUuid, dt.uuid as deviceUuid, dt.address, nt.oid
FROM node AS nt
LEFT JOIN device AS dt ON dt.nodeUuid=nt.uuid
LEFT JOIN sensor_channel AS sct ON sct.deviceUuid=dt.uuid
LEFT JOIN measure AS mt ON mt.sensorChannelUuid=sct.uuid
WHERE dt.deviceTypeUuid=:deviceType
AND nt.deviceStatusUuid=:noLinkUuid
AND (timestampdiff(second,  mt.changedAt, current_timestamp()) < :timeOut)
GROUP BY dt.uuid", $params);
//ORDER BY mt.changedAt DESC ", $params);
//                $this->log('upd query: ' . $command->rawSql);
                $result = $command->query()->readAll();

                // создаём записи в логах о смене статуса, составляем список для изменения статуса
                $uuid2Update = [];
                foreach ($result as $device) {
                    $uuid2Update[] = $device['nodeUuid'];
                    $rc = MainFunctions::deviceRegister($device['deviceUuid'], "Устройство изменило статус на 'В порядке' (" . $device['address'] . ")", $device['oid']);
                    $this->log('MainFunctions::deviceRegister: ' . $rc);
                }

                $params = [
                    ':workUuid' => DeviceStatus::WORK,
                ];
                $inParam = [];
                $inParamSql = $db->getQueryBuilder()->buildCondition(['IN', 'nt.uuid', $uuid2Update], $inParam);
                $params = array_merge($params, $inParam);
                $command = $db->createCommand("
UPDATE node AS nt SET nt.deviceStatusUuid=:workUuid, changedAt=current_timestamp()
WHERE $inParamSql", $params);
//                $this->log('upd query: ' . $command->rawSql);
                $command->execute();

                // для всех шкафов у которых нет координаторов и каналов измерения для них, ставим нет связи
                $params = [
                    ':workUuid' => DeviceStatus::WORK,
                    ':noLinkUuid' => DeviceStatus::NOT_LINK,
                    ':deviceType' => DeviceType::DEVICE_ZB_COORDINATOR,
                ];

                $command = $db->createCommand("UPDATE node AS nt SET nt.deviceStatusUuid=:noLinkUuid
WHERE nt.uuid NOT IN (
SELECT dt.nodeUuid FROM device AS dt
LEFT JOIN sensor_channel AS sct ON sct.deviceUuid=dt.uuid
WHERE dt.deviceTypeUuid=:deviceType
GROUP BY dt.uuid
)
AND nt.deviceStatusUuid=:workUuid", $params);
//                $this->log('upd query: ' . $command->rawSql);
                $command->execute();
            }

            pcntl_signal_dispatch();
            sleep(1);
        }

        if ($this->connection != null) {
            $this->channel->close();
            $this->connection->close();
        }

        $this->log('finish...');
    }

    /**
     * @param AMQPMessage $msg
     */
    public function callback($msg)
    {
//        $this->log('get msg');
        $content = json_decode($msg->body);
        $type = $content->type;
//        $oid = $content->oid;
//        $nid = $content->nid;
//        $address = strtoupper($content->address);
//        $data = $content->data;
        switch ($type) {
            default:
                break;
        }
    }

}
