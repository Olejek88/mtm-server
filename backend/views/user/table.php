<?php
/* @var $searchModel backend\models\UserSearch */

use common\models\User;
use kartik\editable\Editable;
use kartik\grid\GridView;

$this->title = Yii::t('app', 'Управление пользователями');

$gridColumns = [
    [
        'attribute' => '_id',
        'hAlign' => 'center',
        'vAlign' => 'middle',
//        'name' => '#',
        'contentOptions' => [
            'class' => 'table_class',
            'style' => 'width: 50px; text-align: center'
        ],
        'headerOptions' => ['class' => 'text-center'],
        'mergeHeader' => true,
        'content' => function ($data) {
            return $data->_id;
        }
    ],
    [
        'class' => 'kartik\grid\EditableColumn',
        'attribute' => 'name',
//        'name' => 'Название',
        'vAlign' => 'middle',
        'contentOptions' => [
            'class' => 'table_class'
        ],
        'headerOptions' => ['class' => 'text-center'],
        'editableOptions' => [
            'size' => 'lg',
        ],
        'content' => function ($data) {
            return $data->name;
        }
    ],
    [
        'class' => 'kartik\grid\EditableColumn',
        'attribute' => 'type',
        'vAlign' => 'middle',
        'width' => '180px',
        'header' => 'Тип пользователя',
        'mergeHeader' => true,
        'format' => 'raw',
        'value' => function ($model, $key, $index, $widget) {
            if ($model->type == 0)
                return 'Администратор';
            if ($model->type == 1)
                return 'Оператор';
            return 'Пользователь';
        },
        'editableOptions' => function ($model, $key, $index, $widget) {
            return [
                'header' => 'Тип',
                'size' => 'lg',
                'inputType' => Editable::INPUT_DROPDOWN_LIST,
                'displayValueConfig' => [
                    0 => 'Администратор',
                    1 => 'Оператор'
                ],
                'data' => [
                    0 => 'Администратор',
                    1 => 'Оператор'
                ]
            ];
        },
    ],
    [
        'class' => 'kartik\grid\EditableColumn',
        'attribute' => 'status',
        'vAlign' => 'middle',
        'width' => '180px',
        'header' => 'Статус',
        'mergeHeader' => true,
        'format' => 'raw',
        'value' => function ($model, $key, $index, $widget) {
            if ($model->status == User::STATUS_ACTIVE) {
                return 'Активен';
            } else {
                return 'Отключен';
            }
        },
        'editableOptions' => function ($model, $key, $index, $widget) {
            return [
                'header' => 'Тип',
                'size' => 'lg',
                'inputType' => Editable::INPUT_DROPDOWN_LIST,
                'displayValueConfig' => [
                    User::STATUS_ACTIVE => 'Активен',
                    User::STATUS_DELETED => 'Отключен'
                ],
                'data' => [
                    User::STATUS_ACTIVE => 'Активен',
                    User::STATUS_DELETED => 'Отключен'
                ]
            ];
        },
    ],
    [
        'attribute' => 'whoIs',
        'hAlign' => 'center',
        'vAlign' => 'middle',
        'header' => 'Должность',
        'headerOptions' => ['class' => 'kartik-sheet-style'],
        'mergeHeader' => true,
        'format' => 'raw',
        'contentOptions' => [
            'class' => 'table_class'
        ],
    ],
    [
        'class' => 'kartik\grid\ActionColumn',
        'header' => 'Действия',
        'headerOptions' => ['class' => 'kartik-sheet-style'],
    ]
];

echo GridView::widget([
    'id' => 'users-table',
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
        '{export}',
    ],
    'export' => [
        'fontAwesome' => true,
        'target' => GridView::TARGET_BLANK,
        'filename' => 'users'
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
        'heading' => '<i class="glyphicon glyphicon-tags"></i>&nbsp; Пользователи',
        'headingOptions' => ['style' => 'background: #337ab7']
    ],
]);
