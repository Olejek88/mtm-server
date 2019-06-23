<?php

use kartik\slider\Slider;
use yii\helpers\Html;
use yii\widgets\Pjax;

/* @var $device
 */

?>
<div class="box box-info">
    <div class="box-header with-border">
        <h3 class="box-title">Задать уровень освещения</h3>
        <div class="box-tools pull-right">
            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
            <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
        </div>
    </div>
    <div class="box-body">
        <?php
        Pjax::begin(['id' => 'options','enablePushState' => false]); ?>
        <div class="row">
            <div class="col-md-7" style="margin: 10px">
                <?= Html::beginForm(['device/dashboard'], 'post',
                    ['data-pjax' => '', 'class' => 'form-inline']);
                echo Html::hiddenInput('device', $device['uuid']);
                echo Html::hiddenInput('type', 'set');

                echo Slider::widget([
                    'name' => 'value',
                    'sliderColor' => Slider::TYPE_INFO,
                    'handleColor' => Slider::TYPE_INFO,
                    'pluginOptions' => [
                        'orientation' => 'vertical',
                        'handle' => 'square',
                        'reversed' => true,
                        'min' => 0,
                        'max' => 100,
                        'step' => 1,
                        'tooltip' => 'always'
                    ],
                ]);
                ?>
            </div>
            <div class="col-md-4">
                <?php
                echo Html::submitButton('Задать', ['class' => 'btn btn-success', 'name' => 'button']);
                ?>
            </div>
        </div>
        <?php
        echo Html::endForm();
        Pjax::end();
        ?>
    </div>
</div>
