<?php

namespace backend\controllers;

use backend\models\DeviceSearch;
use common\components\MainFunctions;
use common\models\Device;
use common\models\DeviceConfig;
use common\models\DeviceGroup;
use common\models\DeviceProgram;
use common\models\DeviceRegister;
use common\models\DeviceStatus;
use common\models\DeviceType;
use common\models\Group;
use common\models\GroupControl;
use common\models\House;
use common\models\HouseType;
use common\models\Measure;
use common\models\MeasureType;
use common\models\mtm\MtmContactor;
use common\models\mtm\MtmDevLightActionSetLight;
use common\models\mtm\MtmDevLightConfig;
use common\models\mtm\MtmDevLightConfigLight;
use common\models\mtm\MtmPktHeader;
use common\models\mtm\MtmResetCoordinator;
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
            if ($_POST['editableAttribute'] == 'address') {
                $model['address'] = $_POST['Device'][$_POST['editableIndex']]['address'];
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
     * @throws InvalidConfigException
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
     * @throws InvalidConfigException
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
                return $this->render('create', ['model' => $model, 'program' => null]);
            }
            // сохраняем запись
            if ($model->save(false)) {
                if ($model->deviceTypeUuid == DeviceType::DEVICE_LIGHT) {
                    $deviceConfig = DeviceConfig::find()->where(['deviceUuid' => $model->uuid, 'parameter' => 'Программа'])->one();
                    if ($deviceConfig == null) {
                        $deviceConfig = new DeviceConfig();
                        $deviceConfig->uuid = MainFunctions::GUID();
                        $deviceConfig->oid = User::getOid(Yii::$app->user->identity);
                        $deviceConfig->parameter = 'Программа';
                        $deviceConfig->deviceUuid = $model->uuid;
                    }

                    $deviceConfig->value = $model->lightProgram;
                    $deviceConfig->save();
                }

                MainFunctions::register("Добавлено новое оборудование " . $model['deviceType']['title'] . ' ' .
                    $model->node->object->getAddress() . ' [' . $model->node->address . ']');

                if ($model['deviceTypeUuid'] == DeviceType::DEVICE_ELECTRO) {
                    self::createChannel($model->uuid, MeasureType::POWER, "Мощность электроэнергии");
                    self::createChannel($model->uuid, MeasureType::CURRENT, "Ток");
                    self::createChannel($model->uuid, MeasureType::VOLTAGE, "Напряжение");
                    self::createChannel($model->uuid, MeasureType::FREQUENCY, "Частота");
                }

                if ($model['deviceTypeUuid'] == DeviceType::DEVICE_LIGHT) {
                    //self::createChannel($model->uuid, MeasureType::TEMPERATURE, "Температура воздуха");
                }

                return $this->redirect(['view', 'id' => $model->_id]);
            }
            echo json_encode($model->errors);
        }
        return $this->render('create', ['model' => $model, 'program' => null]);
    }

    /**
     * Updates an existing Device model.
     * If update is successful, the browser will be redirected to the 'view' page.
     *
     * @param integer $id Id
     *
     * @return mixed
     * @throws InvalidConfigException
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
                if ($model->deviceTypeUuid == DeviceType::DEVICE_LIGHT) {
                    $deviceConfig = DeviceConfig::find()->where(['deviceUuid' => $model->uuid, 'parameter' => 'Программа'])->one();
                    $deviceConfig->value = $model->lightProgram;
                    $deviceConfig->save();
                    MainFunctions::deviceRegister($deviceConfig['deviceUuid'], "Обновлена конфигурация устройства");
                }

                return $this->redirect(['view', 'id' => $model->_id]);
            } else {
                $program = new DeviceProgram();
                $program->title = $model->lightProgram;
                return $this->render(
                    'update',
                    [
                        'model' => $model,
                        'program' => $program,
                    ]
                );
            }
        } else {
            return $this->render(
                'update',
                [
                    'model' => $model,
                    'program' => $model->getDeviceProgram(),
                ]
            );
        }
    }

    /**
     * Dashboard
     *
     * @return string
     * @throws InvalidConfigException
     */
    public function actionDashboard()
    {
        if (isset($_GET['uuid'])) {
            $device = Device::find()
                ->where(['uuid' => $_GET['uuid']])
                ->one();
            if ($device && $device['deviceTypeUuid'] == DeviceType::DEVICE_ELECTRO)
                return self::actionDashboardElectro($device['uuid']);
        } else
            return self::actionIndex();

        if (isset($_POST['type']) && $_POST['type'] == 'set') {
            if (isset($_POST['device'])) {
                $device = Device::find()->where(['uuid' => $_POST['device']])->one();
                if (isset($_POST['value'])) {
                    $this->set($device, $_POST['value']);
                    MainFunctions::deviceRegister($device['uuid'], "Обновлена конфигурация устройства");
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

                MainFunctions::deviceRegister($device['uuid'], "Обновлена конфигурация устройства");
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
        $sChannel = (SensorChannel::find()->where(['deviceUuid' => $device, 'measureTypeUuid' => MeasureType::POWER]))->one();
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
            $categories .= "'" . date_format(date_create($measure['date']), 'Y-m-d') . "'";
            // TODO важно! временно!
            if ($measure->value < 1000)
                $values .= $measure->value;
            else
                $values .= '0';
            $cnt++;
        }

        // archive days
        $last_measures = (Measure::find()
            ->where(['sensorChannelUuid' => $sChannel])
            ->andWhere(['type' => MeasureType::MEASURE_TYPE_DAYS])
            ->orderBy('date DESC'))
            ->all();
        $cnt = -1;
        $data['days'] = [];
        $data['month'] = [];
        $last_date = '';
        foreach ($last_measures as $measure) {
            if ($measure['date'] != $last_date) {
                $last_date = $measure['date'];
                $cnt++;
            }
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
            if ($cnt > 25) break;
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
        $parameters['increment']['w1']['current'] = "-";
        $parameters['increment']['w2']['current'] = "-";
        $parameters['increment']['w3']['current'] = "-";
        $parameters['increment']['w4']['current'] = "-";
        $parameters['increment']['ws']['current'] = "-";

        $integrates = (Measure::find()
            ->where(['sensorChannelUuid' => $sChannel])
            ->andWhere(['type' => MeasureType::MEASURE_TYPE_TOTAL_CURRENT])
            ->orderBy('date DESC'))
            ->all();
        foreach ($integrates as $measure) {
            if ($measure['parameter'] == 1)
                $parameters['increment']['w1']['current'] = $measure['value'];
            if ($measure['parameter'] == 2)
                $parameters['increment']['w2']['current'] = $measure['value'];
            if ($measure['parameter'] == 3)
                $parameters['increment']['w3']['current'] = $measure['value'];
            if ($measure['parameter'] == 4)
                $parameters['increment']['w4']['current'] = $measure['value'];
            if ($measure['parameter'] == 0)
                $parameters['increment']['ws']['current'] = $measure['value'];
        }

        $month = date("Y-m-01 00:00:00", time());
        $current_month = date("Y-m-01 00:00:00", strtotime("-1 months"));
        $prev_month = date("Y-m-01 00:00:00", strtotime("-2 months"));

        $parameters['increment']['w1']['last'] = "-";
        $parameters['increment']['w2']['last'] = "-";
        $parameters['increment']['w3']['last'] = "-";
        $parameters['increment']['w4']['last'] = "-";
        $parameters['increment']['ws']['last'] = "-";
        $parameters['increment']['w1']['prev'] = "-";
        $parameters['increment']['w2']['prev'] = "-";
        $parameters['increment']['w3']['prev'] = "-";
        $parameters['increment']['w4']['prev'] = "-";
        $parameters['increment']['ws']['prev'] = "-";
        $parameters['month']['w1']['last'] = "-";
        $parameters['month']['w2']['last'] = "-";
        $parameters['month']['w3']['last'] = "-";
        $parameters['month']['w4']['last'] = "-";
        $parameters['month']['ws']['last'] = "-";
        $parameters['month']['w1']['prev'] = "-";
        $parameters['month']['w2']['prev'] = "-";
        $parameters['month']['w3']['prev'] = "-";
        $parameters['month']['w4']['prev'] = "-";
        $parameters['month']['ws']['prev'] = "-";
        $parameters['month']['w1']['current'] = "-";
        $parameters['month']['w2']['current'] = "-";
        $parameters['month']['w3']['current'] = "-";
        $parameters['month']['w4']['current'] = "-";
        $parameters['month']['ws']['current'] = "-";

        $parameters['increment']['date']['last'] = date("Y-m-01", strtotime($current_month));
        $parameters['increment']['date']['prev'] = date("Y-m-01", strtotime($prev_month));
        $parameters['month']['date']['last'] = date("Y-m-01", strtotime($current_month));
        $parameters['month']['date']['prev'] = date("Y-m-01", strtotime($prev_month));
        $parameters['month']['date']['current'] = date("Y-m-01", strtotime($month));

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
                if ($measure['date'] == $month)
                    $parameters['month']['w1']['current'] = $measure['value'];
            }
            if ($measure['parameter'] == 2) {
                if ($measure['date'] == $current_month)
                    $parameters['month']['w2']['last'] = $measure['value'];
                if ($measure['date'] == $prev_month)
                    $parameters['month']['w2']['prev'] = $measure['value'];
                if ($measure['date'] == $month)
                    $parameters['month']['w2']['current'] = $measure['value'];
            }
            if ($measure['parameter'] == 3) {
                if ($measure['date'] == $current_month)
                    $parameters['month']['w3']['last'] = $measure['value'];
                if ($measure['date'] == $prev_month)
                    $parameters['month']['w3']['prev'] = $measure['value'];
                if ($measure['date'] == $month)
                    $parameters['month']['w3']['current'] = $measure['value'];
            }
            if ($measure['parameter'] == 4) {
                if ($measure['date'] == $current_month)
                    $parameters['month']['w4']['last'] = $measure['value'];
                if ($measure['date'] == $prev_month)
                    $parameters['month']['w4']['prev'] = $measure['value'];
                if ($measure['date'] == $month)
                    $parameters['month']['w4']['current'] = $measure['value'];
            }
            if ($measure['parameter'] == 0) {
                if ($measure['date'] == $current_month)
                    $parameters['month']['ws']['last'] = $measure['value'];
                if ($measure['date'] == $prev_month)
                    $parameters['month']['ws']['prev'] = $measure['value'];
                if ($measure['date'] == $month)
                    $parameters['month']['ws']['current'] = $measure['value'];
            }
        }

        $parameters['trends']['title'] = "";
        $measures = [];
        if ($sChannel) {
            $parameters['trends']['title'] = $sChannel['title'];
            $measures = (Measure::find()
                ->where(['sensorChannelUuid' => $sChannel['uuid']])
                ->andWhere(['type' => MeasureType::MEASURE_TYPE_INTERVAL])
                ->andWhere(['parameter' => 0])
                ->orderBy('date DESC'))->limit(200)->all();
        }

        $cnt = 0;
        $parameters['uuid'] = $sChannel['_id'];
        $parameters['trends']['categories'] = '';
        $parameters['trends']['values'] = '';
        foreach (array_reverse($measures) as $measure) {
            if ($cnt > 0) {
                $parameters['trends']['categories'] .= ',';
                $parameters['trends']['values'] .= ',';
            }
            $parameters['trends']['categories'] .= "'" . date("d H:i", strtotime($measure->date)) . "'";
            $parameters['trends']['values'] .= $measure->value;
            $cnt++;
        }

        $parameters['days']['title'] = "";
        $measures = [];
        if ($sChannel) {
            $parameters['trends']['title'] = $sChannel['title'];
            $measures = (Measure::find()
                ->where(['sensorChannelUuid' => $sChannel['uuid']])
                ->andWhere(['type' => MeasureType::MEASURE_TYPE_DAYS])
                ->andWhere(['parameter' => 1])
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
            $parameters['days']['categories'] .= "'" . date_format(date_create($measure['date']), 'd H:i') . "'";
            $parameters['days']['values'] .= $measure->value;
            $cnt++;
        }

        $deviceRegisters = DeviceRegister::find()
            ->where(['deviceUuid' => $device['uuid']])
            ->orderBy('date DESC')
            ->limit(8);
        $parameters['register']['provider'] = new ActiveDataProvider(
            [
                'query' => $deviceRegisters,
                'sort' => false,
                'pagination' => false
            ]
        );

        $parameters['current']['i1'] = "-";
        $parameters['current']['i2'] = "-";
        $parameters['current']['i3'] = "-";
        $parameters['current']['u1'] = "-";
        $parameters['current']['u2'] = "-";
        $parameters['current']['u3'] = "-";
        $parameters['current']['f1'] = "-";
        $parameters['current']['f2'] = "-";
        $parameters['current']['f3'] = "-";
        $parameters['current']['w1'] = "-";
        $parameters['current']['w2'] = "-";
        $parameters['current']['w3'] = "-";
        $parameters['current']['ws'] = "-";

        $measures = (Measure::find()
            ->where(['type' => MeasureType::MEASURE_TYPE_CURRENT])
            ->orderBy('date DESC'))->limit(200)
            ->all();
        foreach ($measures as $measure) {
            if ($measure['sensorChannel']['measureTypeUuid'] == MeasureType::CURRENT &&
                $measure['sensorChannel']['deviceUuid'] == $device['uuid']) {
                if ($measure['parameter'] == 1)
                    $parameters['current']['i1'] = $measure['value'];
                if ($measure['parameter'] == 2)
                    $parameters['current']['i2'] = $measure['value'];
                if ($measure['parameter'] == 3)
                    $parameters['current']['i3'] = $measure['value'];
            }
            if ($measure['sensorChannel']['measureTypeUuid'] == MeasureType::VOLTAGE &&
                $measure['sensorChannel']['deviceUuid'] == $device['uuid']) {
                if ($measure['parameter'] == 1)
                    $parameters['current']['u1'] = $measure['value'];
                if ($measure['parameter'] == 2)
                    $parameters['current']['u2'] = $measure['value'];
                if ($measure['parameter'] == 3)
                    $parameters['current']['u3'] = $measure['value'];
            }
            if ($measure['sensorChannel']['measureTypeUuid'] == MeasureType::FREQUENCY &&
                $measure['sensorChannel']['deviceUuid'] == $device['uuid']) {
                if ($measure['parameter'] == 0)
                    $parameters['current']['f1'] = $measure['value'];
            }
            if ($measure['sensorChannel']['measureTypeUuid'] == MeasureType::POWER &&
                $measure['sensorChannel']['deviceUuid'] == $device['uuid']) {
                if ($measure['parameter'] == 0)
                    $parameters['current']['ws'] = $measure['value'];
                if ($measure['parameter'] == 1)
                    $parameters['current']['w1'] = $measure['value'];
                if ($measure['parameter'] == 2)
                    $parameters['current']['w2'] = $measure['value'];
                if ($measure['parameter'] == 3)
                    $parameters['current']['w3'] = $measure['value'];
            }
        }


        $parameters['trends']['title'] = $sChannel['title'];

        return $this->render(
            'dashboard-electro',
            [
                'device' => $device,
                'parameters' => $parameters,
                'dataAll' => $data
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
    public function actionArchive($uuid)
    {
        if (isset($_GET['uuid'])) {
            $device = Device::find()
                ->where(['uuid' => $uuid])
                ->one();
        } else {
            return $this->actionIndex();
        }

        // power by days
        $sChannel = SensorChannel::find()
            ->where(['deviceUuid' => $device, 'measureTypeUuid' => MeasureType::POWER])
            ->one();
        $last_measures = (Measure::find()
            ->where(['sensorChannelUuid' => $sChannel])
            ->andWhere(['type' => MeasureType::MEASURE_TYPE_DAYS])
            ->andWhere(['parameter' => 1])
            ->orderBy('date DESC'))
            ->limit(100)
            ->all();
        $cnt = 0;
        $data = [];
        $data['trends'] = [];
        $data['trends']['days']['categories'] = '';
        $data['trends']['days']['values'] = '';
        foreach (array_reverse($last_measures) as $measure) {
            if ($cnt > 0) {
                $data['trends']['days']['categories'] .= ',';
                $data['trends']['days']['values'] .= ',';
            }
            $data['trends']['days']['categories'] .= "'" . date_format(date_create($measure['date']), 'd H:i') . "'";
            $data['trends']['days']['values'] .= $measure->value;
            $cnt++;
        }

        // archive days
        $last_measures = (Measure::find()
            ->where(['sensorChannelUuid' => $sChannel])
            ->andWhere(['type' => MeasureType::MEASURE_TYPE_DAYS])
            ->orderBy('date DESC'))
            ->all();
        $cnt = -1;
        $data['days'] = [];
        $data['month'] = [];

        $last_date = '';
        foreach ($last_measures as $measure) {
            if ($measure['date'] != $last_date) {
                $last_date = $measure['date'];
                $cnt++;
            }
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

        // power by month
        $last_measures = (Measure::find()
            ->where(['sensorChannelUuid' => $sChannel])
            ->andWhere(['type' => MeasureType::MEASURE_TYPE_MONTH])
            ->andWhere(['parameter' => 0])
            ->orderBy('date DESC'))
            ->limit(100)
            ->all();
        $cnt = 0;
        $data['trends']['month']['categories'] = '';
        $data['trends']['month']['values'] = '';
        foreach (array_reverse($last_measures) as $measure) {
            if ($cnt > 0) {
                $data['trends']['month']['categories'] .= ',';
                $data['trends']['month']['values'] .= ',';
            }
            $data['trends']['month']['categories'] .= "'" . date_format(date_create($measure['date']), 'm-Y') . "'";
            $data['trends']['month']['values'] .= $measure->value;
            $cnt++;
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
            if ($measure['date'] != $last_date) {
                $last_date = $measure['date'];
                $cnt++;
            }
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

        $data['trends']['title'] = $sChannel['title'];

        //echo json_encode($data);
        return $this->render(
            'archive',
            [
                'dataAll' => $data
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
    public function actionArchiveDays($uuid)
    {
        if (isset($_GET['uuid'])) {
            $device = Device::find()
                ->where(['uuid' => $uuid])
                ->one();
        } else {
            return $this->actionIndex();
        }

        // power by days
        $sChannel = SensorChannel::find()
            ->where(['deviceUuid' => $device, 'measureTypeUuid' => MeasureType::POWER])
            ->one();
        // archive days
        $start_time = '2018-12-31 00:00:00';
        $end_time = '2021-12-31 00:00:00';
        if (isset($_GET['end_time'])) {
            $end_time = date('Y-m-d H:i:s', strtotime($_GET['end_time']));
        }
        if (isset($_GET['start_time'])) {
            $start_time = date('Y-m-d H:i:s', strtotime($_GET['start_time']));
        }

        $last_measures = (Measure::find()
            ->where(['sensorChannelUuid' => $sChannel])
            ->andWhere(['type' => MeasureType::MEASURE_TYPE_DAYS])
            ->andWhere('date >= "' . $start_time . '"')
            ->andWhere('date < "' . $end_time . '"')
            ->orderBy('date DESC'))
            ->all();
        $cnt = -1;
        $data['days'] = [];
        $data['month'] = [];

        $last_date = '';
        foreach ($last_measures as $measure) {
            if ($measure['date'] != $last_date) {
                $last_date = $measure['date'];
                $cnt++;
            }
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

        return $this->render(
            'archive-days',
            [
                'dataAll' => $data
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
            $houses = House::find()
                ->where(['streetUuid' => $street['uuid']])
                ->andWhere(['deleted' => 0])
                ->orderBy('number')
                ->all();
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
                        'title' => $object['title'],
                        'source' => '../device/tree',
                        'uuid' => $object['uuid'],
                        'objectUuid' => $object['uuid'],
                        'expanded' => true,
                        'type' => 'object',
                        'folder' => true
                    ];
                    $devices = Device::find()
                        ->where(['objectUuid' => $object['uuid']])
                        ->andWhere(['deleted' => 0])
                        ->all();
                    foreach ($devices as $device) {
                        $childIdx3 = count($fullTree['children'][$childIdx]['children'][$childIdx2]['children']) - 1;
                        if ($device['deviceStatusUuid'] == DeviceStatus::NOT_MOUNTED) {
                            $class = 'critical1';
                        } elseif ($device['deviceStatusUuid'] == DeviceStatus::NOT_WORK) {
                            $class = 'critical2';
                        } else {
                            $class = 'critical3';
                        }
                        $fullTree['children'][$childIdx]['children'][$childIdx2]['children'][$childIdx3]['children'][] = [
                            'title' => $device['deviceType']['title'] . ' [' . $device['address'] . ']',
                            'status' => '<div class="progress"><div class="'
                                . $class . '">' . $device['deviceStatus']->title . '</div></div>',
                            'register' => $device['port'] . ' [' . $device['address'] . ']',
                            'uuid' => $device['uuid'],
                            'expanded' => true,
                            'objectUuid' => $object['uuid'],
                            'measure' => '',
                            'source' => '../device/tree',
                            'type' => 'device',
                            'date' => $device['date'],
                            'folder' => true
                        ];
                        $channels = SensorChannel::find()->where(['deviceUuid' => $device['uuid']])
                            ->all();
                        foreach ($channels as $channel) {
                            $childIdx4 = count($fullTree['children'][$childIdx]['children'][$childIdx2]['children'][$childIdx3]['children']) - 1;
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
                            $fullTree['children'][$childIdx]['children'][$childIdx2]['children'][$childIdx3]['children'][$childIdx4]['children'][] = [
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
                            'expanded' => false,
                            'register' => $node['address'],
                            'folder' => true
                        ];
                        $devices = Device::find()
                            ->where(['nodeUuid' => $node['uuid']])
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
                                'title' => $device['deviceType']['title'] . ' [' . $device['address'] . ']',
                                'status' => '<div class="progress"><div class="'
                                    . $class . '">' . $device['deviceStatus']->title . '</div></div>',
                                'register' => $device['port'] . ' [' . $device['address'] . ']',
                                'uuid' => $device['uuid'],
                                'expanded' => false,
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
                                $measure = Measure::find()->where(['sensorChannelUuid' => $channel['uuid']])
                                    ->orderBy(['_id' => SORT_DESC])->one();
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
            $houses = House::find()
                ->where(['streetUuid' => $street['uuid']])
                ->andWhere(['deleted' => 0])
                ->orderBy('number')
                ->all();
            foreach ($houses as $house) {
                $childIdx = count($fullTree['children']) - 1;
                $fullTree['children'][$childIdx]['children'][] = [
                    'uuid' => $house['uuid'],
                    'type' => 'house',
                    'expanded' => true,
                    'title' => $house->getFullTitle(),
                    'folder' => true
                ];
                $objects = Objects::find()
                    ->where(['houseUuid' => $house['uuid']])
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
                    $nodes = Node::find()
                        ->where(['objectUuid' => $object['uuid']])
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
                        $devices = Device::find()
                            ->where(['nodeUuid' => $node['uuid']])
                            ->andWhere(['deleted' => 0])
                            ->all();
                        if (isset($_GET['type']))
                            $devices = Device::find()
                                ->where(['nodeUuid' => $node['uuid']])
                                ->andWhere(['deviceTypeUuid' => $_GET['type']])
                                ->andWhere(['deleted' => 0])
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
            $houses = House::find()
                ->where(['streetUuid' => $street['uuid']])
                ->andWhere(['deleted' => 0])
                ->orderBy('number')
                ->all();
            foreach ($houses as $house) {
                $objects = Objects::find()
                    ->where(['houseUuid' => $house['uuid']])
                    ->andWhere(['deleted' => 0])
                    ->all();
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
                        ->andWhere(['in', 'deviceTypeUuid', [DeviceType::DEVICE_LIGHT, DeviceType::DEVICE_LIGHT_WITHOUT_ZB]])
                        ->andWhere(['deleted' => 0])
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
                            'deviceTypeUuid' => $device['deviceTypeUuid'],
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
     * Build tree of device
     *
     * @return mixed
     * @throws InvalidConfigException
     */
    public
    function actionTreeGroup()
    {
        ini_set('memory_limit', '-1');
        $fullTree = array();
        $groups = Group::find()
            ->select('*')
            ->orderBy('title')
            ->all();
        foreach ($groups as $group) {
            $fullTree['children'][] = [
                'title' => $group['title'],
                'expanded' => true,
                'source' => '../device/tree-group',
                'address' => '',
                'status' => '',
                'uuid' => $group['uuid'],
                'type' => 'group',
                'folder' => true
            ];
            $devices = DeviceGroup::find()->where(['groupUuid' => $group['uuid']])->all();
            foreach ($devices as $deviceGroup) {
                $childIdx = count($fullTree['children']) - 1;
                if ($deviceGroup['device']['deviceStatusUuid'] == DeviceStatus::NOT_MOUNTED) {
                    $class = 'critical1';
                } elseif ($deviceGroup['device']['deviceStatusUuid'] == DeviceStatus::NOT_WORK) {
                    $class = 'critical2';
                } else {
                    $class = 'critical3';
                }
                $channels = SensorChannel::find()->where(['deviceUuid' => $deviceGroup['device']['uuid']])->count();
                //$config = SensorConfig::find()->where(['sUuid' => $device['uuid']])->count();
                $config = 'конфигурация';
                $programTitle = $deviceGroup->device->getDeviceProgram();
                $programTitle = $programTitle != null ? $programTitle->title : 'не назначена';
                $fullTree['children'][$childIdx]['children'][] = [
                    'title' => $deviceGroup->device->name . ' [' . $deviceGroup->device->serial . ']' . ' (' . $programTitle . ')',
                    'status' => '<div class="progress"><div class="'
                        . $class . '">' . $deviceGroup['device']['deviceStatus']->title . '</div></div>',
                    'register' => $deviceGroup['device']['port'] . ' [' . $deviceGroup['device']['address'] . ']',
                    'address' => $deviceGroup['device']['object']->getFullTitle(),
                    'uuid' => $deviceGroup['device']['uuid'],
                    'source' => '../device/tree-group',
                    'nodes' => $deviceGroup['device']['address'],
                    'type' => 'device',
                    'deviceTypeUuid' => DeviceType::DEVICE_LIGHT,
                    'channels' => $channels,
                    'config' => $config,
                    'date' => $deviceGroup['device']['date'],
                    'folder' => false
                ];
            }
        }
        $fullTree['children'][] = [
            'title' => 'Без группы',
            'expanded' => true,
            'source' => '../device/tree-group',
            'address' => '',
            'status' => '',
            'uuid' => '0',
            'type' => 'group',
            'folder' => true
        ];
        $devices = Device::find()
            ->where(['device.deviceTypeUuid' => DeviceType::DEVICE_LIGHT])
            ->andWhere(['deleted' => 0])
            ->all();
        foreach ($devices as $device) {
            $group = DeviceGroup::find()->where(['deviceUuid' => $device['uuid']])->one();
            if (!$group) {
                $childIdx = count($fullTree['children']) - 1;
                if ($device['deviceStatusUuid'] == DeviceStatus::NOT_MOUNTED) {
                    $class = 'critical1';
                } elseif ($device['deviceStatusUuid'] == DeviceStatus::NOT_WORK) {
                    $class = 'critical2';
                } else {
                    $class = 'critical3';
                }
                $channels = SensorChannel::find()->where(['deviceUuid' => $device['uuid']])->count();
                $config = 'конфигурация';
                $programTitle = $device->getDeviceProgram();
                $programTitle = $programTitle != null ? $programTitle->title : 'не назначена';
                $fullTree['children'][$childIdx]['children'][] = [
                    'title' => $device->name . ' [' . $device->serial . ']' . ' (' . $programTitle . ')',
                    'status' => '<div class="progress"><div class="'
                        . $class . '">' . $device['deviceStatus']->title . '</div></div>',
                    'register' => $device['port'] . ' [' . $device['address'] . ']',
                    'address' => $device['object']->getFullTitle(),
                    'uuid' => $device['uuid'],
                    'source' => '../device/tree-group',
                    'type' => 'device',
                    'deviceTypeUuid' => DeviceType::DEVICE_LIGHT,
                    'nodes' => $device['node']['address'],
                    'channels' => $channels,
                    'config' => $config,
                    'date' => $device['date'],
                    'folder' => false
                ];
            }
        }
        //echo json_encode($fullTree);
        return $this->render('tree-group', ['device' => $fullTree]);
    }

    /**
     * Build tree of device by user
     *
     * @return mixed
     * @throws InvalidConfigException
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
                    $device = new Device();
                    return $this->renderAjax('_add_form', [
                        'device' => $device,
                        'objectUuid' => $objectUuid,
                        'nodeUuid' => null,
                        'deviceTypeUuid' => $deviceTypeUuid,
                        'source' => $source
                    ]);
                    /*                    $node = new Node();
                                        return $this->renderAjax('../node/_add_form', [
                                            'node' => $node,
                                            'objectUuid' => $uuid,
                                            'source' => $source
                                        ]);*/
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
            if ($type == 'channel') {
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
                if (isset($_POST['deviceUuid'])) {
                    $model = Device::find()->where(['uuid' => $_POST['deviceUuid']])->one();
                } else {
                    $model = new Device();
                }

                if ($model->load(Yii::$app->request->post())) {

                    if ($model->save(false) && isset($_POST['deviceUuid'])) {
                        MainFunctions::deviceRegister($model->uuid, "Изменены параметры устройства " . $model['name']);
                        return $this->redirect($source);
                    }

                    MainFunctions::register("Добавлено новое оборудование " . $model['deviceType']['title'] . ' ' .
                        $model->node->object->getAddress() . ' [' . $model->node->address . ']');

                    if ($model['deviceTypeUuid'] == DeviceType::DEVICE_ELECTRO) {
                        self::createChannel($model->uuid, MeasureType::POWER, "Мощность электроэнергии");
                        self::createChannel($model->uuid, MeasureType::CURRENT, "Ток");
                        self::createChannel($model->uuid, MeasureType::VOLTAGE, "Напряжение");
                        self::createChannel($model->uuid, MeasureType::FREQUENCY, "Частота");
                    }

                    if ($model['deviceTypeUuid'] == DeviceType::DEVICE_LIGHT) {
                        //self::createChannel($model->uuid, MeasureType::TEMPERATURE, "Температура");
                    }
                }
            }
            if ($type == 'channel') {
                if (isset($_POST['sensorUuid']))
                    $model = SensorChannel::find()->where(['uuid' => $_POST['sensorUuid']])->one();
                else
                    $model = new SensorChannel();
                if ($model->load(Yii::$app->request->post())) {
                    if ($model->save(false)) {
                        return $this->redirect($source);
                    }
                }
            }
        }
        //return $this->redirect($source);
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
    static function sendConfig($packet, $org_id, $node_id)
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
            $parameter_name = '';
            if ($parameter == DeviceConfig::PARAM_SET_VALUE) $parameter_name = "Уровень освещения";
            if ($parameter == DeviceConfig::PARAM_FREQUENCY) $parameter_name = "Частота выдачи датчиком статуса";
            if ($parameter == DeviceConfig::PARAM_REGIME) $parameter_name = "Режим работы светильника";
            if ($parameter == DeviceConfig::PARAM_POWER) $parameter_name = "Мощность светильника";
            MainFunctions::deviceRegister($deviceUuid, "Изменена конфигурация светильника " . $parameter_name . " " . $value);
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
    static function getParameter($deviceUuid, $parameter)
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
                        $house['deleted'] = 1;
                        $house->save();
                        return 'ok';
                    }
                }
                if ($type == 'object') {
                    $object = Objects::find()->where(['uuid' => $uuid])->one();
                    if ($object) {
                        $object['deleted'] = 1;
                        $object->save();
                        return 'ok';
                    }
                }
                if ($type == 'channel') {
                    $channel = SensorChannel::find()->where(['uuid' => $uuid])->one();
                    if ($channel) {
                        MainFunctions::register("Удален канал измерения " . $channel['title']);
                        $channel->delete();
                        return 'ok';
                    }
                }

                if ($type == 'device') {
                    $device = Device::find()->where(['uuid' => $uuid])->one();
                    if ($device) {
                        if (!$device->lightProgram) {
                            $program = DeviceProgram::find()->one();
                            if ($program)
                                $device->lightProgram = $program['uuid'];
                        }
                        $device['deleted'] = 1;
                        $device->save();
                        MainFunctions::register("Удалено оборудование " . $device['deviceType']['title'] . ' ' .
                            $device->node->object->getAddress() . ' [' . $device->node->address . ']');
                        MainFunctions::deviceRegister($device['uuid'], "Устройство удалено");

                        return json_encode($device->errors);
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
    function actionTrends($uuid)
    {
        $deviceElectro = Device::find()->where(['uuid' => $uuid])->one();
        $parameters1 = [];
        $parameters2 = [];
        $parameters3 = [];

        if ($deviceElectro) {
            $sensorChannel1 = SensorChannel::find()->where(['deviceUuid' => $deviceElectro['uuid']])
                ->andWhere(['measureTypeUuid' => MeasureType::POWER])->one();

            if ($sensorChannel1) {
                $measures = (Measure::find()
                    ->where(['sensorChannelUuid' => $sensorChannel1['uuid']])
                    ->andWhere(['type' => MeasureType::MEASURE_TYPE_INTERVAL])
                    ->orderBy('date DESC'))
                    ->limit(200)->all();

                $cnt = 0;
                $parameters1['uuid'] = $sensorChannel1['_id'];
                $parameters1['trends']['title'] = $sensorChannel1['title'];
                $parameters1['trends']['categories'] = '';
                $parameters1['trends']['values'] = '';
                foreach (array_reverse($measures) as $measure) {
                    if ($cnt > 0) {
                        $parameters1['trends']['categories'] .= ',';
                        $parameters1['trends']['values'] .= ',';
                    }
                    $parameters1['trends']['categories'] .= "'" . date_format(date_create($measure['date']), 'd H:i') . "'";
                    $parameters1['trends']['values'] .= $measure->value;
                    $cnt++;
                }
            }

            $sensorChannel2 = SensorChannel::find()->where(['deviceUuid' => $deviceElectro['uuid']])
                ->andWhere(['measureTypeUuid' => MeasureType::VOLTAGE])->one();
            if ($sensorChannel2) {
                $measures = (Measure::find()
                    ->where(['sensorChannelUuid' => $sensorChannel2['uuid']])
                    ->andWhere(['type' => MeasureType::MEASURE_TYPE_INTERVAL])
                    ->orderBy('date DESC'))
                    ->limit(200)->all();

                $cnt = 0;
                $parameters2['uuid'] = $sensorChannel2['_id'];
                $parameters2['trends']['title'] = $sensorChannel2['title'];
                $parameters2['trends']['categories'] = '';
                $parameters2['trends']['values'] = '';
                foreach (array_reverse($measures) as $measure) {
                    if ($cnt > 0) {
                        $parameters2['trends']['categories'] .= ',';
                        $parameters2['trends']['values'] .= ',';
                    }
                    $parameters2['trends']['categories'] .= "'" . date_format(date_create($measure['date']), 'd H:i') . "'";
                    $parameters2['trends']['values'] .= $measure->value;
                    $cnt++;
                }
            }

            $sensorChannel3 = SensorChannel::find()->where(['deviceUuid' => $deviceElectro['uuid']])
                ->andWhere(['measureTypeUuid' => MeasureType::CURRENT])->one();
            if ($sensorChannel3) {
                $measures = (Measure::find()
                    ->where(['sensorChannelUuid' => $sensorChannel3['uuid']])
                    ->andWhere(['type' => MeasureType::MEASURE_TYPE_INTERVAL])
                    ->orderBy('date DESC'))
                    ->limit(200)->all();

                $cnt = 0;
                $parameters3['uuid'] = $sensorChannel3['_id'];
                $parameters3['trends']['title'] = $sensorChannel3['title'];
                $parameters3['trends']['categories'] = '';
                $parameters3['trends']['values'] = '';
                foreach (array_reverse($measures) as $measure) {
                    if ($cnt > 0) {
                        $parameters3['trends']['categories'] .= ',';
                        $parameters3['trends']['values'] .= ',';
                    }
                    $parameters3['trends']['categories'] .= "'" . date_format(date_create($measure['date']), 'd H:i') . "'";
                    $parameters3['trends']['values'] .= $measure->value;
                    $cnt++;
                }
            }
        }
        return $this->render(
            'trends',
            [
                'device' => $deviceElectro,
                'parameters1' => $parameters1,
                'parameters2' => $parameters2,
                'parameters3' => $parameters3
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
        $deviceRegisters = DeviceRegister::find()->where(['deviceUuid' => $uuid])->orderBy('date DESC');
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

    /**
     * функция отрабатывает сигналы от дерева и выполняет добавление новой группы
     *
     * @return mixed
     */
    public
    function actionGroupAdd()
    {
        $group = new Group();
        return $this->renderAjax('_add_group', ['group' => $group]);
    }

    /**
     * Creates a new Group.
     * @return mixed
     */
    public
    function actionGroupSave()
    {
        if (isset($_POST["source"]))
            $source = $_POST["source"];
        else $source = '../device/tree-group';

        $model = new Group();
        if ($model->load(Yii::$app->request->post())) {
            if ($model->save(false)) {
                MainFunctions::register("Добавлена новая группа светильников " . $model['title']);
                return $this->redirect($source);
            }
        }
        return $this->redirect($source);
    }

    /**
     * Creates a new Group.
     * @return mixed
     * @throws InvalidConfigException
     * @throws StaleObjectException
     * @throws Throwable
     */
    public
    function actionGroupMove()
    {
        if (isset($_POST["from"]) && isset($_POST["to"])) {
            $model = DeviceGroup::find()
                ->where(['deviceUuid' => $_POST['from']])
                ->one();
            if ($model) {
                if ($_POST['to']) {
                    $model = DeviceGroup::find()
                        ->where(['deviceUuid' => $_POST['from']])
                        ->andWhere(['groupUuid' => $_POST['to']])
                        ->one();
                    if (!$model) {
                        $modelGroup = new DeviceGroup();
                        $modelGroup->uuid = MainFunctions::GUID();
                        $modelGroup->oid = User::getOid(Yii::$app->user->identity);
                        $modelGroup->deviceUuid = $_POST["from"];
                        $modelGroup->groupUuid = $_POST["to"];
                        $modelGroup->save();
                        MainFunctions::deviceRegister($modelGroup->deviceUuid,
                            "Светильник перенесен в " . $modelGroup['group']['title']);
                    }
                } else {
                    $model->delete();
                }

            } else {
                $modelGroup = new DeviceGroup();
                $modelGroup->uuid = MainFunctions::GUID();
                $modelGroup->oid = User::getOid(Yii::$app->user->identity);
                $modelGroup->deviceUuid = $_POST["from"];
                $modelGroup->groupUuid = $_POST["to"];
                $modelGroup->save();
                MainFunctions::deviceRegister($modelGroup->deviceUuid,
                    "Светильник перенесен в " . $modelGroup['group']['title']);
            }
        }
        return self::actionTreeGroup();
    }

    /**
     * @param $state (0 - off, 1 - on)
     * @param $device
     */
    public static function contactor($state, $device)
    {
        $contactor = new MtmContactor();
        $contactor->state = $state;
        $pkt = [
            'type' => 'light',
            'address' => $device->address,
            'data' => $contactor->getBase64Data(),
        ];

        $org_id = User::getOid(Yii::$app->user->identity);
        $org_id = Organisation::find()->where(['uuid' => $org_id])->one()->_id;
        $node_id = $device->node->_id;
        self::sendConfig($pkt, $org_id, $node_id);
        if ($state == 0)
            MainFunctions::register("В шкафу " . $device['node']['object']->getFullTitle() . " ВЫКЛЮЧЕН контактор");
        else
            MainFunctions::register("В шкафу " . $device['node']['object']->getFullTitle() . " ВКЛЮЧЕН контактор");
    }

    /**
     * @param $node
     */
    public static function resetCoordinator($node)
    {
        $reset = new MtmResetCoordinator();
        $pkt = [
            'type' => 'light',
            'address' => 0x0000,
            'data' => $reset->getBase64Data(),
        ];

        $org_oid = User::getOid(Yii::$app->user->identity);
        $org_id = Organisation::find()->where(['uuid' => $org_oid])->one()->_id;
        $node_id = $node->_id;
        self::sendConfig($pkt, $org_id, $node_id);

        MainFunctions::register("В шкафу " . $node['object']->getFullTitle() . " сброшен координатор");
    }

    /**
     * @param $nodeUuid
     * @return mixed|null
     * @throws InvalidConfigException
     */
    static function getSumPowerByNode($nodeUuid)
    {
        $devices = Device::find()
            ->where(['nodeUuid' => $nodeUuid])
            ->andWhere(['deviceTypeUuid' => DeviceType::DEVICE_LIGHT])
            ->all();
        $levels = [
            MtmDevLightConfig::$LIGHT_POWER_12 => 12,
            MtmDevLightConfig::$LIGHT_POWER_40 => 40,
            MtmDevLightConfig::$LIGHT_POWER_60 => 60,
            MtmDevLightConfig::$LIGHT_POWER_80 => 80,
            MtmDevLightConfig::$LIGHT_POWER_100 => 100,
            MtmDevLightConfig::$LIGHT_POWER_120 => 120
        ];
        $power = 0;
        foreach ($devices as $device) {
            $level = self::getParameter($device['uuid'], DeviceConfig::PARAM_POWER);
            $power += $levels[intval($level)];
        }
        return $power;
    }

    /**
     * @param $groupUuid
     * @return mixed|null
     * @throws InvalidConfigException
     */
    static function getSumPowerByGroup($groupUuid)
    {
        $devices = DeviceGroup::find()
            ->where(['groupUuid' => $groupUuid])
            ->one();
        $power = 0;
        foreach ($devices as $device) {
            $levels = [
                MtmDevLightConfig::$LIGHT_POWER_12 => 12,
                MtmDevLightConfig::$LIGHT_POWER_40 => 40,
                MtmDevLightConfig::$LIGHT_POWER_60 => 60,
                MtmDevLightConfig::$LIGHT_POWER_80 => 80,
                MtmDevLightConfig::$LIGHT_POWER_100 => 100,
                MtmDevLightConfig::$LIGHT_POWER_120 => 120
            ];
            $level = self::getParameter($device['deviceUuid'], DeviceConfig::PARAM_POWER);
            $power += $levels[intval($level)];
        }
        return $power;
    }

    /**
     * @return mixed|null
     * @throws InvalidConfigException
     */
    public function actionReportGroup()
    {
        $data['group'] = [];
        $group_num = 0;
        $groups = Group::find()->all();
        foreach ($groups as $group) {
            $data['group'][$group_num]['title'] = $group['title'];
            $data['group'][$group_num]['month'] = [];
            for ($mon = 0; $mon < 12; $mon++) {
                $data['group'][$group_num]['month'][$mon]['date'] = '-';
                $data['group'][$group_num]['month'][$mon]['w1'] = 0;
                $data['group'][$group_num]['month'][$mon]['w2'] = 0;
                $data['group'][$group_num]['month'][$mon]['w3'] = 0;
                $data['group'][$group_num]['month'][$mon]['w4'] = 0;
                $data['group'][$group_num]['month'][$mon]['ws'] = 0;
            }
            $devices = DeviceGroup::find()
                ->where(['groupUuid' => $group['uuid']])
                ->all();
            foreach ($devices as $device) {
                $sumPower = self::getSumPowerByNode($device['device']['nodeUuid']);
                $levels = [
                    MtmDevLightConfig::$LIGHT_POWER_12 => 12,
                    MtmDevLightConfig::$LIGHT_POWER_40 => 40,
                    MtmDevLightConfig::$LIGHT_POWER_60 => 60,
                    MtmDevLightConfig::$LIGHT_POWER_80 => 80,
                    MtmDevLightConfig::$LIGHT_POWER_100 => 100,
                    MtmDevLightConfig::$LIGHT_POWER_120 => 120
                ];
                $level = self::getParameter($device['device']['uuid'], DeviceConfig::PARAM_POWER);
                $power = $levels[intval($level)];
                $knt = 0;
                if ($sumPower)
                    $knt = $power / $sumPower;
                $counter = Device::find()
                    ->where(['nodeUuid' => $device['device']['nodeUuid']])
                    ->andWhere(['deviceTypeUuid' => DeviceType::DEVICE_ELECTRO])
                    ->one();
                if ($counter && $knt > 0) {
                    $sChannel = SensorChannel::find()
                        ->where(['deviceUuid' => $counter['uuid'], 'measureTypeUuid' => MeasureType::POWER])
                        ->one();
                    if ($sChannel) {
                        for ($mon = 0; $mon < 12; $mon++) {
                            if ($mon > 0) {
                                $month = date("Ym01000000", strtotime("-" . $mon . " months"));
                                $data['group'][$group_num]['month'][$mon]['date'] = date("Y-m-01", strtotime("-" . $mon . " months"));
                            } else {
                                $month = date("Ym01000000");
                                $data['group'][$group_num]['month'][$mon]['date'] = date("Y-m-01");
                            }
                            $last_measures = Measure::find()
                                ->where(['sensorChannelUuid' => $sChannel['uuid']])
                                ->andWhere(['date' => $month])
                                ->andWhere(['type' => MeasureType::MEASURE_TYPE_MONTH])
                                ->all();
                            foreach ($last_measures as $measure) {
                                $value = $measure['value'] * $knt;
                                if ($measure['parameter'] == 1)
                                    $data['group'][$group_num]['month'][$mon]['w1'] += $value;
                                if ($measure['parameter'] == 2)
                                    $data['group'][$group_num]['month'][$mon]['w2'] += $value;
                                if ($measure['parameter'] == 3)
                                    $data['group'][$group_num]['month'][$mon]['w3'] += $value;
                                if ($measure['parameter'] == 4)
                                    $data['group'][$group_num]['month'][$mon]['w4'] += $value;
                                if ($measure['parameter'] > 0)
                                    $data['group'][$group_num]['month'][$mon]['ws'] += $value;
                            }
                        }
                    }
                }
            }
            $group_num++;
        }
        return $this->render(
            'report-group',
            [
                'dataAll' => $data
            ]
        );
    }

    /**
     * функция отрабатывает сигналы от дерева и выполняет добавление нового светильника
     *
     * @return mixed
     */
    public
    function actionNewLight()
    {
        if (!Yii::$app->user->can(User::PERMISSION_ADMIN)) {
            return 'Нет прав.';
        }
        if (isset($_POST["latitude"]))
            $latitude = $_POST["latitude"];
        else $latitude = 0;
        if (isset($_POST["longitude"]))
            $longitude = $_POST["longitude"];
        else $longitude = 0;

        $device = new Device();
        $object = new Objects();
        return $this->renderAjax('_add_form_light', [
            'device' => $device,
            'object' => $object,
            'latitude' => $latitude,
            'longitude' => $longitude
        ]);
    }

    /**
     * Creates a new Device model.
     * @return mixed
     * @throws InvalidConfigException
     */
    public
    function actionSaveLight()
    {
        if (!Yii::$app->user->can(User::PERMISSION_ADMIN)) {
            return 'Нет прав.';
        }
        if ($_POST['street']) {
            $house = House::find()
                ->where(['streetUuid' => $_POST['street']])
                ->andWhere(['houseTypeUuid' => HouseType::HOUSE_TYPE_NO_NUMBER])
                ->one();
            if (!$house) {
                $house = new House();
                $house->uuid = (new MainFunctions)->GUID();
                $house->number = '-';
                $house->oid = User::getOid(Yii::$app->user->identity);
                $house->streetUuid = $_POST['street'];
                $house->houseTypeUuid = HouseType::HOUSE_TYPE_NO_NUMBER;
                $house->save();
            }

            $object = new Objects();
            if ($object->load(Yii::$app->request->post())) {
                $object->houseUuid = $house['uuid'];
                if ($object->save(false)) {
                    $model = new Device();
                    if ($model->load(Yii::$app->request->post())) {
                        $model->objectUuid = $object['uuid'];
                        $model->save(false);
                        MainFunctions::deviceRegister($model->uuid, "Добавлен новый светильник по адресу " . $object->getAddress());
                    }
                }
            }
        }
        return $this->redirect("../site/index");
    }

    /**
     * @throws InvalidConfigException
     */
    public
    function actionDate()
    {
        if (isset($_POST["group"]) || isset($_POST["event_start"])
            || isset($_POST["event_end"]) || isset($_POST["type"])) {
            $date = date("Y-m-d H:i:00", strtotime($_POST["event_start"]));
            $groupControl = GroupControl::find()
                ->where(['groupUuid' => $_POST["group"]])
                ->andWhere(['date' => $date])
                ->andWhere(['type' => $_POST["type"]])
                ->one();
            if ($groupControl) {
                $groupControl['date'] = date("Y-m-d H:i:00", strtotime($_POST["event_end"]));
                $groupControl->save();
                MainFunctions::register("Изменено расписание для " . $groupControl['group']['title'] . "
                    (" . $_POST["event_start"] . ") > (" . $_POST["event_end"] . ")");
            } else {
                $groupControl = new GroupControl();
                $groupControl->groupUuid = $_POST["group"];
                $groupControl->oid = User::getOid(Yii::$app->user->identity);
                $groupControl->uuid = MainFunctions::GUID();
                $groupControl->type = $_POST["type"];
                $groupControl->date = date("Y-m-d H:i:00", strtotime($_POST["event_end"]));
                $groupControl->save();
                MainFunctions::register("Изменено расписание для " . $groupControl['group']['title'] . "
                    (" . $_POST["event_start"] . ") > (" . $_POST["event_end"] . ")");
            }
        }
    }
}