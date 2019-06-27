<?php
/* @var $searchModel backend\models\MessageSearch */

use yii\helpers\Html;
use kartik\grid\GridView;

$this->title = Yii::t('app', 'Сообщения');

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
        'value' => function ($data) {
            if ($data['node'])
                return $data['node']['object']->getAddress().' ['.$data['node']['address'].']';
            else
                return 'не задан';
        },
        'filterType' => GridView::FILTER_SELECT2,
        'header' => 'Адрес',
        'filterInputOptions' => ['placeholder' => 'Любой'],
        'format' => 'raw',
    ],
    [
        'header' => 'Проиграть',
        'vAlign' => 'middle',
        'hAlign' => 'center',
        'content' => function ($data) {
            $link = Html::a('<span class="glyphicon glyphicon-play"></span>&nbsp',
                ['/message/send', 'messageUuid' => $data['uuid'], 'nodeId' => $data['node']['_id']], ['title' => 'Проиграть сообщение',]
            );
            return $link;
        },
        'headerOptions' => ['class' => 'kartik-sheet-style'],
    ],
    [
        'class' => 'kartik\grid\DataColumn',
        'attribute' => 'link',
        'vAlign' => 'middle',
        'value' => function ($data) {
            return $data['link'];
        },
        'header' => 'Ссылка',
        'format' => 'raw',
    ],
    [
        'class' => 'kartik\grid\DataColumn',
        'attribute' => 'changedAt',
        'hAlign' => 'center',
        'vAlign' => 'middle',
        'headerOptions' => ['class' => 'kv-sticky-column'],
        'contentOptions' => ['class' => 'kv-sticky-column'],
        'header' => 'Дата изменения',
    ],
    [
        'class' => 'kartik\grid\ActionColumn',
        'header' => 'Действия',
        'headerOptions' => ['class' => 'kartik-sheet-style'],
    ]
];

echo GridView::widget([
    'id' => 'flat-table',
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
            Html::a('Новое',
                ['../message/new'],
                [
                    'class' => 'btn btn-success',
                    'title' => 'Добавить',
                    'data-toggle' => 'modal',
                    'data-target' => '#modalAdd',
                ]
            ),
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
        'heading' => '<i class="glyphicon glyphicon-tags"></i>&nbsp; Объекты',
        'headingOptions' => ['style' => 'background: #337ab7']
    ],
]);

$this->registerJs('$("#modalAdd").on("hidden.bs.modal",
function () {
    window.location.replace("index");
})');

?>
<div class="modal remote fade" id="modalAdd">
    <div class="modal-dialog">
        <div class="modal-content loader-lg"></div>
    </div>
</div>
