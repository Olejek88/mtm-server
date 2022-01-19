<?php

use common\models\Device;
use yii\helpers\Html;

/* @var $model Device */

?>
<div class="col-sm-6">
    <table class="table table-bordered table-condensed table-hover small kv-table">
        <tr style="background-color: #e8e8e8">
            <th colspan="6" class="text-center text-success">Датчики/каналы измерения</th>
        </tr>
        <tr class="active">
            <th class="text-center">#</th>
            <th>Канал измерения</th>
            <th>Регистр</th>
            <th>Тип измерения</th>
            <th class="text-right">Дата</th>
            <th class="text-right">Конфигурация</th>
        </tr>
        <?php foreach ($model->sensorChannels as $sensorChannel) { ?>
            <tr>
                <td class="text-center"><?= $sensorChannel["_id"] ?></td>
                <td class="text-center"><?= $sensorChannel["title"] ?></td>
                <td><?= $sensorChannel['register'] ?></td>
                <td><?= $sensorChannel['measureType']['title'] ?></td>
                <td class="text-right"><?= $sensorChannel["createdAt"] ?></td>
                <?php
                if (($sensorChannel->sensorConfig != null)) {
                    $link = Html::a('Конфигурация', ['/sensor-config/view', 'id' => $sensorChannel->sensorConfig->_id], []);
                } else {
                    $link = Html::a('Конфигурация', ['/sensor-config/create', 'sc' => $sensorChannel->uuid], []);
                }
                ?>
                <td class="text-right"><?= $link ?></td>
            </tr>
        <?php } ?>
    </table>
</div>
