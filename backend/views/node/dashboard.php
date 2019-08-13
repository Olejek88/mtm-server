<?php
use common\models\Device;
use common\models\DeviceType;
use common\models\MeasureType;
use common\models\SensorChannel;

/* @var $node
 * @var $camera
 */

$this->title = Yii::t('app', 'Контроллер');
$this->registerJsFile('/js/vendor/video.min.js');
$this->registerCssFile('/css/vendor/video-js.min.css');

$sensorChannelUuid = 0;
$deviceElectro = Device::find()->where(['nodeUuid' => $node['uuid']])->andWhere(['deviceTypeUuid' => DeviceType::DEVICE_ELECTRO])->one();
if ($deviceElectro) {
    $sensorChannel = SensorChannel::find()->where(['deviceUuid' => $deviceElectro['uuid']])->andWhere(['measureTypeUuid' => MeasureType::POWER])->one();
    if ($sensorChannel)
        $sensorChannelUuid = $sensorChannel['uuid'];
}

?>


<br/>
<!-- Info boxes -->
<div class="row">
    <div class="col-md-12">
    <?= $this->render('widget-node-info', ['node' => $node, 'type' => $type]); ?>
    </div>
</div>
<!-- /.row -->

<!-- Main row -->
<div class="row">
    <!-- Left col -->
    <div class="col-md-5">
        <?= $this->render('widget-archive', ['node' => $node]); ?>
    </div>

    <div class="col-md-7">
        <?= $this->render('widget-power', ['node' => $node]); ?>
    </div>
</div>
<div class="row">
    <div class="col-md-7">
        <?= $this->render('widget-thread', ['node' => $node]); ?>
    </div>

    <div class="col-md-5">
        <?= $this->render('widget-camera', ['camera' => $camera]); ?>
    </div>
</div>

<!-- Main row -->
<div class="row">
    <!-- Left col -->
    <div class="col-md-7">
        <?= $this->render('widget-devices', ['node' => $node]); ?>
    </div>

    <div class="col-md-5">
        <?= $this->render('widget-device-register', ['node' => $node]); ?>
    </div>

    <div class="col-md-8">
        <?= $this->render('widget-sensor-channel', ['node' => $node]); ?>
    </div>

    <div class="col-md-4">

        <?= $this->render('widget-status', ['node' => $node]); ?>
    </div>
</div>
<div class="row">
    <div class="col-md-12">
        <?= $this->render('widget-trends', ['sensorChannelUuid' => $sensorChannelUuid,
            'type' => MeasureType::MEASURE_TYPE_INTERVAL, 'parameter' => 0]); ?>
    </div>
</div>
<!-- /.content-wrapper -->
