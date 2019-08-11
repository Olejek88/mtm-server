<?php

namespace backend\controllers;

use backend\models\DeviceSearch;
use common\components\MainFunctions;
use common\models\Device;
use common\models\DeviceConfig;
use common\models\DeviceRegister;
use common\models\DeviceStatus;
use common\models\DeviceType;
use common\models\House;
use common\models\Measure;
use common\models\MeasureType;
use common\models\mtm\MtmDevLightActionSetLight;
use common\models\mtm\MtmDevLightConfig;
use common\models\mtm\MtmDevLightConfigLight;
use common\models\mtm\MtmPktHeader;
use common\models\Node;
use common\models\Objects;
use common\models\Organisation;
use common\models\Photo;
use common\models\SensorChannel;
use common\models\SensorConfig;
use common\models\Street;
use common\models\User;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Throwable;
use Yii;
use yii\base\InvalidConfigException;
use yii\data\ActiveDataProvider;
use yii\db\StaleObjectException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

/**
 * DeviceController implements the CRUD actions for Device model.
 */
class DeviceController extends Controller
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
     * Lists all Device models.
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

            $model = Device::find()
                ->where(['_id' => $_POST['editableKey']])
                ->one();
            if ($_POST['editableAttribute'] == 'port') {
                $model['port'] = $_POST['Device'][$_POST['editableIndex']]['port'];
            }
            if ($_POST['editableAttribute'] == 'deviceTypeUuid') {
                $model['deviceTypeUuid'] = $_POST['Device'][$_POST['editableIndex']]['deviceTypeUuid'];
            }
            if ($_POST['editableAttribute'] == 'interface') {
                $model['interface'] = $_POST['Device'][$_POST['editableIndex']]['interface'];
            }
            if ($_POST['editableAttribute'] == 'deviceStatusUuid') {
                $model['deviceStatusUuid'] = $_POST['Device'][$_POST['editableIndex']]['deviceStatusUuid'];
            }
            if ($_POST['editableAttribute'] == 'date') {
                $model['date'] = date("Y-m-d H:i:s", $_POST['Device'][$_POST['editableIndex']]['date']);
            }
            $model->save();
            return json_encode('');
        }

        $searchModel = new DeviceSearch();
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
     * Lists all Device models.
     *
     * @return mixed
     */
    public function actionIndexSmall()
    {
        $searchModel = new DeviceSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->pagination->pageSize = 15;

        return $this->render(
            'index-small',
            [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
            ]
        );
    }

    /**
     * Displays a single Device model.
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
     * Creates a new Device model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     *
     * @return mixed
     */
    public function actionCreate()
    {
        if (!Yii::$app->user->can(User::PERMISSION_ADMIN)) {
            return $this->redirect('/site/index');
        }

        $model = new Device();
        if ($model->load(Yii::$app->request->post())) {
            // проверяем все поля, если что-то не так показываем форму с ошибками
            if (!$model->validate()) {
                echo json_encode($model->errors);
                return $this->render('create', ['model' => $model]);
            }
            // сохраняем запись
            if ($model->save(false)) {
                MainFunctions::register("Добавлено новое оборудование " . $model['deviceType']['title'] . ' ' .
                    $model->node->object->getAddress() . ' [' . $model->node->address . ']');

                if ($model['deviceTypeUuid'] == DeviceType::DEVICE_ELECTRO) {
                    self::createChannel($model->uuid, MeasureType::POWER, "Мощность электроэнергии");
                    self::createChannel($model->uuid, MeasureType::CURRENT, "Ток");
                    self::createChannel($model->uuid, MeasureType::VOLTAGE, "Напряжение");
                    self::createChannel($model->uuid, MeasureType::FREQUENCY, "Частота");
                }
                if ($model['deviceTypeUuid'] == DeviceType::DEVICE_LIGHT) {
                    self::createChannel($model->uuid, MeasureType::TEMPERATURE, "Температура воздуха");
                }
                return $this->redirect(['view', 'id' => $model->_id]);
            }
            echo json_encode($model->errors);
        }
        return $this->render('create', ['model' => $model]);
    }

    /**
     * Updates an existing Device model.
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
     * Dashboard
     *
     * @param $uuid
     * @return string
     * @throws InvalidConfigException
     */
    public function actionDashboard()
    {
        if (isset($_GET['uuid'])) {
            $device = Device::find()
                ->where(['uuid' => $_GET['uuid']])
                ->one();
            if ($device && $device['deviceTypeUuid'])
                return self::actionDashboardElectro($device['uuid']);
        } else
            return self::actionIndex();

        if (isset($_POST['type']) && $_POST['type'] == 'set') {
            if (isset($_POST['device'])) {
                $device = Device::find()->where(['uuid' => $_POST['device']])->one();
                if (isset($_POST['value'])) {
                    $this->set($device, $_POST['value']);
                    self::updateConfig($device['uuid'], DeviceConfig::PARAM_SET_VALUE, $_POST['value']);
                }
            }
        }

        if (isset($_POST['type']) && $_POST['type'] == 'params') {
            if (isset($_POST['device'])) {
                $device = Device::find()->where(['uuid' => $_POST['device']])->one();
                $lightConfig = new MtmDevLightConfig();
                $lightConfig->mode = $_POST['mode'];
                $lightConfig->power = $_POST['power'];
                $lightConfig->group = $_POST['group'];
                $lightConfig->frequency = $_POST['frequency'];

                $lightConfig->type = 2;
                $lightConfig->protoVersion = 0;
                $lightConfig->device = MtmPktHeader::$MTM_DEVICE_LIGHT;

                $pkt = [
                    'type' => 'light',
                    'address' => $device['address'], // 16 байт мак адрес в шестнадцатиричном представлении
                    'data' => $lightConfig->getBase64Data(), // закодированые бинарные данные
                ];
                $org_id = User::getOid(Yii::$app->user->identity);
                $org_id = Organisation::find()->where(['uuid' => $org_id])->one()->_id;
                $node_id = $device['node']['_id'];
                self::sendConfig($pkt, $org_id, $node_id);

                self::updateConfig($device['uuid'], DeviceConfig::PARAM_FREQUENCY, $_POST['frequency']);
                self::updateConfig($device['uuid'], DeviceConfig::PARAM_POWER, $_POST['power']);
                self::updateConfig($device['uuid'], DeviceConfig::PARAM_GROUP, $_POST['group']);
                self::updateConfig($device['uuid'], DeviceConfig::PARAM_REGIME, $_POST['mode']);
            }
        }

        if (isset($_POST['type']) && $_POST['type'] == 'config') {
            if (isset($_POST['device'])) {
                $device = Device::find()->where(['uuid' => $_POST['device']])->one();
                $lightConfig = new MtmDevLightConfigLight();
                if (isset($_POST['device'])) {
                    $device = Device::find()->where(['uuid' => $_POST['device']])->one();
                    if ($device && isset($_POST['time0'])) {
                        $lightConfig->time[0] = $_POST['time0'];
                        $lightConfig->value[0] = $_POST['level0'];
                        $lightConfig->time[1] = $_POST['time1'];
                        $lightConfig->value[1] = $_POST['level1'];
                        $lightConfig->time[2] = $_POST['time2'];
                        $lightConfig->value[2] = $_POST['level2'];
                        $lightConfig->time[3] = $_POST['time3'];
                        $lightConfig->value[3] = $_POST['level3'];

                        $lightConfig->type = 3;
                        $lightConfig->protoVersion = 0;
                        $lightConfig->device = MtmPktHeader::$MTM_DEVICE_LIGHT;

                        $pkt = [
                            'type' => 'light',
                            'address' => $device['address'], // 16 байт мак адрес в шестнадцатиричном представлении
                            'data' => $lightConfig->getBase64Data(), // закодированые бинарные данные
                        ];
                        $org_id = User::getOid(Yii::$app->user->identity);
                        $org_id = Organisation::find()->where(['uuid' => $org_id])->one()->_id;
                        $node_id = $device['node']['_id'];
                        self::sendConfig($pkt, $org_id, $node_id);
                        self::updateConfig($device['uuid'], DeviceConfig::PARAM_TIME0, $_POST['time0']);
                        self::updateConfig($device['uuid'], DeviceConfig::PARAM_LEVEL0, $_POST['level0']);
                        self::updateConfig($device['uuid'], DeviceConfig::PARAM_TIME1, $_POST['time1']);
                        self::updateConfig($device['uuid'], DeviceConfig::PARAM_LEVEL1, $_POST['level1']);
                        self::updateConfig($device['uuid'], DeviceConfig::PARAM_TIME2, $_POST['time2']);
                        self::updateConfig($device['uuid'], DeviceConfig::PARAM_LEVEL2, $_POST['level2']);
                        self::updateConfig($device['uuid'], DeviceConfig::PARAM_TIME3, $_POST['time3']);
                        self::updateConfig($device['uuid'], DeviceConfig::PARAM_LEVEL3, $_POST['level3']);
                    }
                }
            }
        }

        $parameters['mode'] = self::getParameter($device['uuid'], DeviceConfig::PARAM_REGIME);
        $parameters['group'] = self::getParameter($device['uuid'], DeviceConfig::PARAM_GROUP);
        $parameters['power'] = self::getParameter($device['uuid'], DeviceConfig::PARAM_POWER);
        $parameters['frequency'] = self::getParameter($device['uuid'], DeviceConfig::PARAM_FREQUENCY);
        $parameters['value'] = self::getParameter($device['uuid'], DeviceConfig::PARAM_SET_VALUE);

        $parameters['time0'] = self::getParameter($device['uuid'], DeviceConfig::PARAM_TIME0);
        $parameters['level0'] = self::getParameter($device['uuid'], DeviceConfig::PARAM_LEVEL0);
        $parameters['time1'] = self::getParameter($device['uuid'], DeviceConfig::PARAM_TIME1);
        $parameters['level1'] = self::getParameter($device['uuid'], DeviceConfig::PARAM_LEVEL1);
        $parameters['time2'] = self::getParameter($device['uuid'], DeviceConfig::PARAM_TIME2);
        $parameters['level2'] = self::getParameter($device['uuid'], DeviceConfig::PARAM_LEVEL2);
        $parameters['time3'] = self::getParameter($device['uuid'], DeviceConfig::PARAM_TIME3);
        $parameters['level3'] = self::getParameter($device['uuid'], DeviceConfig::PARAM_LEVEL3);

        return $this->render(
            'dashboard',
            [
                'device' => $device,
                'parameters' => $parameters
            ]
        );
    }

    /**
     * Dashboard
     *
     * @param $uuid
     * @return string
     * @throws InvalidConfigException
     */
    public function actionDashboardElectro($uuid)
    {
        if (isset($_GET['uuid'])) {
            $device = Device::find()
                ->where(['uuid' => $uuid])
                ->one();
        } else {
            return $this->actionIndex();
        }

        // power by days
        $sChannel = (SensorChannel::find()->select('uuid')
            ->where(['deviceUuid' => $device, 'measureTypeUuid' => MeasureType::POWER]));
        $last_measures = (Measure::find()
            ->where(['sensorChannelUuid' => $sChannel])
            ->andWhere(['type' => MeasureType::MEASURE_TYPE_DAYS])
            ->andWhere(['parameter' => 0])
            ->orderBy('date DESC'))
            ->limit(100)
            ->all();
        $cnt = 0;
        $data = [];
        $categories = '';
        $values = '';
        foreach (array_reverse($last_measures) as $measure) {
            if ($cnt > 0) {
                $categories .= ',';
                $values .= ',';
            }
            $categories .= "'" . $measure->date . "'";
            $values .= $measure->value;
            $cnt++;
        }

        // archive days
        $last_measures = (Measure::find()
            ->where(['sensorChannelUuid' => $sChannel])
            ->andWhere(['type' => MeasureType::MEASURE_TYPE_DAYS])
            ->orderBy('date DESC'))
            ->all();
        $cnt = -1;
        $last_date = '';
        foreach ($last_measures as $measure) {
            if ($measure['date'] != $last_date)
                $last_date = $measure['date'];
            $data['days'][$cnt]['date'] = $measure['date'];
            if ($measure['parameter'] == 1)
                $data['days'][$cnt]['w1'] = $measure['value'];
            if ($measure['parameter'] == 2)
                $data['days'][$cnt]['w2'] = $measure['value'];
            if ($measure['parameter'] == 3)
                $data['days'][$cnt]['w3'] = $measure['value'];
            if ($measure['parameter'] == 4)
                $data['days'][$cnt]['w4'] = $measure['value'];
            if ($measure['parameter'] == 0)
                $data['days'][$cnt]['ws'] = $measure['value'];
        }

        // archive month
        $last_measures = (Measure::find()
            ->where(['sensorChannelUuid' => $sChannel])
            ->andWhere(['type' => MeasureType::MEASURE_TYPE_MONTH])
            ->orderBy('date DESC'))
            ->all();
        $cnt = -1;
        $last_date = '';
        foreach ($last_measures as $measure) {
            if ($measure['date'] != $last_date)
                $last_date = $measure['date'];
            $data['month'][$cnt]['date'] = $measure['date'];
            if ($measure['parameter'] == 1)
                $data['month'][$cnt]['w1'] = $measure['value'];
            if ($measure['parameter'] == 2)
                $data['month'][$cnt]['w2'] = $measure['value'];
            if ($measure['parameter'] == 3)
                $data['month'][$cnt]['w3'] = $measure['value'];
            if ($measure['parameter'] == 4)
                $data['month'][$cnt]['w4'] = $measure['value'];
            if ($measure['parameter'] == 0)
                $data['month'][$cnt]['ws'] = $measure['value'];
        }

        // integrate
        $integrates = (Measure::find()
            ->where(['sensorChannelUuid' => $sChannel])
            ->andWhere(['type' => MeasureType::MEASURE_TYPE_TOTAL_CURRENT])
            ->orderBy('date DESC'))
            ->all();
        foreach ($integrates as $measure) {
            if ($measure['parameter'] == 1)
                $parameters['increment']['w1'] = $measure['value'];
            if ($measure['parameter'] == 2)
                $parameters['increment']['w2'] = $measure['value'];
            if ($measure['parameter'] == 3)
                $parameters['increment']['w3'] = $measure['value'];
            if ($measure['parameter'] == 4)
                $parameters['increment']['w4'] = $measure['value'];
            if ($measure['parameter'] == 0)
                $parameters['increment']['ws'] = $measure['value'];
        }

        $current_month = '2019-08-01 00:00:00';
        $prev_month = '2019-08-01 00:00:00';
        $parameters['increment']['date']['last'] = $current_month;
        $parameters['increment']['date']['prev'] = $prev_month;
        $parameters['month']['date']['last'] = $current_month;
        $parameters['month']['date']['prev'] = $prev_month;
        $integrates = (Measure::find()
            ->where(['sensorChannelUuid' => $sChannel])
            ->andWhere(['type' => MeasureType::MEASURE_TYPE_TOTAL])
            ->orderBy('date DESC'))
            ->all();
        foreach ($integrates as $measure) {
            if ($measure['parameter'] == 1) {
                if ($measure['date'] == $current_month)
                    $parameters['increment']['w1']['last'] = $measure['value'];
                if ($measure['date'] == $prev_month)
                    $parameters['increment']['w1']['prev'] = $measure['value'];
            }
            if ($measure['parameter'] == 2) {
                if ($measure['date'] == $current_month)
                    $parameters['increment']['w2']['last'] = $measure['value'];
                if ($measure['date'] == $prev_month)
                    $parameters['increment']['w2']['prev'] = $measure['value'];
            }
            if ($measure['parameter'] == 3) {
                if ($measure['date'] == $current_month)
                    $parameters['increment']['w3']['last'] = $measure['value'];
                if ($measure['date'] == $prev_month)
                    $parameters['increment']['w3']['prev'] = $measure['value'];
            }
            if ($measure['parameter'] == 4) {
                if ($measure['date'] == $current_month)
                    $parameters['increment']['w4']['last'] = $measure['value'];
                if ($measure['date'] == $prev_month)
                    $parameters['increment']['w4']['prev'] = $measure['value'];
            }
            if ($measure['parameter'] == 0) {
                if ($measure['date'] == $current_month)
                    $parameters['increment']['ws']['last'] = $measure['value'];
                if ($measure['date'] == $prev_month)
                    $parameters['increment']['ws']['prev'] = $measure['value'];
            }
        }
        $integrates = (Measure::find()
            ->where(['sensorChannelUuid' => $sChannel])
            ->andWhere(['type' => MeasureType::MEASURE_TYPE_MONTH])
            ->orderBy('date DESC'))
            ->all();
        foreach ($integrates as $measure) {
            if ($measure['parameter'] == 1) {
                if ($measure['date'] == $current_month)
                    $parameters['month']['w1']['last'] = $measure['value'];
                if ($measure['date'] == $prev_month)
                    $parameters['month']['w1']['prev'] = $measure['value'];
            }
            if ($measure['parameter'] == 2) {
                if ($measure['date'] == $current_month)
                    $parameters['month']['w2']['last'] = $measure['value'];
                if ($measure['date'] == $prev_month)
                    $parameters['month']['w2']['prev'] = $measure['value'];
            }
            if ($measure['parameter'] == 3) {
                if ($measure['date'] == $current_month)
                    $parameters['month']['w3']['last'] = $measure['value'];
                if ($measure['date'] == $prev_month)
                    $parameters['month']['w3']['prev'] = $measure['value'];
            }
            if ($measure['parameter'] == 4) {
                if ($measure['date'] == $current_month)
                    $parameters['month']['w4']['last'] = $measure['value'];
                if ($measure['date'] == $prev_month)
                    $parameters['month']['w4']['prev'] = $measure['value'];
            }
            if ($measure['parameter'] == 0) {
                if ($measure['date'] == $current_month)
                    $parameters['month']['ws']['last'] = $measure['value'];
                if ($measure['date'] == $prev_month)
                    $parameters['month']['ws']['prev'] = $measure['value'];
            }
        }

        $parameters['trends']['title']="";
        $measures=[];
        if ($sChannel) {
            $parameters['trends']['title'] = $sChannel['title'];
            $measures = (Measure::find()
                ->where(['sensorChannelUuid' => $sChannel['uuid']])
                ->andWhere(['type' => MeasureType::MEASURE_TYPE_CURRENT])
                ->andWhere(['parameter' => 0])
                ->orderBy('date DESC'))->limit(200)->all();
        }

        $cnt = 0;
        $parameters['trends']['categories'] = '';
        $parameters['trends']['values'] = '';
        foreach (array_reverse($measures) as $measure) {
            if ($cnt > 0) {
                $parameters['trends']['categories'] .= ',';
                $parameters['trends']['values'] .= ',';
            }
            $parameters['trends']['categories'] .= "'" . $measure->date . "'";
            $parameters['trends']['values'] .= $measure->value;
            $cnt++;
        }

        $parameters['trends']['title']="";
        $measures=[];
        if ($sChannel) {
            $parameters['trends']['title'] = $sChannel['title'];
            $measures = (Measure::find()
                ->where(['sensorChannelUuid' => $sChannel['uuid']])
                ->andWhere(['type' => MeasureType::MEASURE_TYPE_CURRENT])
                ->andWhere(['parameter' => 0])
                ->orderBy('date DESC'))->limit(200)->all();
        }

        $cnt = 0;
        $parameters['days']['categories'] = '';
        $parameters['days']['values'] = '';
        foreach (array_reverse($measures) as $measure) {
            if ($cnt > 0) {
                $parameters['days']['categories'] .= ',';
                $parameters['days']['values'] .= ',';
            }
            $parameters['days']['categories'] .= "'" . $measure->date . "'";
            $parameters['days']['values'] .= $measure->value;
            $cnt++;
        }

        $deviceRegisters = DeviceRegister::find()
            ->where(['deviceUuid' => $device['uuid']])->all();
        $parameters['register']['provider'] = new ActiveDataProvider(
            [
                'query' => $deviceRegisters,
                'sort' =>false,
            ]
        );

        return $this->render(
            'dashboard',
            [
                'device' => $device,
                'parameters' => $parameters
            ]
        );
    }

    /**
     * Build tree of device
     *
     * @return mixed
     * @throws InvalidConfigException
     */
    public
    function actionTree()
    {
        ini_set('memory_limit', '-1');
        $fullTree = array();
        $streets = Street::find()
            ->select('*')
            ->where(['deleted' => 0])
            ->orderBy('title')
            ->all();
        foreach ($streets as $street) {
            $fullTree['children'][] = [
                'title' => $street['title'],
                'source' => '../device/tree',
                'uuid' => $street['uuid'],
                'expanded' => true,
                'type' => 'street',
                'folder' => true
            ];
            $houses = House::find()->where(['streetUuid' => $street['uuid']])
                ->andWhere(['deleted' => 0])
                ->orderBy('number')->all();
            foreach ($houses as $house) {
                $childIdx = count($fullTree['children']) - 1;
                $fullTree['children'][$childIdx]['children'][] = [
                    'uuid' => $house['uuid'],
                    'type' => 'house',
                    'expanded' => true,
                    'title' => $house->getFullTitle(),
                    'folder' => true
                ];
                $objects = Objects::find()->where(['houseUuid' => $house['uuid']])
                    ->andWhere(['deleted' => 0])
                    ->all();
                foreach ($objects as $object) {
                    $childIdx2 = count($fullTree['children'][$childIdx]['children']) - 1;
                    $fullTree['children'][$childIdx]['children'][$childIdx2]['children'][] = [
                        'title' => $object['objectType']['title'] . ' ' . $object['title'],
                        'source' => '../device/tree',
                        'uuid' => $object['uuid'],
                        'expanded' => true,
                        'type' => 'object',
                        'folder' => true
                    ];
                    $nodes = Node::find()->where(['objectUuid' => $object['uuid']])
                        ->andWhere(['deleted' => 0])
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
                            'status' => '<div class="progress"><div class="' . $class . '">' . $node['deviceStatus']->title . '</div></div>',
                            'title' => 'Контроллер [' . $node['address'] . ']',
                            'source' => '../device/tree',
                            'objectUuid' => $object['uuid'],
                            'uuid' => $node['uuid'],
                            'type' => 'node',
                            'expanded' => true,
                            'register' => $node['address'],
                            'folder' => true
                        ];
                        $devices = Device::find()->where(['nodeUuid' => $node['uuid']])
                            ->andWhere(['deleted' => 0])
                            ->all();
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
                                'uuid' => $device['uuid'],
                                'expanded' => true,
                                'objectUuid' => $object['uuid'],
                                'nodeUuid' => $node['uuid'],
                                'measure' => '',
                                'source' => '../device/tree',
                                'type' => 'device',
                                'date' => $device['date'],
                                'folder' => true
                            ];
                            $channels = SensorChannel::find()->where(['deviceUuid' => $device['uuid']])
                                ->all();
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
                                    'uuid' => $channel['uuid'],
                                    'source' => '../device/tree',
                                    'type' => 'channel',
                                    'measure' => $measure,
                                    'date' => $date,
                                    'folder' => false
                                ];
                            }
                        }
                    }
                }
            }
        }
        $deviceTypes = DeviceType::find()->all();
        $items = ArrayHelper::map($deviceTypes, 'uuid', 'title');

        return $this->render(
            'tree',
            ['device' => $fullTree, 'deviceTypes' => $items]
        );
    }

    /**
     * Build tree of device
     *
     * @return mixed
     * @throws InvalidConfigException
     */
    public
    function actionTreeSmall()
    {
        ini_set('memory_limit', '-1');
        $fullTree = array();
        $streets = Street::find()
            ->select('*')
            ->orderBy('title')
            ->all();
        foreach ($streets as $street) {
            $fullTree['children'][] = [
                'title' => $street['title'],
                'source' => '../device/tree',
                'uuid' => $street['uuid'],
                'expanded' => true,
                'type' => 'street',
                'folder' => true
            ];
            $houses = House::find()->where(['streetUuid' => $street['uuid']])->
            orderBy('number')->all();
            foreach ($houses as $house) {
                $childIdx = count($fullTree['children']) - 1;
                $fullTree['children'][$childIdx]['children'][] = [
                    'uuid' => $house['uuid'],
                    'type' => 'house',
                    'expanded' => true,
                    'title' => $house->getFullTitle(),
                    'folder' => true
                ];
                $objects = Objects::find()->where(['houseUuid' => $house['uuid']])->all();
                foreach ($objects as $object) {
                    $childIdx2 = count($fullTree['children'][$childIdx]['children']) - 1;
                    $fullTree['children'][$childIdx]['children'][$childIdx2]['children'][] = [
                        'title' => $object['objectType']['title'] . ' ' . $object['title'],
                        'source' => '../device/tree',
                        'uuid' => $object['uuid'],
                        'expanded' => true,
                        'type' => 'object',
                        'folder' => true
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
                            'source' => '../device/tree',
                            'objectUuid' => $object['uuid'],
                            'uuid' => $node['uuid'],
                            'type' => 'node',
                            'expanded' => true,
                            'register' => $node['address'],
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
                                'uuid' => $device['uuid'],
                                'expanded' => true,
                                'objectUuid' => $object['uuid'],
                                'nodeUuid' => $node['uuid'],
                                'measure' => '',
                                'source' => '../device/tree',
                                'type' => 'device',
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
                                        $date = $config['changedAt'];
                                    }
                                } else {
                                    $date = $measure['date'];
                                }
                                $fullTree['children'][$childIdx]['children'][$childIdx2]['children'][$childIdx3]['children'][$childIdx4]['children'][$childIdx5]['children'][] = [
                                    'title' => $channel['title'],
                                    'uuid' => $channel['uuid'],
                                    'date' => $date,
                                    'folder' => false
                                ];
                            }
                        }
                    }
                }
            }
        }
        $deviceTypes = DeviceType::find()->all();
        $items = ArrayHelper::map($deviceTypes, 'uuid', 'title');

        return $this->render(
            'tree-small',
            ['device' => $fullTree, 'deviceTypes' => $items]
        );
    }

    /**
     * Build tree of device
     *
     * @return mixed
     * @throws InvalidConfigException
     */
    public
    function actionTreeLight()
    {
        ini_set('memory_limit', '-1');
        $fullTree = array();
        $streets = Street::find()
            ->select('*')
            ->orderBy('title')
            ->all();
        foreach ($streets as $street) {
            $fullTree['children'][] = [
                'title' => $street['title'],
                'expanded' => true,
                'source' => '../device/tree-light',
                'uuid' => $street['uuid'],
                'type' => 'street',
                'folder' => true
            ];
            $houses = House::find()->where(['streetUuid' => $street['uuid']])->
            orderBy('number')->all();
            foreach ($houses as $house) {
                $objects = Objects::find()->where(['houseUuid' => $house['uuid']])->all();
                foreach ($objects as $object) {
                    $childIdx = count($fullTree['children']) - 1;
                    $fullTree['children'][$childIdx]['children'][] = [
                        'title' => $house->getFullTitle() . ', ' . $object['title'],
                        'expanded' => true,
                        'source' => '../device/tree-light',
                        'uuid' => $object['uuid'],
                        'type' => 'object',
                        'deviceTypeUuid' => DeviceType::DEVICE_LIGHT,
                        'folder' => true
                    ];
                    $devices = Device::find()->where(['objectUuid' => $object['uuid']])
                        ->andWhere(['deviceTypeUuid' => DeviceType::DEVICE_LIGHT])
                        ->all();
                    foreach ($devices as $device) {
                        $childIdx2 = count($fullTree['children'][$childIdx]['children']) - 1;
                        if ($device['deviceStatusUuid'] == DeviceStatus::NOT_MOUNTED) {
                            $class = 'critical1';
                        } elseif ($device['deviceStatusUuid'] == DeviceStatus::NOT_WORK) {
                            $class = 'critical2';
                        } else {
                            $class = 'critical3';
                        }
                        $channels = SensorChannel::find()->where(['deviceUuid' => $device['uuid']])->count();
                        //$config = SensorConfig::find()->where(['sUuid' => $device['uuid']])->count();
                        $config = 'конфигурация';
                        $fullTree['children'][$childIdx]['children'][$childIdx2]['children'][] = [
                            'title' => $device['deviceType']['title'],
                            'status' => '<div class="progress"><div class="'
                                . $class . '">' . $device['deviceStatus']->title . '</div></div>',
                            'register' => $device['port'] . ' [' . $device['address'] . ']',
                            'address' => $device['address'],
                            'uuid' => $device['uuid'],
                            'source' => '../device/tree-light',
                            'type' => 'device',
                            'deviceTypeUuid' => DeviceType::DEVICE_LIGHT,
                            'channels' => $channels,
                            'config' => $config,
                            'date' => $device['date'],
                            'folder' => false
                        ];
                    }
                }
            }
        }
        $deviceTypes = DeviceType::find()->all();
        $items = ArrayHelper::map($deviceTypes, 'uuid', 'title');

        return $this->render(
            'tree-light',
            ['device' => $fullTree, 'deviceTypes' => $items]
        );
    }

    /**
     * Build tree of device by user
     *
     * @return mixed
     */
    public
    function actionReport()
    {
        $searchModel = new DeviceSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->query->andWhere(['deviceTypeUuid' => DeviceType::DEVICE_ELECTRO]);
        $dataProvider->pagination->pageSize = 50;
        return $this->render(
            'report',
            [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
            ]
        );
    }

    /**
     * Deletes an existing Device model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     *
     * @param integer $id Id
     *
     * @return mixed
     * @throws NotFoundHttpException
     * @throws Throwable
     * @throws StaleObjectException
     */
    public
    function actionDelete($id)
    {
        if (!Yii::$app->user->can(User::PERMISSION_ADMIN)) {
            return $this->redirect('/site/index');
        }

        $device = $this->findModel($id);
        $photos = Photo::find()
            ->select('*')
            ->where(['deviceUuid' => $device['uuid']])
            ->all();
        foreach ($photos as $photo) {
            $photo->delete();
        }

        $measures = Measure::find()
            ->select('*')
            ->where(['deviceUuid' => $device['uuid']])
            ->all();
        foreach ($measures as $measure) {
            $measure->delete();
        }

        $this->findModel($id)->delete();
        return $this->redirect(['index']);
    }

    /**
     * Finds the Device model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     *
     * @param integer $id Id
     *
     * @return Device the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected
    function findModel($id)
    {
        if (($model = Device::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    /**
     *
     * @param $device
     * @param $value
     */
    function set($device, $value)
    {
        $lightConfig = new MtmDevLightActionSetLight();
        $lightConfig->value = $value;
        $lightConfig->type = 5;
        $lightConfig->protoVersion = 0;
        $lightConfig->device = MtmPktHeader::$MTM_DEVICE_LIGHT;
        $lightConfig->action = 2;
        //send to $device['address'];
        $pkt = [
            'type' => 'light',
            'address' => $device['address'], // 16 байт мак адрес в шестнадцатиричном представлении
            'data' => $lightConfig->getBase64Data(), // закодированые бинарные данные
        ];
        $org_id = User::getOid(Yii::$app->user->identity);
        $org_id = Organisation::find()->where(['uuid' => $org_id])->one()->_id;
        $node_id = $device['node']['_id'];
        self::sendConfig($pkt, $org_id, $node_id);
    }

    /**
     * функция отрабатывает сигналы от дерева и выполняет добавление нового оборудования
     *
     * @return mixed
     */
    public
    function actionNew()
    {
        if (!Yii::$app->user->can(User::PERMISSION_ADMIN)) {
            return 'Нет прав.';
        }

        if (isset($_POST["selected_node"])) {
            if (isset($_POST["uuid"]))
                $uuid = $_POST["uuid"];
            else $uuid = 0;
            if (isset($_POST["type"]))
                $type = $_POST["type"];
            else $type = 0;
            if (isset($_POST["deviceTypeUuid"]))
                $deviceTypeUuid = $_POST["deviceTypeUuid"];
            else $deviceTypeUuid = 0;
            if (isset($_POST["objectUuid"]))
                $objectUuid = $_POST["objectUuid"];
            else $objectUuid = '';

            if (isset($_POST["source"]))
                $source = $_POST["source"];
            else $source = '../device/tree';

            if ($uuid && $type) {
                if ($type == 'street') {
                    $house = new House();
                    return $this->renderAjax('../object/_add_house_form', [
                        'streetUuid' => $uuid,
                        'house' => $house,
                        'source' => $source
                    ]);
                }
                if ($type == 'house') {
                    $object = new Objects();
                    return $this->renderAjax('../object/_add_object_form', [
                        'houseUuid' => $uuid,
                        'object' => $object,
                        'source' => $source
                    ]);
                }
                if ($type == 'object') {
                    $node = new Node();
                    return $this->renderAjax('../node/_add_form', [
                        'node' => $node,
                        'objectUuid' => $uuid,
                        'source' => $source
                    ]);
                }
                if ($type == 'node') {
                    $device = new Device();
                    return $this->renderAjax('_add_form', [
                        'device' => $device,
                        'objectUuid' => $objectUuid,
                        'nodeUuid' => $uuid,
                        'deviceTypeUuid' => $deviceTypeUuid,
                        'source' => $source
                    ]);
                }
                if ($type == 'device') {
                    $sensorChannel = new SensorChannel();
                    return $this->renderAjax('../sensor-channel/_add_sensor_channel', [
                        'model' => $sensorChannel,
                        'deviceUuid' => $uuid,
                        'source' => $source
                    ]);
                }
            }
        }
        return 'Нельзя добавить объект в этом месте';
    }

    /**
     * функция отрабатывает сигналы от дерева и выполняет редактирование оборудования
     *
     * @return mixed
     * @throws InvalidConfigException
     */
    public
    function actionEdit()
    {
        if (!Yii::$app->user->can(User::PERMISSION_ADMIN)) {
            return 'Нет прав.';
        }

        if (isset($_POST["source"]))
            $source = $_POST["source"];
        else $source = '../device/tree';

        if (isset($_POST["uuid"]))
            $uuid = $_POST["uuid"];
        else $uuid = 0;
        if (isset($_POST["type"]))
            $type = $_POST["type"];
        else $type = 0;

        if ($uuid && $type) {
            if ($type == 'street') {
                $street = Street::find()->where(['uuid' => $uuid])->one();
                if ($street) {
                    return $this->renderAjax('../object/_add_street_form', [
                        'street' => $street,
                        'streetUuid' => $uuid,
                        'source' => $source
                    ]);
                }
            }
            if ($type == 'house') {
                $house = House::find()->where(['uuid' => $uuid])->one();
                if ($house) {
                    return $this->renderAjax('../object/_add_house_form', [
                        'houseUuid' => $uuid,
                        'house' => $house,
                        'source' => $source
                    ]);
                }
            }
            if ($type == 'object') {
                $object = Objects::find()->where(['uuid' => $uuid])->one();
                if ($object) {
                    return $this->renderAjax('../object/_add_object_form', [
                        'objectUuid' => $uuid,
                        'object' => $object,
                        'source' => $source
                    ]);
                }
            }
            if ($type == 'node') {
                $node = Node::find()->where(['uuid' => $uuid])->one();
                if ($node) {
                    return $this->renderAjax('../node/_add_form', [
                        'nodeUuid' => $uuid,
                        'node' => $node,
                        'source' => $source
                    ]);
                }
            }
            if ($type == 'device') {
                $device = Device::find()->where(['uuid' => $uuid])->one();
                return $this->renderAjax('../device/_add_form', [
                    'deviceTypeUuid' => $device['deviceTypeUuid'],
                    'device' => $device,
                    'source' => $source
                ]);
            }
            if ($type == 'sensor-channel') {
                $sensorChannel = SensorChannel::find()->where(['uuid' => $uuid])->one();
                return $this->renderAjax('../sensor-channel/_add_sensor_channel', [
                    'model' => $sensorChannel,
                    'source' => $source
                ]);
            }
        }
        return "";
    }

    /**
     * Creates a new Device model.
     * @return mixed
     * @throws InvalidConfigException
     */
    public
    function actionSave()
    {
        if (!Yii::$app->user->can(User::PERMISSION_ADMIN)) {
            return 'Нет прав.';
        }

        if (isset($_POST["type"]))
            $type = $_POST["type"];
        else $type = 0;
        if (isset($_POST["source"]))
            $source = $_POST["source"];
        else $source = '../device/tree';


        if ($type) {
            if ($type == 'street') {
                if (isset($_POST['streetUuid'])) {
                    $model = Street::find()->where(['uuid' => $_POST['streetUuid']])->one();
                    if ($model->load(Yii::$app->request->post())) {
                        if ($model->save(false)) {
                            return $this->redirect($source);
                        }
                    }
                }
            }
            if ($type == 'house') {
                if (isset($_POST['houseUuid']))
                    $model = House::find()->where(['uuid' => $_POST['houseUuid']])->one();
                else
                    $model = new House();
                if ($model->load(Yii::$app->request->post())) {
                    if ($model->save(false)) {
                        return $this->redirect($source);
                    }
                }
            }
            if ($type == 'object') {
                if (isset($_POST['objectUuid']))
                    $model = Objects::find()->where(['uuid' => $_POST['objectUuid']])->one();
                else
                    $model = new Objects();
                if ($model->load(Yii::$app->request->post())) {
                    if ($model->save(false)) {
                        return $this->redirect($source);
                    }
                }
            }
            if ($type == 'node') {
                if (isset($_POST['nodeUuid']))
                    $model = Node::find()->where(['uuid' => $_POST['nodeUuid']])->one();
                else
                    $model = new Node();
                if ($model->load(Yii::$app->request->post())) {
                    if ($model->save(false) && isset($_POST['nodeUuid'])) {
                        return $this->redirect($source);
                    }
                }
            }
            if ($type == 'device') {
                if (isset($_POST['deviceUuid']))
                    $model = Device::find()->where(['uuid' => $_POST['deviceUuid']])->one();
                else
                    $model = new Device();
                if ($model->load(Yii::$app->request->post())) {
                    if ($model->save(false) && isset($_POST['deviceUuid'])) {
                        //return $this->redirect($source);
                    }
                    if ($model['deviceTypeUuid'] == DeviceType::DEVICE_ELECTRO) {
                        self::createChannel($model->uuid, MeasureType::POWER, "Мощность электроэнергии");
                        self::createChannel($model->uuid, MeasureType::CURRENT, "Ток");
                        self::createChannel($model->uuid, MeasureType::VOLTAGE, "Напряжение");
                        self::createChannel($model->uuid, MeasureType::FREQUENCY, "Частота");
                    }
                    if ($model['deviceTypeUuid'] == DeviceType::DEVICE_LIGHT) {
                        self::createChannel($model->uuid, MeasureType::TEMPERATURE, "Температура");
                    }
                }
            }
        }
        return $this->redirect($source);
    }

    /**
     * функция отрабатывает сигналы от дерева и выполняет добавление нового оборудования
     *
     * @return mixed
     * @throws InvalidConfigException
     */
    public
    function actionSetConfig()
    {
        if (!Yii::$app->user->can(User::PERMISSION_ADMIN)) {
            return 'Нет прав.';
        }

        if (isset($_POST["selected_node"])) {
            if (isset($_POST["uuid"]))
                $uuid = $_POST["uuid"];
            else $uuid = 0;

            if ($uuid) {
                $device = Device::find()->where(['uuid' => $_POST['uuid']])->one();

                $parameters['mode'] = self::getParameter($device['uuid'], DeviceConfig::PARAM_REGIME);
                $parameters['group'] = self::getParameter($device['uuid'], DeviceConfig::PARAM_GROUP);
                $parameters['power'] = self::getParameter($device['uuid'], DeviceConfig::PARAM_POWER);
                $parameters['frequency'] = self::getParameter($device['uuid'], DeviceConfig::PARAM_FREQUENCY);
                $parameters['value'] = self::getParameter($device['uuid'], DeviceConfig::PARAM_SET_VALUE);

                $parameters['time0'] = self::getParameter($device['uuid'], DeviceConfig::PARAM_TIME0);
                $parameters['level0'] = self::getParameter($device['uuid'], DeviceConfig::PARAM_LEVEL0);
                $parameters['time1'] = self::getParameter($device['uuid'], DeviceConfig::PARAM_TIME1);
                $parameters['level1'] = self::getParameter($device['uuid'], DeviceConfig::PARAM_LEVEL1);
                $parameters['time2'] = self::getParameter($device['uuid'], DeviceConfig::PARAM_TIME2);
                $parameters['level2'] = self::getParameter($device['uuid'], DeviceConfig::PARAM_LEVEL2);
                $parameters['time3'] = self::getParameter($device['uuid'], DeviceConfig::PARAM_TIME3);
                $parameters['level3'] = self::getParameter($device['uuid'], DeviceConfig::PARAM_LEVEL3);

                return $this->renderAjax('_edit_light_config', [
                    'device' => $device,
                    'parameters' => $parameters
                ]);
            }
        }
        return 'Нельзя сконфигурировать устройство';
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

    /**
     * @param $deviceUuid
     * @param $parameter
     * @param $value
     * @throws InvalidConfigException
     */
    function updateConfig($deviceUuid, $parameter, $value)
    {
        $deviceConfig = DeviceConfig::find()->where(['deviceUuid' => $deviceUuid])->andWhere(['parameter' => $parameter])->one();
        if ($deviceConfig) {
            $deviceConfig['value'] = $value;
            $deviceConfig->save();
        } else {
            $deviceConfig = new DeviceConfig();
            $deviceConfig->uuid = MainFunctions::GUID();
            $deviceConfig->deviceUuid = $deviceUuid;
            $deviceConfig->value = $value;
            $deviceConfig->parameter = $parameter;
            $deviceConfig->oid = User::getOid(Yii::$app->user->identity);
            $deviceConfig->save();
            //echo json_encode($deviceConfig->errors);
            //exit(0);
        }
    }

    /**
     * @param $deviceUuid
     * @param $parameter
     * @return mixed|null
     * @throws InvalidConfigException
     */
    function getParameter($deviceUuid, $parameter)
    {
        $deviceConfig = DeviceConfig::find()->where(['deviceUuid' => $deviceUuid])->andWhere(['parameter' => $parameter])->one();
        if ($deviceConfig) {
            return $deviceConfig['value'];
        } else {
            return null;
        }
    }

    /**
     * @param $deviceUuid
     * @param $measureTypeUuid
     * @param $title
     */
    function createChannel($deviceUuid, $measureTypeUuid, $title)
    {
        $sensorChannel = new SensorChannel();
        $sensorChannel->uuid = MainFunctions::GUID();
        $sensorChannel->deviceUuid = $deviceUuid;
        $sensorChannel->measureTypeUuid = $measureTypeUuid;
        $sensorChannel->oid = User::getOid(Yii::$app->user->identity);
        $sensorChannel->register = "0";
        $sensorChannel->title = $title;
        $sensorChannel->save();
    }

    /**
     * функция отрабатывает сигналы от дерева и выполняет удаление
     *
     * @return mixed
     * @throws StaleObjectException
     * @throws \Throwable
     */
    public
    function actionRemove()
    {
        if (!Yii::$app->user->can(User::PERMISSION_ADMIN)) {
            return 'Нет прав.';
        }

        if (isset($_POST["selected_node"])) {
            if (isset($_POST["uuid"]))
                $uuid = $_POST["uuid"];
            else $uuid = 0;
            if (isset($_POST["type"]))
                $type = $_POST["type"];
            else $type = 0;

            if ($uuid && $type) {
                if ($type == 'street') {
                    $street = Street::find()->where(['uuid' => $uuid])->one();
                    if ($street) {
                        $house = House::find()->where(['streetUuid' => $street['uuid']])->one();
                        if (!$house) {
                            $street->delete();
                            return 'ok';
                        }
                    }
                }
                if ($type == 'house') {
                    $house = House::find()->where(['uuid' => $uuid])->one();
                    if ($house) {
                        $house['deleted'] = true;
                        $house->save();
                        return 'ok';
                    }
                }
                if ($type == 'object') {
                    $object = Objects::find()->where(['uuid' => $uuid])->one();
                    if ($object) {
                        $object['deleted'] = true;
                        $object->save();
                        return 'ok';
                    }

                }
                if ($type == 'device') {
                    $device = Device::find()->where(['uuid' => $uuid])->one();
                    if ($device) {
                        $device['deleted'] = true;
                        $device->save();
                        return 'ok';
                    }
                }
            }
        }
        return 'Нельзя удалить этот объект';
    }

    /**
     * @param $uuid
     * @return string
     * @throws InvalidConfigException
     */
    public
    function actionRegister($uuid)
    {
        $deviceRegisters = DeviceRegister::find()->where(['deviceUuid' => $uuid]);
        $provider = new ActiveDataProvider(
            [
                'query' => $deviceRegisters,
                'sort' => false,
            ]
        );
        return $this->render(
            'view',
            [
                'provider' => $provider
            ]
        );
    }
}