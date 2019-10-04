<?php
/* @var $searchModel backend\models\DeviceSearch */

use common\models\Measure;
use common\models\MeasureType;
use kartik\grid\GridView;
use yii\helpers\Html;

$this->title = Yii::t('app', 'Шкафы управления');

$gridColumns = [
    [
        'attribute' => '_id',
        'hAlign' => 'center',
        'vAlign' => 'middle',
        'header' => '#',
        'mergeHeader' => true,
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
        'mergeHeader' => true,
        'value' => function ($data) {
            return Html::a($data['object']->getAddress(),
                ['/node/dashboard', 'uuid' => $data['uuid'], 'type' => 0]);
        },
        'header' => 'Адрес',
        'format' => 'raw',
    ],
    [
        'class' => 'kartik\grid\DataColumn',
        'vAlign' => 'middle',
        'hAlign' => 'center',
        'width' => '60px',
        'mergeHeader' => true,
        'value' => function ($data) {
            if (strtotime($data['lastDate'])+50000>time())
                return "<span class='badge badge-green'>есть</span>";
            else
                return "<span class='badge badge-red'>нет</span>";
        },
        'header' => 'Связь',
        'format' => 'raw',
    ],
    [
        'class' => 'kartik\grid\DataColumn',
        'vAlign' => 'middle',
        'hAlign' => 'center',
        'width' => '60px',
        'mergeHeader' => true,
        'value' => function ($data) {
            if ($data['security'])
                return "<span class='badge badge-green'>Закрыт</span>";
            else
                return "<span class='badge badge-red'>Открыт</span>";
        },
        'header' => 'Шкаф',
        'format' => 'raw',
    ],
    [
        'class' => 'kartik\grid\DataColumn',
        'vAlign' => 'middle',
        'hAlign' => 'center',
        'width' => '60px',
        'mergeHeader' => true,
        'value' => function ($data) {
            $u1 = Measure::getLastMeasureNodeByType(MeasureType::VOLTAGE,$data['uuid'],MeasureType::MEASURE_TYPE_CURRENT,0);
            if ($u1['value'] && $u1['value']>200 && $u1['value']<251)
                return "<span class='badge badge-green'>В норме</span>";
            else
                return "<span class='badge badge-red'>Авария</span>";
        },
        'header' => 'Питание',
        'format' => 'raw',
    ],
    [
        'class' => 'kartik\grid\DataColumn',
        'vAlign' => 'middle',
        'hAlign' => 'center',
        'width' => '80px',
        'mergeHeader' => true,
        'value' => function ($data) {
            $u1 = Measure::getLastMeasureNodeByType(MeasureType::VOLTAGE,$data['uuid'],MeasureType::MEASURE_TYPE_CURRENT,1);
            $u2 = Measure::getLastMeasureNodeByType(MeasureType::VOLTAGE,$data['uuid'],MeasureType::MEASURE_TYPE_CURRENT,2);
            $u3 = Measure::getLastMeasureNodeByType(MeasureType::VOLTAGE,$data['uuid'],MeasureType::MEASURE_TYPE_CURRENT,3);
            if (!$u1) $u1 = '-';
            else $u1 = number_format($u1['value'], 3);
            if (!$u2) $u2 = '-';
            else $u2 = number_format($u2['value'], 3);
            if (!$u3) $u3 = '-';
            else $u3 = number_format($u3['value'], 3);

            return "<span style='color: darkgreen'>".$u1.",".$u2.",".$u3."</span>";
        },
        'header' => 'Напряжение, В',
        'format' => 'raw',
    ],
    [
        'class' => 'kartik\grid\DataColumn',
        'vAlign' => 'middle',
        'hAlign' => 'center',
        'width' => '80px',
        'mergeHeader' => true,
        'value' => function ($data) {
            $i1 = Measure::getLastMeasureNodeByType(MeasureType::CURRENT,$data['uuid'],MeasureType::MEASURE_TYPE_CURRENT, 1);
            $i2 = Measure::getLastMeasureNodeByType(MeasureType::CURRENT,$data['uuid'],MeasureType::MEASURE_TYPE_CURRENT,2);
            $i3 = Measure::getLastMeasureNodeByType(MeasureType::CURRENT,$data['uuid'],MeasureType::MEASURE_TYPE_CURRENT,3);
            if (!$i1) $i1 = '-';
            else $i1 = $i1['value'];
            if (!$i2) $i2 = '-';
            else $i2 = $i2['value'];
            if (!$i3) $i3 = '-';
            else $i3 = $i3['value'];

            return "<span style='color: darkgreen'>".$i1.",".$i2.",".$i3."</span>";
        },
        'header' => 'Ток, А',
        'format' => 'raw',
    ],
    [
        'class' => 'kartik\grid\DataColumn',
        'vAlign' => 'middle',
        'hAlign' => 'center',
        'width' => '80px',
        'mergeHeader' => true,
        'value' => function ($data) {
            $w = Measure::getLastMeasureNodeByType(MeasureType::POWER,$data['uuid'],MeasureType::MEASURE_TYPE_CURRENT,0);
            if (!$w) $w = '-';
            else $w = $w['value'];
            return "<span style='color: darkgreen'>".$w."</span>";
        },
        'header' => 'Мощность, кВт',
        'format' => 'raw',
    ],
    [
        'class' => 'kartik\grid\DataColumn',
        'vAlign' => 'middle',
        'hAlign' => 'center',
        'width' => '80px',
        'mergeHeader' => true,
        'value' => function ($data) {
            $w = Measure::getLastMeasureNodeByType(MeasureType::POWER, $data['uuid'], MeasureType::MEASURE_TYPE_TOTAL_CURRENT, 0);
            if (!$w) $w = '-';
            else $w = $w['value'];
            return "<span style='color: darkgreen'>".$w."</span>";
        },
        'header' => 'Энергия, кВт/ч',
        'format' => 'raw',
    ],
    [
        'class' => 'kartik\grid\DataColumn',
        'vAlign' => 'middle',
        'hAlign' => 'center',
        'width' => '80px',
        'mergeHeader' => true,
        'value' => function ($data) {
            return $data['software'];
        },
        'header' => 'Версия ПО',
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
        'heading' => '<i class="glyphicon glyphicon-tags"></i>&nbsp; Шкафы управления освещением',
        'headingOptions' => ['style' => 'background: #337ab7']
    ],
]);
