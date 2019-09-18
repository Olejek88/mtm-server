<?php
/* @var $device
 * @var $parameters
 * @var $dataAll
 */

use yii\helpers\Html;

?>
<div class="box box-success">
    <div class="box-header with-border">
        <h3 class="box-title">Архив показаний по месяцам</h3>
        <div class="box-tools pull-right">
            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
            <div class="btn-group">
                <button type="button" class="btn btn-box-tool dropdown-toggle" data-toggle="dropdown">
                    <i class="fa fa-wrench"></i></button>
                <ul class="dropdown-menu" role="menu">
                    <li><?php echo Html::a("Измерения", ['/measures']); ?></li>
                </ul>
            </div>
            <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
        </div>
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
                    foreach ($dataAll['month'] as $data) {
                        echo '<tr data-key="1">
                              <td class="text-center kv-align-center kv-align-middle" data-col-seq="0">'.$data['date'].'</td>
                              <td class="text-center kv-align-center kv-align-middle" data-col-seq="1">'.$data['w1'].'</td>
                              <td class="text-center kv-align-center kv-align-middle" data-col-seq="2">'.$data['w2'].'</td>
                              <td class="text-center kv-align-center kv-align-middle" data-col-seq="3">'.$data['w3'].'</td>
                              <td class="text-center kv-align-center kv-align-middle" data-col-seq="4">'.$data['w4'].'</td>
                              <td class="text-center kv-align-center kv-align-middle" data-col-seq="5">' . ($data['w1'] + $data['w2'] + $data['w3'] + $data['w4']) . '</td>
                              </tr>';
                    }
                ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
