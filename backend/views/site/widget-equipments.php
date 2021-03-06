<?php

use common\models\DeviceType;
use yii\helpers\Html;

/* @var $devices */
?>

<div class="box box-primary">
    <div class="box-header with-border">
        <h3 class="box-title">Недавно добавленное оборудование</h3>

        <div class="box-tools pull-right">
            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
            </button>
            <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i>
            </button>
        </div>
    </div>
    <!-- /.box-header -->
    <div class="box-body">
        <ul class="products-list product-list-in-box">
            <?php
            $count=0;
            foreach ($devices as $device) {
                $path = '/images/no-image-icon-4.png';
                if ($device['deviceTypeUuid'] == DeviceType::DEVICE_COUNTER)
                    $path = '/images/elektro.jpg';
                if ($device['deviceTypeUuid']==DeviceType::DEVICE_LIGHT)
                    $path = '/images/light.jpg';
                print '<li class="item">
                                <div class="product-img">
                                    <img class="img-circle" src="' . Html::encode($path) . '" alt="' . $device['deviceType']->title . '">
                                </div>
                                <div class="product-info">
                                    <a href="/device/view?id=' . $device["_id"] . '" class="product-title">' . $device["serial"] . '
                                    <span class="label label-warning pull-right">' . $device['deviceType']->title . '</span></a>
                                    <span class="product-description">' . $device["node"]['address'] . ' / ' .
                                    $device["node"]['object']->getAddress().'
                                    </span>
                                </div></li>';
                $count++;
                if ($count>5) break;
            }
            ?>
            <!-- /.item -->
        </ul>
    </div>
    <!-- /.box-body -->
    <div class="box-footer text-center">
        <?php echo Html::a('Все оборудование', ['/device/tree'],
            ['class' => 'btn btn-sm btn-info btn-flat pull-left']); ?>
    </div>
    <!-- /.box-footer -->
</div>
<!-- /.box -->
