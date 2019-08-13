<?php

/* @var $sensorChannelUuid
 * @var $type
 * @var $parameter
 */

use common\models\Device;
use common\models\DeviceType;
use common\models\Measure;
use common\models\MeasureType;
use common\models\SensorChannel;
use yii\helpers\Html;

$this->registerJsFile('/js/vendor/lib/HighCharts/highcharts.js');
$this->registerJsFile('/js/vendor/lib/HighCharts/modules/exporting.js');

$title="";
$measures=[];
if ($sensorChannelUuid) {
    $sensorChannel = (SensorChannel::find()->where(['uuid' => $sensorChannelUuid]))->one();
    if ($sensorChannel)
        $title = $sensorChannel['title'];
    $measures = (Measure::find()
        ->where(['sensorChannelUuid' => $sensorChannelUuid])
        ->andWhere(['type' => MeasureType::MEASURE_TYPE_INTERVAL])
        ->orderBy('date DESC'))
        ->limit(200)->all();
}

$cnt = 0;
$categories = '';
$values = '';
foreach (array_reverse($measures) as $measure) {
    if ($cnt > 0) {
        $categories .= ',';
        $values .= ',';
    }
    $categories .= "'" . $measure->date . "'";
    $values .= $measure->value;
    $cnt++;
}
?>
<div class="box">
    <div class="box-header with-border">
        <h3 class="box-title"><?php echo $title ?></h3>
    </div>
    <div class="box-body">
        <div class="row">
            <div class="col-md-12">
                <div class="chart">
                    <div id="container-line" style="height: 350px;"></div>
                    <script type="text/javascript">
                        document.addEventListener("DOMContentLoaded", function () {
                            Highcharts.chart('container-line', {
                                data: {
                                    table: 'datatable'
                                },
                                chart: {
                                    type: 'line'
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
                                    min: 0,
                                    title: {
                                        text: '<?= $title ?>'
                                    }
                                },
                                series: [{
                                    name: '<?= $title ?>',
                                    data: [<?php echo $values; ?>]
                                }]
                            });
                        });
                    </script>
                </div>
            </div>
        </div>
    </div>
</div>


