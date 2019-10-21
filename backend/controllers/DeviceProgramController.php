<?php

namespace backend\controllers;

use common\components\MainFunctions;
use common\models\DeviceConfig;
use common\models\GroupControl;
use common\models\NodeControl;
use common\models\User;
use Yii;
use common\models\DeviceProgram;
use yii\base\InvalidConfigException;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use Throwable;
use yii\db\StaleObjectException;
use yii2fullcalendar\models\Event;

/**
 * DeviceProgramController implements the CRUD actions for DeviceProgram model.
 */
class DeviceProgramController extends Controller
{
    /**
     * {@inheritdoc}
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
     * Lists all DeviceProgram models.
     * @return mixed
     * @throws InvalidConfigException
     */
    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => DeviceProgram::find(),
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single DeviceProgram model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Finds the DeviceProgram model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return DeviceProgram the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = DeviceProgram::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    /**
     * Creates a new DeviceProgram model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new DeviceProgram();
        $model->oid = User::getOid(Yii::$app->user->identity);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->_id]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing DeviceProgram model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            MainFunctions::deviceRegister($model->uuid, "Изменена программа работы");
            return $this->redirect(['view', 'id' => $model->_id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing DeviceProgram model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     * @throws Throwable
     * @throws StaleObjectException
     */
    public function actionDelete($id)
    {

        $model = $this->findModel($id);
        $used = DeviceConfig::find()->where(['parameter' => DeviceConfig::PARAM_LIGHT_PROGRAM, 'value' => $model->title])->all();
        if (count($used) > 0) {
            Yii::$app->session->setFlash('error', '<h3>Эту программу нельзя удалить, так как она используется.</h3>');
            return $this->render('view', [
                'model' => $model,
            ]);
        }

        $model->delete();
        return $this->redirect(['index']);
    }

    /**
     * @return string
     * @throws InvalidConfigException
     */
    public function actionCalendar()
    {
        $events = [];
        $coordinates = ObjectController::getAverageCoordinates();
        $groupControls = GroupControl::find()->all();
        //$today = strtotime("2019-01-01 00:00:00");
        $today = time();
        for ($count = 0; $count < 365; $count++) {
            $sunrise_time = date_sunrise($today, SUNFUNCS_RET_TIMESTAMP, $coordinates['latitude'], $coordinates['longitude'], 90, 5) + 3600 * 5;
            $sunset_time = date_sunset($today, SUNFUNCS_RET_TIMESTAMP, $coordinates['latitude'], $coordinates['longitude'], 90, 5) + 3600 * 5;
            //$sunrise = date_sunrise($today, SUNFUNCS_RET_STRING, $sum_latitude, $sum_longitude, 90, 5);
            //$sunset = date_sunset($today, SUNFUNCS_RET_STRING, $sum_latitude, $sum_longitude, 90, 5);

            $on = 0;
            $off = 0;
            foreach ($groupControls as $groupControl) {
                $date = date("Y-m-d", strtotime($groupControl['date']));
                $currentDate = date("Y-m-d", $today);
                if ($date == $currentDate) {
                    if ($groupControl['type'] == 0) {
                        $off = 1;
                        $event = new Event();
                        $event->id = $count * 2;
                        $event->title = "выключение";
                        $event->backgroundColor = 'orange';
                        $event->start = $groupControl['date'];
                        $event->color = '#ffffff';
                        $events[] = $event;
                    }
                    if ($groupControl['type'] == 1) {
                        $on = 1;
                        $event = new Event();
                        $event->id = $count * 2 + 1;
                        $event->title = "включение";
                        if ($groupControl['deviceProgramUuid'])
                            $event->title = "включение [" . $groupControl['deviceProgram']['title'] . "]";
                        $event->backgroundColor = 'green';
                        $event->start = $groupControl['date'];
                        $event->color = '#ffffff';
                        $events[] = $event;
                    }
                }

            }

            if ($off == 0) {
                $event = new Event();
                $event->id = $count * 2;
                $event->title = "выключение";
                $event->backgroundColor = 'orange';
                $event->start = date("Y-m-d H:i:s", $sunrise_time);
                $event->color = '#ffffff';
                $events[] = $event;
            }

            if ($on == 0) {
                $event = new Event();
                $event->id = $count * 2 + 1;
                $event->title = "включение";
                $event->backgroundColor = 'green';
                $event->start = date("Y-m-d H:i:s", $sunset_time);
                $event->color = '#ffffff';
                $events[] = $event;
            }
            //echo date("Y-m-d H:i",$event->start).PHP_EOL;
            $today += 24 * 3600;
        }

        return $this->render('calendar', [
            'events' => $events
        ]);
    }

    /**
     * @return string
     * @throws InvalidConfigException
     */
    public function actionCalendarNode()
    {
        $events = [];
        $coordinates = ObjectController::getAverageCoordinates();
        $nodeControls = NodeControl::find()->all();
        $today = time();
        for ($count = 0; $count < 365; $count++) {
            $sunrise_time = date_sunrise($today, SUNFUNCS_RET_TIMESTAMP, $coordinates['latitude'], $coordinates['longitude'], 90, 5) + 3600 * 5;
            $sunset_time = date_sunset($today, SUNFUNCS_RET_TIMESTAMP, $coordinates['latitude'], $coordinates['longitude'], 90, 5) + 3600 * 5;
            //$sunrise = date_sunrise($today, SUNFUNCS_RET_STRING, $sum_latitude, $sum_longitude, 90, 5);
            //$sunset = date_sunset($today, SUNFUNCS_RET_STRING, $sum_latitude, $sum_longitude, 90, 5);

            $on = 0;
            $off = 0;
            foreach ($nodeControls as $nodeControl) {
                $date = date("Y-m-d", strtotime($nodeControl['date']));
                $currentDate = date("Y-m-d", $today);
                if ($date == $currentDate) {
                    if ($nodeControl['type'] == 0) {
                        $off = 1;
                        $event = new Event();
                        $event->id = $count * 2;
                        $event->title = "выключение";
                        $event->backgroundColor = 'orange';
                        $event->start = $nodeControl['date'];
                        $event->color = '#ffffff';
                        $events[] = $event;
                    }
                    if ($nodeControl['type'] == 1) {
                        $on = 1;
                        $event = new Event();
                        $event->id = $count * 2 + 1;
                        $event->title = "включение";
                        $event->backgroundColor = 'green';
                        $event->start = $nodeControl['date'];
                        $event->color = '#ffffff';
                        $events[] = $event;
                    }
                }

            }

            if ($off == 0) {
                $event = new Event();
                $event->id = $count * 2;
                $event->title = "выключение";
                $event->backgroundColor = 'orange';
                $event->start = date("Y-m-d H:i:s", $sunrise_time);
                $event->color = '#ffffff';
                $events[] = $event;
            }

            if ($on == 0) {
                $event = new Event();
                $event->id = $count * 2 + 1;
                $event->title = "включение";
                $event->backgroundColor = 'green';
                $event->start = date("Y-m-d H:i:s", $sunset_time);
                $event->color = '#ffffff';
                $events[] = $event;
            }
            //echo date("Y-m-d H:i",$event->start).PHP_EOL;
            $today += 24 * 3600;
        }

        return $this->render('calendar-node', [
            'events' => $events
        ]);
    }
}
