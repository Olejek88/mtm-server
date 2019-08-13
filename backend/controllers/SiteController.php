<?php

namespace backend\controllers;

use backend\models\DeviceSearch;
use backend\models\SignupForm;
use backend\models\UserSearch;
use common\components\MainFunctions;
use common\models\Camera;
use common\models\City;
use common\models\Device;
use common\models\DeviceStatus;
use common\models\DeviceType;
use common\models\House;
use common\models\Journal;
use common\models\LoginForm;
use common\models\Measure;
use common\models\MeasureType;
use common\models\Node;
use common\models\Objects;
use common\models\SensorChannel;
use common\models\SensorConfig;
use common\models\Street;
use common\models\User;
use Throwable;
use Yii;
use yii\base\InvalidConfigException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\Html;
use yii\web\Controller;

/**
 * Site controller
 *
 * @property mixed $layers
 */
class SiteController extends Controller
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
                        'actions' => ['login', 'error'],
                        'allow' => true,
                    ],
                    [
                        'actions' => ['signup', 'error'],
                        'allow' => true,
                    ],
                    [
                        'actions' => ['logout', 'index', 'dashboard', 'test', 'timeline'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * Actions
     *
     * @return array
     */
    public function actions()
    {
        $actions = parent::actions();
        unset($actions['error']);
        return $actions;
    }

    /**
     * Displays map.
     *
     * @return string
     * @throws InvalidConfigException
     */
    public function actionIndex()
    {
        /**
         * Работа с картой
         */
        $objectSelect = Objects::find()
            ->select('_id, title, latitude, longitude')
            ->asArray()
            ->all();

        $cnt = 0;
        $objectsGroup = 'var objects=L.layerGroup([';
        $objectsList = '';
        foreach ($objectSelect as $object) {
            $objectsList .= 'var object' . $object["_id"]
                . '= L.marker([' . $object["latitude"]
                . ',' . $object["longitude"] . ']).bindPopup("<b>'
                . $object["title"] . '</b>").openPopup();';
            if ($cnt > 0) {
                $objectsGroup .= ',';
            }

            $objectsGroup .= 'object' . $object["_id"];
            $cnt++;
        }


        $layers = self::getLayers();


        return $this->render(
            'index',
            [
                'devicesList' => $layers['devicesList'],
                'devicesGroup' => $layers['devicesGroup'],
                'camerasList' => $layers['camerasList'],
                'camerasGroup' => $layers['camerasGroup'],
                'polylineList' => $layers['polylineList'],
                'nodesList' => $layers['nodesList'],
                'nodesGroup' => $layers['nodesGroup'],
                'coordinates' => $layers['coordinates']
            ]
        );
    }

    /**
     * Dashboard
     *
     * @return string
     * @throws InvalidConfigException
     */
    public function actionDashboard()
    {
        $searchModel = new UserSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->pagination->pageSize = 15;

        $accountUser = Yii::$app->user->identity;
        $currentUser = User::find()
            ->where(['_id' => $accountUser['id']])
            ->asArray()
            ->one();

        $counts['city'] = City::find()->count();
        $counts['street'] = Street::find()->count();
        $counts['objects'] = Objects::find()->count();
        $counts['device'] = Device::find()->count();
        $counts['elektro'] = Device::find()->where(['deviceTypeUuid' => DeviceType::DEVICE_COUNTER])->count();
        $counts['light'] = Device::find()->where(['deviceTypeUuid' => DeviceType::DEVICE_LIGHT])->count();
        $counts['channel'] = SensorChannel::find()->count();
        $counts['node'] = Node::find()->count();
        $counts['deviceType'] = DeviceType::find()->count();

        $last_measures = Measure::find()
            ->where('createdAt > (NOW()-(4*24*3600000))')
            ->count();
        $complete = 0;

//        $measures = Measure::find()
//            ->orderBy('date')
//            ->all();

        $users = User::find()
            ->where(['oid' => User::getOid(Yii::$app->user->identity)])
            ->all();

        /**
         * Работа с картой
         */
        $layers = self::getLayers();

        ini_set('memory_limit', '-1');
        $fullTree = array();
        $streets = Street::find()
            ->select('*')
            ->orderBy('title')
            ->all();
        foreach ($streets as $street) {
            $fullTree['children'][] = [
                'title' => $street['title'],
                'folder' => true,
                'expanded' => true
            ];
            $houses = House::find()->where(['streetUuid' => $street['uuid']])->
            orderBy('number')->all();
            foreach ($houses as $house) {
                $childIdx = count($fullTree['children']) - 1;
                $fullTree['children'][$childIdx]['children'][] = [
                    'title' => $house->getFullTitle(),
                    'folder' => true,
                    'expanded' => true
                ];
                $objects = Objects::find()->where(['houseUuid' => $house['uuid']])->all();
                foreach ($objects as $object) {
                    $childIdx2 = count($fullTree['children'][$childIdx]['children']) - 1;
                    $fullTree['children'][$childIdx]['children'][$childIdx2]['children'][] = [
                        'title' => $object['objectType']['title'] . ' ' . $object['title'],
                        'folder' => true,
                        'expanded' => true
                    ];
                    $nodes = Node::find()->where(['objectUuid' => $object['uuid']])->all();
                    foreach ($nodes as $node) {
                        $childIdx3 = count($fullTree['children'][$childIdx]['children'][$childIdx2]['children']) - 1;
                        if ($node['deviceStatusUuid'] == DeviceStatus::NOT_MOUNTED) {
                            $class = 'critical1';
                        } elseif ($node['deviceStatusUuid'] == DeviceStatus::NOT_WORK) {
                            $class = 'critical2';
                        } else {
                            $class = 'critical3';
                        }
                        $fullTree['children'][$childIdx]['children'][$childIdx2]['children'][$childIdx3]['children'][] = [
                            'status' => '<div class="progress"><div class="' . $class . '">' . $node['deviceStatus']->title . '</div></div>',
                            'title' => 'Контроллер [' . $node['address'] . ']',
                            'register' => $node['address'],
                            'expanded' => true,
                            'folder' => true
                        ];
                        $devices = Device::find()->where(['nodeUuid' => $node['uuid']])->all();
                        if (isset($_GET['type']))
                            $devices = Device::find()->where(['nodeUuid' => $node['uuid']])
                                ->andWhere(['deviceTypeUuid' => $_GET['type']])
                                ->all();
                        foreach ($devices as $device) {
                            $childIdx4 = count($fullTree['children'][$childIdx]['children'][$childIdx2]['children'][$childIdx3]['children']) - 1;
                            if ($device['deviceStatusUuid'] == DeviceStatus::NOT_MOUNTED) {
                                $class = 'critical1';
                            } elseif ($device['deviceStatusUuid'] == DeviceStatus::NOT_WORK) {
                                $class = 'critical2';
                            } else {
                                $class = 'critical3';
                            }
                            $fullTree['children'][$childIdx]['children'][$childIdx2]['children'][$childIdx3]['children'][$childIdx4]['children'][] = [
                                'title' => $device['deviceType']['title'],
                                'status' => '<div class="progress"><div class="'
                                    . $class . '">' . $device['deviceStatus']->title . '</div></div>',
                                'register' => $device['port'] . ' [' . $device['address'] . ']',
                                'measure' => '',
                                'date' => $device['date'],
                                'folder' => true
                            ];
                            $channels = SensorChannel::find()->where(['deviceUuid' => $device['uuid']])->all();
                            foreach ($channels as $channel) {
                                $childIdx5 = count($fullTree['children'][$childIdx]['children'][$childIdx2]['children'][$childIdx3]['children'][$childIdx4]['children']) - 1;
                                $measure = Measure::find()->where(['sensorChannelUuid' => $channel['uuid']])->one();
                                $date = '-';
                                if (!$measure) {
                                    $config = null;
                                    $config = SensorConfig::find()->where(['sensorChannelUuid' => $channel['uuid']])->one();
                                    if ($config) {
                                        $measure = Html::a('конфигурация', ['sensor-config/view', 'id' => $config['_id']]);
                                        $date = $config['changedAt'];
                                    }
                                } else {
                                    $date = $measure['date'];
                                    $measure = $measure['value'];
                                }
                                $fullTree['children'][$childIdx]['children'][$childIdx2]['children'][$childIdx3]['children'][$childIdx4]['children'][$childIdx5]['children'][] = [
                                    'title' => $channel['title'],
                                    'register' => $channel['register'],
                                    'value' => $measure,
                                    'date' => $date,
                                    'folder' => false
                                ];
                            }
                        }
                    }
                }
            }
        }
        $devices = Device::find()->all();
        $cameras = Camera::find()->all();

        foreach ($cameras as $camera) {
            $camera->startTranslation();
        }

        $searchModel = new DeviceSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->pagination->pageSize = 15;

        return $this->render(
            'dashboard',
            [
                'counts' => $counts,
//                'measures' => $measures,
                'devices' => $devices,
                'cameras' => $cameras,
                'users' => $users,
                'tree' => $fullTree,
                'devicesList' => $layers['devicesList'],
                'devicesGroup' => $layers['devicesGroup'],
                'camerasList' => $layers['camerasList'],
                'camerasGroup' => $layers['camerasGroup'],
                'nodesList' => $layers['nodesList'],
                'nodesGroup' => $layers['nodesGroup'],
                'last_measures' => $last_measures,
                'complete' => $complete,
                'currentUser' => $currentUser,
                'searchModel' => $searchModel,
                'coordinates' => $layers['coordinates'],
                'dataProvider' => $dataProvider
            ]
        );
    }

    /**
     * Login action.
     *
     * @return string
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        } else {
            $model->password = '';
            return $this->render('login', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Action error
     *
     * @return string
     */
    public function actionError()
    {
        if (Yii::$app->getUser()->isGuest) {
            Yii::$app->getResponse()->redirect("/")->send();
        } else {
            $exception = Yii::$app->errorHandler->exception;
            if ($exception !== null) {
                $statusCode = $exception->statusCode;
                $name = $exception->getName();
                $message = $exception->getMessage();
                return $this->render(
                    'error',
                    [
                        'exception' => $exception,
                        'name' => $name . " " . $statusCode,
                        'message' => $message
                    ]
                );
            }
        }

        return '';
    }

    /**
     * Logout action.
     *
     * @return string
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * Формируем код записи о событии
     * @param $date
     * @param $type
     * @param $id
     * @param $title
     * @param $text
     * @param $user
     *
     * @return string
     */
    public static function formEvent($date, $type, $id, $title, $text, $user)
    {
        $event = '<li>';
        if ($type == 'measure')
            $event .= '<i class="fa fa-wrench bg-red"></i>';
        if ($type == 'alarm')
            $event .= '<i class="fa fa-calendar bg-aqua"></i>';

        $event .= '<div class="timeline-item">';
        $event .= '<span class="time"><i class="fa fa-clock-o"></i> ' . date("M j, Y h:m", strtotime($date)) . '</span>';
        if ($type == 'measure')
            $event .= '<span class="timeline-header" style="vertical-align: middle">
                        <span class="btn btn-primary btn-xs">' . $user . '</span>&nbsp;' .
                Html::a('Снято показание &nbsp;',
                    ['/measure/view', 'id' => Html::encode($id)]) . $title . '</span>';

        if ($type == 'alarm')
            $event .= '&nbsp;<span class="btn btn-primary btn-xs">' . $user . '</span>&nbsp;
                    <span class="timeline-header" style="vertical-align: middle">' .
                Html::a('Зафиксировано событие &nbsp;',
                    ['/alarm/view', 'id' => Html::encode($id)]) . $title . '</span>';

        $event .= '<div class="timeline-body">' . $text . '</div>';
        $event .= '</div></li>';
        return $event;
    }

    /**
     * Displays a timeline
     *
     * @return mixed
     */
    public function actionTimeline()
    {
        $events = [];
        $journals = Journal::find()
            ->orderBy('date DESC')
            ->limit(10)
            ->all();
        foreach ($journals as $journal) {
            $text = '<i class="fa fa-calendar"></i>&nbsp;' . $journal['description'];
            $events[] = ['date' => $journal['date'], 'event' => self::formEvent($journal['date'], 'journal', 0,
                $journal['description'], $text, $journal['user']->name)];
        }

        $sort_events = MainFunctions::array_msort($events, ['date' => SORT_DESC]);
        $today = date("j-m-Y h:m");

        return $this->render(
            'timeline',
            [
                'events' => $sort_events,
                'today_date' => $today
            ]
        );
    }

    /**
     * @return mixed
     * @throws InvalidConfigException
     */
    public function getLayers()
    {
        $devices = Device::find()->all();

        $cnt = 0;
        $default_coordinates = "[55.54,61.36]";
        $coordinates = $default_coordinates;
        $polylineList = '';
        $equipmentsGroup = 'var devices=L.layerGroup([';
        $equipmentsList = '';
        foreach ($devices as $device) {
            if ($device["object"]["latitude"] > 0) {
                if ($device['deviceTypeUuid'] == DeviceType::DEVICE_LIGHT) {
                    $link = '<b>' . Html::a($device["deviceType"]["title"],
                            ['/device/dashboard', 'uuid' => $device['uuid'], 'type' => 'light']) . '</span>'
                        . '</b>';
                } else {
                    $link = '<b>' . Html::a($device["deviceType"]["title"],
                            ['/node/dashboard', 'uuid' => $device['node']['uuid'], 'type' => 'device']) . '</span>'
                        . '</b>';
                }
                $equipmentsList .= 'var device'
                    . $device["_id"]
                    . '= L.marker([' . $device["object"]["latitude"]
                    . ',' . $device["object"]["longitude"]
                    . '], {icon: lightIcon}).bindPopup(\'' .
                    $link . '<br/>'
                    . $device["object"]->getAddress() . '\').openPopup();';
                $coordinates = "[" . $device["object"]["latitude"] . "," . $device["object"]["longitude"] . "]";
                if ($coordinates == $default_coordinates && $device["object"]["latitude"] > 0) {
                    $coordinates = "[" . $device["object"]["latitude"] . "," . $device["object"]["longitude"] . "]";
                }
                if ($cnt > 0) {
                    $equipmentsGroup .= ',';
                }

                $equipmentsGroup .= 'device' . $device["_id"];
                $cnt++;
            }
        }
        $equipmentsGroup .= ']);' . PHP_EOL;

        $cameras = Camera::find()->all();
        $cnt = 0;
        $camerasGroup = 'var cameras=L.layerGroup([';
        $camerasList = '';
        foreach ($cameras as $camera) {
            if ($camera["object"]["latitude"] > 0) {
                $camerasList .= 'var camera'
                    . $camera["_id"]
                    . '= L.marker([' . $camera["object"]["latitude"]
                    . ',' . $camera["object"]["longitude"]
                    . '], {icon: cameraIcon}).bindPopup(\'<b>'
                    . Html::a($camera["title"],
                        ['/camera/dashboard', 'uuid' => $camera['uuid']]) . '</span>'
                    . '</b><br/>'
                    . $camera["object"]->getAddress() . '\').openPopup();';
                $coordinates = "[" . $camera["object"]["latitude"] . "," . $camera["object"]["longitude"] . "]";
                if ($coordinates == $default_coordinates && $camera["object"]["latitude"] > 0) {
                    $coordinates = "[" . $camera["object"]["latitude"] . "," . $camera["object"]["longitude"] . "]";
                }
                if ($cnt > 0) {
                    $camerasGroup .= ',';
                }

                $camerasGroup .= 'camera' . $camera["_id"];
                $cnt++;
            }
        }
        $camerasGroup .= ']);' . PHP_EOL;

        $nodes = Node::find()->all();
        $cnt = 0;
        $nodesGroup = 'var nodes=L.layerGroup([';
        $nodesList = '';
        foreach ($nodes as $node) {
            if ($node["object"]["latitude"] > 0) {
                $link = "<span class=\'badge\' style=\'background-color: green; height: 18px; padding:3px; margin-top: -2px\'>есть</span>";
                $security = "<span class=\'badge\' style=\'background-color: green; height: 18px; padding:3px; margin-top: -2px\'>в норме</span>";
                $power = "<span class=\'badge\' style=\'background-color: green; height: 18px; padding:3px; margin-top: -2px\'>в норме</span>";
                $temperature = "<span class=\'badge\' style=\'background-color: green; height: 18px; padding:3px; margin-top: -2px\'>28.82(C)</span>";
                $contactors = "-";
                $u = "-";
                $w = "-";
                $w_total = "-";
                $device = Device::find()->where(['deviceTypeUuid' => DeviceType::DEVICE_ELECTRO])
                    ->andWhere(['nodeUuid' => $node['uuid']])->one();
                //echo json_encode($device['uuid']);
                if ($device) {
                    $channels = SensorChannel::find()->where(['deviceUuid' => $device['uuid']])->all();
                    foreach ($channels as $channel) {
                        //echo json_encode($channel['uuid']);
                        $measure = Measure::find()->where(['sensorChannelUuid' => $channel['uuid']])
                            ->andWhere(['type' => MeasureType::MEASURE_TYPE_CURRENT])
                            ->orderBy('date DESC')
                            ->one();
                        if ($measure) {
                            if ($channel['measureTypeUuid'] == MeasureType::POWER)
                                $w = $measure['value'];
                            if ($channel['measureTypeUuid'] == MeasureType::VOLTAGE)
                                $u = $measure['value'];
                            if ($channel['measureTypeUuid'] == MeasureType::CURRENT)
                                $w_total = $measure['value'];
                        }
                    }
                }

                $coords[] = [$node['object']['latitude'], $node['object']['longitude']];
                $devices = Device::find()
                    ->where(['deviceTypeUuid' => DeviceType::DEVICE_LIGHT])
                    ->andWhere(['nodeUuid' => $node['uuid']])
                    ->all();
                foreach ($devices as $device) {
                    $coords[] = [$device['object']['latitude'], $device['object']['longitude']];
                }
                if (count($coords)) {
                    $polylineList .= 'L.polyline(' . json_encode($coords) . ', {weight: 3, color: \'green\'}).addTo(map);';
                    //$polylineList .= 'var polylinePoints' . $cnt . ' = ' . json_encode($coords) . ';';
                    //$polylineList .= 'var polyline' . $cnt . ' = L.polyline(polylinePoints' . $cnt . ').addTo(map);';
                }

                $software = $node["software"];
                $phone = $node["address"];
                $nodesList .= 'var node'
                    . $node["_id"]
                    . '= L.marker([' . $node["object"]["latitude"]
                    . ',' . $node["object"]["longitude"]
                    . '], {icon: nodeIcon}).bindPopup(\'<b>'
                    . Html::a($node["object"]->getAddress(),
                        ['/node/dashboard', 'uuid' => $node['uuid'], 'type' => 'node']) . '</span>'
                    . '</b><br/>'
                    . 'Связь: ' . $link . '<br/>'
                    . 'Охрана: ' . $security . '<br/>'
                    . 'Питание: ' . $power . '<br/>'
                    . 'Контакторы: ' . $contactors . '<br/>'
                    . 'Температура: ' . $temperature . '<br/>'
                    . 'Напряжение,В: ' . $u . '<br/>'
                    . 'Мощность,кВт/ч: ' . $w . '<br/>'
                    . 'Сумма,кВт: ' . $w_total . '<br/>'
                    . 'Версия ПО: ' . $software . '<br/>'
                    . 'Телефон/адрес: ' . $phone . '<br/>\').openPopup();';
                $coordinates = "[" . $node["object"]["latitude"] . "," . $node["object"]["longitude"] . "]";
                if ($coordinates == $default_coordinates && $node["object"]["latitude"] > 0) {
                    $coordinates = "[" . $node["object"]["latitude"] . "," . $node["object"]["longitude"] . "]";
                }
                if ($cnt > 0) {
                    $nodesGroup .= ',';
                }
                $nodesGroup .= 'node' . $node["_id"];
                $cnt++;
            }
        }
        $nodesGroup .= ']);' . PHP_EOL;

        $layer['coordinates'] = $coordinates;
        $layer['nodesList'] = $nodesList;
        $layer['nodesGroup'] = $nodesGroup;
        $layer['devicesList'] = $equipmentsList;
        $layer['devicesGroup'] = $equipmentsGroup;
        $layer['camerasList'] = $camerasList;
        $layer['camerasGroup'] = $camerasGroup;
        $layer['polylineList'] = $polylineList;

        return $layer;
    }

    /**
     * Signup action.
     *
     * @return string
     * @throws Throwable
     */
    public function actionSignup()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new SignupForm();
        if ($model->load(Yii::$app->request->post()) && $model->signup()) {
            Yii::$app->user->login(User::findByUsername($model->username), true ? 3600 * 24 * 30 : 0);
            return $this->goBack();
        } else {
            $model->password = '';
            return $this->render('signup', [
                'model' => $model,
            ]);
        }
    }
}
