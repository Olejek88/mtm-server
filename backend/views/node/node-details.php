<?php

use common\models\Device;
use common\models\DeviceStatus;
use kartik\grid\GridView;
use yii\data\ActiveDataProvider;
use yii\helpers\Html;

/* @var $model */

$gridColumns = [
    [
        'attribute' => '_id',
        'hAlign' => 'center',
        'vAlign' => 'middle',
        'contentOptions' => [
            'class' => 'table_class',
            'style' => 'width: 50px; text-align: center'
        ],
        'headerOptions' => ['class' => 'text-center'],
        'content' => function ($data) {
            return $data->_id;
        }
    ],
    [
        'class' => 'kartik\grid\DataColumn',
        'attribute' => 'nodeUuid',
        'vAlign' => 'middle',
        'width' => '180px',
        'value' => function ($data) {
            return $data['node']['object']->getAddress() . ' [' . $data['node']['address'] . ']';
        },
        'filterType' => GridView::FILTER_SELECT2,
        'header' => 'Адрес',
        'filterInputOptions' => ['placeholder' => 'Любой'],
        'format' => 'raw',
    ],
    [
        'attribute' => 'deviceTypeUuid',
        'hAlign' => 'center',
        'vAlign' => 'middle',
        'width' => '180px',
        'value' => 'deviceType.title',
        'mergeHeader' => true,
        'filterType' => GridView::FILTER_SELECT2,
        'header' => 'Тип',
        'format' => 'raw',
        'contentOptions' => [
            'class' => 'table_class'
        ],
    ],
    [
        'attribute' => 'deviceStatusUuid',
        'header' => 'Статус',
        'contentOptions' => [
            'class' => 'table_class'
        ],
        'headerOptions' => ['class' => 'text-center'],
        'value' => function ($data) {
            $color = 'background-color: gray';
            if ($data['deviceStatusUuid'] == DeviceStatus::UNKNOWN ||
                $data['deviceStatusUuid'] == DeviceStatus::NOT_MOUNTED)
                $color = 'background-color: gray';
            if ($data['deviceStatusUuid'] == DeviceStatus::NOT_WORK ||
                $data['deviceStatusUuid'] == DeviceStatus::NOT_LINK)
                $color = 'background-color: red';
            if ($data['deviceStatusUuid'] == DeviceStatus::WORK)
                $color = 'background-color: green';
            $title = $data['deviceStatus']['title'];
            return "<span class='badge' style='" . $color . "; height: 12px; margin-top: -3px'> </span>&nbsp;" .
                $title;
        },
        'mergeHeader' => true,
        'format' => 'raw',
        'hAlign' => 'center',
        'vAlign' => 'middle',
        'width' => '180px',
    ],
    [
        'attribute' => 'interface',
        'hAlign' => 'center',
        'vAlign' => 'middle',
        'mergeHeader' => true,
        'contentOptions' => [
            'class' => 'table_class'
        ],
        'headerOptions' => ['class' => 'text-center'],
        'content' => function ($data) {
            $interfaces = [
                '0' => 'не указан',
                '1' => 'Последовательный порт',
                '2' => 'Zigbee',
                '3' => 'Ethernet'
            ];
            return $interfaces[$data["interface"]];
        },
    ],
    [
        'attribute' => 'serial',
        'vAlign' => 'middle',
        'width' => '180px',
        'filterType' => GridView::FILTER_SELECT2,
        'header' => 'Серийный',
        'filterInputOptions' => ['placeholder' => 'Любой'],
        'format' => 'raw',
    ],
    [
        'attribute' => 'port',
        'vAlign' => 'middle',
        'width' => '180px',
        'filterType' => GridView::FILTER_SELECT2,
        'header' => 'Порт',
        'filterInputOptions' => ['placeholder' => 'Любой'],
        'mergeHeader' => true,
        'format' => 'raw',
    ],
    [
        'attribute' => 'address',
        'vAlign' => 'middle',
        'width' => '180px',
        'mergeHeader' => true,
        'filterType' => GridView::FILTER_SELECT2,
        'header' => 'Адрес',
        'filterInputOptions' => ['placeholder' => 'Любой'],
        'format' => 'raw',
    ]
];

$devices = Device::find()->where(['deleted' => 0])->andWhere(['nodeUuid' => $model['uuid']]);
$provider = new ActiveDataProvider(
    [
        'query' => $devices,
        'sort' => false,
    ]
);

echo GridView::widget([
    'id' => 'flat-table',
    'dataProvider' => $provider,
    'columns' => $gridColumns,
    'containerOptions' => ['style' => 'overflow: auto'], // only set when $responsive = false
    'headerRowOptions' => ['class' => 'kartik-sheet-style'],
    'filterRowOptions' => ['class' => 'kartik-sheet-style'],
    'beforeHeader' => [
        '{toggleData}'
    ],
    'toolbar' => [
        ['content' =>
            Html::a('Новый', ['/device/create'], ['class' => 'btn btn-success'])
        ],
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
        'heading' => '<i class="glyphicon glyphicon-tags"></i>&nbsp; Устройства',
        'headingOptions' => ['style' => 'background: #337ab7']
    ],
]);
