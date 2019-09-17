<?php
/* @var $node
 */

use common\models\Device;
use common\models\DeviceStatus;

?>
<div class="info-box">
    <div class="box-body">
        <div class="row">
            <div class="col-md-12" style="margin: 5px">
                <table class="kv-grid-table table table-hover table-bordered table-condensed kv-table-wrap">
                    <tbody>
                        <?php
                        $devices = Device::find()
                            ->where(['nodeUuid' => $node['uuid']])
                            ->andWhere(['deleted' => 0])
                            ->andWhere('deviceStatusUuid !=\'' . DeviceStatus::WORK . '\'')
                            ->limit(5)
                            ->all();
                        foreach ($devices as $device) {
                            echo '<tr class="kartik-sheet-style" style="height: 20px; background-color: lightcoral">
                                    <td class="" data-col-seq="0" style="color: white">(' .
                                $device['deviceType']['title'] . ') ' .
                                $device['name'] . ' [' . $device['address'] . '] имеет статус ' . $device['deviceStatus']['title'] . ' с ' .
                                $device['changedAt'] . '</td></tr>';
                        }
                        ?>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
