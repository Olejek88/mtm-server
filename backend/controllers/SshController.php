<?php

namespace backend\controllers;

use backend\models\SshForm;
use common\models\Node;
use common\models\Organisation;
use common\models\User;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Yii;
use yii\data\ArrayDataProvider;
use yii\filters\VerbFilter;
use yii\web\Controller;

/**
 * AreaController implements the CRUD actions for Area model.
 */
class SshController extends Controller
{
    /**
     * @param $password
     * @param $localPort
     * @param $bindIp
     * @param $remotePort
     * @param $user
     * @param $remoteHost
     * @return string
     */
    public static function getSshpassCmd($password, $localPort, $bindIp, $remotePort, $user, $remoteHost)
    {
        $cmd = "/usr/bin/sshpass -p '{$password}' ";
        $cmd .= "/usr/bin/ssh -C -N -R {$localPort}:{$bindIp}:{$remotePort} ";
        $cmd .= "-o StrictHostKeyChecking=no -o UserKnownHostsFile=/dev/null ";
        $cmd .= "{$user}@{$remoteHost} -p {$remotePort} > /dev/null 2>&1 &";
        return $cmd;
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all Area models.
     * @return mixed
     */
    public function actionIndex()
    {
        $request = Yii::$app->request;
        $model = new SshForm();
        if ($request->isPost) {
            if ($model->load($request->post()) && $model->validate()) {
                $pkt = [
                    'type' => 'ssh',
                    'action' => 'start',
                    'password' => $model->password,
                    'localPort' => $model->localPort,
                    'bindIp' => $model->bindIp,
                    'remotePort' => $model->remotePort,
                    'user' => $model->user,
                    'remoteHost' => $model->remoteHost,
                ];
                $org_id = User::getOid(Yii::$app->user->identity);
                $org_id = Organisation::find()->where(['uuid' => $org_id])->one()->_id;
                $node = Node::find()->where(['uuid' => $_GET['uuid']])->asArray()->one();
                self::sendCommand($pkt, $org_id, $node['_id']);
                return $this->redirect('ssh/index');
            }
        } else {
            $model = new SshForm();
            $model->localPort = 41234;
            $model->bindIp = 'localhost';
            $model->remotePort = 22;
            $model->remoteHost = 'iot.mtm-smart.com';
            $model->user = 'support';
            $model->password = '';
        }

        $dataProvider = new ArrayDataProvider([
            'allModels' => self::getSshpassProcesses(),
        ]);

        return $this->render('index', [
            'model' => $model,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * функция отправляет команду на контроллер
     *
     * @param $packet
     * @param $org_id
     * @param $node_id
     */
    function sendCommand($packet, $org_id, $node_id)
    {
        $params = Yii::$app->params;
        if (!isset($params['amqpServer']['host']) ||
            !isset($params['amqpServer']['port']) ||
            !isset($params['amqpServer']['user']) ||
            !isset($params['amqpServer']['password'])) {
            return;
        }

        $connection = new AMQPStreamConnection($params['amqpServer']['host'],
            $params['amqpServer']['port'],
            $params['amqpServer']['user'],
            $params['amqpServer']['password']);

        $channel = $connection->channel();

        // инициализация exhange
        $channel->exchange_declare('light', 'direct', false, true, false);

        // отправка сообщения на шкаф с _id=1, принадлежащий организации с _id=1
        $message = new AMQPMessage(json_encode($packet), array('delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT));
        $channel->basic_publish($message, 'light', 'routeNode-' . $org_id . '-' . $node_id); // queryNode-1-1
    }

    private function getSshpassProcesses()
    {
        $processes = [];
        $cmd = 'ps aux | grep sshpass';
        exec($cmd, $output);
        foreach ($output as $item) {
            $pos = strpos($item, '/usr/bin/sshpass');
            if ($pos !== false) {
                preg_match('/\s(\d+)\s/', $item, $matches);
                $processes[] = ['id' => $matches[1], 'cmd' => substr($item, $pos)];
            }
        }

        return $processes;
    }

    public function actionDelete($id)
    {
        $processes = self::getSshpassProcesses();
        if (!empty($processes[$id])) {
            $cmd = "/bin/kill {$processes[$id]['id']}";
            exec($cmd);
        }

        return $this->redirect('ssh/index');
    }

}
