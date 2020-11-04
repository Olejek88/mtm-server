<?php

use kartik\widgets\DatePicker;
use kartik\widgets\Select2;
use yii\helpers\Html;

/** @var $groupUuid */
/** @var $dataAll */
/** @var $startDate */

$this->title = Yii::t('app', 'Архив по группам');

?>
<div class="row">
    <form action="report-group">
        <table style="width: 800px; padding: 3px; background-color: white; align-content: center">
            <tr>
                <td style="width: 300px">
                    <?php
                    echo DatePicker::widget([
                            'name' => 'start_time',
                            'value' => $startDate,
                            'removeButton' => false,
                            'pluginOptions' => [
                                'autoclose' => true,
                                'format' => 'yyyy-mm',
                                'startView' => 'year',
                                'minViewMode' => 'months',
                            ],
                        ]) . '</td><td style="width: 300px">' .
                        Select2::widget([
                            'name' => 'group',
                            'data' => $groups,
                            'value' => $groupUuid,
                        ]) .
                        '</td><td style="width: 100px">' .
                        Html::submitButton(Yii::t('app', 'Выбрать'), ['class' => 'btn btn-info']) . '</td></tr>' ?>
        </table>
    </form>
</div>
<div class="row">
    <div class="col-md-12">
        <div class="box box-success">
            <div class="box-header with-border">
                <h3 class="box-title"><?= $this->title ?></h3>
            </div>
            <div class="box-body">
                <div id="requests-table-container" class="panel table-responsive kv-grid-container"
                     style="overflow: auto">
                    <table class="kv-grid-table table table-hover table-bordered table-condensed kv-table-wrap">
                        <thead>
                        <tr class="kartik-sheet-style" style="height: 20px">
                            <th class="text-center kv-align-middle" data-col-seq="0" style="width: 25%;">Дата</th>
                            <?php
                            foreach ($dataAll['group'] as $data) {
                                echo '<th class="text-center kv-align-middle" colspan="3">' . $data['title'] . '</th>';
                            }
                            ?>
                        </tr>
                        <tr class="kartik-sheet-style" style="height: 20px">
                            <th class="text-center kv-align-middle" data-col-seq="0" style="width: 25%;"></th>
                            <?php
                            foreach ($dataAll['group'] as $data) {
                                echo '<th class="text-center kv-align-middle">Тариф1, кВт*ч</th>';
                                echo '<th class="text-center kv-align-middle">Тариф2, кВт*ч</th>';
                                echo '<th class="text-center kv-align-middle">Сумма, кВт*ч</th>';
                            }
                            ?>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        $group_num = 0;
                        for ($mon = 0; $mon < 12; $mon++) {
                            echo '<tr data-key="1">';
                            $cnt = 0;
                            foreach ($dataAll['group'] as $data) {
                                if ($cnt == 0)
                                    echo '<td class="text-center kv-align-center kv-align-middle" style="white-space: nowrap">' . $data['month'][$mon]['date'] . '</td>';
                                echo '<td class="text-center kv-align-center kv-align-middle">' . number_format($data['month'][$mon]['w1'], 3) . '</td>';
                                echo '<td class="text-center kv-align-center kv-align-middle">' . number_format($data['month'][$mon]['w2'], 3) . '</td>';
                                echo '<td class="text-center kv-align-center kv-align-middle">' . number_format($data['month'][$mon]['ws'], 3) . '</td>';
                                $cnt++;
                            }
                            echo '</tr>';
                        }
                        ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
