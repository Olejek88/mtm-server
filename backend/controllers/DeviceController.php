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
use Yii;
use yii\db\StaleObjectException;
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

        if (Yii::$app->getUser()->isGuest) {
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
     * @throws NotFoundHttpException
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
        return $this->render(
            'tree-user'
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
     * Deletes an existing Device model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     *
     * @param integer $id Id
     *
     * @return mixed
     * @throws NotFoundHttpException
     * @throws \Throwable
     * @throws StaleObjectException
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
