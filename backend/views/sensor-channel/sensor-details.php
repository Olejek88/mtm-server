<?php

use common\models\Device;
use common\models\Measure;
use common\models\SensorChannel;

/* @var $model Device */

$equipment = Device::find()
    ->where(['uuid' => $model['uuid']])
    ->one();
$measures = [];

if ($equipment) {
    $sensorChannel = SensorChannel::find()
        ->where(['uuid' => $equipment['uuid']])
        ->one();
    if ($sensorChannel) {
        $measures = Measure::find()
            ->where(['sensorChannelUuid' => $sensorChannel['uuid']])
            ->all();
    }
}
$categories = "[";
$values = "name: 'Значения', data: [";
$zero = 0;
$num = rand(0, 1000);
$counts = 0;
foreach ($measures as $measure) {
    if ($counts > 0) {
        $values .= ",";
        $categories .= ",";
    }
    $values .= $measure['value'];
    $categories .= "'" . date_format(date_create($measure['date']), 'Y-m-d') . "'";
    $counts++;
}
$values .= "]";
$categories .= "]";
?>

<h3><?php echo $equipment['deviceType']->title ?></h3>
<div class="row">
    <div class="col-sm-2">
        <table class="table table-bordered table-condensed table-hover small kv-table">
            <tr class="success">
                <th colspan="2" class="text-center text-danger">Последние показания</th>
            </tr>
            <?php
            foreach ($measures as $measure) {
                echo '<tr><td>' . $measure['date'] . '</td>
                                          <td class="text-right">' . $measure['value'] . '</td></tr>';
            }
            ?>
        </table>
    </div>
    <div class="col-sm-4">
        <table class="table table-bordered table-condensed table-hover small kv-table">
            <tr class="danger">
                <td class="text-center">
                    <div id="container<?php echo $num ?>" style="height: 250px; width: 430px"></div>
                    <script src="/js/vendor/lib/HighCharts/highcharts.js"></script>
                    <script src="/js/vendor/lib/HighCharts/modules/exporting.js"></script>
                    <script type="text/javascript">
                        Highcharts.chart('container<?php echo $num ?>', {
                            data: {
                                table: 'data_table'
                            },
                            chart: {
                                type: 'column'
                            },
                            title: {
                                text: ''
                            },
                            legend: {
                                enabled: false
                            },
                            xAxis: {
                                categories:
                                <?php
                                echo $categories;
                                ?>
                            },
                            tooltip: {
                                headerFormat: '<b>{point.x}</b><br/>',
                                pointFormat: '{series.name}: {point.y}'
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
                                    text: 'Последние данные'
                                }
                            },
                            series: [{
                                <?php
                                echo $values;
                                ?>
                            }]
                        });
                    </script>
                </td>
            </tr>
        </table>
    </div>
</div>
