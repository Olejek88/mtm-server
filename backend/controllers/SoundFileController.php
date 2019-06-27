<?php

namespace backend\controllers;

use common\models\Organisation;
use common\models\User;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Yii;
use common\models\SoundFile;
use backend\models\SoundFileSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use Throwable;
use yii\base\InvalidConfigException;

/**
 * SoundFileController implements the CRUD actions for SoundFile model.
 */
class SoundFileController extends Controller
{
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
     * Lists all SoundFile models.
     * @return mixed
     * @throws InvalidConfigException
     */
    public function actionIndex()
    {
        $searchModel = new SoundFileSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single SoundFile model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     * @throws InvalidConfigException
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        return $this->render('view', [
            'model' => $model,
        ]);
    }

    /**
     * Finds the SoundFile model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return SoundFile the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     * @throws InvalidConfigException
     */
    protected function findModel($id)
    {
        if (($model = SoundFile::find()->where(['_id' => $id, 'deleted' => 0])->one()) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    /**
     * Creates a new SoundFile model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new SoundFile();
        if ($model->load(Yii::$app->request->post())) {
            // process uploaded image file instance
            $uFile = $model->uploadSoundFile();

            if ($model->save()) {
                // upload only if valid uploaded file instance found
                if ($uFile !== false) {
                    $filePath = $model->getSoundFile();
                    if (!file_exists($model->uploadPath)) {
                        mkdir($model->uploadPath, 0777, true);
                    }

                    $uFile->saveAs($filePath);
                }
                return $this->redirect(['view', 'id' => $model->_id]);
            }
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing SoundFile model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     * @throws InvalidConfigException
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $model->scenario = 'update';

        $oldFilePath = $model->getSoundFile();
        $oldFileName = $model->soundFile;

        if ($model->load(Yii::$app->request->post())) {
            // process uploaded image file instance
            $uFile = $model->uploadSoundFile();

            // revert back if no valid file instance uploaded
            if ($uFile === false) {
                $model->soundFile = $oldFileName;
            }

            if ($model->save()) {
                // upload only if valid uploaded file instance found
                if ($uFile !== false && unlink($oldFilePath)) { // delete old and overwrite
                    $path = $model->getSoundFile();
                    $uFile->saveAs($path);
                }
                return $this->redirect(['view', 'id' => $model->_id]);
            }
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing SoundFile model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     * @throws Throwable
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $model->scenario = 'delete';
        $model->deleted = true;
        $model->deleteSoundFile();
        $model->save();

        return $this->redirect(['index']);
    }

    /**
     * функция отрабатывает сигналы от дерева и выполняет добавление нового оборудования
     *
     * @return mixed
     */
    public
    function actionNew()
    {
        $message = new SoundFile();
        return $this->renderAjax('_add_form', [
            'model' => $message
        ]);
    }

    /**
     * Creates a new Message model.
     * @return mixed
     */
    public
    function actionSave()
    {
        $model = new SoundFile();
        if ($model->load(Yii::$app->request->post())) {
            // получаем изображение для последующего сохранения
            $uFile = $model->uploadSoundFile();

            if ($uFile !== false) {
                $filePath = $model->getSoundFile();
                if (!file_exists($model->uploadPath)) {
                    mkdir($model->uploadPath, 0777, true);
                }
                $uFile->saveAs($filePath);
            }
            if ($model->save(false)) {
                return $this->redirect('index');
            }
        }
        echo json_encode($model->errors);
        //return $this->redirect('index');
    }

    /**
     * @return mixed
     */
    public
    function actionSend()
    {
        if ($_GET['messageUuid'] && $_GET['nodeId']) {
            $pkt = [
                'type' => 'sound',
                'action' => 'play',
                'uuid' => $_GET['messageUuid'],
            ];
            $org_id = User::getOid(Yii::$app->user->identity);
            $org_id = Organisation::find()->where(['uuid' => $org_id])->one()->_id;
            $node_id = $_GET['nodeId'];
            self::sendConfig($pkt, $org_id, $node_id);
        }
        return $this->redirect('index');
    }

    /**
     * функция отправляет конфигурацию на светильник
     *
     * @param $packet
     * @param $org_id
     * @param $node_id
     */
    function sendConfig($packet, $org_id, $node_id)
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
}
