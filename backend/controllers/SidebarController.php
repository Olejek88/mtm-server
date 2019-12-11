<?php

namespace backend\controllers;

use common\models\Camera;
use common\models\Device;
use common\models\DeviceType;
use common\models\MeasureType;
use common\models\Node;
use common\models\Objects;
use common\models\Organisation;
use common\models\SensorChannel;
use common\models\Street;
use common\models\User;
use Yii;

$accountUser = Yii::$app->user->identity;

$currentUser = User::findOne(['_id' => $accountUser['id']]);
Yii::$app->view->params['currentUser'] = $currentUser;
$userImage = Yii::$app->request->baseUrl . '/images/unknown2.png';

Yii::$app->view->params['userImage'] = $userImage;

$user_id = User::getOid(Yii::$app->user->identity);
$org = Organisation::find()->where(['uuid' => $user_id])->one();

$counts['street'] = Street::find()->where(['deleted' => 0])->count();
$counts['objects'] = Objects::find()->where(['deleted' => 0])->count();
$counts['device'] = Device::find()->where(['deleted' => 0])->count();
$counts['camera'] = Camera::find()->where(['deleted' => 0])->count();
$counts['sensors'] = SensorChannel::find()->where(['measureTypeUuid' => MeasureType::SENSOR_CO2])->count();
$counts['elektro'] = Device::find()->where(['deviceTypeUuid' => DeviceType::DEVICE_COUNTER])
    ->andWhere(['deleted' => 0])
    ->count();
$counts['light'] = Device::find()->where(['deviceTypeUuid' => DeviceType::DEVICE_LIGHT])
    ->andWhere(['deleted' => 0])
    ->count();
$counts['light2'] = Device::find()->where(['deviceTypeUuid' => DeviceType::DEVICE_LIGHT_WITHOUT_ZB])
    ->andWhere(['deleted' => 0])
    ->count();
$counts['channel'] = SensorChannel::find()->count();
$counts['node'] = Node::find()->where(['deleted' => 0])->count();
