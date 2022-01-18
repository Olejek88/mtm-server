<?php

/* @var $searchModel backend\models\LostLightSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

/* @var $this yii\web\View */

use kartik\export\ExportMenu;
use kartik\grid\GridView;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

$this->title = 'Неисправные светильники';

$gridColumns = [
    [
        'class' => 'kartik\grid\SerialColumn',
    ],
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
$exportMenu = ExportMenu::widget([
        'dataProvider' => $dataProvider,
        'columns' => $gridColumns,
        'autoWidth' => false,
        'onRenderSheet' => function ($sheet, $widget) use ($reportDate) {
            /** @var $sheet Worksheet */
            $sheet->getColumnDimension('A')->setWidth(5);
            $sheet->getColumnDimension('B')->setWidth(25);
            $sheet->getColumnDimension('C')->setWidth(30);
            $sheet->getColumnDimension('D')->setWidth(20);
            $sheet->getColumnDimension('E')->setWidth(40);
            $sheet->insertNewRowBefore(1);
            $sheet->mergeCells('A1:E1');
            $sheet->setCellValue('A1', 'Неисправные светильники на ' . $reportDate);
            $sheet->getRowDimension('1')->setRowHeight(50);
            $sheet->getStyle('A1')->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                ->setVertical(Alignment::VERTICAL_TOP);
        },
        'batchSize' => 10,
        'target' => '_blank',
//        'folder' => '@webroot/tmp', // this is default save folder on server
//        'dropdownOptions' => [
//            'label' => 'Export All', 'class' => 'btn btn-default'
//        ],
        'exportConfig' => [
            ExportMenu::FORMAT_CSV => false,
            ExportMenu::FORMAT_TEXT => false,
            ExportMenu::FORMAT_HTML => false,
        ],
        'filename' => 'Неисправные светильники - ' . $reportDate,
    ]) . "<hr>\n";
echo GridView::widget([
    'id' => 'equipment-table',
    'dataProvider' => $dataProvider,
//    'filterModel' => $searchModel,
    'columns' => $gridColumns,
    'containerOptions' => ['style' => 'overflow: auto'], // only set when $responsive = false
    'headerRowOptions' => ['class' => 'kartik-sheet-style'],
    'filterRowOptions' => ['class' => 'kartik-sheet-style'],
    'beforeHeader' => [
        [
            'columns' => [
                [
                    'content' => "Неисправные светильника на $reportDate",
                    'options' => ['colspan' => 10, 'class' => 'text-center warning']
                ],
            ],
        ],
    ],
    'toolbar' => [
        [
            'content' =>
//            Html::a('Добавить недостающие', ['/equipment/new'], ['class' => 'btn btn-success']),
//            Html::a('Новое', ['/device/create'], ['class' => 'btn btn-success']),
//            Html::a('<i class="glyphicon glyphicon-repeat"></i>', ['grid-demo'],
//                ['data-pjax' => 0, 'class' => 'btn btn-default', 'title' => Yii::t('app', 'Reset Grid')]),
//                '',
                $exportMenu,
        ],
//        '{export}',
    ],
    'toolbarContainerOptions' => [
        'class' => [
            'pull-left',
        ],
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
