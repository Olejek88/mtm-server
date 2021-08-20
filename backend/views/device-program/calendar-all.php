<?php

use yii\helpers\Html;
use yii\web\JsExpression;

$this->title = 'Расписание работы шкафов';

/* @var $nodes */
?>

<div class="modal remote fade" id="modalTaskInfo">
    <div class="modal-dialog" style="width: 800px">
        <div class="modal-content loader-lg" id="modalContent">
        </div>
    </div>
</div>

<div class="site-index">
    <div class="body-content">
        <?php
        foreach ($nodes as $node) {
            echo Html::a($node['address'], '/device-program/calendar-node?node=' . $node['uuid']) . '</br>';
        }
        ?>
    </div>
</div>
