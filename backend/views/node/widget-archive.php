<?php

use common\models\Device;
use common\models\DeviceType;
use common\models\Measure;
use common\models\MeasureType;
use common\models\SensorChannel;
use kartik\grid\GridView;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;

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

        $measures = [];
        $device = (Device::find()->select('uuid')->where(['deleted' => 0])
            ->andWhere(['nodeUuid' => $node['uuid'], 'deviceTypeUuid' => DeviceType::DEVICE_COUNTER]))->asArray()->all();
        if ($device) {
            $sChannel = SensorChannel::find()->select('uuid')
                ->where([
                    'deviceUuid' => ArrayHelper::map($device, 'uuid', 'uuid'),
                    'measureTypeUuid' => MeasureType::POWER
                ])
                ->asArray()->all();
            if ($sChannel) {
                $measures = (Measure::find()
                    ->where(['sensorChannelUuid' => ArrayHelper::map($sChannel, 'uuid', 'uuid')])
                    ->andWhere(['parameter' => 0])
                    ->andWhere(['type' => MeasureType::MEASURE_TYPE_INTERVAL])
                    ->limit(5)
                    ->orderBy('date DESC'));
            }
        }
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
