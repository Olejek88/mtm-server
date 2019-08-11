<?php
/* @var $device
 * @var $parameters
 */

use common\models\Device;
use common\models\User;
use yii\helpers\Html;

$this->title = Yii::t('app', 'Устройство');
?>

<br/>
<!-- Info boxes -->
<!-- /.row -->

<div class="row">
    <!-- Left col -->
    <div class="col-md-7">
        <?= $this->render('widget-electro-integrate', ['device' => $device, 'parameters' => $parameters]); ?>
    </div>

    <div class="col-md-5">
        <?= $this->render('widget-electro-register', ['device' => $device, 'parameters' => $parameters]); ?>
    </div>
</div>
<div class="row">
    <div class="col-md-12">
        <?= $this->render('widget-electro-trends', ['device' => $device]); ?>
    </div>
</div>
<div class="row">
    <div class="col-md-4">
        <div class="row">
            <?= $this->render('widget-light-power', ['device' => $device]); ?>
        </div>
        <div class="row">
            <?= $this->render('widget-light-params', ['device' => $device, 'parameters' => $parameters]); ?>
        </div>
    </div>
    <div class="col-md-8">
        <?= $this->render('widget-light-archive', ['device' => $device]); ?>
    </div>
</div>

