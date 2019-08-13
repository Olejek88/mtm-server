<?php
/* @var $searchModel backend\models\DeviceSearch */

use common\models\Measure;
use common\models\MeasureType;
use common\models\SensorChannel;
use kartik\grid\GridView;
use yii\helpers\Html;

$this->title = Yii::t('app', 'Отчет по потреблению электроэнергии');

$gridColumns = [
    [
        'attribute' => '_id',
        'hAlign' => 'center',
        'vAlign' => 'middle',
        'contentOptions' => [
            'class' => 'table_class',
            'style' => 'width: 40px; text-align: center'
        ],
        'headerOptions' => ['class' => 'text-center'],
        'content' => function ($data) {
            return $data->_id;
        }
    ],
    [
        'class' => 'kartik\grid\DataColumn',
        'vAlign' => 'middle',
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
        'width' => '100px',
        'value' => function ($data) {
            return $data['name'];
        },
        'header' => 'Название счетчика',
        'format' => 'raw',
    ],
    [
        'class' => 'kartik\grid\DataColumn',
        'vAlign' => 'middle',
        'width' => '100px',
        'value' => function ($data) {
            return $data['serial'];
        },
        'header' => 'Серийный №',
        'format' => 'raw',
    ],
    [
        'class' => 'kartik\grid\DataColumn',
        'vAlign' => 'middle',
        'width' => '100px',
        'value' => function ($data) {
            return $data['node']['phone'];
        },
        'header' => 'Тел.номер',
        'format' => 'raw',
    ],
    [
        'class' => 'kartik\grid\DataColumn',
        'vAlign' => 'middle',
        'width' => '100px',
        'value' => function ($data) {
            $sensorChannel = SensorChannel::find()->where(['deviceUuid' => $data['uuid']])->one();
            if ($sensorChannel) {
                $tarif = Measure::getLastMeasure($sensorChannel, MeasureType::MEASURE_TYPE_CURRENT, 0);
                return $tarif['value'];
            }
            return '-';
        },
        'header' => 'Тариф 1',
        'format' => 'raw',
    ],
    [
        'class' => 'kartik\grid\DataColumn',
        'vAlign' => 'middle',
        'width' => '100px',
        'value' => function ($data) {
            $sensorChannel = SensorChannel::find()->where(['deviceUuid' => $data['uuid']])->one();
            if ($sensorChannel) {
                $tarif['value'] = Measure::getLastMeasure($sensorChannel, MeasureType::MEASURE_TYPE_CURRENT, 1);
                return $tarif['value'];
            }
            return '-';
        },
        'header' => 'Тариф 2',
        'format' => 'raw',
    ],
    [
        'class' => 'kartik\grid\DataColumn',
        'vAlign' => 'middle',
        'width' => '100px',
        'value' => function ($data) {
            $sensorChannel = SensorChannel::find()->where(['deviceUuid' => $data['uuid']])->one();
            if ($sensorChannel) {
                $tarif['value'] = Measure::getLastMeasure($sensorChannel, MeasureType::MEASURE_TYPE_CURRENT, 2);
                return $tarif['value'];
            }
            return '-';
        },
        'header' => 'Тариф 3',
        'format' => 'raw',
    ],
    [
        'class' => 'kartik\grid\DataColumn',
        'vAlign' => 'middle',
        'width' => '100px',
        'value' => function ($data) {
            $sensorChannel = SensorChannel::find()->where(['deviceUuid' => $data['uuid']])->one();
            if ($sensorChannel) {
                $tarif['value'] = Measure::getLastMeasure($sensorChannel, MeasureType::MEASURE_TYPE_CURRENT, 3);
                return $tarif['value'];
            }
            return '-';
        },
        'header' => 'Тариф 4',
        'format' => 'raw',
    ],
    [
        'class' => 'kartik\grid\DataColumn',
        'vAlign' => 'middle',
        'width' => '100px',
        'value' => function ($data) {
            $sensorChannel = SensorChannel::find()->where(['deviceUuid' => $data['uuid']])->one();
            if ($sensorChannel) {
                $tarif['value'] = Measure::getLastMeasure($sensorChannel, MeasureType::MEASURE_TYPE_CURRENT, 4);
                return $tarif['value'];
            }
            return '-';
        },
        'header' => 'Сумма',
        'format' => 'raw',
    ]
];

echo GridView::widget([
    'id' => 'equipment-table',
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    'columns' => $gridColumns,
    'containerOptions' => ['style' => 'overflow: auto'], // only set when $responsive = false
    'headerRowOptions' => ['class' => 'kartik-sheet-style'],
    'filterRowOptions' => ['class' => 'kartik-sheet-style'],
    'beforeHeader' => [
        '{toggleData}'
    ],
    'toolbar' => [
        '{export}'
    ],
    'export' => [
        'fontAwesome' => true,
        'target' => GridView::TARGET_BLANK,
        'filename' => 'equipments'
    ],
    'pjax' => true,
    'showPageSummary' => false,
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
        'heading' => '<i class="glyphicon glyphicon-tags"></i>&nbsp; Текущие показания',
        'headingOptions' => ['style' => 'background: #337ab7']
    ],
]);
