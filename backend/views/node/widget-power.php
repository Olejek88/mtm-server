<?php

/* @var $categories
 * @var $values
 * @var $node
 */

use common\models\Device;
use common\models\DeviceType;
use common\models\Measure;
use common\models\MeasureType;
use common\models\SensorChannel;
use yii\helpers\Html;

$this->registerJsFile('/js/vendor/lib/HighCharts/highcharts.js');
$this->registerJsFile('/js/vendor/lib/HighCharts/modules/exporting.js');

$device = (Device::find()->select('uuid')
    ->where(['nodeUuid' => $node['uuid'], 'deviceTypeUuid' => DeviceType::DEVICE_COUNTER]));
$sChannel = (SensorChannel::find()->select('uuid')
    ->where(['deviceUuid' => $device, 'measureTypeUuid' => MeasureType::POWER]));
$last_measures = (Measure::find()
    ->where(['sensorChannelUuid' => $sChannel])->orderBy('date DESC'))->limit(100)->all();

$cnt = 0;
$categories = '';
$values = '';
foreach ($last_measures as $measure) {
    if ($cnt > 0) {
        $categories .= ',';
        $values .= ',';
    }
    $categories .= "'" . date("d H:i", strtotime($measure->date)) . "'";
    $values .= $measure->value;
    $cnt++;
}

?>
<div class="box">
    <div class="box-header with-border">
        <h3 class="box-title">Последние измерения</h3>

        <div class="box-tools pull-right">
            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
            <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
        </div>
    </div>
    <div class="box-body">
        <div class="row">
            <div class="col-md-12">
                <p class="text-center">
                    <strong>Потребляемая мощность</strong>
                </p>
                <div class="chart">
                    <div id="container" style="height: 250px;"></div>
                    <script type="text/javascript">
                        document.addEventListener("DOMContentLoaded", function () {
                            Highcharts.chart('container', {
                                data: {
                                    table: 'datatable'
                                },
                                chart: {
                                    type: 'column'
                                },
                                title: {
                                    text: ''
                                },
                                xAxis: {
                                    categories: [<?php echo $categories; ?>]
                                },
                                legend: {
                                    align: 'right',
                                    x: -300,
                                    verticalAlign: 'top',
                                    y: 0,
                                    floating: true,
                                    backgroundColor: (Highcharts.theme && Highcharts.theme.background2) || 'white',
                                    borderColor: '#CCC',
                                    borderWidth: 1,
                                    shadow: false
                                },
                                tooltip: {
                                    headerFormat: '<b>{point.x}</b><br/>',
                                    pointFormat: '{series.name}: {point.y}<br/>Всего: {point.stackTotal}'
                                },
                                plotOptions: {
                                    column: {
                                        stacking: 'normal',
                                        dataLabels: {
                                            enabled: true,
                                            color: (Highcharts.theme && Highcharts.theme.dataLabelsColor) || 'white'
                                        }
                                    }
                                },
                                yAxis: {
                                    title: {
                                        text: 'Потребляемая мощность'
                                    }
                                },
                                series: [{
                                    name: 'Потребляемая мощность (кВт/ч)',
                                    data: [<?php echo $values; ?>]
                                }]
                            });
                        });
                    </script>
                </div>
                <!-- /.chart-responsive -->
            </div>
            <!-- /.col -->
        </div>
        <!-- /.row -->
    </div>
</div>


