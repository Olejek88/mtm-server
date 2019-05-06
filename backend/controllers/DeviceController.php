<?php

namespace backend\controllers;

use backend\models\DeviceSearch;
use common\components\MainFunctions;
use common\models\Device;
use common\models\DeviceStatus;
use common\models\DeviceType;
use common\models\Objects;
use common\models\House;
use common\models\Measure;
use common\models\Message;
use common\models\Photo;
use common\models\Street;
use common\models\Users;
use Yii;
use yii\filters\VerbFilter;
use yii\helpers\Html;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\UnauthorizedHttpException;

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
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Init
     *
     * @return void
     * @throws UnauthorizedHttpException
     */
    public function init()
    {

        if (\Yii::$app->getUser()->isGuest) {
            throw new UnauthorizedHttpException();
        }

    }

    /**
     * Lists all Device models.
     *
     * @return mixed
     */
    public function actionIndex()
    {
        if (isset($_POST['editableAttribute'])) {
            $model = Device::find()
                ->where(['_id' => $_POST['editableKey']])
                ->one();
            if ($_POST['editableAttribute'] == 'serial') {
                $model['serial'] = $_POST['Device'][$_POST['editableIndex']]['serial'];
            }
            if ($_POST['editableAttribute'] == 'port') {
                $model['tag'] = $_POST['Device'][$_POST['editableIndex']]['port'];
            }
            if ($_POST['editableAttribute'] == 'deviceTypeUuid') {
                $model['deviceTypeUuid'] = $_POST['Device'][$_POST['editableIndex']]['deviceTypeUuid'];
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
     * Displays a single Device model.
     *
     * @param integer $id Id
     *
     * @return mixed
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
        $model = new Device();

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
     * Creates a new Device models.
     *
     * @return mixed
     */
    public function actionNew()
    {
        $devices = array();
        $device_count = 0;
        $objects = Objects::find()
            ->select('*')
            ->all();
        foreach ($objects as $object) {
            $device = Device::find()
                ->select('*')
                ->where(['objectUuid' => $object['uuid']])
                ->one();
            if ($device == null) {
                $device = new Device();
                $device->uuid = MainFunctions::GUID();
                $device->nodeUuid = $object['uuid'];
                $device->deviceTypeUuid = DeviceType::EQUIPMENT_HVS;
                $device->deviceStatusUuid = DeviceStatus::UNKNOWN;
                $device->serial = '222222';
                $device->interface = 1;
                $device->date = date('Y-m-d H:i:s');
                $device->changedAt = date('Y-m-d H:i:s');
                $device->createdAt = date('Y-m-d H:i:s');
                $device->save();
                $devices[$device_count] = $device;
                $device_count++;
            } else {
                if ($device['deviceTypeUuid'] != DeviceType::EQUIPMENT_HVS) {
                    $device['deviceTypeUuid'] = DeviceType::EQUIPMENT_HVS;
                    $device['changedAt'] = date('Y-m-d H:i:s');
                    $device->save();
                    echo $device['uuid'] . '<br/>';
                }
            }
        }
        return $this->render('new', ['devices' => $devices]);
    }


    /**
     * Updates an existing Device model.
     * If update is successful, the browser will be redirected to the 'view' page.
     *
     * @param integer $id Id
     *
     * @return mixed
     */
    public function actionUpdate($id)
    {
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
     * Build tree of device
     *
     * @return mixed
     */
    public function actionTree()
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
                ['device-type/view', 'id' => $type['_id']]
            );
            $devices = Device::find()
                ->select('*')
                ->where(['deviceTypeUuid' => $type['uuid']])
                ->orderBy('serial')
                ->all();
            $oCnt1 = 0;
            foreach ($devices as $device) {
                $fullTree[$oCnt0][$c][$oCnt1]['title']
                    = Html::a(
                    'ул.' . $device['house']['street']['title'] . ', д.' . $device['house']['number'] . ', кв.' . $device['flat']['number'],
                    ['device/view', 'id' => $device['_id']]
                );
                if ($device['deviceStatusUuid'] == DeviceStatus::NOT_MOUNTED) {
                    $class = 'critical1';
                } elseif ($device['deviceStatusUuid'] == DeviceStatus::NOT_WORK) {
                    $class = 'critical2';
                } else {
                    $class = 'critical3';
                }
                $fullTree[$oCnt0][$c][$oCnt1]['status'] = '<div class="progress"><div class="'
                    . $class . '">' . $device['deviceStatus']->title . '</div></div>';
                $fullTree[$oCnt0][$c][$oCnt1]['date'] = $device['testDate'];
                $fullTree[$oCnt0][$c][$oCnt1]['serial'] = $device['serial'];

                $measure = Measure::find()
                    ->select('*')
                    ->where(['deviceUuid' => $device['uuid']])
                    ->orderBy('date DESC')
                    ->one();
                if ($measure) {
                    $fullTree[$oCnt0][$c][$oCnt1]['measure_date'] = $measure['date'];
                    $fullTree[$oCnt0][$c][$oCnt1]['measure_value'] = $measure['value'];
                    $fullTree[$oCnt0][$c][$oCnt1]['measure_user'] = $measure['user']->name;
                } else {
                    $fullTree[$oCnt0][$c][$oCnt1]['measure_date'] = $device['changedAt'];
                    $fullTree[$oCnt0][$c][$oCnt1]['measure_value'] = "не снимались";
                    $fullTree[$oCnt0][$c][$oCnt1]['measure_user'] = "-";
                }

                $photo = Photo::find()
                    ->select('*')
                    ->where(['objectUuid' => $device['uuid']])
                    ->orderBy('createdAt DESC')
                    ->one();
                if ($photo) {
                    $fullTree[$oCnt0][$c][$oCnt1]['photo_date'] = $photo['createdAt'];
                    $fullTree[$oCnt0][$c][$oCnt1]['photo'] = Html::a(
                        '<img width="100px" src="/storage/device/' . $photo['uuid'] . '.jpg" />',
                        ['storage/device/' . $photo['uuid'] . '.jpg']
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
            ['device' => $fullTree]
        );
    }

    /**
     * Build tree of device by user
     *
     * @param integer $id Id
     * @param $date_start
     * @param $date_end
     * @return mixed
     */
    public function actionTable($id, $date_start, $date_end)
    {
        ini_set('memory_limit', '-1');
        $c = 'children';
        $fullTree = array();
        $user = Users::find()
            ->select('*')
            ->where(['id' => $id])
            ->one();
        if ($user) {
            $oCnt1 = 0;
            $gut_total_count = 0;
            $object_count = 0;
            $user_houses = UserHouse::find()->select('houseUuid')->where(['userUuid' => $user['uuid']])->all();
            foreach ($user_houses as $user_house) {
                $flats = Objects::find()->select('uuid')->where(['houseUuid' => $user_house['houseUuid']])->all();
                foreach ($flats as $flat) {
                    $device = Device::find()
                        ->select('*')
                        ->where(['flatUuid' => $flat['uuid']])
                        ->orderBy('changedAt desc')
                        ->one();
                    if ($device) {
                        $gut=0;
                        $object_count++;
                        $fullTree[$oCnt1]['title']
                            = Html::a(
                            'ул.' . $device['house']['street']['title'] . ', д.' .
                            $device['house']['number'] . ', кв.' . $device['flat']['number'],
                            ['device/view', 'id' => $device['_id']]
                        );

                        $measures = Measure::find()
                            ->where(['deviceUuid' => $device['uuid']])
                            ->orderBy('date DESC')
                            ->all();
                        $oCnt2=0;
                        // есть измерение, есть/нет фото
                        foreach ($measures as $measure) {
                            $fullTree[$oCnt1][$c][$oCnt2]
                                ['measure_date'] = $measure['date'];
                            $fullTree[$oCnt1][$c][$oCnt2]
                                ['measure'] = $measure['value'];
                            $photo_flat_count = Photo::find()
                                ->where(['objectUuid' => $device['uuid']])
                                ->andWhere(['date' >= ($measure['date']-3600)])
                                ->andWhere(['date' < ($measure['date']+3600)])
                                ->count();

                            if ($photo_flat_count>0) {
                                $class = 'critical3';
                                $status = 'А. Отличное';
                            } else {
                                $class = 'critical2';
                                $status = 'Б. Удовлетворительное';
                            }
                            $fullTree[$oCnt1][$c][$oCnt2]['status'] = '<div class="progress"><div class="'
                                . $class . '">' . $status . '</div></div>';
                            $gut=1;
                            $oCnt2++;
                        }

                        // есть комментарий,  нет измерения, есть/нет фото
                        $messages = Message::find()
                            ->where(['flatUuid' => $device['flat']['uuid']])
                            ->orderBy('date DESC')
                            ->all();
                        foreach ($messages as $message) {
                            $measure_count = Measure::find()
                                ->where(['deviceUuid' => $device['uuid']])
                                ->andWhere(['date' >= ($message['date'] - 1200)])
                                ->andWhere(['date' < ($message['date'] + 1200)])
                                ->count();

                            $photo_flat_count = Photo::find()
                                ->where(['objectUuid' => $device['uuid']])
                                ->andWhere(['date' >= ($message['date'] - 1200)])
                                ->andWhere(['date' < ($message['date'] + 1200)])
                                ->count();

                            if ($measure_count==0 && $photo_flat_count>0) {
                                $fullTree[$oCnt1][$c][$oCnt2]
                                ['measure_date'] = $message['date'];
                                $fullTree[$oCnt1][$c][$oCnt2]
                                ['measure'] = 'есть сообщение';
                                $class = 'critical3';
                                $status = 'Отличное';
                                $fullTree[$oCnt1][$c][$oCnt2]['status'] = '<div class="progress"><div class="'
                                    . $class . '">Б.' . $status . '</div></div>';
                                $oCnt2++;
                                $gut=1;
                            }
                        }

                        if ($gut==0) {
                            $class = 'critical1';
                            $status = 'Не удовлетворительное';
                            $fullTree[$oCnt1][$c][$oCnt2]['status'] = '<div class="progress"><div class="'
                                . $class . '">А.' . $status . '</div></div>';
                        } else
                            $gut_total_count++;
                    }
                }
                $fullTree[$oCnt1]['title'] = 'Всего';
                $percent = 0;
                if ($object_count>0)
                    $percent = number_format($gut_total_count*100/$object_count,2);
                $fullTree[$oCnt1]['measure_date'] = 'Показаний: ' . $gut_total_count . '[' . $percent . ']';
            }
        }
        return $this->render(
            'tree-user',
            ['device' => $fullTree]
        );
    }

    /**
     * Build tree of device by user
     *
     * @return mixed
     */
    public function actionTreeUser()
    {
        ini_set('memory_limit', '-1');
        $c = 'children';
        $fullTree = array();
        $users = Users::find()
            ->select('*')
            ->where('name != "sUser"')
            ->andWhere('name != "Иванов О.А."')
            ->orderBy('_id')
            ->all();
        $oCnt0 = 0;
        foreach ($users as $user) {
            $fullTree[$oCnt0]['title'] = Html::a(
                $user['name'],
                ['user/view', 'id' => $user['_id']]
            );
            /*            $query = Device::find()
                            ->select('*')
                            ->where(['flatUuid' => (
                                Flat::find()->select('uuid')->where(['houseUuid' => (
                                    UserHouse::find()->select('houseUuid')->where(['userUuid' => $user['uuid']])->all()
                                )]))]);
                        //$query->with('house');
                        $devices = $query->orderBy('changedAt')->groupBy('flatUuid')->all();*/
            $oCnt1 = 0;
            $measure_total_count = 0;
            $measure_count = 0;
            $photo_count = 0;
            $message_count = 0;
            $user_houses = UserHouse::find()->select('houseUuid')->where(['userUuid' => $user['uuid']])->all();
            foreach ($user_houses as $user_house) {
                $flats = Objects::find()->select('uuid')->where(['houseUuid' => $user_house['houseUuid']])->all();
                foreach ($flats as $flat) {
                    $device = Device::find()
                        ->select('*')
                        ->where(['flatUuid' => $flat['uuid']])
                        ->orderBy('changedAt desc')
                        ->one();
                    if ($device) {
                        $fullTree[$oCnt0][$c][$oCnt1]['title']
                            = Html::a(
                            'ул.' . $device['house']['street']['title'] . ', д.' .
                            $device['house']['number'] . ', кв.' . $device['flat']['number'],
                            ['device/view', 'id' => $device['_id']]
                        );

                        $message_flat_count = Message::find()
                            ->where(['flatUuid' => $device['flat']['uuid']])
                            ->count();
                        $photo_flat_count = Photo::find()
                            ->where(['objectUuid' => $device['uuid']])
                            ->count();

                        $message = Message::find()
                            ->select('*')
                            ->orderBy('date DESC')
                            ->where(['flatUuid' => $device['flat']['uuid']])
                            ->one();
                        $message_text = '[' . $photo_flat_count . '/' . $message_flat_count . '] ['.$message['date'].']';
                        if ($message != null) {
                            $message_text .= substr($message['message'], 0, 150);
                            $message_count++;
                        }
                        $fullTree[$oCnt0][$c][$oCnt1]['message'] = mb_convert_encoding($message_text, 'UTF-8', 'UTF-8');

                        $photo = Photo::find()
                            ->select('*')
                            ->where(['objectUuid' => $device['uuid']])
                            ->orderBy('createdAt DESC')
                            ->one();
                        if ($photo) {
                            $fullTree[$oCnt0][$c][$oCnt1]['photo'] = Html::a($photo['createdAt'],
                                ['storage/device/' . $photo['uuid'] . '.jpg']
                            );
                            $fullTree[$oCnt0][$c][$oCnt1]['photo_user'] = $photo['user']->name;
                            $photo_count++;
                        } else {
                            $fullTree[$oCnt0][$c][$oCnt1]['photo'] = '-';
                            $fullTree[$oCnt0][$c][$oCnt1]['photo_user'] = '-';
                        }

                        $measures = Measure::find()
                            ->select('*')
                            ->where(['deviceUuid' => $device['uuid']])
                            ->orderBy('date')
                            ->all();
                        $measure_count_column=0;
                        $fullTree[$oCnt0][$c][$oCnt1]['measure_date0'] = '';
                        $fullTree[$oCnt0][$c][$oCnt1]['measure_value0'] = '';
                        $fullTree[$oCnt0][$c][$oCnt1]['measure_date1'] = '';
                        $fullTree[$oCnt0][$c][$oCnt1]['measure_value1'] = '';
                        $fullTree[$oCnt0][$c][$oCnt1]['measure_date2'] = '';
                        $fullTree[$oCnt0][$c][$oCnt1]['measure_value2'] = '';
                        $fullTree[$oCnt0][$c][$oCnt1]['measure_date3'] = '';
                        $fullTree[$oCnt0][$c][$oCnt1]['measure_value3'] = '';
                        $fullTree[$oCnt0][$c][$oCnt1]['measure_user'] = '';

                        $measure=null;
                        foreach ($measures as $measure) {
                            $fullTree[$oCnt0][$c][$oCnt1]['measure_date'.$measure_count_column] = $measure['date'];
                            $fullTree[$oCnt0][$c][$oCnt1]['measure_value'.$measure_count_column] = $measure['value'];
                            $fullTree[$oCnt0][$c][$oCnt1]['measure_user'] = $measure['user']->name;
                            $measure_count_column++;
                            if ($measure_count_column>3) break;
                            $measure_total_count++;
                        }

                        if ($device['deviceStatusUuid'] == DeviceStatus::NOT_MOUNTED) {
                            $class = 'critical1';
                        } elseif ($device['deviceStatusUuid'] == DeviceStatus::NOT_WORK) {
                            $class = 'critical2';
                        } elseif ($device['deviceStatusUuid'] == DeviceStatus::UNKNOWN) {
                            $class = 'critical4';
                        } else {
                            $class = 'critical3';
                        }

                        $status = $device['deviceStatus']->title;
                        if ($measure) {
                            //echo $measure['date'].' | '.time() .'-'. strtotime($measure['date']). ' < ' . (3600 * 24 * 7 * 1).'<br/>';
                            if (time() - strtotime($measure['date']) > (3600 * 24 * 7 * 1)) {
                                $class = 'critical2';
                                $status = 'Посещался';
                            } else
                                $measure_count++;
                        } else {
                            if ($message != null) {
                                $class = 'critical4';
                                $status = 'Не попали';
                            } else {
                                if ($photo == null) {
                                    $class = 'critical1';
                                    $status = 'Не посещался';
                                } else {
                                    $class = 'critical2';
                                    $status = 'Нет показаний';
                                }
                            }
                        }

                        $fullTree[$oCnt0][$c][$oCnt1]['status'] = '<div class="progress"><div class="'
                            . $class . '">' . $status . '</div></div>';

                        $fullTree[$oCnt0][$c][$oCnt1]['date'] = $device['testDate'];
                        $fullTree[$oCnt0][$c][$oCnt1]['serial'] = $device['serial'];

                        $oCnt1++;
                    }
                }
                if ($oCnt1 > 0) {
                    if ($oCnt1 > 0) {
                        $ok = $measure_count * 100 / $oCnt1;
                        if ($ok < 20) {
                            $fullTree[$oCnt0]['status'] = '<div class="progress"><div class="critical1">Очень плохо</div></div>';
                        } elseif ($ok < 45) {
                            $fullTree[$oCnt0]['status'] = '<div class="progress"><div class="critical2">Плохо</div></div>';
                        } elseif ($ok < 70) {
                            $fullTree[$oCnt0]['status'] = '<div class="progress"><div class="critical4">Средне</div></div>';
                        } else {
                            $fullTree[$oCnt0]['status'] = '<div class="progress"><div class="critical3">Хорошо</div></div>';
                        }
                    }
                    $fullTree[$oCnt0]['measure_date0'] = 'Показаний: ' . $measure_count . '[' . $measure_total_count . ']';
                    $fullTree[$oCnt0]['measure_value0'] = $measure_count . '[' . number_format($measure_count * 100 / $oCnt1, 2) . '%]';
                    $fullTree[$oCnt0]['photo'] = $photo_count . '[' . number_format($photo_count * 100 / $oCnt1, 2) . '%]';
                    $fullTree[$oCnt0]['message'] = $message_count . '[' . number_format($message_count * 100 / $oCnt1, 2) . '%]';
                }
            }
            $oCnt0++;
        }
        return $this->render(
            'tree-user',
            ['device' => $fullTree]
        );
    }

    /**
     * Build tree of device by user
     *
     * @return mixed
     */
    public function actionTreeStreet()
    {
        ini_set('memory_limit', '-1');
        $c = 'children';
        $fullTree = array();
        $streets = Street::find()
            ->select('*')
            ->orderBy('title')
            ->all();
        $oCnt0 = 0;
        foreach ($streets as $street) {
            $last_user = '';
            $last_date = '';
            $house_count = 0;
            $house_visited = 0;
            $photo_count = 0;
            $fullTree[$oCnt0]['title'] = Html::a(
                $street['title'],
                ['street/view', 'id' => $street['_id']]
            );
            $oCnt1 = 0;
            $houses = House::find()->select('uuid,number')->where(['streetUuid' => $street['uuid']])->
            orderBy('number')->all();
            foreach ($houses as $house) {
                $user_house = UserHouse::find()->select('_id')->where(['houseUuid' => $house['uuid']])->one();
                $user = Users::find()->where(['uuid' =>
                    UserHouse::find()->where(['houseUuid' => $house['uuid']])->one()
                ])->one();
                $flats = Objects::find()->select('uuid,number')->where(['houseUuid' => $house['uuid']])->all();
                foreach ($flats as $flat) {
                    $house_count++;
                    $visited = 0;
                    $devices = Device::find()->where(['flatUuid' => $flat['uuid']])->all();
                    foreach ($devices as $device) {
                        $fullTree[$oCnt0][$c][$oCnt1]['title']
                            = Html::a(
                            'ул.' . $device['house']['street']['title'] . ', д.' . $device['house']['number'] . ', кв.' . $device['flat']['number'],
                            ['device/view', 'id' => $device['_id']]
                        );

                        if ($user != null)
                            $fullTree[$oCnt0][$c][$oCnt1]['user'] = Html::a(
                                $user['name'],
                                ['user-house/delete', 'id' => $user_house['_id']], ['target' => '_blank']
                            );

                        if ($device['deviceStatusUuid'] == DeviceStatus::NOT_MOUNTED) {
                            $class = 'critical1';
                        } elseif ($device['deviceStatusUuid'] == DeviceStatus::NOT_WORK) {
                            $class = 'critical2';
                        } elseif ($device['deviceStatusUuid'] == DeviceStatus::UNKNOWN) {
                            $class = 'critical4';
                        } else {
                            $class = 'critical3';
                        }
                        $fullTree[$oCnt0][$c][$oCnt1]['status'] = '<div class="progress"><div class="'
                            . $class . '">' . $device['deviceStatus']->title . '</div></div>';
                        $fullTree[$oCnt0][$c][$oCnt1]['date'] = $device['testDate'];
                        //$fullTree[$oCnt0][$c][$oCnt1]['serial'] = $device['serial'];

                        $measure = Measure::find()
                            ->select('*')
                            ->where(['deviceUuid' => $device['uuid']])
                            ->orderBy('date DESC')
                            ->one();
                        if ($measure) {
                            $fullTree[$oCnt0][$c][$oCnt1]['measure_date'] = $measure['date'];
                            $fullTree[$oCnt0][$c][$oCnt1]['measure_value'] = $measure['value'];
                            $fullTree[$oCnt0][$c][$oCnt1]['measure_user'] = $measure['user']->name;
                            $last_user = $measure['user']->name;
                            $last_date = $measure['date'];
                            $house_visited++;
                            $visited++;
                        } else {
                            $fullTree[$oCnt0][$c][$oCnt1]['measure_date'] = $device['changedAt'];
                            $fullTree[$oCnt0][$c][$oCnt1]['measure_value'] = "не снимались";
                            $fullTree[$oCnt0][$c][$oCnt1]['measure_user'] = "-";
                        }

                        $message = Message::find()
                            ->select('*')
                            ->orderBy('date DESC')
                            ->where(['flatUuid' => $device['flat']['uuid']])
                            ->one();
                        if ($message != null) {
                            $fullTree[$oCnt0][$c][$oCnt1]['message'] =
                                mb_convert_encoding(substr($message['message'], 0, 150), 'UTF-8', 'UTF-8');
                            if ($visited == 0)
                                $visited = 1;
                            $house_visited++;
                        }

                        $photo = Photo::find()
                            ->select('*')
                            ->where(['objectuid' => $device['uuid']])
                            ->orderBy('createdAt DESC')
                            ->one();
                        if ($photo) {
                            $fullTree[$oCnt0][$c][$oCnt1]['photo_date'] = $photo['createdAt'];
                            $fullTree[$oCnt0][$c][$oCnt1]['photo'] = Html::a('фото',
                                ['storage/device/' . $photo['uuid'] . '.jpg']
                            );
                            $fullTree[$oCnt0][$c][$oCnt1]['photo_user'] = $photo['user']->name;
                            $last_user = $photo['user']->name;
                            $photo_count++;
                            if ($visited == 0) {
                                $visited = 1;
                                $house_visited++;
                            }
                        } else {
                            $fullTree[$oCnt0][$c][$oCnt1]['photo_date'] = 'нет фото';
                            $fullTree[$oCnt0][$c][$oCnt1]['photo'] = '-';
                            $fullTree[$oCnt0][$c][$oCnt1]['photo_user'] = '-';
                        }
                        $oCnt1++;
                    }
                }
            }
            $fullTree[$oCnt0]['measure_user'] = $last_user;
            $fullTree[$oCnt0]['measure_date'] = $last_date;
            $fullTree[$oCnt0]['photo_user'] = $last_user;
            $fullTree[$oCnt0]['photo_date'] = $last_date;
            $fullTree[$oCnt0]['photo'] = $photo_count;
            $ok = 0;
            if ($house_count > 0)
                $ok = $house_visited * 100 / $house_count;
            if ($ok > 100) $ok = 100;
            if ($ok < 20) {
                $fullTree[$oCnt0]['status'] = '<div class="progress"><div class="critical1">' .
                    number_format($ok, 2) . '%</div></div>';
            } elseif ($ok < 45) {
                $fullTree[$oCnt0]['status'] = '<div class="progress"><div class="critical2">' .
                    number_format($ok, 2) . '%</div></div>';
            } elseif ($ok < 70) {
                $fullTree[$oCnt0]['status'] = '<div class="progress"><div class="critical4">' .
                    number_format($ok, 2) . '%</div></div>';
            } else {
                $fullTree[$oCnt0]['status'] = '<div class="progress"><div class="critical3">' .
                    number_format($ok, 2) . '%</div></div>';
            }
            $oCnt0++;
        }
        return $this->render(
            'tree-street',
            ['device' => $fullTree]
        );
    }

    /**
     * Build tree of device by user
     *
     * @return mixed
     */
    public function actionTreeMeasure()
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
                    $devices = Device::find()->where(['objectUuid' => $object['uuid']])->all();
                    foreach ($devices as $device) {
                        $fullTree[$oCnt0]['title']
                            = Html::a(
                            'ул.' . $device['house']['street']['title'] . ', д.' . $device['house']['number'] . ', кв.' . $device['object']['number'],
                            ['device/view', 'id' => $device['_id']]
                        );

                        $measures = Measure::find()
                            ->select('*')
                            ->where(['deviceUuid' => $device['uuid']])
                            ->orderBy('date')
                            ->all();

                        $measure_count_column=0;
                        $fullTree[$oCnt0]['measure_date0'] = '';
                        $fullTree[$oCnt0]['measure_value0'] = '';
                        $fullTree[$oCnt0]['measure_date1'] = '';
                        $fullTree[$oCnt0]['measure_value1'] = '';
                        $fullTree[$oCnt0]['measure_date2'] = '';
                        $fullTree[$oCnt0]['measure_value2'] = '';
                        $fullTree[$oCnt0]['measure_date3'] = '';
                        $fullTree[$oCnt0]['measure_value3'] = '';
                        $fullTree[$oCnt0]['measure_user'] = '';
                        $measure_first=0;
                        $measure_last=0;
                        $measure_date_first=0;
                        $measure_date_last=0;
                        foreach ($measures as $measure) {
                            $fullTree[$oCnt0]['measure_date'.$measure_count_column] = $measure['date'];
                            $fullTree[$oCnt0]['measure_value'.$measure_count_column] = $measure['value'];
                            $fullTree[$oCnt0]['measure_user'] = $measure['user']->name;
                            if ($measure_count_column==0) {
                                $measure_first = $measure['value'];
                                $measure_date_first = $measure['date'];
                            }
                            else {
                                $measure_last=$measure['value'];
                                $measure_date_last = $measure['date'];
                            }
                            $measure_count_column++;
                            if ($measure_count_column>3) break;
                        }

                        $datetime1 = date_create($measure_date_first);
                        $datetime2 = date_create($measure_date_last);
                        if ($datetime2 && $datetime1) {
                            $diff = $datetime2->diff($datetime1);
                            $interval = $diff->format("%h")+($diff->days*24);
                            $value = number_format($measure_last-$measure_first,2);
                        }
                        else {
                            $interval = 0;
                            $value=0;
                        }
                        $fullTree[$oCnt0]['interval'] = $interval;
                        $fullTree[$oCnt0]['value'] = $value;
                        if ($interval>0)
                            $fullTree[$oCnt0]['relative'] = number_format($value/$interval,2);

                        $message = Message::find()
                            ->select('*')
                            ->orderBy('date DESC')
                            ->where(['flatUuid' => $device['flat']['uuid']])
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
            ['device' => $fullTree]
        );
    }

    /**
     * Deletes an existing Device model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     *
     * @param integer $id Id
     *
     * @return mixed
     */
    public
    function actionDelete($id)
    {
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
}
