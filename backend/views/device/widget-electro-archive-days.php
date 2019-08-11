<?php
/* @var $device
 * @var $parameters
 * @var $data
 */

use common\models\mtm\MtmDevLightConfig;
use kartik\slider\Slider;
use kartik\widgets\Select2;
use yii\helpers\Html;
use yii\widgets\Pjax;

?>
<div class="box box-success">
    <div class="box-header with-border">
        <h3 class="box-title">Архив показаний по дням</h3>
    </div>
    <div class="box-body">
        <div id="requests-table-container" class="panel table-responsive kv-grid-container" style="overflow: auto">
            <table class="kv-grid-table table table-hover table-bordered table-condensed kv-table-wrap">
                <thead>
                <tr class="kartik-sheet-style" style="height: 20px">
                    <th class="text-center kv-align-middle" data-col-seq="0" style="width: 25%;">Дата</th>
                    <th class="text-center kv-align-middle" data-col-seq="1" style="width: 15%;">Тариф1, кВт*ч</th>
                    <th class="text-center kv-align-center kv-align-middle" data-col-seq="2" style="width: 15%;">Тариф2, кВт*ч</th>
                    <th class="text-center kv-align-middle" data-col-seq="3" style="width: 15%;">Тариф3, кВт*ч</th>
                    <th class="text-center kv-align-center kv-align-middle" data-col-seq="4">Тариф4, кВт*ч</th>
                    <th class="text-center kv-align-center kv-align-middle" data-col-seq="5">Сумма, кВт*ч</th>
                </tr>
                </thead>
                <tbody>
                <?php
                    foreach ($data['days'] as $data) {
                        echo '<tr data-key="1">
                              <td class="kv-align-center kv-align-middle" data-col-seq="0">'.$data['date'].'</td>
                              <td class="kv-align-center kv-align-middle" data-col-seq="1">'.$data['w1'].'</td>
                              <td class="kv-align-center kv-align-middle" data-col-seq="1">'.$data['w2'].'</td>
                              <td class="kv-align-center kv-align-middle" data-col-seq="1">'.$data['w3'].'</td>
                              <td class="kv-align-center kv-align-middle" data-col-seq="1">'.$data['w4'].'</td>
                              <td class="kv-align-center kv-align-middle" data-col-seq="1">'.$data['ws'].'</td>
                              </tr>';
                    }
                ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
