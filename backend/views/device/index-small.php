<?php
/* @var $searchModel backend\models\DeviceSearch */

use common\models\DeviceStatus;
use common\models\DeviceType;
use kartik\editable\Editable;
use kartik\grid\GridView;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

$this->title = Yii::t('app', 'Оборудование');

$gridColumns = [
    [
        'class' => 'kartik\grid\DataColumn',
        'attribute' => 'nodeUuid',
        'vAlign' => 'middle',
        'width' => '180px',
        'value' => function ($data) {
            return $data['node']['object']->getAddress().' ['.$data['node']['address'].']';
        },
        'filterType' => GridView::FILTER_SELECT2,
        'header' => 'Адрес',
        'filterInputOptions' => ['placeholder' => 'Любой'],
        'format' => 'raw',
    ],
    [
        'class' => 'kartik\grid\DataColumn',
        'attribute' => 'deviceTypeUuid',
        'hAlign' => 'center',
        'vAlign' => 'middle',
        'width' => '180px',
        'value' => 'deviceType.title',
        'filterType' => GridView::FILTER_SELECT2,
        'header' => 'Тип ' . Html::a('<span class="glyphicon glyphicon-plus"></span>',
                '/device-type/create?from=device/index',
                ['title' => Yii::t('app', 'Добавить')]),
        'filter' => ArrayHelper::map(DeviceType::find()->orderBy('title')->all(),
            'uuid', 'title'),
        'filterWidgetOptions' => [
            'pluginOptions' => ['allowClear' => true],
        ],
        'filterInputOptions' => ['placeholder' => 'Любой'],
        'format' => 'raw',
        'contentOptions' => [
            'class' => 'table_class'
        ],
    ],
    [
        'class' => 'kartik\grid\DataColumn',
        'attribute' => 'deviceStatusUuid',
        'header' => 'Статус ' . Html::a('<span class="glyphicon glyphicon-plus"></span>',
                '/device-status/create?from=device/index',
                ['title' => Yii::t('app', 'Добавить')]),
        'contentOptions' => [
            'class' => 'table_class'
        ],
        'headerOptions' => ['class' => 'text-center'],
        'hAlign' => 'center',
        'vAlign' => 'middle',
        'width' => '180px',
    ],
];

echo GridView::widget([
    'id' => 'equipment-table',
    'dataProvider' => $dataProvider,
    'columns' => $gridColumns,
    'containerOptions' => ['style' => 'overflow: auto'], // only set when $responsive = false
    'filterRowOptions' => ['class' => 'kartik-sheet-style'],
    'beforeHeader' => [
        '{toggleData}'
    ],
    'pjax' => true,
    'showPageSummary' => false,
    'pageSummaryRowOptions' => ['style' => 'line-height: 0; padding: 0'],
    'summary' => '',
    'toolbar' => [
        ['content' => ''
        ],
        '',
    ],
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
