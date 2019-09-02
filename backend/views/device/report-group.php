<?php
/* @var $device
 * @var $dataAll
 */

use common\models\Device;
use kartik\widgets\DatePicker;
use yii\helpers\Html;

$start_date = '2018-12-31';
$end_date = '2021-12-31';
$start_time = '2018-12-31 00:00:00';
$end_time = '2021-12-31 00:00:00';

$type = '';
$this->title = Yii::t('app', 'Архив по группам');

if (isset($_GET['type']))
    $type = $_GET['type'];
if (isset($_GET['end_time'])) {
    $end_date = $_GET['end_time'];
    $end_time = date('Y-m-d H:i:s', strtotime($end_date));
}
if (isset($_GET['start_time'])) {
    $start_date = $_GET['start_time'];
    $start_time = date('Y-m-d H:i:s', strtotime($start_date));
}
?>
<div class="row">
    <form action="report-group">
        <table style="width: 800px; padding: 3px; background-color: white; align-content: center">
            <tr><td style="width: 300px">
                <?php echo DatePicker::widget([
                'name' => 'start_time',
                'value' => $start_date,
                'removeButton' => false,
                'pluginOptions' => [
                'autoclose' => true,
                'format' => 'yyyy-mm-dd'
                ]
                ]).'</td><td style="width: 300px">'.
                DatePicker::widget([
                'name' => 'end_time',
                'value' => $end_date,
                'removeButton' => false,
                'pluginOptions' => [
                'autoclose' => true,
                'format' => 'yyyy-mm-dd'
                ]
                ]).'</td><td style="width: 100px">'.
                Html::submitButton(Yii::t('app', 'Выбрать'), ['class' => 'btn btn-info']).'</td></tr>' ?>
        </table>
    </form>
</div>
<div class="row">
    <div class="col-md-12">
        <div class="box box-success">
            <div class="box-header with-border">
                <h3 class="box-title"><?= $this->title ?></h3>
            </div>
            <div class="box-body">
                <div id="requests-table-container" class="panel table-responsive kv-grid-container" style="overflow: auto">
                    <table class="kv-grid-table table table-hover table-bordered table-condensed kv-table-wrap">
                        <thead>
                        <tr class="kartik-sheet-style" style="height: 20px">
                            <th class="text-center kv-align-middle" data-col-seq="0" style="width: 25%;">Дата</th>
                            <?php
                                foreach ($dataAll['group'] as $data) {
                                    echo '<th class="text-center kv-align-middle" colspan="3">'.$data['title'].'</th>';
                                }
                            ?>
                        </tr>
                        <tr class="kartik-sheet-style" style="height: 20px">
                            <th class="text-center kv-align-middle" data-col-seq="0" style="width: 25%;"></th>
                            <?php
                            foreach ($dataAll['group'] as $data) {
                                echo '<th class="text-center kv-align-middle">Тариф1, кВт*ч</th>';
                                echo '<th class="text-center kv-align-middle">Тариф2, кВт*ч</th>';
                                echo '<th class="text-center kv-align-middle">Сумма, кВт*ч</th>';
                            }
                            ?>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        $group_num=0;
                        for ($mon=0; $mon<12; $mon++) {
                            echo '<tr data-key="1">';
                            $cnt=0;
                            foreach ($dataAll['group'] as $data) {
                                if ($cnt == 0)
                                    echo '<td class="text-center kv-align-center kv-align-middle">' . $data['month'][$mon]['date'] . '</td>';
                                echo '<td class="text-center kv-align-center kv-align-middle">' . $data['month'][$mon]['w1'] . '</td>';
                                echo '<td class="text-center kv-align-center kv-align-middle">' . $data['month'][$mon]['w2'] . '</td>';
                                echo '<td class="text-center kv-align-center kv-align-middle">' . $data['month'][$mon]['ws'] . '</td>';
                                $cnt++;
                            }
                            echo '</tr>';
                        }
                        ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
