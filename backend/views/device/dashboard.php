<?php

use common\models\Device;

/* @var Device $device
 * @var $parameters
 */

$this->title = Yii::t('app', 'Сводная страница по устройству');
?>

<br/>
<div class="row row-message" style="display: none;">
    <div class="col-md-12 col-message bg-red">
    </div>
</div>

<!-- Info boxes -->
<div class="row">
    <div class="col-md-12">
        <?= $this->render('widget-light-info', ['device' => $device]); ?>
    </div>
</div>
<!-- /.row -->

<div class="row">
    <!-- Left col -->
    <div class="col-md-3">
        <?= $this->render('widget-light-set', ['device' => $device, 'parameters' => $parameters]); ?>
    </div>

    <div class="col-md-4">
        <?= $this->render('widget-light-params', ['device' => $device, 'parameters' => $parameters]); ?>
    </div>

    <div class="col-md-5">
        <?= $this->render('widget-light-config', ['device' => $device, 'parameters' => $parameters]); ?>
    </div>


</div>
<div class="row">
    <div class="col-md-7">
        <?= $this->render('widget-light-register', ['device' => $device]); ?>
    </div>

    <div class="col-md-5">
        <?= $this->render('widget-light-list', ['device' => $device]); ?>
    </div>
</div>

<script>
    let interval = setInterval(function () {
        $.ajax({
            url: "/device/is-work",
            type: "get",
            data: {
                id: <?= $device->_id ?>
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


