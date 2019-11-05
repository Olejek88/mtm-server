<?php
/* @var $node
 */

use common\models\Device;
use common\models\DeviceRegister;
use kartik\grid\GridView;
use yii\data\ActiveDataProvider;

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
                'content' => function ($data) {
                    return $data->_id;
                }
            ],
            [
                'attribute' => 'date',
                'contentOptions' => [
                    'class' => 'table_class'
                ],
                'headerOptions' => ['class' => 'text-center'],
                'hAlign' => 'center',
                'vAlign' => 'middle',
                'width' => '100px',
            ],
            [
                'attribute' => 'deviceUuid',
                'hAlign' => 'center',
                'vAlign' => 'middle',
                'width' => '180px',
                'value' => 'device.name',
                'format' => 'raw',
                'contentOptions' => [
                    'class' => 'table_class'
                ],
            ],
            [
                'attribute' => 'description',
                'contentOptions' => [
                    'class' => 'table_class'
                ],
                'headerOptions' => ['class' => 'text-center'],
                'hAlign' => 'center',
                'vAlign' => 'middle',
                'width' => '180px',
            ]
        ];

        $devices = Device::find()->select('uuid')->where(['nodeUuid' => $node['uuid']])
            ->andWhere(['deleted' => 0])->all();
        $devicesList2 = [];
        foreach ($devices as $device) {
            $devicesList2[] = $device['uuid'];
        }

        $deviceRegisters = DeviceRegister::find()
            ->where(['IN','deviceUuid', $devicesList2])
            ->limit(8)
            ->orderBy('date desc');
        $provider = new ActiveDataProvider(
            [
                'query' => $deviceRegisters,
                'sort' =>false,
                'pagination' => false
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
                    'heading' => '<i class="glyphicon glyphicons-spade"></i>&nbsp; Журнал устройств',
                    'headingOptions' => ['style' => 'background: #337ab7']
                ],
            ]
        );
        ?>
    </div>
</div>
