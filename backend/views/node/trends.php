<?php

use common\models\MeasureType;
use yii\web\View;

/* @var $node
 * @var $camera
 * @var $sensorChannelPowerUuid
 * @var $sensorChannelVoltageUuid
 * @var $sensorChannelCurrentUuid
 * @var $sensorChannelFrequencyUuid
 */

$this->title = Yii::t('app', 'Контроллер');
$this->registerJsFile('/js/vendor/video.min.js', ['position' => View::POS_BEGIN]);
$this->registerCssFile('/css/vendor/video-js.min.css');

?>

<br/>
<div class="row">
    <div class="col-md-12">
        <?= $this->render('widget-trends', ['sensorChannelUuid' => $sensorChannelPowerUuid,
            'type' => MeasureType::MEASURE_TYPE_INTERVAL, 'parameter' => 0]); ?>
    </div>
</div>
<div class="row">
    <div class="col-md-12">
        <?= $this->render('widget-trends', ['sensorChannelUuid' => $sensorChannelVoltageUuid,
            'type' => MeasureType::MEASURE_TYPE_INTERVAL, 'parameter' => 0]); ?>
    </div>
</div>
<div class="row">
    <div class="col-md-12">
        <?= $this->render('widget-trends', ['sensorChannelUuid' => $sensorChannelCurrentUuid,
            'type' => MeasureType::MEASURE_TYPE_INTERVAL, 'parameter' => 0]); ?>
    </div>
</div>
<div class="row">
    <div class="col-md-12">
        <?= $this->render('widget-trends', ['sensorChannelUuid' => $sensorChannelFrequencyUuid,
            'type' => MeasureType::MEASURE_TYPE_INTERVAL, 'parameter' => 0]); ?>
    </div>
</div>
