<?php

namespace backend\controllers;

use backend\models\MeasureSearchType;
use common\models\MeasureType;
use common\models\User;
use Yii;
use yii\db\StaleObjectException as StaleObjectExceptionAlias;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use Throwable;

/**
 * MeasureTypeController implements the CRUD actions for MeasureType model.
 */
class MeasureTypeController extends Controller
{
    const POWER = '7BDB38C7-EF93-49D4-8FE3-89F2A2AEDB48';
    const TEMPERATURE = '54051538-38F7-44A3-A9B5-C8B5CD4A2936';
    const VOLTAGE = '29A52371-E9EC-4D1F-8BCB-80F489A96DD3';
    const FREQUENCY = '041DED21-D211-4C0B-BCD6-02E392654332';
    const CURRENT = 'E38C561F-9E88-407E-A465-83803A625627';
    const STATUS = 'E45EA488-DB97-4D38-9067-6B4E29B965F8';

    private static $hardUuid = [
        self::POWER,
        self::TEMPERATURE,
        self::VOLTAGE,
        self::FREQUENCY,
        self::CURRENT,
        self::STATUS,
    ];

    /**
     * @inheritdoc
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
     * Lists all MeasureType models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new MeasureSearchType();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->pagination->pageSize = 20;

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single MeasureType model.
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
     * Creates a new ModelType model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        if (!Yii::$app->user->can(User::PERMISSION_ADMIN)) {
            return $this->redirect('/site/index');
        }

        $model = new MeasureType();
        $searchModel = new MeasureSearchType();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->pagination->pageSize = 20;

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->_id]);
        } else {
            return $this->render('create', [
                'model' => $model,
                'dataProvider' => $dataProvider
            ]);
        }
    }

    /**
     * Updates an existing MeasureType model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
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

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->_id]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing MeasureType model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException
     * @throws Throwable
     * @throws StaleObjectExceptionAlias
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
            return $this->redirect(['/site/index']);
        }

        $model->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the MeasureType model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return MeasureType the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = MeasureType::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
