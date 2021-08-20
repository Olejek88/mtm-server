<?php

namespace backend\controllers;

use common\components\MainFunctions;
use common\models\DeviceConfig;
use common\models\DeviceProgram;
use common\models\Group;
use common\models\GroupControl;
use common\models\Node;
use common\models\NodeControl;
use common\models\User;
use Throwable;
use Yii;
use yii\base\InvalidConfigException;
use yii\data\ActiveDataProvider;
use yii\db\StaleObjectException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
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
            MainFunctions::register("Изменена программа работы: '{$model->title}'");
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
        $defProgram = "";
        $coordinates = ObjectController::getAverageCoordinates();
        if (isset($_GET["group"]))
            $group = $_GET["group"];
        else $group = 0;
        $range = 365;
        $shift = 30;

        $today = time() - 3600 * 24 * $shift;
        $today = strtotime(date('Y-m-d', $today));
        $groupControls = GroupControl::find()
            ->where(['groupUuid' => $group])
            ->where(['between', 'date', date('Y-m-d', $today),
                date('Y-m-d', $today + 86400 * ($range + $shift))])
            ->all();
        $group = Group::find()->where(['uuid' => $group])->limit(1)->one();
        if ($group && $group['deviceProgramUuid']) {
            $defProgram = $group['deviceProgram']['title'];
        }

        $groupControlArray = [];
        foreach ($groupControls as $groupControl) {
            $grpCtlTimestamp = strtotime($groupControl->date);
            $groupControlArray[date("Y-m-d", $grpCtlTimestamp)][$groupControl->type] = $groupControl;
        }

        unset($groupControls);

        for ($count = 0; $count < $range; $count++) {
//            $sunrise_time = date_sunrise($today, SUNFUNCS_RET_TIMESTAMP, $coordinates['latitude'], $coordinates['longitude']);
//            $sunset_time = date_sunset($today, SUNFUNCS_RET_TIMESTAMP, $coordinates['latitude'], $coordinates['longitude']);

            $on = 0;
            $off = 0;
            $currentDate = date("Y-m-d", $today);
            if (isset($groupControlArray[$currentDate])) {
//                if (isset($groupControlArray[$currentDate][0])) {
//                    $elem = $groupControlArray[$currentDate][0];
//                    $off = 1;
//                    $event = new Event();
//                    $event->id = $count * 2;
//                    $event->title = "выключение";
//                    $event->backgroundColor = 'orange';
//                    $event->start = $elem['date'];
//                    $event->color = '#ffffff';
//                    $events[] = $event;
//                }

                if (isset($groupControlArray[$currentDate][1])) {
                    $elem = $groupControlArray[$currentDate][1];
                    $on = 1;
                    $event = new Event();
                    $event->id = $count * 2 + 1;
                    $event->title = "включение [" . $defProgram . "]";
                    if ($elem['deviceProgramUuid'])
                        $event->title = "Программа [" . $elem['deviceProgram']['title'] . "]";
                    $event->backgroundColor = 'green';
                    $event->start = $elem['date'];
                    $event->color = '#ffffff';
                    $events[] = $event;
                }
            }

//            if ($off == 0) {
//                $event = new Event();
//                $event->id = $count * 2;
//                $event->title = "выключение";
//                $event->backgroundColor = 'orange';
//                $event->start = date("Y-m-d H:i:s", $today);
//                $event->color = '#ffffff';
//                $events[] = $event;
//            }

            if ($on == 0) {
                $event = new Event();
                $event->id = $count * 2 + 1;
                $event->title = "Программа [" . $defProgram . "]";
                $event->backgroundColor = 'green';
                $event->start = date("Y-m-d H:i:s", $today);
                $event->color = '#ffffff';
                $events[] = $event;
            }

            $today += 24 * 3600;
        }

        return $this->render('calendar', [
            'events' => $events,
            'groupTitle' => $group['title'],
        ]);
    }

    /**
     * @param string $node
     * @return string
     * @throws InvalidConfigException
     * @throws NotFoundHttpException
     */
    public function actionCalendarNode($node)
    {
        $events = [];
        if (($nodeObj = Node::find()->where(['uuid' => $node])->one()) === null) {
            throw new NotFoundHttpException('The requested page does not exist.');
        }

        $range = 365;
        $shift = 30;
        $today = time() - 3600 * 24 * $shift;

        $nodeControls = NodeControl::find()
            ->where(['nodeUuid' => $node])
            ->where(['between', 'date', date('Y-m-d', $today),
                date('Y-m-d', $today + 86400 * ($range + $shift))])
            ->all();

        $nodeControlArray = [];
        foreach ($nodeControls as $nodeControl) {
            $nodeCtlTimestamp = strtotime($nodeControl->date);
            $nodeControlArray[date("Y-m-d", $nodeCtlTimestamp)][$nodeControl->type] = $nodeControl;
        }

        unset($nodeControls);


        for ($count = 0; $count < $range; $count++) {
            $sunrise_time = date_sunrise($today, SUNFUNCS_RET_TIMESTAMP, $nodeObj->object->latitude, $nodeObj->object->longitude);
            $sunset_time = date_sunset($today, SUNFUNCS_RET_TIMESTAMP, $nodeObj->object->latitude, $nodeObj->object->longitude);

            $on = 0;
            $off = 0;
            $currentDate = date("Y-m-d", $today);
            if (isset($nodeControlArray[$currentDate])) {
                if (isset($nodeControlArray[$currentDate][0])) {
                    $elem = $nodeControlArray[$currentDate][0];
                    $off = 1;
                    $event = new Event();
                    $event->id = $count * 2;
                    $event->title = "выключение";
                    $event->backgroundColor = 'orange';
                    $event->start = $elem['date'];
                    $event->color = '#ffffff';
                    $events[] = $event;
                }

                if (isset($nodeControlArray[$currentDate][1])) {
                    $elem = $nodeControlArray[$currentDate][1];
                    $on = 1;
                    $event = new Event();
                    $event->id = $count * 2 + 1;
                    $event->title = "включение";
                    $event->backgroundColor = 'green';
                    $event->start = $elem['date'];
                    $event->color = '#ffffff';
                    $events[] = $event;
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
            'events' => $events,
            'nodeTitle' => $nodeObj->address,
        ]);
    }

    /**
     * @return string
     * @throws InvalidConfigException
     */
    public function actionCalendarAll()
    {
        if (!Yii::$app->user->can(User::PERMISSION_ADMIN)) {
            return $this->redirect('/site/index');
        }

        $nodes = Node::find()->where(['deleted' => 0])->with(['object.house.street'])->asArray()->all();
        if (count($nodes) == 1) {
            return Yii::$app->response->redirect('/device-program/calendar-node?node=' . $nodes[0]['uuid']);
        }

        return $this->render('calendar-all', [
            'nodes' => $nodes,
        ]);
    }
}
