<?php

use common\models\Device;
use common\models\Measure;
use common\models\SensorChannel;
use kartik\grid\GridView;
use yii\data\ActiveDataProvider;
use common\models\DeviceType;
use common\models\MeasureType;

/* @var $node
 */
?>

<div class="box box-primary">
    <!-- /.box-header -->
    <div class="box-body">
        <?php
        $gridColumns = [
            [
                'attribute' => 'date',
                'contentOptions' => [
                    'class' => 'table_class'
                ],
                'headerOptions' => ['class' => 'text-center'],
            ],
            [
                'attribute' => 'value',
                'hAlign' => 'center',
                'vAlign' => 'middle',
                'headerOptions' => ['class' => 'kv-sticky-column'],
                'contentOptions' => ['class' => 'kv-sticky-column'],
            ],
            [
                'attribute' => 'sensorChannel.title',
                'hAlign' => 'center',
                'vAlign' => 'middle',
                'contentOptions' => [
                    'class' => 'table_class'
                ],
                'headerOptions' => ['class' => 'text-center'],
            ],
            /*            [
                            'attribute' => 'sensorChannel.measureType.title',
                            'hAlign' => 'center',
                            'header' => 'Тип измерения',
                            'vAlign' => 'middle',
                            'contentOptions' => [
                                'class' => 'table_class'
                            ],
                            'headerOptions' => ['class' => 'text-center'],
                        ]*/
        ];

        $device = (Device::find()->select('uuid')
            ->where(['nodeUuid' => $node['uuid'], 'deviceTypeUuid' => DeviceType::DEVICE_ELECTRO]));
        $sChannel = (SensorChannel::find()->select('uuid')
            ->where(['deviceUuid' => $device, 'measureTypeUuid' => MeasureType::POWER]));
        $measures = (Measure::find()
            ->where(['sensorChannelUuid' => $sChannel])->limit(5)->orderBy('date DESC'));
        $provider = new ActiveDataProvider(
            [
                'query' => $measures,
                'sort' => false,
                'pagination' => false,
            ]
        );

        echo GridView::widget(
            [
                'dataProvider' => $provider,
                'columns' => $gridColumns,
                'headerRowOptions' => ['class' => 'kartik-sheet-style'],
                'filterRowOptions' => ['class' => 'kartik-sheet-style'],
                'containerOptions' => ['style' => 'overflow: auto'],
                'beforeHeader' => [
                    '{toggleData}'
                ],
                'toolbar' => [
                    []
                ],
                'pjax' => true,
                'showPageSummary' => false,
                'pageSummaryRowOptions' => ['style' => 'line-height: 0; padding: 0'],
                'summary' => '',
                'bordered' => true,
                'striped' => false,
                'condensed' => true,
                'responsive' => false,
                'hover' => true,
                'floatHeader' => false,
                'panel' => [
                    'type' => GridView::TYPE_PRIMARY,
                    'heading' => '<i class="glyphicon glyphicons-spade"></i>&nbsp; Последние измерения',
                    'headingOptions' => ['style' => 'background: #337ab7']
                ],
            ]
        );
        ?>
    </div>
</div>
<!-- /.box -->
