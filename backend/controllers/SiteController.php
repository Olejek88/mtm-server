<?php

namespace backend\controllers;

use backend\models\DeviceRegisterSearch;
use backend\models\DeviceSearch;
use backend\models\SignupForm;
use backend\models\UserSearch;
use common\components\MainFunctions;
use common\models\Area;
use common\models\Camera;
use common\models\City;
use common\models\Device;
use common\models\DeviceConfig;
use common\models\DeviceGroup;
use common\models\DeviceStatus;
use common\models\DeviceType;
use common\models\Group;
use common\models\GroupControl;
use common\models\House;
use common\models\HouseType;
use common\models\Journal;
use common\models\LoginForm;
use common\models\Measure;
use common\models\MeasureType;
use common\models\mtm\MtmDevLightConfig;
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
use yii2fullcalendar\models\Event;

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
                'lightList' => $layers['lightList'],
                'lightGoodList' => $layers['lightGoodList'],
                'lightBadList' => $layers['lightBadList'],
                'sensorCO2List' => $layers['sensorCO2List'],
                'lightGroup' => $layers['lightGroup'],
                'lightGoodGroup' => $layers['lightGoodGroup'],
                'lightBadGroup' => $layers['lightBadGroup'],
                'sensorCO2Group' => $layers['sensorCO2Group'],
                'camerasList' => $layers['camerasList'],
                'camerasGroup' => $layers['camerasGroup'],
                'polylineList' => $layers['polylineList'],
                'nodesList' => $layers['nodesList'],
                'nodesGroup' => $layers['nodesGroup'],
                'coordinates' => $layers['coordinates'],
                'define' => $layers['define'],
                'postCode' => $layers['postCode']
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
        $counts['street'] = Street::find()->where(['deleted' => 0])->asArray()->count();
        $counts['objects'] = Objects::find()->where(['deleted' => 0])->asArray()->count();
        $counts['device'] = Device::find()->where(['deleted' => 0])->asArray()->count();
        $counts['elektro'] = Device::find()->where(['deviceTypeUuid' => DeviceType::DEVICE_COUNTER])
            ->andWhere(['deleted' => 0])
            ->asArray()
            ->count();
        $counts['light'] = Device::find()->where(['deviceTypeUuid' => DeviceType::DEVICE_LIGHT])
            ->andWhere(['deleted' => 0])
            ->asArray()
            ->count();
        $counts['sensors'] = SensorChannel::find()->where(['measureTypeUuid' => MeasureType::SENSOR_CO2])->asArray()->count();
        $counts['node'] = Node::find()->where(['deleted' => 0])->asArray()->count();
        $counts['deviceType'] = DeviceType::find()->count();

        $complete = 0;

//        $measures = Measure::find()
//            ->orderBy('date')
//            ->all();

        $users = User::find()
            ->where(['oid' => User::getOid(Yii::$app->user->identity)])
            ->asArray()
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
            ->asArray()
            ->all();
        foreach ($streets as $street) {
            $fullTree['children'][] = [
                'title' => $street['title'],
                'folder' => true,
                'expanded' => true
            ];
            $houses = House::find()
                ->where(['streetUuid' => $street['uuid']])
                ->andWhere(['deleted' => 0])
                ->orderBy('number')
                ->with(['street'])
                ->asArray()
                ->all();
            foreach ($houses as $house) {
                if ($house['houseTypeUuid'] != HouseType::HOUSE_TYPE_NO_NUMBER) {
                    $fullTitle = 'ул.' . $house['street']['title'] . ', д.' . $house['number'];
                } else {
                    $fullTitle = 'ул.' . $house['street']['title'] . ' - без адреса';
                }

                $childIdx = count($fullTree['children']) - 1;
                $fullTree['children'][$childIdx]['children'][] = [
                    'title' => $fullTitle,
                    'folder' => true,
                    'expanded' => true
                ];
                $objects = Objects::find()
                    ->where(['houseUuid' => $house['uuid']])
                    ->andWhere(['deleted' => 0])
                    ->with(['objectType'])
                    ->asArray()
                    ->all();
                foreach ($objects as $object) {
                    $childIdx2 = count($fullTree['children'][$childIdx]['children']) - 1;
                    $fullTree['children'][$childIdx]['children'][$childIdx2]['children'][] = [
                        'title' => $object['objectType']['title'] . ' ' . $object['title'],
                        'folder' => true,
                        'expanded' => true
                    ];
                    $nodes = Node::find()
                        ->where(['objectUuid' => $object['uuid']])
                        ->andWhere(['deleted' => 0])
                        ->with(['deviceStatus'])
                        ->asArray()
                        ->all();
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
                            'status' => '<div class="progress"><div class="' . $class . '">' . $node['deviceStatus']['title'] . '</div></div>',
                            'title' => 'Контроллер [' . $node['address'] . ']',
                            'register' => $node['address'],
                            'expanded' => true,
                            'folder' => true
                        ];
                        $devices = Device::find()->where(['nodeUuid' => $node['uuid']])->all();
                        if (isset($_GET['type']))
                            $devices = Device::find()
                                ->where(['nodeUuid' => $node['uuid']])
                                ->andWhere(['deviceTypeUuid' => $_GET['type']])
                                ->andWhere(['deleted' => 0])
                                ->asArray()
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
                            $channels = SensorChannel::find()->where(['deviceUuid' => $device['uuid']])
                                ->asArray()
                                ->all();
                            foreach ($channels as $channel) {
                                $childIdx5 = count($fullTree['children'][$childIdx]['children'][$childIdx2]['children'][$childIdx3]['children'][$childIdx4]['children']) - 1;
                                $measure = Measure::find()->where(['sensorChannelUuid' => $channel['uuid']])
                                    ->asArray()
                                    ->limit(1)
                                    ->one();
                                $date = '-';
                                if (!$measure) {
                                    $config = null;
                                    $config = SensorConfig::find()->where(['sensorChannelUuid' => $channel['uuid']])
                                        ->asArray()
                                        ->limit(1)
                                        ->one();
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

        $devices = Device::find()->asArray();
        $cameras = Camera::find()->all();
        $sensorsCO = SensorChannel::find()->where(['measureTypeUuid' => MeasureType::SENSOR_CO2])->asArray();

        $registerSearch = new DeviceRegisterSearch();
        $dataProviderRegister = $registerSearch->search(Yii::$app->request->queryParams);

        foreach ($cameras as $camera) {
            $camera->startTranslation();
        }

        $searchModel = new DeviceSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
//        $dataProvider->pagination->pageSize = 15;

        $events = [];
        $program = "";
        $coordinates = ObjectController::getAverageCoordinates();
        if (isset($_GET["group"]))
            $group = $_GET["group"];
        else $group = 0;
        $groupControls = GroupControl::find()
            ->where(['groupUuid' => $group])
            ->asArray()
            ->all();
        $group = Group::find()
            ->where(['uuid' => $group])
            ->with(['deviceProgram'])
            ->asArray()
            ->limit(1)
            ->one();
        if ($group && $group['deviceProgramUuid'])
            $program = $group['deviceProgram']['title'];
        //$today = strtotime("2019-01-01 00:00:00");
        $today = time() - 3600 * 24 * 30;
        for ($count = 0; $count < 365; $count++) {
            $sunrise_time = date_sunrise($today, SUNFUNCS_RET_TIMESTAMP, $coordinates['latitude'], $coordinates['longitude']);
            $sunset_time = date_sunset($today, SUNFUNCS_RET_TIMESTAMP, $coordinates['latitude'], $coordinates['longitude']);

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
                        $event->title = "включение [" . $program . "]";
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
                $event->title = "включение [" . $program . "]";
                $event->backgroundColor = 'green';
                $event->start = date("Y-m-d H:i:s", $sunset_time);
                $event->color = '#ffffff';
                $events[] = $event;
            }
            $today += 24 * 3600;
        }
        $reportDataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $reportDataProvider->query->andWhere(['deviceTypeUuid' => DeviceType::DEVICE_ELECTRO]);

        $sensorsCOData = $sensorsCO->asArray()->limit(1)->one();
        /** @var SensorChannel $sensorsCOData */
        /** @var Measure $measure */
        $measureTitle = 'Нет датчиков экологии';
        $measureChart['values'] = '';
        $measureChart['dates'] = '';
        if ($sensorsCOData) {
            $measureTitle = $sensorsCOData['title'];
            $measures = Measure::find()
                ->where(['sensorChannelUuid' => $sensorsCOData['uuid']])
                ->orderBy('date DESC')
                ->asArray()
                ->limit(10)
                ->all();
            $count = 0;
            foreach ($measures as $measure) {
                if ($count > 0) {
                    $measureChart['values'] .= ',';
                    $measureChart['dates'] .= ',';
                }
                $measureChart['values'] .= $measure['value'];
                $measureChart['dates'] .= '\'' . $measure['date'] . '\'';
                $count++;
            }
        }
        if (Yii::$app->user->can(User::PERMISSION_ADMIN))
            $view = 'dashboard-admin';
        else
            $view = 'dashboard';

        return $this->render(
            $view,
            [
                'counts' => $counts,
                'events' => $events,
                'devices' => $devices,
                'cameras' => $cameras,
                'measureChart' => $measureChart,
                'measureTitle' => $measureTitle,
                'users' => $users,
                'tree' => $fullTree,
                'reportDataProvider' => $reportDataProvider,
                'dataProviderRegister' => $dataProviderRegister,
                'lightList' => $layers['lightList'],
                'lightGoodList' => $layers['lightGoodList'],
                'lightBadList' => $layers['lightBadList'],
                'sensorCO2List' => $layers['sensorCO2List'],
                'lightGroup' => $layers['lightGroup'],
                'sensorCO' => $sensorsCO,
                'lightGoodGroup' => $layers['lightGoodGroup'],
                'lightBadGroup' => $layers['lightBadGroup'],
                'sensorCO2Group' => $layers['sensorCO2Group'],
                'camerasList' => $layers['camerasList'],
                'camerasGroup' => $layers['camerasGroup'],
                'nodesList' => $layers['nodesList'],
                'nodesGroup' => $layers['nodesGroup'],
                'complete' => $complete,
                'currentUser' => $currentUser,
                'searchModel' => $searchModel,
                'coordinates' => $layers['coordinates'],
                'dataProvider' => $dataProvider,
                'define' => $layers['define'],
                'postCode' => $layers['postCode']
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
     * @throws InvalidConfigException
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
        $areaUuid = Yii::$app->request->getQueryParam('area');
        $nodeUuids = null;
        if (!empty($areaUuid)) {
            $area = Area::find()->where(['uuid' => $areaUuid])->asArray()->with('areaNodes')->limit(1)->one();
            $areaNodes = $area['areaNodes'];
            $nodeUuids = [];
            foreach ($areaNodes as $areaNode) {
                $nodeUuids[] = $areaNode['nodeUuid'];
            }
        }

        $devQuery = Device::find()
            ->where(['NOT IN', 'deviceTypeUuid', [DeviceType::DEVICE_COUNTER, DeviceType::DEVICE_ZB_COORDINATOR]])
            ->andWhere(['deleted' => 0]);
        if (!empty($nodeUuids)) {
            $devQuery->andWhere(['nodeUuid' => $nodeUuids]);
        }

        $devices = $devQuery->all();

        $cnt = 0;
        $cntGood = 0;
        $cntBad = 0;
        $cntCO2 = 0;

        $default_coordinates = "[55.54,61.36]";
        $coordinates = $default_coordinates;
        $polylineList = '';

        $lightGroup = 'var lights=L.layerGroup([';
        $lightGoodGroup = 'var lights_good=L.layerGroup([';
        $lightBadGroup = 'var lights_bad=L.layerGroup([';
        $sensorCO2Group = 'var sensor1=L.layerGroup([';

        $lightList = '';
        $lightGoodList = '';
        $lightBadList = '';
        $sensorCO2List = '';

        $nominal_power = '-';
        $group = '-';
        $t = '-';
        $define = '';
        $current_power = '-';
        $rssi = '-';

        foreach ($devices as $device) {
            if ($device["object"]["latitude"] > 0) {
                $warnings = '';
                $dimming = '-';
                if ($device['deviceTypeUuid'] == DeviceType::DEVICE_LIGHT) {
                    $current_power = '-';
                    $rssi = '-';
                    $hops = '-';
                    $link = '<b>' . Html::a($device['name'],
                            ['/device/dashboard', 'uuid' => $device['uuid'], 'type' => 'light']) . '</span>'
                        . '</b>';
                    // реальные группы
                    $deviceGroup = DeviceGroup::find()->where(['deviceUuid' => $device['uuid']])
                        ->with(['group'])
                        ->limit(1)
                        ->asArray()
                        ->one();
                    if ($deviceGroup) {
                        $group = $deviceGroup['group']['title'];
                    }

                    // не реальные группы, на самом деле идентификаторы программ по которым работают светильники
                    $group = DeviceController::getParameter($device['uuid'], DeviceConfig::PARAM_GROUP);
                    if ($group == null) {
                        $group = '-';
                    }

                    $nominal_power = DeviceController::getParameter($device['uuid'], DeviceConfig::PARAM_POWER);
                    $nominal_power = MtmDevLightConfig::getPowerString($nominal_power);
                    if ($device['deviceTypeUuid'] == DeviceType::DEVICE_LIGHT) {
                        /** @var SensorChannel $sChannel */
                        $sChannel = $device->getSensorChannel(MeasureType::POWER)->limit(1)->one();
                        if ($sChannel != null) {
                            /** @var Measure $cMeasure */
                            $cMeasure = $sChannel->getMeasureOne()->limit(1)->one();
                            if ($cMeasure != null) {
                                $current_power = $cMeasure->value;
                            }
                        }

                        $sChannel = $device->getSensorChannel(MeasureType::RSSI)->limit(1)->one();
                        if ($sChannel != null) {
                            /** @var Measure $cMeasure */
                            $cMeasure = $sChannel->getMeasureOne()->limit(1)->one();
                            if ($cMeasure != null) {
                                $rssi = $cMeasure->value;
                            }
                        }

                        $sChannel = $device->getSensorChannel(MeasureType::HOP_COUNT)->limit(1)->one();
                        if ($sChannel != null) {
                            /** @var Measure $cMeasure */
                            $cMeasure = $sChannel->getMeasureOne()->limit(1)->one();
                            if ($cMeasure != null) {
                                $hops = $cMeasure->value;
                            }
                        }

                        $sChannel = $device->getSensorChannel(MeasureType::SENSOR_CO2)->limit(1)->one();
                        if ($sChannel != null) {
                            /** @var Measure $cMeasure */
                            $cMeasure = $sChannel->getMeasureOne()->limit(1)->one();
                            $sensorValue = '-';
                            if ($cMeasure != null) {
                                $sensorValue = $cMeasure->value;
                            }

                            $dev = 'var sensor'
                                . $sChannel["_id"]
                                . '= L.marker([' . $device["object"]["latitude"]
                                . ',' . $device["object"]["longitude"]
                                . '], {icon: sensorCO2Icon}).bindPopup(\''
                                . $sChannel["title"] . '</span>'
                                . '<br/>'
                                . 'Значение: ' . $sensorValue . '<br/>'
                                . '\').openPopup();';
                            $sensorCO2List .= $dev;
                            if ($cntCO2 > 0) $sensorCO2Group .= ',';
                            $cntCO2++;
                            $sensorCO2Group .= 'sensor' . $sChannel["_id"];
                        }
                        $rssi = $rssi . ' / ' . $hops;
                    }

                    $dimming = DeviceController::getParameter($device['uuid'], DeviceConfig::PARAM_SET_VALUE);
                    $measure = Measure::find()
                        ->where(['sensorChannelUuid' => (SensorChannel::find()
                            ->select('uuid')
                            ->where(['measureTypeUuid' => MeasureType::TEMPERATURE])
                            ->andWhere(['deviceUuid' => $device['uuid']]))])
                        ->asArray()
                        ->limit(1)
                        ->one();
                    if ($measure) {
                        $t = $measure['value'];
                    }

                    $coordinator = Device::find()
                        ->where(['nodeUuid' => $device['node']['uuid']])
                        ->andWhere(['deleted' => 0])
                        ->andWhere(['deviceTypeUuid' => DeviceType::DEVICE_ZB_COORDINATOR])
                        ->asArray()
                        ->limit(1)
                        ->one();
                    if ($coordinator) {
                        $measure = (Measure::find()
                            ->with('sensorChannel')
                            ->leftJoin('sensor_channel',
                                'sensor_channel.oid=:oid and sensor_channel.measureTypeUuid=:mtype and measure.sensorChannelUuid=sensor_channel.uuid',
                                [
                                    ':mtype' => MeasureType::COORD_IN2,
                                    ':oid' => User::getOid(Yii::$app->user->identity),
                                ])
                            ->orderBy('date DESC'))
                            ->asArray()
                            ->limit(1)
                            ->one();
                        if ($measure && $measure['sensorChannel'] &&
                            $measure['sensorChannel']['measureTypeUuid'] == MeasureType::COORD_IN2 &&
                            $measure['sensorChannel']['deviceUuid'] == $coordinator['uuid']) {
                            $contactor = $measure['value'];
                            if ($contactor) {
                                $dimming = "-";
                            } else {
                                $dimming = DeviceController::getParameter($device['uuid'], DeviceConfig::PARAM_SET_VALUE);
                            }
                        }
                    }
                } else if ($device['deviceTypeUuid'] == DeviceType::DEVICE_LIGHT_WITHOUT_ZB) {
                    $link = '<b>' . $device['name'] . '</b>';
                    // реальные группы
                    $deviceGroup = DeviceGroup::find()->where(['deviceUuid' => $device['uuid']])
                        ->asArray()
                        ->limit(1)
                        ->one();
                    if ($deviceGroup) {
                        $group = $deviceGroup['group']['title'];
                    }
                } else {
                    $link = '<b>' . Html::a($device['name'],
                            ['/node/dashboard', 'uuid' => $device['node']['uuid'], 'type' => 'device']) . '</span>'
                        . '</b>';
                }

                if ($device['deviceStatusUuid'] == DeviceStatus::WORK) {
                    $icon = 'lightIcon';
                } else {
                    $icon = 'lightIconBad';
                    $t = "-";
                    $warnings = 'Статус: <span class="badge badge-green-red">' . $device['deviceStatus']['title'] . '</span>';
                }
                $define .= 'var deviceIcon' . $device["_id"] . '=' . $icon . ';';
                $define .= 'var deviceStatus' . $device["_id"] . '=1;';

                $dev = 'var device'
                    . $device["_id"]
                    . '= L.marker([' . $device["object"]["latitude"]
                    . ',' . $device["object"]["longitude"]
                    . '], {icon: ' . $icon . '}).bindPopup(\''
                    . $link . '</span>'
                    . '<br/>'
                    . 'Уровень освещения: ' . $dimming . '<br/>'
                    . 'Мощность: ' . $nominal_power . '<br/>'
                    . 'Текущая мощность: ' . $current_power . '<br/>'
                    . 'Группа ' . $group . '<br/>'
                    . 'Температура: ' . $t . '<br/>'
                    . 'RSSI: ' . $rssi . '<br/>'
                    . $warnings
                    . '\').openPopup();';
                $lightList .= $dev;
                if ($device['deviceStatusUuid'] == DeviceStatus::WORK) {
                    $lightGoodList .= $dev;
                    if ($cntGood > 0) $lightGoodGroup .= ',';
                    $lightGoodGroup .= 'device' . $device["_id"];
                    $cntGood++;
                } else {
                    $lightBadList .= $dev;
                    if ($cntBad > 0) $lightBadGroup .= ',';
                    $lightBadGroup .= 'device' . $device["_id"];
                    $cntBad++;
                }

                $coordinates = "[" . $device["object"]["latitude"] . "," . $device["object"]["longitude"] . "]";
                if ($coordinates == $default_coordinates && $device["object"]["latitude"] > 0) {
                    $coordinates = "[" . $device["object"]["latitude"] . "," . $device["object"]["longitude"] . "]";
                }
                if ($cnt > 0) {
                    $lightGroup .= ',';
                }
                $lightGroup .= 'device' . $device["_id"];
                $cnt++;
            }
        }
        $lightGroup .= ']);' . PHP_EOL;
        $lightGoodGroup .= ']);' . PHP_EOL;
        $lightBadGroup .= ']);' . PHP_EOL;
        $sensorCO2Group .= ']);' . PHP_EOL;

        $cameras = Camera::find()->where(['deleted' => 0])->all();
        $cnt = 0;
        $camerasGroup = 'var cameras=L.layerGroup([';
        $camerasList = '';
        foreach ($cameras as $camera) {
            if ($camera["object"]["latitude"] > 0) {
                $warnings = '';
                if ($camera['deviceStatusUuid'] == DeviceStatus::WORK)
                    $icon = 'cameraIcon';
                else {
                    $icon = 'cameraIconBad';
                    $warnings = 'Статус: <span class="badge badge-green-red">' . $camera['deviceStatus']['title'] . '</span>';
                }
                $camerasList .= 'var camera'
                    . $camera["_id"]
                    . '= L.marker([' . $camera["object"]["latitude"]
                    . ',' . $camera["object"]["longitude"]
                    . '], {icon: ' . $icon . '}).bindPopup(\'<b>'
                    . Html::a($camera["title"],
                        ['/camera/dashboard', 'uuid' => $camera['uuid']]) . '</b><br/>'
                    . $camera["object"]->getAddress() . '<br/>'
                    . $warnings
                    . '\').openPopup();';
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

        $nodesQuery = Node::find()
            ->andWhere(['deleted' => 0]);
        if (!empty($nodeUuids)) {
            $nodesQuery->andWhere(['uuid' => $nodeUuids]);
        }

        $nodes = $nodesQuery
            ->with(['object.house.street'])
            ->with(['deviceStatus'])
            ->asArray()
            ->limit(1)
            ->all();

        $cnt = 0;
        $nodesGroup = 'var nodes=L.layerGroup([';
        $nodesList = '';
        foreach ($nodes as $node) {
            if ($node["object"]["latitude"] > 0) {
                $link = '<span class="badge badge-green-small">есть</span>';
                $power = '<span class="badge badge-green-small">в норме</span>';
                $warnings = '';
                if ($node['deviceStatusUuid'] == DeviceStatus::NOT_LINK)
                    $link = '<span class="badge badge-red-small">нет</span>';
                if ($node['deviceStatusUuid'] == DeviceStatus::WORK)
                    $icon = 'nodeIcon';
                else {
                    $icon = 'nodeIconBad';
                    $warnings = 'Статус узла: <span class="badge badge-red-small">' .
                        $node['deviceStatus']['title'] . '</span>';
                }

                $contactors = "-";
                $security = '-';
                $temperature = '-';
                $coordinator = Device::find()
                    ->where(['nodeUuid' => $node['uuid'], 'deviceTypeUuid' => DeviceType::DEVICE_ZB_COORDINATOR])->one();
                if ($coordinator != null) {
                    /** @var @var SensorChannel $sChannel */
                    // состояние контактора
                    $sChannel = $coordinator->getSensorChannel(MeasureType::CONTACTOR_STATE)
                        ->limit(1)
                        ->one();
                    if ($sChannel != null) {
                        $value = $sChannel->getMeasureOne()
                            ->asArray()
                            ->limit(1)
                            ->one();
                        if ($value != null) {
                            $contactors = $value['value'] == 0 ? 'Включен' : 'Выключен';
                            $contState = $value['value'] == 0 ? 'badge-green-small' : 'badge-red-small';
                            $contactors = '<span class="badge ' . $contState . '">' . $contactors . '</span>';
                        }
                    }

                    // состояние двери шкафа
                    $sChannel = $coordinator->getSensorChannel(MeasureType::DOOR_STATE)
                        ->limit(1)
                        ->one();
                    if ($sChannel != null) {
                        $value = $sChannel->getMeasureOne()
                            ->asArray()
                            ->limit(1)
                            ->one();
                        if ($value != null) {
                            $security = $value['value'] == 0 ? 'в норме' : 'сработала';
                            $doorState = $value['value'] == 0 ? 'badge-green-small' : 'badge-red-small';
                            $security = '<span class="badge ' . $doorState . '">' . $security . '</span>';
                        }
                    }

                    // температура
                    $sChannel = $coordinator->getSensorChannel(MeasureType::TEMPERATURE)
                        ->limit(1)
                        ->one();
                    if ($sChannel != null) {
                        $value = $sChannel->getMeasureOne()
                            ->asArray()
                            ->limit(1)
                            ->one();
                        if ($value != null) {
                            $temperature = '<span class="badge badge-green-small">' . $value['value'] . '&deg;C</span>';
                        }
                    }
                }

                $u = "-";
                $w = "-";
                $w_total = "-";
                $device = Device::find()
                    ->where(['deviceTypeUuid' => DeviceType::DEVICE_ELECTRO])
                    ->andWhere(['deleted' => 0])
                    ->andWhere(['nodeUuid' => $node['uuid']])
                    ->with(['deviceStatus'])
                    ->asArray()
                    ->limit(1)
                    ->one();
                //echo json_encode($device['uuid']);
                if ($device) {
                    if ($device['deviceStatusUuid'] != DeviceStatus::WORK) {
                        $warnings .= 'Статус счетчика: <span class="badge badge-red-small">' . $device['deviceStatus']['title'] . '</span></br>';
                    }

                    $channels = SensorChannel::find()->where(['deviceUuid' => $device['uuid']])->all();
                    foreach ($channels as $channel) {
                        //echo json_encode($channel['uuid']);
                        $measure = Measure::find()->where(['sensorChannelUuid' => $channel['uuid']])
                            ->andWhere(['type' => MeasureType::MEASURE_TYPE_CURRENT])
                            ->orderBy('date DESC')
                            ->asArray()
                            ->limit(1)
                            ->one();
                        if ($measure) {
                            if ($channel['measureTypeUuid'] == MeasureType::POWER)
                                $w = number_format($measure['value'], 3);
                            if ($channel['measureTypeUuid'] == MeasureType::VOLTAGE)
                                $u = number_format($measure['value'], 2);
                        }
                        $measure = Measure::find()
                            ->where(['sensorChannelUuid' => $channel['uuid']])
                            ->andWhere(['type' => MeasureType::MEASURE_TYPE_TOTAL_CURRENT])
                            ->orderBy('date DESC')
                            ->asArray()
                            ->limit(1)
                            ->one();
                        if ($measure) {
                            if ($channel['measureTypeUuid'] == MeasureType::POWER)
                                $w_total = number_format($measure['value'], 2);
                        }
                    }
                }

                if ($node['object']['house']['houseTypeUuid'] == HouseType::HOUSE_TYPE_NO_NUMBER) {
                    $address = 'ул.' . $node['object']['house']['street']['title'] . ', без адреса';
                } else {
                    $address = 'ул.' . $node['object']['house']['street']['title'] . ', д.' . $node['object']['house']['number'] . ' ' . $node['object']['title'];
                }

                $software = $node["software"];
                $phone = $node["address"];
                $nodesList .= 'var node'
                    . $node["_id"]
                    . '= L.marker([' . $node["object"]["latitude"]
                    . ',' . $node["object"]["longitude"]
                    . '], {icon: ' . $icon . '}).bindPopup(\'<b>'
                    . Html::a($address,
                        ['/node/dashboard', 'uuid' => $node['uuid'], 'type' => 'node']) . '</span>'
                    . '</b><br/>'
                    . 'Связь: ' . $link . '<br/>'
                    . 'Охрана: ' . $security . '<br/>'
                    . 'Питание: ' . $power . '<br/>'
                    . 'Контактор: ' . $contactors . '<br/>'
                    . 'Температура: ' . $temperature . '<br/>'
                    . 'Напряжение,В: ' . $u . '<br/>'
                    . 'Мощность,кВт: ' . $w . '<br/>'
                    . 'Энергия,кВт/ч: ' . $w_total . '<br/>'
                    . 'Версия ПО: ' . $software . '<br/>'
                    . 'Адрес: ' . $node['address'] . '<br/>'
                    . $warnings
                    . '\').openPopup();';

                $nodesList .= 'node' . $node["_id"] . '.on(\'click\', function(e) {';
                $devices = Device::find()
                    ->where(['IN', 'deviceTypeUuid', [DeviceType::DEVICE_LIGHT, DeviceType::DEVICE_LIGHT_WITHOUT_ZB]])
                    ->andWhere(['deleted' => 0])
                    ->andWhere(['nodeUuid' => $node['uuid']])
                    ->all();
                foreach ($devices as $device) {
                    $coords[] = [$device['object']['latitude'], $device['object']['longitude']];
                    $nodesList .= 'if (deviceStatus' . $device["_id"] . '==1) { device' . $device["_id"] . '.setIcon(deviceIcon' . $device["_id"] . '); deviceStatus' . $device["_id"] . '=0; }';
                    $nodesList .= PHP_EOL;
                    $nodesList .= ' else { device' . $device["_id"] . '.setIcon(lightIconSelect); deviceStatus' . $device["_id"] . '=1; } ';
                    $nodesList .= PHP_EOL;
                }
                $nodesList .= '});';

                $coords[] = [$node['object']['latitude'], $node['object']['longitude']];
                if (count($coords)) {
                    $polylineList .= 'L.polyline(' . json_encode($coords) . ', {weight: 3, color: \'green\'}).addTo(map);';
                }

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

        $postCode = PHP_EOL;
        $postCode .= 'map.on(\'dblclick\', function(e) {
            var marker = new L.marker(e.latlng, {icon: lightIconBad}).addTo(map);
            marker.on(\'click\', function(e) {            
                $.ajax({
                    url: "../device/new-light",
                    type: "post",
                    data: {
                        latitude: e.latlng.lat,
                        longitude: e.latlng.lng
                    },
                    success: function (data) { 
                        $(\'#modalAddEquipment\').modal(\'show\');
                        $(\'#modalContentEquipment\').html(data);
                    }
                }); 
            });            
        });';

        $layer['coordinates'] = $coordinates;
        $layer['nodesList'] = $nodesList;
        $layer['nodesGroup'] = $nodesGroup;

        $layer['lightList'] = $lightList;
        $layer['lightGoodList'] = $lightGoodList;
        $layer['lightBadList'] = $lightBadList;
        $layer['sensorCO2List'] = $sensorCO2List;

        $layer['lightGroup'] = $lightGroup;
        $layer['lightGoodGroup'] = $lightGoodGroup;
        $layer['lightBadGroup'] = $lightBadGroup;
        $layer['sensorCO2Group'] = $sensorCO2Group;

        $layer['camerasList'] = $camerasList;
        $layer['camerasGroup'] = $camerasGroup;
        $layer['polylineList'] = $polylineList;
        $layer['define'] = $define;
        $layer['postCode'] = $postCode;

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
