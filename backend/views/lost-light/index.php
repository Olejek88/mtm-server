<?php

/* @var $searchModel backend\models\LostLightIndexSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

/* @var $this yii\web\View */

use kartik\grid\GridView;
use yii\helpers\Html;

$this->title = 'Родительские MAC';

$gridColumns = [
    [
        'class' => 'kartik\grid\DataColumn',
        'attribute' => 'date',
        'label' => 'Дата',
        'hAlign' => 'center',
        'content' => function ($model) {

            return Html::a(date('d.m.Y', strtotime($model['date'])), [
                '/lost-light/view',
                'uuid' => $model->nodeUuid,
                'date' => $model->date,
            ]);
        }
    ],
    [
        'class' => 'kartik\grid\DataColumn',
        'attribute' => 'nodeAddress',
        'label' => 'Адрес шкафа',
        'vAlign' => 'center',
    ],
];

echo GridView::widget([
    'id' => 'lost-light-table',
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
//        [
//            'content' => '',
//        ],
//        '{export}',
    ],
//    'export' => [
//        'fontAwesome' => true,
//        'target' => GridView::TARGET_BLANK,
//        'filename' => 'equipments'
//    ],
    'pjax' => false,
    'showPageSummary' => true,
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
        'heading' => '<i class="glyphicon glyphicon-tags"></i>&nbsp; Отчёты по неисправным светильникам',
        'headingOptions' => ['style' => 'background: #337ab7']
    ],
]);
