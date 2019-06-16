<?php
/* @var $node */

use common\models\Camera;
use common\models\Device;
use common\models\Measure;
use common\models\Node;
use common\models\SensorChannel;

$cameraCount = Camera::find()->where(['nodeUuid' => $node['uuid']])->count();
$deviceCount = Device::find()->where(['nodeUuid' => $node['uuid']])->count();
$channelCount = SensorChannel::find()->where(['deviceUuid' => (Node::find()->where(['uuid' => $node['uuid']])->one())])->count();
$measureCount = Measure::find()->count();

?>

<div class="col-12">
    <div class="invoice p-3 mb-3">
        <!-- title row -->
        <div class="row invoice-info">
            <div class="col-sm-5 invoice-col">
                <span class="info-box-icon"><i class="fa fa-cogs"></i></span>
            </div>
            <!-- /.col -->
            <div class="col-sm-7 invoice-col">
                <address>
                    <strong>Камер</strong> <?= $cameraCount ?><br>
                    <strong>Устройств</strong> <?= $deviceCount ?><br>
                    <strong>Каналов</strong> <?= $channelCount ?><br>
                    <strong>Измерений</strong> <?= $measureCount ?><br>
                </address>
            </div>
        </div>
    </div>
</div>
