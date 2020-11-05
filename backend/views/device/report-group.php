<?php

use kartik\grid\GridView;
use kartik\widgets\DatePicker;
use kartik\widgets\Select2;
use yii\data\ArrayDataProvider;

/** @var $groupUuid */
/** @var $startDate */
/** @var $dataProvider */
/** @var $groupNames */
/** @var $groups */

$this->title = Yii::t('app', 'Архив по группам');

$gridColumns = [
    [
        'hAlign' => 'center',
        'vAlign' => 'middle',
        'mergeHeader' => true,
        'contentOptions' => [
            'class' => 'table_class',
            'style' => 'width: 40px'
        ],
        'headerOptions' => ['class' => 'text-center'],
        'content' => function ($value) {
            return $value['date'];
        }
    ],
];

$beforeHeaderColumns = [
    ['content' => 'Дата', 'options' => ['colspan' => 1, 'class' => 'text-center warning']],
];
/** @var ArrayDataProvider $dataProvider */
$groupCount = (count($dataProvider->allModels[0]) - 1) / 5;
for ($i = 0; $i < $groupCount; $i++) {
    $groupId = 'g' . $i;
    $beforeHeaderColumns[] = ['content' => $groupNames[$i], 'options' => ['colspan' => 3, 'class' => 'text-center warning']];
    $gridColumns[] = [
        'class' => 'kartik\grid\DataColumn',
        'vAlign' => 'middle',
        'hAlign' => 'center',
        'mergeHeader' => true,
        'width' => '180px',
        'header' => 'Тариф1, кВт*ч',
        'format' => 'raw',
        'content' => function ($value) use ($groupId) {
            return number_format($value[$groupId . 'w1'], 3);
        }
    ];
    $gridColumns[] = [
        'class' => 'kartik\grid\DataColumn',
        'vAlign' => 'middle',
        'hAlign' => 'center',
        'mergeHeader' => true,
        'width' => '180px',
        'header' => 'Тариф2, кВт*ч',
        'format' => 'raw',
        'content' => function ($value) use ($groupId) {
            return number_format($value[$groupId . 'w2'], 3);
        }
    ];
    $gridColumns[] = [
        'class' => 'kartik\grid\DataColumn',
        'vAlign' => 'middle',
        'hAlign' => 'center',
        'mergeHeader' => true,
        'width' => '180px',
        'header' => 'Сумма, кВт*ч',
        'format' => 'raw',
        'content' => function ($value) use ($groupId) {
            return number_format($value[$groupId . 'ws'], 3);
        }
    ];
}


$datePicker = DatePicker::widget([
    'name' => 'start_time',
    'value' => $startDate,
    'removeButton' => false,
    'pjaxContainerId' => 'report-group-container',
    'pluginOptions' => [
        'autoclose' => true,
        'format' => 'yyyy-mm',
        'startView' => 'year',
        'minViewMode' => 'months',
    ],
    'options' => [
        'readonly' => true,
        'class' => ['add-filter'],
    ],
]);

$groupSelect = Select2::widget([
    'name' => 'group',
    'data' => $groups,
    'value' => $groupUuid,
    'class' => 'add-filter',
    'pjaxContainerId' => 'report-group-container',
    'pluginOptions' => [
//        'allowClear' => true,
        'width' => '600px',
    ],
    'options' => [
        'class' => ['add-filter'],
        'placeholder' => 'Выберите группу',
        'multiple' => true,
        'readonly' => true,
    ],
]);

echo GridView::widget([
    'id' => 'program-report-table',
    'filterSelector' => '.add-filter',
    'dataProvider' => $dataProvider,
//    'filterModel' => $searchModel,
    'columns' => $gridColumns,
    'showPageSummary' => true,
    'containerOptions' => ['style' => 'overflow: auto'], // only set when $responsive = false
    'headerRowOptions' => ['class' => 'kartik-sheet-style'],
    'filterRowOptions' => ['class' => 'kartik-sheet-style'],
    'beforeHeader' => [
        '{toggleData}',
        [
            'columns' => [
                [
                    'content' => 'За период с ' . date('Y-m', strtotime($startDate . ' -12 month')) . " по $startDate",
                    'options' => ['colspan' => 1 + $groupCount * 5, 'class' => 'text-center warning']
                ],
            ],
        ],
        [
            'columns' => $beforeHeaderColumns,
        ]
    ],
    'toolbar' => [
        [
            'content' => $groupSelect,
        ],
        [
            'content' => $datePicker,
            'options' => [
                'style' => 'width:200px',
            ],
        ],
        '{export}',
    ],

    'export' => [
        'fontAwesome' => true,
        'target' => GridView::TARGET_BLANK,
        'filename' => 'report-group'
    ],
    'pjax' => true,
    'pjaxSettings' => [
        'options' => [
            'id' => 'report-group-container',
            'enablePushState' => false,
        ],
    ],
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
        'heading' => '<i class="glyphicon glyphicon-tags"></i>&nbsp; Потребление по группам',
        'headingOptions' => ['style' => 'background: #337ab7']
    ],
]);
