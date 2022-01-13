<?php

/* @var $searchModel backend\models\LostLightSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

/* @var $this yii\web\View */

use kartik\grid\GridView;

$this->title = 'Неисправные светильники';

$gridColumns = [
    [
        'attribute' => 'title',
        'hAlign' => 'center',
        'vAlign' => 'middle',
    ],
    [
        'attribute' => 'macAddress',
        'vAlign' => 'middle',
        'hAlign' => 'center',
        'width' => '180px',
//        'filterType' => GridView::FILTER_SELECT2,
        'header' => 'MAC',
        'headerOptions' => ['class' => 'text-center'],
        'mergeHeader' => true,
        'filterInputOptions' => ['placeholder' => 'Любой'],
        'format' => 'raw',
    ],
    [
        'attribute' => 'status',
        'contentOptions' => [
            'class' => 'table_class'
        ],
        'headerOptions' => ['class' => 'text-center'],
        'mergeHeader' => true,
        'hAlign' => 'center',
        'vAlign' => 'middle',
//        'width' => '180px',
    ],
    [
        'class' => 'kartik\grid\DataColumn',
        'attribute' => 'nodeAddress',
        'vAlign' => 'middle',
//        'width' => '180px',
        'mergeHeader' => true,
        'header' => 'Адрес',
        'format' => 'raw',
    ],
];

$reportDate = date('d.m.Y', strtotime(Yii::$app->request->getQueryParam('date')));
echo GridView::widget([
    'id' => 'equipment-table',
    'dataProvider' => $dataProvider,
//    'filterModel' => $searchModel,
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
    ],
    'exportConfig' => [
        GridView::HTML => [
            'filename' => 'Неисправные светильники - ' . $reportDate,
        ],
        GridView::CSV => [
            'filename' => 'Неисправные светильники - ' . $reportDate,
        ],
        GridView::TEXT => [
            'filename' => 'Неисправные светильники - ' . $reportDate,
        ],
        GridView::EXCEL => [
            'filename' => 'Неисправные светильники - ' . $reportDate,
        ],
        GridView::PDF => [
            'filename' => 'Неисправные светильники - ' . $reportDate,
        ],
        GridView::JSON => [
            'filename' => 'Неисправные светильники - ' . $reportDate,
        ],
    ],
    'pjax' => false,
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
        'heading' => '<i class="glyphicon glyphicon-tags"></i>&nbsp; Неисправные светильники',
        'headingOptions' => ['style' => 'background: #337ab7']
    ],
]);
