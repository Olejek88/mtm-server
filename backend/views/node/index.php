<?php
/* @var $searchModel backend\models\NodeSearch */

use common\models\DeviceStatus;
use common\models\Operation;
use kartik\editable\Editable;
use kartik\grid\GridView;
use yii\helpers\Html;

$this->title = Yii::t('app', 'Контроллера');

$gridColumns = [
    [
        'attribute' => '_id',
        'hAlign' => 'center',
        'vAlign' => 'middle',
        'mergeHeader' => true,
        'header' => '#',
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
        'class' => 'kartik\grid\ExpandRowColumn',
        'width' => '50px',
        'hAlign' => 'center',
        'vAlign' => 'middle',
        'mergeHeader' => true,
        'value' => function ($model, $key, $index, $column) {
            return GridView::ROW_COLLAPSED;
        },
        'detail' => function ($model, $key, $index, $column) {
            return Yii::$app->controller->renderPartial('node-details', ['model' => $model]);
        },
        'expandIcon' => '<span class="glyphicon glyphicon-expand"></span>',
        'headerOptions' => ['class' => 'kartik-sheet-style'],
        'expandOneOnly' => true
    ],
    [
        'class' => 'kartik\grid\DataColumn',
        'attribute' => 'objectUuid',
        'vAlign' => 'middle',
        'mergeHeader' => true,
        'value' => function ($data) {
            return Html::a($data['object']['house']['street']->title . ', ' . $data['object']['house']->number . '-' . $data['object']->title,
                ['/node/dashboard', 'uuid' => $data['uuid'], 'type' => 'node'], ['data-pjax' => 0]);
        },
        'header' => 'Объект ' . Html::a('<span class="glyphicon glyphicon-plus"></span>',
                '/object/create?from=equipment/index',
                ['title' => Yii::t('app', 'Добавить')]),
        'format' => 'raw',
    ],
    [
        'class' => 'kartik\grid\EditableColumn',
        'attribute' => 'deviceStatusUuid',
        'mergeHeader' => true,
        'header' => 'Статус ' . Html::a('<span class="glyphicon glyphicon-plus"></span>',
                '/device-status/create?from=equipment/index',
                ['title' => Yii::t('app', 'Добавить')]),
        'contentOptions' => [
            'class' => 'table_class'
        ],
        'headerOptions' => ['class' => 'text-center'],
        'hAlign' => 'center',
        'vAlign' => 'middle',
        'width' => '180px',
        'editableOptions' => function () {
            $status = [];
            $list = [];
            $statuses = DeviceStatus::find()->orderBy('title')->all();
            foreach ($statuses as $stat) {
                $color = 'background-color: gray';
                if ($stat['uuid'] == DeviceStatus::UNKNOWN ||
                    $stat['uuid'] == DeviceStatus::NOT_MOUNTED)
                    $color = 'background-color: gray';
                if ($stat['uuid'] == DeviceStatus::NOT_WORK || $stat['uuid'] == DeviceStatus::NOT_LINK)
                    $color = 'background-color: red';
                if ($stat['uuid'] == DeviceStatus::WORK)
                    $color = 'background-color: green';
                $list[$stat['uuid']] = $stat['title'];
                $status[$stat['uuid']] = "<span class='badge' style='" . $color . "; height: 12px; margin-top: -3px'> </span>&nbsp;" .
                    $stat['title'];
            }
            return [
                'header' => 'Статус',
                'size' => 'md',
                'inputType' => Editable::INPUT_DROPDOWN_LIST,
                'displayValueConfig' => $status,
                'data' => $list
            ];
        },
    ],
    [
        'class' => 'kartik\grid\EditableColumn',
        'attribute' => 'address',
        'mergeHeader' => true,
        'width' => '180px',
        'hAlign' => 'center',
        'vAlign' => 'middle',
        'contentOptions' => [
            'class' => 'table_class'
        ],
        'headerOptions' => ['class' => 'text-center'],
        'content' => function ($data) {
            return $data->address;
        }
    ],
    [
        'class' => 'kartik\grid\EditableColumn',
        'attribute' => 'phone',
        'mergeHeader' => true,
        'width' => '180px',
        'hAlign' => 'center',
        'vAlign' => 'middle',
        'contentOptions' => [
            'class' => 'table_class'
        ],
        'headerOptions' => ['class' => 'text-center']
    ],
    [
        'class' => 'kartik\grid\EditableColumn',
        'attribute' => 'software',
        'hAlign' => 'center',
        'vAlign' => 'middle',
        'mergeHeader' => true,
        'contentOptions' => [
            'class' => 'table_class'
        ],
        'headerOptions' => ['class' => 'text-center']
    ],
    [
        'class' => 'kartik\grid\ActionColumn',
        'header' => 'Действия',
        'template' => '{update} {delete}',
        'headerOptions' => ['class' => 'kartik-sheet-style'],
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
        ['content' =>
            Html::a('Новое', ['/node/create'], ['class' => 'btn btn-success']),
            Html::a('<i class="glyphicon glyphicon-repeat"></i>', ['grid-demo'],
                ['data-pjax' => 0, 'class' => 'btn btn-default', 'title' => Yii::t('app', 'Reset Grid')])
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
        'heading' => '<i class="glyphicon glyphicon-tags"></i>&nbsp; Контроллеры',
        'headingOptions' => ['style' => 'background: #337ab7']
    ],
]);
