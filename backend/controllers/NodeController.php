<?php

namespace backend\controllers;

use backend\models\NodeSearch;
use common\models\Camera;
use common\models\Device;
use common\models\DeviceRegister;
use common\models\DeviceStatus;
use common\models\DeviceType;
use common\models\House;
use common\models\Measure;
use common\models\MeasureType;
use common\models\Message;
use common\models\Node;
use common\models\Objects;
use common\models\Photo;
use common\models\SensorChannel;
use common\models\Street;
use common\models\User;
use Yii;
use yii\base\InvalidConfigException;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\Html;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

/**
 * NodeController implements the CRUD actions for Node model.
 */
class NodeController extends Controller
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
     * Lists all Node models.
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

            $model = Node::find()
                ->where(['_id' => $_POST['editableKey']])
                ->one();
            if ($_POST['editableAttribute'] == 'address') {
                $model['address'] = $_POST['Node'][$_POST['editableIndex']]['address'];
            }
            if ($_POST['editableAttribute'] == 'objectUuid') {
                $model['objectUuid'] = $_POST['Node'][$_POST['editableIndex']]['objectUuid'];
            }
            if ($_POST['editableAttribute'] == 'nodeUuid') {
                $model['nodeUuid'] = $_POST['Node'][$_POST['editableIndex']]['nodeUuid'];
            }
            if ($_POST['editableAttribute'] == 'software') {
                $model['software'] = $_POST['Node'][$_POST['editableIndex']]['software'];
            }
            if ($_POST['editableAttribute'] == 'deviceStatusUuid') {
                $model['deviceStatusUuid'] = $_POST['Node'][$_POST['editableIndex']]['deviceStatusUuid'];
            }
            if ($_POST['editableAttribute'] == 'phone') {
                $model['phone'] = $_POST['Node'][$_POST['editableIndex']]['phone'];
            }

            $model->save();
            return json_encode('');
        }

        $searchModel = new NodeSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->pagination->pageSize = 15;
        return $this->render(
            'index',
            [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
            ]
        );
    }

    /**
     * Build tree of device by user
     *
     * @return mixed
     * @throws InvalidConfigException
     */
    public function actionStatus()
    {
        $searchModel = new NodeSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->pagination->pageSize = 50;
        return $this->render(
            'status',
            [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
            ]
        );
    }

    /**
     * Displays a single Node model.
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
                'model' => $this->findModel($id),
            ]
        );
    }

    /**
     * Creates a new Node model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     *
     * @return mixed
     */
    public function actionCreate()
    {
        if (!Yii::$app->user->can(User::PERMISSION_ADMIN)) {
            return $this->redirect('/site/index');
        }

        $model = new Node();
        if ($model->load(Yii::$app->request->post())) {
            // проверяем все поля, если что-то не так показываем форму с ошибками
            if (!$model->validate()) {
                echo json_encode($model->errors);
                return $this->render('create', ['model' => $model]);
            }
            // сохраняем запись
            if ($model->save(false)) {
                return $this->redirect(['view', 'id' => $model->_id]);
            }
            echo json_encode($model->errors);
        }
        return $this->render('create', ['model' => $model]);
    }

    /**
     * Creates a new Node models.
     *
     * @return mixed
     */
    public function actionNew()
    {
        if (!Yii::$app->user->can(User::PERMISSION_ADMIN)) {
            return $this->redirect('index');
        }

        $equipments = array();
        /*        $equipment_count = 0;
                $objects = Objects::find()
                    ->select('*')
                    ->all();*/
        return $this->render('new', ['equipments' => $equipments]);
    }

    /**
     * Dashboard
     *
     * @param $uuid
     * @param $type
     * @return string
     * @throws InvalidConfigException
     */
    public function actionDashboard($uuid, $type)
    {
        if (isset($_POST['on'])) {
            $device = Device::find()->where(['uuid' => $_POST['device']])->one();
            if ($device)
                DeviceController::contactor($_POST['on'], $device);
        }

        $node = Node::find()
            ->where(['uuid' => $uuid])
            ->one();
        $camera = null;
        $energy = null;
        $coordinator = null;
        $parameters = [];

        if ($node) {
            if (isset($_POST['reset'])) {
                DeviceController::resetCoordinator($node);
            }

            $camera = Camera::find()->where(['nodeUuid' => $node['uuid']])->one();
            if ($camera) {
                $camera->startTranslation();
            }
            $parameters['control']['signal'] = $node['security'];

            $energy = Device::find()
                ->where(['nodeUuid' => $node['uuid']])
                ->andWhere(['deviceTypeUuid' => DeviceType::DEVICE_ELECTRO])
                ->one();

            $coordinator = Device::find()
                ->where(['nodeUuid' => $node['uuid']])
                ->andWhere(['deviceTypeUuid' => DeviceType::DEVICE_ZB_COORDINATOR])
                ->one();
        }

        if ($energy) {
            $measures = (Measure::find()
                ->where(['type' => MeasureType::MEASURE_TYPE_CURRENT])
                ->orderBy('date DESC'))
                ->limit(100)
                ->all();
            foreach ($measures as $measure) {
                if ($measure['sensorChannel']['measureTypeUuid'] == MeasureType::VOLTAGE &&
                    $measure['sensorChannel']['deviceUuid'] == $energy['uuid']) {
                    if ($measure['parameter'] == 1)
                        $parameters['control']['u'] = $measure['value'];
                }
                if ($measure['sensorChannel']['measureTypeUuid'] == MeasureType::CURRENT &&
                    $measure['sensorChannel']['deviceUuid'] == $energy['uuid']) {
                    if ($measure['parameter'] == 1)
                        $parameters['control']['i'] = $measure['value'];
                }
                if ($measure['sensorChannel']['measureTypeUuid'] == MeasureType::POWER &&
                    $measure['sensorChannel']['deviceUuid'] == $energy['uuid']) {
                    if ($measure['parameter'] == 0)
                        $parameters['control']['w'] = $measure['value'];
                }
            }
        }

        if ($coordinator) {
            $measure = (Measure::find()
                ->where(['sensor_channel.measureTypeUuid' => MeasureType::COORD_IN1])
                ->joinWith('sensorChannel')
                ->orderBy('date DESC'))
                ->one();
            if ($measure['sensorChannel']['measureTypeUuid'] == MeasureType::COORD_IN1 &&
                $measure['sensorChannel']['deviceUuid'] == $coordinator['uuid']) {
                $parameters['control']['door'] = $measure['value'];
            }

            $measure = (Measure::find()
                ->where(['sensor_channel.measureTypeUuid' => MeasureType::COORD_IN2])
                ->joinWith('sensorChannel')
                ->orderBy('date DESC'))
                ->one();
            if ($measure['sensorChannel']['measureTypeUuid'] == MeasureType::COORD_IN2 &&
                $measure['sensorChannel']['deviceUuid'] == $coordinator['uuid']) {
                $parameters['control']['contactor'] = $measure['value'];
            }
            $measure = (Measure::find()
                ->where(['sensor_channel.measureTypeUuid' => MeasureType::COORD_DIGI1])
                ->joinWith('sensorChannel')
                ->orderBy('date DESC'))
                ->one();
            if ($measure['sensorChannel']['measureTypeUuid'] == MeasureType::COORD_DIGI1 &&
                $measure['sensorChannel']['deviceUuid'] == $coordinator['uuid']) {
                $parameters['control']['relay'] = $measure['value'];
            }
        }

        $parameters['u1'] = Measure::getLastMeasureNodeByType(MeasureType::VOLTAGE, $node['uuid'],
            MeasureType::MEASURE_TYPE_CURRENT, 1);
        $parameters['u2'] = Measure::getLastMeasureNodeByType(MeasureType::VOLTAGE, $node['uuid'],
            MeasureType::MEASURE_TYPE_CURRENT, 2);
        $parameters['u3'] = Measure::getLastMeasureNodeByType(MeasureType::VOLTAGE, $node['uuid'],
            MeasureType::MEASURE_TYPE_CURRENT, 3);
        if (!$parameters['u1']) $parameters['u1'] = '-';
        else $parameters['u1'] = $parameters['u1']['value'];
        if (!$parameters['u2']) $parameters['u2'] = '-';
        else $parameters['u2'] = $parameters['u2']['value'];
        if (!$parameters['u3']) $parameters['u3'] = '-';
        else $parameters['u3'] = $parameters['u3']['value'];

        $parameters['voltage'] = "<span style='color: darkgreen'>" . $parameters['u1'] . "," . $parameters['u2'] . "," . $parameters['u3'] . "</span>";

        $parameters['i1'] = Measure::getLastMeasureNodeByType(MeasureType::CURRENT, $node['uuid'],
            MeasureType::MEASURE_TYPE_CURRENT, 1);
        $parameters['i2'] = Measure::getLastMeasureNodeByType(MeasureType::CURRENT, $node['uuid'],
            MeasureType::MEASURE_TYPE_CURRENT, 2);
        $parameters['i3'] = Measure::getLastMeasureNodeByType(MeasureType::CURRENT, $node['uuid'],
            MeasureType::MEASURE_TYPE_CURRENT, 3);
        if (!$parameters['i1']) $parameters['i1'] = '-';
        else $parameters['i1'] = $parameters['i1']['value'];
        if (!$parameters['i2']) $parameters['i2'] = '-';
        else $parameters['i2'] = $parameters['i2']['value'];
        if (!$parameters['i3']) $parameters['i3'] = '-';
        else $parameters['i3'] = $parameters['i3']['value'];

        $parameters['current'] = "<span style='color: darkgreen'>" . $parameters['i1'] . "," . $parameters['i2'] . "," . $parameters['i3'] . "</span>";

        $parameters['w'] = Measure::getLastMeasureNodeByType(MeasureType::POWER, $node['uuid'],
            MeasureType::MEASURE_TYPE_CURRENT, 0);
        if (!$parameters['w']) $parameters['w'] = '-';
        else $parameters['w'] = $parameters['w']['value'];

        $parameters['power'] = "<span style='color: darkgreen'>" . $parameters['w'] . "</span>";

        $w = Measure::getLastMeasureNodeByType(MeasureType::POWER, $node['uuid'],
            MeasureType::MEASURE_TYPE_TOTAL_CURRENT, 0);
        if (!$w) $w = '-';
        else $w = $w['value'];
        $parameters['total'] = "<span style='color: darkgreen'>" . $w . "</span>";

        return $this->render(
            'dashboard',
            [
                'node' => $node,
                'coordinator' => $coordinator,
                'camera' => $camera,
                'type' => $type,
                'parameters' => $parameters
            ]
        );
    }

    /**
     * Updates an existing Node model.
     * If update is successful, the browser will be redirected to the 'view' page.
     *
     * @param integer $id Id
     *
     * @return mixed
     * @throws NotFoundHttpException
     */
    public
    function actionUpdate($id)
    {
        if (!Yii::$app->user->can(User::PERMISSION_ADMIN)) {
            return $this->redirect('/site/index');
        }

        $model = $this->findModel($id);
        if ($model->load(Yii::$app->request->post())) {
            if ($model->save()) {
                return $this->redirect(['view', 'id' => $model->_id]);
            } else {
                return $this->render(
                    'update',
                    [
                        'model' => $model,
                    ]
                );
            }
        } else {
            return $this->render(
                'update',
                [
                    'model' => $model,
                ]
            );
        }
    }

    /**
     * Build tree of equipment
     *
     * @return mixed
     * @throws InvalidConfigException
     */
    public
    function actionTree()
    {
        $c = 'children';
        $fullTree = array();
        $types = DeviceType::find()
            ->select('*')
            ->orderBy('title')
            ->all();
        $oCnt0 = 0;
        foreach ($types as $type) {
            $fullTree[$oCnt0]['title'] = Html::a(
                $type['title'],
                ['equipment-type/view', 'id' => $type['_id']]
            );
            $equipments = Node::find()
                ->select('*')
                ->where(['equipmentTypeUuid' => $type['uuid']])
                ->andWhere(['deleted' => 0])
                ->orderBy('serial')
                ->all();
            $oCnt1 = 0;
            foreach ($equipments as $equipment) {
                $fullTree[$oCnt0][$c][$oCnt1]['title']
                    = Html::a(
                    'ул.' . $equipment['house']['street']['title'] . ', д.' . $equipment['house']['number'] . ', кв.' . $equipment['flat']['number'],
                    ['equipment/view', 'id' => $equipment['_id']]
                );
                if ($equipment['equipmentStatusUuid'] == DeviceStatus::NOT_MOUNTED) {
                    $class = 'critical1';
                } elseif ($equipment['equipmentStatusUuid'] == DeviceStatus::NOT_WORK) {
                    $class = 'critical2';
                } else {
                    $class = 'critical3';
                }
                $fullTree[$oCnt0][$c][$oCnt1]['status'] = '<div class="progress"><div class="'
                    . $class . '">' . $equipment['equipmentStatus']->title . '</div></div>';
                $fullTree[$oCnt0][$c][$oCnt1]['date'] = $equipment['testDate'];
                $fullTree[$oCnt0][$c][$oCnt1]['serial'] = $equipment['serial'];

                $measure = Measure::find()
                    ->select('*')
                    ->where(['equipmentUuid' => $equipment['uuid']])
                    ->orderBy('date DESC')
                    ->one();
                if ($measure) {
                    $fullTree[$oCnt0][$c][$oCnt1]['measure_date'] = $measure['date'];
                    $fullTree[$oCnt0][$c][$oCnt1]['measure_value'] = $measure['value'];
                    $fullTree[$oCnt0][$c][$oCnt1]['measure_user'] = $measure['user']->name;
                } else {
                    $fullTree[$oCnt0][$c][$oCnt1]['measure_date'] = $equipment['changedAt'];
                    $fullTree[$oCnt0][$c][$oCnt1]['measure_value'] = "не снимались";
                    $fullTree[$oCnt0][$c][$oCnt1]['measure_user'] = "-";
                }

                $photo = Photo::find()
                    ->select('*')
                    ->where(['objectUuid' => $equipment['uuid']])
                    ->orderBy('createdAt DESC')
                    ->one();
                if ($photo) {
                    $fullTree[$oCnt0][$c][$oCnt1]['photo_date'] = $photo['createdAt'];
                    $fullTree[$oCnt0][$c][$oCnt1]['photo'] = Html::a(
                        '<img width="100px" src="/storage/equipment/' . $photo['uuid'] . '.jpg" />',
                        ['storage/equipment/' . $photo['uuid'] . '.jpg']
                    );
                    $fullTree[$oCnt0][$c][$oCnt1]['photo_user'] = $photo['user']->name;
                } else {
                    $fullTree[$oCnt0][$c][$oCnt1]['photo_date'] = 'нет фото';
                    $fullTree[$oCnt0][$c][$oCnt1]['photo'] = '-';
                    $fullTree[$oCnt0][$c][$oCnt1]['photo_user'] = '-';
                }
                $oCnt1++;
            }
            $oCnt0++;
        }
        return $this->render(
            'tree',
            ['equipment' => $fullTree]
        );
    }


    /**
     * Build tree of equipment by user
     *
     * @return mixed
     * @throws InvalidConfigException
     */
//    public function actionTreeStreet()
//    {
//        ini_set('memory_limit', '-1');
//        $c = 'children';
//        $fullTree = array();
//        $streets = Street::find()
//            ->select('*')
//            ->orderBy('title')
//            ->all();
//        $oCnt0 = 0;
//        foreach ($streets as $street) {
//            $last_user = '';
//            $last_date = '';
//            $house_count = 0;
//            $house_visited = 0;
//            $photo_count = 0;
//            $fullTree[$oCnt0]['title'] = Html::a(
//                $street['title'],
//                ['street/view', 'id' => $street['_id']]
//            );
//            $oCnt1 = 0;
//            $houses = House::find()->select('uuid,number')->where(['streetUuid' => $street['uuid']])->
//            orderBy('number')->all();
//            foreach ($houses as $house) {
//                $user_house = UserHouse::find()->select('_id')->where(['houseUuid' => $house['uuid']])->one();
//                $user = Users::find()->where(['uuid' =>
//                    UserHouse::find()->where(['houseUuid' => $house['uuid']])->one()
//                ])->one();
//                $flats = Objects::find()->select('uuid,number')->where(['houseUuid' => $house['uuid']])->all();
//                foreach ($flats as $flat) {
//                    $house_count++;
//                    $visited = 0;
//                    $equipments = Node::find()->where(['flatUuid' => $flat['uuid']])->all();
//                    foreach ($equipments as $equipment) {
//                        $fullTree[$oCnt0][$c][$oCnt1]['title']
//                            = Html::a(
//                            'ул.' . $equipment['house']['street']['title'] . ', д.' . $equipment['house']['number'] . ', кв.' . $equipment['flat']['number'],
//                            ['equipment/view', 'id' => $equipment['_id']]
//                        );
//
//                        if ($user != null)
//                            $fullTree[$oCnt0][$c][$oCnt1]['user'] = Html::a(
//                                $user['name'],
//                                ['user-house/delete', 'id' => $user_house['_id']], ['target' => '_blank']
//                            );
//
//                        if ($equipment['equipmentStatusUuid'] == DeviceStatus::NOT_MOUNTED) {
//                            $class = 'critical1';
//                        } elseif ($equipment['equipmentStatusUuid'] == DeviceStatus::NOT_WORK) {
//                            $class = 'critical2';
//                        } elseif ($equipment['equipmentStatusUuid'] == DeviceStatus::UNKNOWN) {
//                            $class = 'critical4';
//                        } else {
//                            $class = 'critical3';
//                        }
//                        $fullTree[$oCnt0][$c][$oCnt1]['status'] = '<div class="progress"><div class="'
//                            . $class . '">' . $equipment['equipmentStatus']->title . '</div></div>';
//                        $fullTree[$oCnt0][$c][$oCnt1]['date'] = $equipment['testDate'];
//                        //$fullTree[$oCnt0][$c][$oCnt1]['serial'] = $equipment['serial'];
//
//                        $measure = Measure::find()
//                            ->select('*')
//                            ->where(['equipmentUuid' => $equipment['uuid']])
//                            ->orderBy('date DESC')
//                            ->one();
//                        if ($measure) {
//                            $fullTree[$oCnt0][$c][$oCnt1]['measure_date'] = $measure['date'];
//                            $fullTree[$oCnt0][$c][$oCnt1]['measure_value'] = $measure['value'];
//                            $fullTree[$oCnt0][$c][$oCnt1]['measure_user'] = $measure['user']->name;
//                            $last_user = $measure['user']->name;
//                            $last_date = $measure['date'];
//                            $house_visited++;
//                            $visited++;
//                        } else {
//                            $fullTree[$oCnt0][$c][$oCnt1]['measure_date'] = $equipment['changedAt'];
//                            $fullTree[$oCnt0][$c][$oCnt1]['measure_value'] = "не снимались";
//                            $fullTree[$oCnt0][$c][$oCnt1]['measure_user'] = "-";
//                        }
//
//                        $message = Message::find()
//                            ->select('*')
//                            ->orderBy('date DESC')
//                            ->where(['flatUuid' => $equipment['flat']['uuid']])
//                            ->one();
//                        if ($message != null) {
//                            $fullTree[$oCnt0][$c][$oCnt1]['message'] =
//                                mb_convert_encoding(substr($message['message'], 0, 150), 'UTF-8', 'UTF-8');
//                            if ($visited == 0)
//                                $visited = 1;
//                            $house_visited++;
//                        }
//
//                        $photo = Photo::find()
//                            ->select('*')
//                            ->where(['objectuid' => $equipment['uuid']])
//                            ->orderBy('createdAt DESC')
//                            ->one();
//                        if ($photo) {
//                            $fullTree[$oCnt0][$c][$oCnt1]['photo_date'] = $photo['createdAt'];
//                            $fullTree[$oCnt0][$c][$oCnt1]['photo'] = Html::a('фото',
//                                ['storage/equipment/' . $photo['uuid'] . '.jpg']
//                            );
//                            $fullTree[$oCnt0][$c][$oCnt1]['photo_user'] = $photo['user']->name;
//                            $last_user = $photo['user']->name;
//                            $photo_count++;
//                            if ($visited == 0) {
//                                $visited = 1;
//                                $house_visited++;
//                            }
//                        } else {
//                            $fullTree[$oCnt0][$c][$oCnt1]['photo_date'] = 'нет фото';
//                            $fullTree[$oCnt0][$c][$oCnt1]['photo'] = '-';
//                            $fullTree[$oCnt0][$c][$oCnt1]['photo_user'] = '-';
//                        }
//                        $oCnt1++;
//                    }
//                }
//            }
//            $fullTree[$oCnt0]['measure_user'] = $last_user;
//            $fullTree[$oCnt0]['measure_date'] = $last_date;
//            $fullTree[$oCnt0]['photo_user'] = $last_user;
//            $fullTree[$oCnt0]['photo_date'] = $last_date;
//            $fullTree[$oCnt0]['photo'] = $photo_count;
//            $ok = 0;
//            if ($house_count > 0)
//                $ok = $house_visited * 100 / $house_count;
//            if ($ok > 100) $ok = 100;
//            if ($ok < 20) {
//                $fullTree[$oCnt0]['status'] = '<div class="progress"><div class="critical1">' .
//                    number_format($ok, 2) . '%</div></div>';
//            } elseif ($ok < 45) {
//                $fullTree[$oCnt0]['status'] = '<div class="progress"><div class="critical2">' .
//                    number_format($ok, 2) . '%</div></div>';
//            } elseif ($ok < 70) {
//                $fullTree[$oCnt0]['status'] = '<div class="progress"><div class="critical4">' .
//                    number_format($ok, 2) . '%</div></div>';
//            } else {
//                $fullTree[$oCnt0]['status'] = '<div class="progress"><div class="critical3">' .
//                    number_format($ok, 2) . '%</div></div>';
//            }
//            $oCnt0++;
//        }
//        return $this->render(
//            'tree-street',
//            ['equipment' => $fullTree]
//        );
//    }

    /**
     * Build tree of equipment by user
     *
     * @return mixed
     * @throws InvalidConfigException
     */
    public
    function actionTreeMeasure()
    {
        ini_set('memory_limit', '-1');
        $fullTree = array();
        $streets = Street::find()
            ->select('*')
            ->orderBy('title')
            ->all();
        $oCnt0 = 0;
        foreach ($streets as $street) {
            $house_count = 0;
            $house_visited = 0;
            $houses = House::find()->select('uuid,number')->where(['streetUuid' => $street['uuid']])->
            orderBy('number')->all();
            foreach ($houses as $house) {
                $objects = Objects::find()->select('uuid,number')->where(['objectUuid' => $house['uuid']])->all();
                foreach ($objects as $object) {
                    $house_count++;
                    $visited = 0;
                    $equipments = Node::find()->where(['objectUuid' => $object['uuid']])->all();
                    foreach ($equipments as $equipment) {
                        $fullTree[$oCnt0]['title']
                            = Html::a(
                            'ул.' . $equipment['house']['street']['title'] . ', д.' . $equipment['house']['number'] . ', кв.' . $equipment['object']['number'],
                            ['equipment/view', 'id' => $equipment['_id']]
                        );

                        $measures = Measure::find()
                            ->select('*')
                            ->where(['equipmentUuid' => $equipment['uuid']])
                            ->orderBy('date DESC')
                            ->all();

                        $measure_count_column = 0;
                        $fullTree[$oCnt0]['measure_date0'] = '';
                        $fullTree[$oCnt0]['measure_value0'] = '';
                        $fullTree[$oCnt0]['measure_date1'] = '';
                        $fullTree[$oCnt0]['measure_value1'] = '';
                        $fullTree[$oCnt0]['measure_date2'] = '';
                        $fullTree[$oCnt0]['measure_value2'] = '';
                        $fullTree[$oCnt0]['measure_date3'] = '';
                        $fullTree[$oCnt0]['measure_value3'] = '';
                        $fullTree[$oCnt0]['measure_user'] = '';
                        $measure_first = 0;
                        $measure_last = 0;
                        $measure_date_first = 0;
                        $measure_date_last = 0;
                        foreach ($measures as $measure) {
                            $fullTree[$oCnt0]['measure_date' . $measure_count_column] = $measure['date'];
                            $fullTree[$oCnt0]['measure_value' . $measure_count_column] = $measure['value'];
                            $fullTree[$oCnt0]['measure_user'] = $measure['user']->name;
                            if ($measure_count_column == 0) {
                                $measure_first = $measure['value'];
                                $measure_date_first = $measure['date'];
                            } else {
                                $measure_last = $measure['value'];
                                $measure_date_last = $measure['date'];
                            }
                            $measure_count_column++;
                            if ($measure_count_column > 3) break;
                        }

                        $datetime1 = date_create($measure_date_first);
                        $datetime2 = date_create($measure_date_last);
                        if ($datetime2 && $datetime1) {
                            $diff = $datetime2->diff($datetime1);
                            $interval = $diff->format("%h") + ($diff->days * 24);
                            $value = number_format($measure_last - $measure_first, 2);
                        } else {
                            $interval = 0;
                            $value = 0;
                        }
                        $fullTree[$oCnt0]['interval'] = $interval;
                        $fullTree[$oCnt0]['value'] = $value;
                        if ($interval > 0)
                            $fullTree[$oCnt0]['relative'] = number_format($value / $interval, 2);

                        $message = Message::find()
                            ->select('*')
                            ->orderBy('date DESC')
                            ->where(['flatUuid' => $equipment['flat']['uuid']])
                            ->one();
                        if ($message != null) {
                            $fullTree[$oCnt0]['message'] =
                                mb_convert_encoding(substr($message['message'], 0, 150), 'UTF-8', 'UTF-8');
                            if ($visited == 0)
                                $visited = 1;
                            $house_visited++;
                        }
                        $oCnt0++;
                    }
                }
            }
        }
        return $this->render(
            'tree-measure',
            ['equipment' => $fullTree]
        );
    }

    /**
     * Deletes an existing Node model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     *
     * @param integer $id Id
     *
     * @return mixed
     * @throws InvalidConfigException
     */
    public
    function actionDelete($id)
    {
        if (!Yii::$app->user->can(User::PERMISSION_ADMIN)) {
            return $this->redirect('/site/index');
        }

        $node = Node::find()->where(['_id' => $id])->one();
        if ($node) {
            $node['deleted'] = true;
            $node->save();
        }
        return $this->redirect(['index']);
    }

    /**
     * Finds the Node model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     *
     * @param integer $id Id
     *
     * @return Node the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected
    function findModel($id)
    {
        if (($model = Node::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    /**
     * @param $uuid
     * @return string
     * @throws InvalidConfigException
     */
    public
    function actionTrends($uuid)
    {
        $node = Node::find()
            ->where(['uuid' => $uuid])
            ->one();

        $sensorChannelPowerUuid = 0;
        $sensorChannelVoltageUuid = 0;
        $sensorChannelCurrentUuid = 0;
        $sensorChannelFrequencyUuid = 0;
        $deviceElectro = Device::find()
            ->where(['nodeUuid' => $node['uuid']])
            ->andWhere(['deleted' => 0])
            ->andWhere(['deviceTypeUuid' => DeviceType::DEVICE_ELECTRO])
            ->one();
        if ($deviceElectro) {
            $sensorChannel1 = SensorChannel::find()->where(['deviceUuid' => $deviceElectro['uuid']])
                ->andWhere(['measureTypeUuid' => MeasureType::POWER])->one();
            if ($sensorChannel1)
                $sensorChannelPowerUuid = $sensorChannel1['uuid'];
            $sensorChannel2 = SensorChannel::find()->where(['deviceUuid' => $deviceElectro['uuid']])
                ->andWhere(['measureTypeUuid' => MeasureType::VOLTAGE])->one();
            if ($sensorChannel2)
                $sensorChannelVoltageUuid = $sensorChannel2['uuid'];
            $sensorChannel3 = SensorChannel::find()->where(['deviceUuid' => $deviceElectro['uuid']])
                ->andWhere(['measureTypeUuid' => MeasureType::CURRENT])->one();
            if ($sensorChannel3)
                $sensorChannelCurrentUuid = $sensorChannel3['uuid'];
            $sensorChannel4 = SensorChannel::find()->where(['deviceUuid' => $deviceElectro['uuid']])
                ->andWhere(['measureTypeUuid' => MeasureType::FREQUENCY])->one();
            if ($sensorChannel4)
                $sensorChannelFrequencyUuid = $sensorChannel4['uuid'];
        }
        return $this->render(
            'trends',
            [
                'sensorChannelPowerUuid' => $sensorChannelPowerUuid,
                'sensorChannelVoltageUuid' => $sensorChannelVoltageUuid,
                'sensorChannelCurrentUuid' => $sensorChannelCurrentUuid,
                'sensorChannelFrequencyUuid' => $sensorChannelFrequencyUuid
            ]
        );
    }

    /**
     * @param $uuid
     * @return string
     * @throws InvalidConfigException
     */
    public
    function actionRegister($uuid)
    {
        $deviceRegisters = DeviceRegister::find()
            ->where(['deviceUuid' => (Node::find()->where(['uuid' => $uuid])->one())]);
        $provider = new ActiveDataProvider(
            [
                'query' => $deviceRegisters,
                'sort' => false,
            ]
        );
        return $this->render(
            'register',
            [
                'provider' => $provider
            ]
        );
    }
}
