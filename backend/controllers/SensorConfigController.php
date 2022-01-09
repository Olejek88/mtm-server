<?php

namespace backend\controllers;

use backend\models\SensorConfigSearch;
use common\models\MeasureType;
use common\models\SensorConfig;
use common\models\User;
use Throwable;
use Yii;
use yii\base\InvalidConfigException;
use yii\db\StaleObjectException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

/**
 * SensorConfigController implements the CRUD actions for SensorConfig model.
 */
class SensorConfigController extends Controller
{
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
     * Lists all SensorConfig models.
     *
     * @return mixed
     * @throws InvalidConfigException
     */
    public function actionIndex()
    {
        if (isset($_POST['editableAttribute'])) {
            if (!Yii::$app->user->can(User::PERMISSION_ADMIN)) {
                return json_encode('Нет прав.');
            }

            $model = SensorConfig::find()
                ->where(['_id' => $_POST['editableKey']])
                ->one();
            if ($model->save())
                return json_encode('success');
            return json_encode('failed');
        }

        $searchModel = new SensorConfigSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->pagination->pageSize = 50;
        return $this->render('table', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single SensorConfig model.
     *
     * @param integer $id Id
     *
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        $form = 'view';
        if ($model != null && in_array($model->sensorChannel->measureTypeUuid, [MeasureType::DOOR_STATE, MeasureType::CONTACTOR_STATE])) {
            $value = json_decode($model->config, true);
            $model->threshold = $value['threshold'];
            $form = 'view_in';
        }

        return $this->render(
            $form,
            [
                'model' => $model,
            ]
        );
    }

    /**
     * Creates a new SensorConfig model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     *
     * @return mixed
     */
    public function actionCreate()
    {
        if (!Yii::$app->user->can(User::PERMISSION_ADMIN)) {
            return $this->redirect('/site/index');
        }

        $form = '_form';
        $model = new SensorConfig();
        $searchModel = new SensorConfigSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->pagination->pageSize = 50;

        $sensorChannelUuid = Yii::$app->request->get('sc');
        if ($sensorChannelUuid != null) {
            $model->sensorChannelUuid = $sensorChannelUuid;
            if (in_array($model->sensorChannel->measureTypeUuid, [MeasureType::DOOR_STATE, MeasureType::CONTACTOR_STATE])) {
                $model->threshold = 1024;
                $form = '_form_in';
            }
        }

        if ($model->load(Yii::$app->request->post())) {
            // проверяем все поля, если что-то не так показываем форму с ошибками
            if (!$model->validate()) {
                return $this->render('create',
                    [
                        'model' => $model,
                        'dataProvider' => $dataProvider,
                        'form' => $form,
                    ]
                );
            }

            if (in_array($model->sensorChannel->measureTypeUuid, [MeasureType::DOOR_STATE, MeasureType::CONTACTOR_STATE])) {
                $model->config = json_encode(['threshold' => $model->config]);
                $form = '_form_in';
            }

            // сохраняем запись
            if ($model->save(false)) {
                return $this->redirect(['view', 'id' => $model->_id]);
            } else {
                return $this->render('create',
                    [
                        'model' => $model,
                        'dataProvider' => $dataProvider,
                        'form' => $form,
                    ]
                );
            }
        } else {
            return $this->render('create', [
                    'model' => $model,
                    'dataProvider' => $dataProvider,
                    'form' => $form,
                ]
            );
        }
    }

    /**
     * Updates an existing SensorConfig model.
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

        $form = '_form';
        $model = $this->findModel($id);
        if ($model->load(Yii::$app->request->post())) {
            if (in_array($model->sensorChannel->measureTypeUuid, [MeasureType::DOOR_STATE, MeasureType::CONTACTOR_STATE])) {
                $model->config = json_encode(['threshold' => $model->threshold]);
                $form = '_form_in';
            }

            // сохраняем модель
            if ($model->save()) {
                return $this->redirect(['view', 'id' => $model->_id]);
            } else {
                return $this->render(
                    'update',
                    [
                        'model' => $model,
                        'form' => $form,
                    ]
                );
            }
        } else {
            if (in_array($model->sensorChannel->measureTypeUuid, [MeasureType::DOOR_STATE, MeasureType::CONTACTOR_STATE])) {
                $value = json_decode($model->config, true);
                $model->threshold = isset($value['threshold']) ? $value['threshold'] : 1024;
                $form = '_form_in';
            }

            return $this->render(
                'update',
                [
                    'model' => $model,
                    'form' => $form,
                ]
            );
        }
    }

    /**
     * Deletes an existing SensorConfig model.
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

        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the SensorConfig model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     *
     * @param integer $id Id
     *
     * @return SensorConfig the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = SensorConfig::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
