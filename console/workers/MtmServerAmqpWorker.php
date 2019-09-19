<?php

namespace console\workers;

use common\models\DeviceStatus;
use common\models\DeviceType;
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
                $params = [
                    ':timeOut' => $linkTimeOut,
                    ':noLinkUuid' => DeviceStatus::NOT_LINK,
                    ':workUuid' => DeviceStatus::WORK,
                    ':deviceType' => DeviceType::DEVICE_ZB_COORDINATOR,
                ];
                $command = $db->createCommand("UPDATE mtm.node AS nt SET nt.deviceStatusUuid=:noLinkUuid, changedAt=current_timestamp()
WHERE nt.uuid IN (
SELECT dt.nodeUuid FROM mtm.device AS dt
LEFT JOIN mtm.sensor_channel AS sct ON sct.deviceUuid=dt.uuid
LEFT JOIN mtm.measure AS mt ON mt.sensorChannelUuid=sct.uuid
WHERE dt.deviceTypeUuid=:deviceType
AND (timestampdiff(second,  mt.changedAt, current_timestamp()) > :timeOut OR mt.changedAt IS NULL)
GROUP BY dt.uuid
ORDER BY mt.changedAt DESC
)
AND nt.deviceStatusUuid=:workUuid", $params);
//                $this->log('upd query: ' . $command->rawSql);
                $command->execute();

                // для всех шкафов от которых были получены пакеты со статусом координатора менее 30 секунд назад,
                // а статус был "Нет связи", устанавливаем статус "В порядке"
                $command = $db->createCommand("UPDATE mtm.node AS nt SET nt.deviceStatusUuid=:workUuid, changedAt=current_timestamp()
WHERE nt.uuid IN (
SELECT dt.nodeUuid FROM mtm.device AS dt
LEFT JOIN mtm.sensor_channel AS sct ON sct.deviceUuid=dt.uuid
LEFT JOIN mtm.measure AS mt ON mt.sensorChannelUuid=sct.uuid
WHERE dt.deviceTypeUuid=:deviceType
AND (timestampdiff(second,  mt.changedAt, current_timestamp()) < :timeOut)
GROUP BY dt.uuid
ORDER BY mt.changedAt DESC
)
AND nt.deviceStatusUuid=:noLinkUuid", $params);
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
