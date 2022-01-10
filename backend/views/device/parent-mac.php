<?php
/* @var $searchModel backend\models\DeviceSearch */

/* @var $dataProvider yii\data\ActiveDataProvider */

use kartik\grid\GridView;

$this->title = 'Родительские MAC';

$gridColumns = [
    [
        'attribute' => '_id',
        'hAlign' => 'center',
        'vAlign' => 'middle',
        'mergeHeader' => true,
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
        'mergeHeader' => true,
        'value' => function ($data) {
            return $data['object']->getFullTitle() . ' [' . $data['node']['address'] . ']';
        },
        'header' => 'Адрес',
        'format' => 'raw',
    ],
    [
        'attribute' => 'name',
        'label' => 'Название',
        'hAlign' => 'center',
    ],
    [
        'attribute' => 'deviceStatusUuid',
        'contentOptions' => [
            'class' => 'table_class'
        ],
        'headerOptions' => ['class' => 'text-center'],
        'mergeHeader' => true,
        'hAlign' => 'center',
        'vAlign' => 'middle',
        'width' => '180px',
        'value' => function ($model) {
            return $model->deviceStatus->title;
        },
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
        'attribute' => 'address',
        'vAlign' => 'middle',
        'width' => '180px',
        'filterType' => GridView::FILTER_SELECT2,
        'header' => 'MAC',
        'headerOptions' => ['class' => 'text-center'],
        'mergeHeader' => true,
        'filterInputOptions' => ['placeholder' => 'Любой'],
        'format' => 'raw',
    ],
    [
        'hAlign' => 'center',
        'vAlign' => 'middle',
        'mergeHeader' => true,
        'contentOptions' => [
            'class' => 'table_class'
        ],
        'headerOptions' => ['class' => 'text-center'],
        'header' => 'Родительский MAC',
        'value' => function ($model) {
            return $model->parentMac->value;
        },
    ],
];

echo GridView::widget([
    'id' => 'equipment-table',
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    'columns' => $gridColumns,
    'containerOptions' => ['style' => 'overflow: auto'], // only set when $responsive = false
    'headerRowOptions' => ['class' => 'kartik-sheet-style'],
    'filterRowOptions' => ['class' => 'kartik-sheet-style'],
//    'beforeHeader' => [
//        '{toggleData}'
//    ],
    'toolbar' => [
        [
            'content' =>
//            Html::a('Добавить недостающие', ['/equipment/new'], ['class' => 'btn btn-success']),
//            Html::a('Новое', ['/device/create'], ['class' => 'btn btn-success']),
//            Html::a('<i class="glyphicon glyphicon-repeat"></i>', ['grid-demo'],
//                ['data-pjax' => 0, 'class' => 'btn btn-default', 'title' => Yii::t('app', 'Reset Grid')]),
                '',
        ],
        '{export}',
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
        'heading' => '<i class="glyphicon glyphicon-tags"></i>&nbsp; Родительские MAC',
        'headingOptions' => ['style' => 'background: #337ab7']
    ],
]);
