<?php

namespace console\workers;

use common\models\Device;
use common\models\LightStatus;
use common\models\mtm\MtmDevLightStatus;
use common\models\Node;
use common\models\Organisation;
use inpassor\daemon\Worker;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Exception\AMQPTimeoutException;
use PhpAmqpLib\Message\AMQPMessage;
use Yii;
use ErrorException;
use Exception;
use yii\base\InvalidConfigException;

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
            exit(-1);
        }

        $this->connection = new AMQPStreamConnection($params['amqpServer']['host'],
            $params['amqpServer']['port'],
            $params['amqpServer']['user'],
            $params['amqpServer']['password']);

        $this->channel = $this->connection->channel();
        $this->channel->exchange_declare(self::EXCHANGE, 'direct', false, true, false);
        $this->channel->queue_declare(self::QUERY_LSERVER, false, true, false, false);
        $this->channel->queue_bind(self::QUERY_LSERVER, self::EXCHANGE, self::ROUTE_TO_LSERVER);
        $this->channel->basic_consume(self::QUERY_LSERVER, '', false, false, false, false, [&$this, 'callback']);

        pcntl_signal(SIGTERM, [&$this, 'handler']);
        pcntl_signal(SIGINT, [&$this, 'handler']);

        $this->log('init complete');
    }


    /**
     * @throws Exception
     */
    public function run()
    {
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
            }

            pcntl_signal_dispatch();
            sleep(1);
        }

        $this->channel->close();
        $this->connection->close();
        $this->log('finish...');
    }

    /**
     * @param AMQPMessage $msg
     * @throws InvalidConfigException
     */
    public function callback($msg)
    {
//        $this->log('get msg');
        $content = json_decode($msg->body);
        $type = $content->type;
        $oid = $content->oid;
        $nid = $content->nid;
        $address = strtoupper($content->address);
        $data = $content->data;
        switch ($type) {
            case 'lightstatus' :
                $status = new MtmDevLightStatus();
                if ($status->loadBase64Data($data)) {
                    $this->log('Parse error light data.');
                } else {
//                    $this->log('Успешно разобрали данные статуса светильника!!!');

                    $orgUuid = null;
                    $nodeUuid = null;
                    $deviceUuid = null;

                    $organisation = Organisation::findOne($oid);
                    if ($organisation != null) {
                        $orgUuid = $organisation->uuid;
                    }

                    $node = Node::findOne($nid);
                    if ($node != null) {
                        $nodeUuid = $node->uuid;
                    }

                    $device = Device::find()->where(['oid' => $orgUuid, 'nodeUuid' => $nodeUuid, 'address' => $address])->one();
                    if ($device != null) {
                        $deviceUuid = $device->uuid;
                    } else {
                        $this->log('Not found device: oid=' . $oid . ', $bid=' . $nid . ', address=' . $address);
                        return;
                    }

                    $lightStatus = LightStatus::find()->where(['oid' => $orgUuid, 'deviceUuid' => $deviceUuid])->one();
                    if ($lightStatus == null) {
                        $lightStatus = new LightStatus();
                    }

                    $lightStatus->oid = $orgUuid;
                    $lightStatus->deviceUuid = $deviceUuid;
                    $lightStatus->date = date('Y-m-d H:i:s');
                    $lightStatus->address = $address;
                    $lightStatus->setAlerts($status->alert);
                    $lightStatus->setStatus($status->data);
                    if ($lightStatus->save()) {
                        /** @var AMQPChannel $channel */
                        $channel = $msg->delivery_info['channel'];
                        $channel->basic_ack($msg->delivery_info['delivery_tag']);
                    } else {
                        $this->log('Не удалось записать статус светильника...');
                        foreach ($lightStatus->getErrors() as $error) {
                            $this->log($error);
                        }
                    }
                }
                break;
            default:
                break;
        }
    }

}
