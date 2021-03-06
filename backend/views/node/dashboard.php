<?php

use common\models\Device;
use common\models\DeviceType;
use common\models\MeasureType;
use common\models\Node;
use common\models\SensorChannel;
use yii\web\View;

/* @var Node $node
 * @var $camera
 * @var $coordinator
 * @var $parameters
 * @var $counterValue
 * @var $counterDate
 */

$this->title = Yii::t('app', 'Контроллер');
$this->registerJsFile('/js/vendor/video.min.js', ['position' => View::POS_BEGIN]);
$this->registerCssFile('/css/vendor/video-js.min.css');

$sensorChannelUuid = 0;
$deviceElectro = Device::find()->where(['nodeUuid' => $node['uuid']])->andWhere(['deviceTypeUuid' => DeviceType::DEVICE_ELECTRO])->one();
if ($deviceElectro) {
    $sensorChannel = SensorChannel::find()->where(['deviceUuid' => $deviceElectro['uuid']])->andWhere(['measureTypeUuid' => MeasureType::VOLTAGE])->one();
    if ($sensorChannel)
        $sensorChannelUuid = $sensorChannel['uuid'];
}

?>


<br/>
<div class="row row-message" style="display: none;">
    <div class="col-md-12 col-message bg-red">
    </div>
</div>

<!-- Info boxes -->
<div class="row">
    <div class="col-md-4">
        <?= $this->render('widget-node-info', ['node' => $node, 'type' => $type, 'device' => $deviceElectro]); ?>
    </div>
    <div class="col-md-8">
        <?= $this->render('widget-node-warnings', ['node' => $node]); ?>
    </div>
</div>
<!-- /.row -->

<div class="row">
    <!-- Left col -->
    <div class="col-md-8">
        <?= $this->render('widget-sensor-channel', ['node' => $node]); ?>
    </div>

    <div class="col-md-4">
        <?= $this->render('widget-status', ['node' => $node, 'parameters' => $parameters, 'device' => $coordinator]); ?>
    </div>

    <div class="col-md-4">
        <?= $this->render('widget-counter-value', ['counterDate' => $counterDate, 'counterValue' => $counterValue, 'node' => $node]); ?>
    </div>

    <div class="col-md-7">
        <?= $this->render('widget-devices', ['node' => $node]); ?>
    </div>

    <div class="col-md-5">
        <?= $this->render('widget-device-register', ['node' => $node]); ?>
    </div>

</div>
<div class="row">
    <div class="col-md-12">
        <?= $this->render('widget-trends', ['sensorChannelUuid' => $sensorChannelUuid,
            'type' => MeasureType::MEASURE_TYPE_INTERVAL, 'parameter' => 0]); ?>
    </div>
</div>

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
        <?php
        if ($camera)
            echo $this->render('widget-camera', ['camera' => $camera]);
        ?>
    </div>
</div>

<!-- /.content-wrapper -->
<script>
    let interval = setInterval(function () {
        $.ajax({
            url: "/node/is-work",
            type: "get",
            data: {
                id: <?= $node->_id ?>
            },
            success: function (result) {
                if (result.isWork) {
                    $('.row-message').hide();
                } else {
                    $('.row-message').show();
                }

                $('.col-message').html(result.message);
            },
            error: function (result) {
            }
        })
    }, 3000);
</script>