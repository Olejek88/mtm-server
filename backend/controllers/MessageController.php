<?php

namespace backend\controllers;

use common\components\MainFunctions;
use common\models\Users;
use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use common\models\Message;
use backend\models\MessageSearch;

/**
 * MessageController implements the CRUD actions for Message model.
 */
class MessageController extends Controller
{
    protected $modelClass = Message::class;

    /**
     * Lists all Message models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new MessageSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->pagination->pageSize = 25;

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Message model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Displays a messagebox
     * @return mixed
     */
    public function actionList()
    {
        $accountUser = Yii::$app->user->identity;
        $currentUser = Users::find()
            ->where(['userId' => $accountUser['id']])
            ->asArray()
            ->one();

        $messages = Message::find()->where(['fromUserUuid' => $currentUser['uuid']])
            ->orWhere(['toUserUuid' => $currentUser['uuid']])
            ->orderBy('date DESC')
            ->all();
        $income = Message::find()->where(['toUserUuid' => $currentUser['uuid']])
            ->orderBy('date DESC')
            ->all();
        $sent = Message::find()->where(['fromUserUuid' => $currentUser['uuid']])
            ->orderBy('date DESC')
            ->all();

        return $this->render('list', [
            'messages' => $messages,
            'income' => $income,
            'sent' => $sent
        ]);
    }

    public function actionSearch()
    {
        /**
         * [Базовые определения]
         * @var [type]
         */
        $model             = 'Test';

        return $this->render('search', [
            'model'            => $model,
        ]);
    }

    /**
     * Creates a new Message model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Message();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['table']);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * @param $action
     * @return bool
     * @throws \yii\web\BadRequestHttpException
     */
    public function beforeAction($action)
    {
        $this->enableCsrfValidation = false;
        if ($action->id === 'index' || $action->id === 'create'
            || $action->id === 'update' || $action->id === 'delete') {
            $this->enableCsrfValidation = true;
        }
        return parent::beforeAction($action);
    }

    /**
     * Creates a new Message model in chat for all users
     * @return mixed
     */
    public function actionSend()
    {
        $this->enableCsrfValidation = false;
        $model = new Message();
        $model->uuid = MainFunctions::GUID();
        $accountUser = Yii::$app->user->identity;
        $currentUser = Users::findOne(['userId' => $accountUser['id']]);
        $model->fromUserUuid = $currentUser['uuid'];
        $model->text = $_POST["message"];
        $model->toUserUuid = $model->fromUserUuid;
        $model->status = 0;
        $model->date = date("Ymd");
        $model->save();
        return $this->redirect(['/site/dashboard']);
    }

/**
     * Updates an existing Message model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->_id]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing Message model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException
     * @throws \Exception
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the Message model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Message the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Message::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    /**
     * функция отрабатывает сигналы от дерева и выполняет добавление нового оборудования
     *
     * @return mixed
     */
    public
    function actionNew()
    {
        $message = new Message();
        return $this->renderAjax('_add_form', [
            'message' => $message
        ]);
    }

    /**
     * Creates a new Equipment model.
     * @return mixed
     */
    public
    function actionSave()
    {
        $model = new Message();
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect('list');
        }
        return false;
    }

}
