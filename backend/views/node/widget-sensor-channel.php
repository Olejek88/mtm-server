<?php
/* @var $node
 */

use common\models\Device;
use common\models\Measure;
use common\models\SensorChannel;
use kartik\grid\GridView;
use yii\data\ActiveDataProvider;

?>
<div class="info-box">
    <div class="box-body">
        <?php
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
                'mergeHeader' => true,
                'content' => function ($data) {
                    return $data->_id;
                }
            ],
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
                'vAlign' => 'middle',
                'width' => '150px',
                'header' => 'Тип измерения',
                'value' => 'measureType.title',
                'format' => 'raw',
                'contentOptions' => [
                    'class' => 'table_class'
                ],
            ],
            [
                'vAlign' => 'middle',
                'hAlign' => 'center',
                'header' => 'Значение',
                'mergeHeader' => true,
                'contentOptions' => [
                    'class' => 'table_class'
                ],
                'headerOptions' => ['class' => 'text-center'],
                'content' => function ($data) {
                    $measure = Measure::find()
                        ->where(['sensorChannelUuid' => $data['uuid']])
                        ->orderBy('createdAt DESC')
                        ->limit(1)
                        ->one();
                    if ($measure)
                        return $measure['value'].' ['.$measure['date'].']';
                    else
                        return '-';
                }
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
            ]
        ];

        $devices = Device::find()->select('uuid')
            ->where(['nodeUuid' => $node['uuid']])
            ->andWhere(['deleted' => 0])
            ->all();
        $devicesList2 = [];
        foreach ($devices as $device) {
            $devicesList2[] = $device['uuid'];
        }
        $channels = SensorChannel::find()->where(['IN','deviceUuid', $devicesList2]);
        $provider = new ActiveDataProvider(
            [
                'query' => $channels,
                'sort' =>false,
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
                    'heading' => '<i class="glyphicon glyphicons-spade"></i>&nbsp; Каналы измерения',
                    'headingOptions' => ['style' => 'background: #337ab7']
                ],
            ]
        );
        ?>
    </div>
</div>
