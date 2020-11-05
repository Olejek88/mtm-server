<?php

use common\models\Measure;
use common\models\MeasureType;
use common\models\SensorChannel;
use kartik\grid\GridView;
use kartik\widgets\DatePicker;
use yii\helpers\Html;

/** @var $searchModel backend\models\DeviceSearch */
/** @var $start_date */
/** @var $end_date */

$this->title = Yii::t('app', 'Отчет по потреблению электроэнергии');

$gridColumns = [
    [
        'attribute' => '_id',
        'hAlign' => 'center',
        'vAlign' => 'middle',
        'mergeHeader' => true,
        'contentOptions' => [
            'class' => 'table_class',
            'style' => 'width: 40px'
        ],
        'headerOptions' => ['class' => 'text-center'],
        'content' => function ($data) {
            return $data->_id;
        }
    ],
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
        'value' => function ($data) {
            return $data['name'];
        },
        'mergeHeader' => true,
        'header' => 'Название счетчика',
        'format' => 'raw',
    ],
    [
        'class' => 'kartik\grid\DataColumn',
        'vAlign' => 'middle',
        'hAlign' => 'center',
        'width' => '100px',
        'value' => function ($data) {
            return $data['serial'];
        },
        'header' => 'Серийный №',
        'mergeHeader' => true,
        'format' => 'raw',
    ],
    [
        'class' => 'kartik\grid\DataColumn',
        'vAlign' => 'middle',
        'hAlign' => 'center',
        'width' => '100px',
        'mergeHeader' => true,
        'pageSummary' => false,
        'value' => function ($data) {
            return $data['address'];
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
        'value' => function ($data) use ($start_date, $end_date) {
            $sensorChannel = SensorChannel::find()
                ->where(['deviceUuid' => $data['uuid']])
                ->andWhere(['measureTypeUuid' => MeasureType::POWER])
                ->one();
            if ($sensorChannel) {
                $value = Measure::getSumMeasureBetweenDates($sensorChannel['uuid'], $start_date, date('Y-m-d', strtotime($end_date . ' +1 day')), 1);
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
        'value' => function ($data) use ($start_date, $end_date) {
            $sensorChannel = SensorChannel::find()
                ->where(['deviceUuid' => $data['uuid']])
                ->andWhere(['measureTypeUuid' => MeasureType::POWER])
                ->one();
            if ($sensorChannel) {
                $value = Measure::getSumMeasureBetweenDates($sensorChannel['uuid'], $start_date, date('Y-m-d', strtotime($end_date . ' +1 day')), 2);
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
        'value' => function ($data) use ($start_date, $end_date) {
            $sensorChannel = SensorChannel::find()
                ->where(['deviceUuid' => $data['uuid']])
                ->andWhere(['measureTypeUuid' => MeasureType::POWER])
                ->one();
            if ($sensorChannel) {
                $value = Measure::getSumMeasureBetweenDates($sensorChannel['uuid'], $start_date, date('Y-m-d', strtotime($end_date . ' +1 day')), 3);
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
        'value' => function ($data) use ($start_date, $end_date) {
            $sensorChannel = SensorChannel::find()
                ->where(['deviceUuid' => $data['uuid']])
                ->andWhere(['measureTypeUuid' => MeasureType::POWER])
                ->one();
            if ($sensorChannel) {
                $value = Measure::getSumMeasureBetweenDates($sensorChannel['uuid'], $start_date, date('Y-m-d', strtotime($end_date . ' +1 day')), 4);
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
        'value' => function ($data) use ($start_date, $end_date) {
            $sensorChannel = SensorChannel::find()
                ->where(['deviceUuid' => $data['uuid']])
                ->andWhere(['measureTypeUuid' => MeasureType::POWER])
                ->one();
            if ($sensorChannel) {
                $value = Measure::getSumMeasureBetweenDates($sensorChannel['uuid'], $start_date, date('Y-m-d', strtotime($end_date . ' +1 day')), 0);
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
    'id' => 'equipment-table',
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    'columns' => $gridColumns,
    'showPageSummary' => true,
    'containerOptions' => ['style' => 'overflow: auto'], // only set when $responsive = false
    'headerRowOptions' => ['class' => 'kartik-sheet-style'],
    'filterRowOptions' => ['class' => 'kartik-sheet-style'],
    'beforeHeader' => [
        [
            'columns' => [
                [
                    'content' => "За период с $start_date по $end_date",
                    'options' => ['colspan' => 10, 'class' => 'text-center warning']
                ],
            ],
        ],
    ],
    'toolbar' => [
        ['content' =>
            '<form action="report"><table style="width: 800px; padding: 3px"><tr><td style="width: 300px">' .
            DatePicker::widget([
                'name' => 'start_date',
                'value' => $start_date,
                'removeButton' => false,
                'pluginOptions' => [
                    'autoclose' => true,
                    'format' => 'yyyy-mm-dd'
                ]
            ]) . '</td><td style="width: 300px">' .
            DatePicker::widget([
                'name' => 'end_date',
                'value' => $end_date,
                'removeButton' => false,
                'pluginOptions' => [
                    'autoclose' => true,
                    'format' => 'yyyy-mm-dd'
                ]
            ]) . '</td><td style="width: 100px">' . Html::submitButton(Yii::t('app', 'Выбрать'), [
                'class' => 'btn btn-info']) . '</td>
                <td style="width: 100px">{export}</td></tr></table></form>',
            'options' => ['style' => 'width:100%']
        ]
    ],
    'export' => [
        'fontAwesome' => true,
        'target' => GridView::TARGET_BLANK,
        'filename' => 'equipments'
    ],
    'pjax' => true,
    'pageSummaryRowOptions' => ['style' => 'line-height: 0; padding: 0'],
    'summary' => '',
    'bordered' => true,
    'striped' => false,
    'condensed' => false,
    'responsive' => true,
    'persistResize' => false,
    'hover' => true,
    'panel' => [
        'type' => GridView::TYPE_PRIMARY,
        'heading' => '<i class="glyphicon glyphicon-tags"></i>&nbsp; Отчет по потреблению электроэнергии',
        'headingOptions' => ['style' => 'background: #337ab7']
    ],
]);
