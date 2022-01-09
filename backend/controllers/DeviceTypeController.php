<?php

namespace backend\controllers;

use backend\models\DeviceSearchType;
use common\models\DeviceType;
use common\models\User;
use Throwable;
use Yii;
use yii\db\StaleObjectException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

/**
 * DeviceTypeController implements the CRUD actions for DeviceType model.
 */
class DeviceTypeController extends Controller
{
    private static $hardUuid = [
        DeviceType::DEVICE_COUNTER,
        DeviceType::DEVICE_ZB_COORDINATOR,
        DeviceType::DEVICE_ZB_COORDINATOR_E18,
        DeviceType::DEVICE_LIGHT,
        DeviceType::DEVICE_LIGHT_WITHOUT_ZB
    ];


    /**
     * Behaviors
     *
     * @inheritdoc
     *
     * @return array
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all StageTemplate models.
     *
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new DeviceSearchType();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->pagination->pageSize = 100;

        return $this->render(
            'index',
            [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
            ]
        );
    }

    /**
     * Displays a single DeviceType model.
     *
     * @param integer $id Id
     *
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function actionView($id)
    {
        return $this->render(
            'view',
            [
                'model' => $this->findModel($id)
            ]
        );
    }

    /**
     * Creates a new DeviceType model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     *
     * @return mixed
     */
    public function actionCreate()
    {
        if (!Yii::$app->user->can(User::PERMISSION_ADMIN)) {
            return $this->redirect('/site/index');
        }

        $model = new DeviceType();
        $searchModel = new DeviceSearchType();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->pagination->pageSize = 10;

        if (Yii::$app->request->isPost) {
            $model->load(Yii::$app->request->post());
            if ($model->save()) {
                return $this->redirect(['view', 'id' => $model->_id]);
            }
        }
        return $this->render(
            'create',
            [
                'model' => $model,
                'dataProvider' => $dataProvider
            ]
        );
    }

    /**
     * Updates an existing DeviceType model.
     * If update is successful, the browser will be redirected to the 'view' page.
     *
     * @param integer $id Id
     *
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function actionUpdate($id)
    {
        if (!Yii::$app->user->can(User::PERMISSION_ADMIN)) {
            return $this->redirect('/site/index');
        }

        $model = $this->findModel($id);
        if ($model == null) {
            return $this->redirect(['/site/index']);
        } else if (in_array($model->uuid, self::$hardUuid)) {
            return $this->redirect(['view', 'id' => $model->_id]);
        }

        if (Yii::$app->request->isPost) {
            // $model->load(Yii::$app->request->post()) && $model->save()
            $model->load(Yii::$app->request->post());
            $model->save();
            return $this->redirect(['view', 'id' => $model->_id]);
        } else {
            return $this->render(
                'update',
                [
                    'model' => $model
                ]
            );
        }
    }

    /**
     * Deletes an existing DeviceType model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     *
     * @param integer $id Id
     *
     * @return mixed
     * @throws NotFoundHttpException
     * @throws Throwable
     * @throws StaleObjectException
     */
    public function actionDelete($id)
    {
        if (!Yii::$app->user->can(User::PERMISSION_ADMIN)) {
            return $this->redirect('/site/index');
        }

        $model = $this->findModel($id);
        if ($model == null) {
            return $this->redirect(['/site/index']);
        } else if (in_array($model->uuid, self::$hardUuid)) {
            return $this->redirect(['view', 'id' => $model->_id]);
        }

        $model->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the DeviceType model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     *
     * @param integer $id Id
     *
     * @return DeviceType the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = DeviceType::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
