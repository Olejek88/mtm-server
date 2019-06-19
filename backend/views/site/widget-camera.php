<?php

use common\models\Camera;
use common\models\DeviceStatus;
use yii\helpers\Html;

$camera = Camera::find()->one();
?>

<div class="box box-success">
    <div class="box-header with-border">
        <h3 class="box-title">Камера</h3>
        <div class="box-tools pull-right">
            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
            </button>
            <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i>
            </button>
        </div>
    </div>
    <!-- /.box-header -->
    <div class="box-body no-padding">
        <div class="col-sm-7 invoice-col">
            <div class="product-img">
                <?php echo Html::img('@web/images/camera_view.jpg') ?>
            </div>
        </div>
        <!-- /.col -->
        <div class="col-sm-5 invoice-col">
            <address>
                <?php
                if ($camera) {
                    $color = 'background-color: white';
                    if ($camera['deviceStatusUuid'] == DeviceStatus::UNKNOWN ||
                        $camera['deviceStatusUuid'] == DeviceStatus::NOT_MOUNTED)
                        $color = 'background-color: gray';
                    if ($camera['deviceStatusUuid'] == DeviceStatus::NOT_WORK)
                        $color = 'background-color: lightred';
                    if ($camera['deviceStatusUuid'] == DeviceStatus::WORK)
                        $color = 'background-color: green';
                    $status = "<span class='badge' style='" . $color . "; height: 12px; margin-top: -3px'> </span>&nbsp;"
                        .$camera['deviceStatus']['title'];

                    echo '<strong>&nbsp;&nbsp;' . $camera['object']->getAddress() . '</strong> <br>';
                    echo '<strong>Статус</strong>&nbsp;&nbsp;' . $status . '<br>';
                    echo '<strong>Адрес</strong>&nbsp;&nbsp;' . $camera['address'] . '<br>';
                }
                ?>
            </address>
        </div>
    </div>
</div>
