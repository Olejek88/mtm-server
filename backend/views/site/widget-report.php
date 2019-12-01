<?php

/* @var $reportDataProvider
 */

use common\models\Measure;
use common\models\MeasureType;
use common\models\SensorChannel;
use kartik\grid\GridView;
use yii\helpers\Html;

?>

<div class="box box-success">
    <div class="box-header with-border">
        <h3 class="box-title">Отчет по потреблению</h3>
        <div class="box-tools pull-right">
            <div class="btn-group">
                <?php echo Html::a("<button type='button' class='btn btn-box-tool'>
                    <i class='fa fa-link'></i></button>", ['/device/report']); ?>
            </div>
            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
            </button>
            <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i>
            </button>
        </div>
    </div>
    <div class="box-body">
        <?php
        $gridColumns = [
            [
                'class' => 'kartik\grid\DataColumn',
                'vAlign' => 'middle',
                'mergeHeader' => true,
                'width' => '180px',
                'value' => function ($data) {
                    return Html::a($data['node']['object']->getAddress(),
                        ['/device/dashboard', 'uuid' => $data['uuid']]);
                },
                'header' => 'Адрес',
                'format' => 'raw',
            ],
            [
                'class' => 'kartik\grid\DataColumn',
                'vAlign' => 'middle',
                'hAlign' => 'center',
                'width' => '100px',
                'mergeHeader' => true,
                'pageSummary' => true,
                'value' => function ($data) {
                    $start_time = '2018-12-31 00:00:00';
                    $end_time = '2021-12-31 00:00:00';
                    if (isset($_GET['end_time'])) {
                        $end_time = date('Y-m-d H:i:s', strtotime($_GET['end_time']));
                    }
                    if (isset($_GET['start_time'])) {
                        $start_time = date('Y-m-d H:i:s', strtotime($_GET['start_time']));
                    }
                    $sensorChannel = SensorChannel::find()
                        ->where(['deviceUuid' => $data['uuid']])
                        ->andWhere(['measureTypeUuid' => MeasureType::POWER])
                        ->one();
                    if ($sensorChannel) {
                        $value = Measure::getSumMeasureBetweenDates($sensorChannel, $start_time, $end_time, 1);
                        return $value;
                    }
                    return '-';
                },
                'header' => 'Тариф 1',
                'format' => 'raw',
            ],
            [
                'class' => 'kartik\grid\DataColumn',
                'vAlign' => 'middle',
                'hAlign' => 'center',
                'width' => '100px',
                'mergeHeader' => true,
                'pageSummary' => true,
                'value' => function ($data) {
                    $start_time = '2018-12-31 00:00:00';
                    $end_time = '2021-12-31 00:00:00';
                    if (isset($_GET['end_time'])) {
                        $end_time = date('Y-m-d H:i:s', strtotime($_GET['end_time']));
                    }
                    if (isset($_GET['start_time'])) {
                        $start_time = date('Y-m-d H:i:s', strtotime($_GET['start_time']));
                    }
                    $sensorChannel = SensorChannel::find()
                        ->where(['deviceUuid' => $data['uuid']])
                        ->andWhere(['measureTypeUuid' => MeasureType::POWER])
                        ->one();
                    if ($sensorChannel) {
                        $value = Measure::getSumMeasureBetweenDates($sensorChannel, $start_time, $end_time, 2);
                        return $value;
                    }
                    return '-';
                },
                'header' => 'Тариф 2',
                'format' => 'raw',
            ],
            [
                'class' => 'kartik\grid\DataColumn',
                'vAlign' => 'middle',
                'hAlign' => 'center',
                'width' => '100px',
                'mergeHeader' => true,
                'pageSummary' => true,
                'value' => function ($data) {
                    $start_time = '2018-12-31 00:00:00';
                    $end_time = '2021-12-31 00:00:00';
                    if (isset($_GET['end_time'])) {
                        $end_time = date('Y-m-d H:i:s', strtotime($_GET['end_time']));
                    }
                    if (isset($_GET['start_time'])) {
                        $start_time = date('Y-m-d H:i:s', strtotime($_GET['start_time']));
                    }
                    $sensorChannel = SensorChannel::find()
                        ->where(['deviceUuid' => $data['uuid']])
                        ->andWhere(['measureTypeUuid' => MeasureType::POWER])
                        ->one();
                    if ($sensorChannel) {
                        $value = Measure::getSumMeasureBetweenDates($sensorChannel, $start_time, $end_time, 3);
                        return $value;
                    }
                    return '-';
                },
                'header' => 'Тариф 3',
                'format' => 'raw',
            ],
            [
                'class' => 'kartik\grid\DataColumn',
                'vAlign' => 'middle',
                'hAlign' => 'center',
                'width' => '100px',
                'mergeHeader' => true,
                'pageSummary' => true,
                'value' => function ($data) {
                    $start_time = '2018-12-31 00:00:00';
                    $end_time = '2021-12-31 00:00:00';
                    if (isset($_GET['end_time'])) {
                        $end_time = date('Y-m-d H:i:s', strtotime($_GET['end_time']));
                    }
                    if (isset($_GET['start_time'])) {
                        $start_time = date('Y-m-d H:i:s', strtotime($_GET['start_time']));
                    }
                    $sensorChannel = SensorChannel::find()
                        ->where(['deviceUuid' => $data['uuid']])
                        ->andWhere(['measureTypeUuid' => MeasureType::POWER])
                        ->one();
                    if ($sensorChannel) {
                        $value = Measure::getSumMeasureBetweenDates($sensorChannel, $start_time, $end_time, 4);
                        return $value;
                    }
                    return '-';
                },
                'header' => 'Тариф 4',
                'format' => 'raw',
            ],
            [
                'class' => 'kartik\grid\DataColumn',
                'vAlign' => 'middle',
                'hAlign' => 'center',
                'width' => '100px',
                'mergeHeader' => true,
                'pageSummary' => true,
                'value' => function ($data) {
                    $start_time = '2018-12-31 00:00:00';
                    $end_time = '2021-12-31 00:00:00';
                    if (isset($_GET['end_time'])) {
                        $end_time = date('Y-m-d H:i:s', strtotime($_GET['end_time']));
                    }
                    if (isset($_GET['start_time'])) {
                        $start_time = date('Y-m-d H:i:s', strtotime($_GET['start_time']));
                    }
                    $sensorChannel = SensorChannel::find()
                        ->where(['deviceUuid' => $data['uuid']])
                        ->andWhere(['measureTypeUuid' => MeasureType::POWER])
                        ->one();
                    if ($sensorChannel) {
                        $value = Measure::getSumMeasureBetweenDates($sensorChannel, $start_time, $end_time, 0);
                        return $value;
                    }
                    return '-';
                },
                'footer' => '0',
                'header' => 'Сумма',
                'format' => 'raw',
            ]
        ];

        echo GridView::widget([
            'id' => 'report-table',
            'dataProvider' => $reportDataProvider,
            'columns' => $gridColumns,
            'containerOptions' => ['style' => 'overflow: auto'], // only set when $responsive = false
            'headerRowOptions' => ['class' => 'kartik-sheet-style'],
            'filterRowOptions' => ['class' => 'kartik-sheet-style'],
            'beforeHeader' => [
                '{toggleData}'
            ],
            'toolbar' => [
                []
            ],
            'showPageSummary' => false,
            'pjax' => true,
            'summary' => '',
            'bordered' => true,
            'striped' => false,
            'condensed' => false,
            'responsive' => true,
            'persistResize' => false,
            'hover' => true,
        ]);
        ?>
    </div>
</div>