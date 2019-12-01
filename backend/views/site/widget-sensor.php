<?php

use common\models\Device;
use common\models\DeviceStatus;
use common\models\DeviceType;
use common\models\Measure;
use common\models\MeasureType;
use common\models\SensorChannel;
use kartik\grid\GridView;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

?>

<div class="box box-primary">
    <div class="box-header with-border">
        <h3 class="box-title">Таблица работоспособности</h3>
        <div class="box-tools pull-right">
            <div class="btn-group">
                <?php echo Html::a("<button type='button' class='btn btn-box-tool'>
                    <i class='fa fa-link'></i></button>", ['/device']); ?>
            </div>
            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
            </button>
            <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i>
            </button>
        </div>
    </div>
    <div class="box-body">
        <?php
        $gridColumns = [
            [
                'attribute' => 'deviceUuid',
                'vAlign' => 'middle',
                'header' => 'Устройство',
                'mergeHeader' => true,
                'contentOptions' => [
                    'class' => 'table_class'
                ],
                'headerOptions' => ['class' => 'text-center'],
                'content' => function ($data) {
                    return $data['device']->getFullTitle();
                }
            ],
            [
                'attribute' => 'measureType.title',
                'vAlign' => 'middle',
                'hAlign' => 'center',
                'width' => '150px',
                'header' => 'Тип измерения',
                'format' => 'raw',
                'contentOptions' => [
                    'class' => 'table_class'
                ],
            ],
            [
                'attribute' => 'register',
                'hAlign' => 'center',
                'vAlign' => 'middle',
                'header' => 'Регистр',
                'format' => 'raw',
                'headerOptions' => ['class' => 'kartik-sheet-style'],
                'mergeHeader' => true,
                'contentOptions' => [
                    'class' => 'table_class'
                ],
            ],
            [
                'hAlign' => 'center',
                'vAlign' => 'middle',
                'header' => 'Значение',
                'format' => 'raw',
                'value' => function ($data) {
                    $measure = Measure::find()
                        ->where(['sensorChannelUuid' => $data['uuid']])
                        ->orderBy('date DESC')
                        ->one();
                    $value = "-";
                    if ($measure)
                        $value = $measure->value;
                    return $value;
                },
                'headerOptions' => ['class' => 'kartik-sheet-style'],
                'mergeHeader' => true,
                'contentOptions' => [
                    'class' => 'table_class'
                ],
            ],
            [
                'attribute' => 'changedAt',
                'hAlign' => 'center',
                'vAlign' => 'middle',
                'header' => 'Измерение',
                'value' => function ($data) {
                    $measure = Measure::find()
                        ->where(['sensorChannelUuid' => $data['uuid']])
                        ->orderBy('date DESC')
                        ->one();
                    $value = "-";
                    if ($measure)
                        $value = $measure->date;
                    return $value;
                },
                'format' => 'raw',
                'headerOptions' => ['class' => 'kartik-sheet-style'],
                'mergeHeader' => true,
                'contentOptions' => [
                    'class' => 'table_class'
                ],
            ],
        ];

        $devices = SensorChannel::find()->where(['OR',
            ['measureTypeUuid' => MeasureType::SENSOR_CO2]]);
        $provider = new ActiveDataProvider(
            [
                'query' => $devices,
                'sort' => false,
            ]
        );

        echo GridView::widget([
            'id' => 'equipment-table',
            'dataProvider' => $provider,
            'columns' => $gridColumns,
            'containerOptions' => ['style' => 'overflow: auto'], // only set when $responsive = false
            'headerRowOptions' => ['class' => 'kartik-sheet-style'],
            'filterRowOptions' => ['class' => 'kartik-sheet-style'],
            'pjax' => true,
            'toolbar' => [
                []
            ],
            'showPageSummary' => false,
            'pageSummaryRowOptions' => ['style' => 'line-height: 0; padding: 0'],
            'summary' => '',
            'bordered' => true,
            'striped' => false,
            'condensed' => false,
            'responsive' => true,
            'persistResize' => false,
            'hover' => true,
            'rowOptions' => function ($model) {
                if ($model->device->deviceStatusUuid != DeviceStatus::WORK)
                    return ['class' => 'danger'];
            }
        ]);
        ?>
    </div>
</div>
