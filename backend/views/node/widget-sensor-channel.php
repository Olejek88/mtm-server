<?php
/* @var $node
 */

use common\models\Device;
use common\models\Measure;
use common\models\MeasureType;
use common\models\Node;
use common\models\SensorChannel;
use kartik\editable\Editable;
use kartik\grid\GridView;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;

?>
<div class="info-box">
    <!-- /.box-header -->
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
                'hAlign' => 'center',
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

        $channels = SensorChannel::find()->where(['deviceUuid' => (Node::find()->where(['uuid' => $node['uuid']])->one())]);
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