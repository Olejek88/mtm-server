<?php
/* @var $node
 * @var $device
 */

use common\models\DeviceStatus;
use yii\helpers\Html;

?>
<div class="box box-success">
    <div class="box-header with-border">
        <h3 class="box-title">Информация по шкафу управления <?php echo $node['object']->getAddress() ?></h3>
        <div class="box-tools pull-right">
            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
            </button>
            <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i>
            </button>
        </div>
    </div>
    <!-- /.box-header -->
    <div class="row">
        <div class="col-md-4" style="margin: 5px">
            <?php
            if ($node['deviceStatusUuid'] == DeviceStatus::NOT_MOUNTED) {
                $class = 'critical1';
            } elseif ($node['deviceStatusUuid'] == DeviceStatus::NOT_WORK) {
                $class = 'critical2';
            } elseif ($node['deviceStatusUuid'] == DeviceStatus::UNKNOWN) {
                $class = 'critical4';
            } else {
                $class = 'critical3';
            }
            echo "<span class='badge' style='color: green; height: 12px; margin-top: -3px'> </span>";
            ?>
            <small class="float-right">Адрес: <?php echo $node['address'] ?> [Версия
                ПО: <?php echo $node["software"] ?>]
            </small><br/>
            <small class="float-right">Время последней связи: <?php echo $node['lastDate'] ?></small>
        </div>
        <div class="col-md-3">
            <small class="float-right">Телефон: <?php echo $node['phone'] ?></small><br/>
        </div>
        <div class="col-md-3">
            <span class="float-right"><?php echo Html::a('<i class="fa fa-bar-chart"></i> Тренды',['../device/trends', 'uuid' => $device['uuid']]) ?></span><br/>
            <span class="float-right"><?php echo Html::a('<i class="fa fa-table"></i> Журнал',['../device/register', 'uuid' => $device['uuid']]) ?></span>
        </div>
    </div>
</div>
