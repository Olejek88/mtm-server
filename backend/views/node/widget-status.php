<?php
/* @var $node
 */

use common\models\Measure;
use common\models\MeasureType;
use yii\helpers\Html;

if (strtotime($node['lastDate']) + 50000 > time())
    $link = "<span class='badge badge-green'>есть</span>";
else
    $link = "<span class='badge badge-red'>нет</span>";

if ($node['security'])
    $security = "<span class='badge badge-green'>Закрыт</span>";
else
    $security = "<span class='badge badge-red'>Открыт</span>";

$u1 = Measure::getLastMeasureNodeByType(MeasureType::VOLTAGE, $node['uuid'], MeasureType::MEASURE_TYPE_CURRENT, 0);
if ($u1['value'] && $u1['value'] > 200 && $u1['value'] < 251)
    $status = "<span class='badge badge-green'>В норме</span>";
else
    $status = "<span class='badge badge-red'>Авария</span>";

$u1 = Measure::getLastMeasureNodeByType(MeasureType::VOLTAGE, $node['uuid'], MeasureType::MEASURE_TYPE_CURRENT, 1);
$u2 = Measure::getLastMeasureNodeByType(MeasureType::VOLTAGE, $node['uuid'], MeasureType::MEASURE_TYPE_CURRENT, 2);
$u3 = Measure::getLastMeasureNodeByType(MeasureType::VOLTAGE, $node['uuid'], MeasureType::MEASURE_TYPE_CURRENT, 3);
if (!$u1) $u1 = '-';
else $u1 = $u1['value'];
if (!$u2) $u2 = '-';
else $u2 = $u2['value'];
if (!$u3) $u3 = '-';
else $u3 = $u3['value'];

$voltage = "<span style='color: darkgreen'>" . $u1 . "," . $u2 . "," . $u3 . "</span>";

$i1 = Measure::getLastMeasureNodeByType(MeasureType::CURRENT, $node['uuid'], MeasureType::MEASURE_TYPE_CURRENT, 1);
$i2 = Measure::getLastMeasureNodeByType(MeasureType::CURRENT, $node['uuid'], MeasureType::MEASURE_TYPE_CURRENT, 2);
$i3 = Measure::getLastMeasureNodeByType(MeasureType::CURRENT, $node['uuid'], MeasureType::MEASURE_TYPE_CURRENT, 3);
if (!$i1) $i1 = '-';
else $i1 = $i1['value'];
if (!$i2) $i2 = '-';
else $i2 = $i2['value'];
if (!$i3) $i3 = '-';
else $i3 = $i3['value'];

$current = "<span style='color: darkgreen'>" . $i1 . "," . $i2 . "," . $i3 . "</span>";

$w = Measure::getLastMeasureNodeByType(MeasureType::POWER, $node['uuid'], MeasureType::MEASURE_TYPE_CURRENT, 0);
if (!$w) $w = '-';
else $w = $w['value'];
$power = "<span style='color: darkgreen'>" . $w . "</span>";

$w = Measure::getLastMeasureNodeByType(MeasureType::POWER, $node['uuid'], MeasureType::MEASURE_TYPE_TOTAL, 0);
if (!$w) $w = '-';
else $w = $w['value'];
$total = "<span style='color: darkgreen'>" . $w . "</span>";

?>
<div class="box box-success">
    <div class="box-header with-border">
        <h3 class="box-title">Статус шкафа</h3>
    </div>
    <div class="box-body">
        <table class="kv-grid-table table table-hover table-bordered table-condensed kv-table-wrap">
            <tbody>
            <tr data-key="1">
                <td class="table_class kv-align-middle" data-col-seq="0">Связь</td>
                <td class="table_class kv-align-middle" data-col-seq="1"><?php echo $link ?></td>
            </tr>
            <tr data-key="2">
                <td class="table_class kv-align-middle" data-col-seq="0">Охрана</td>
                <td class="table_class kv-align-middle" data-col-seq="1"><?php echo $security ?></td>
            </tr>
            <tr data-key="3">
                <td class="table_class kv-align-middle" data-col-seq="0">Питание</td>
                <td class="table_class kv-align-middle" data-col-seq="1"><?php echo $status ?></td>
            </tr>
            <tr data-key="4">
                <td class="table_class kv-align-middle" data-col-seq="0">Напряжение,В</td>
                <td class="table_class kv-align-middle" data-col-seq="1"><?php echo $voltage ?></td>
            </tr>
            <tr data-key="5">
                <td class="table_class kv-align-middle" data-col-seq="0">Ток, А</td>
                <td class="table_class kv-align-middle" data-col-seq="1"><?php echo $current ?></td>
            </tr>
            <tr data-key="6">
                <td class="table_class kv-align-middle" data-col-seq="0">Мощность, кВт</td>
                <td class="table_class kv-align-middle" data-col-seq="1"><?php echo $power ?></td>
            </tr>
            <tr data-key="7">
                <td class="table_class kv-align-middle" data-col-seq="0">Энергия, кВт/ч</td>
                <td class="table_class kv-align-middle" data-col-seq="1"><?php echo $total ?></td>
            </tr>
            </tbody>
        </table>
    </div>
</div>
