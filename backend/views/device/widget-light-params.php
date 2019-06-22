<?php
/* @var $device
 */

use common\models\mtm\MtmDevLightConfig;
use kartik\slider\Slider;
use kartik\widgets\Select2;
use yii\helpers\Html;
use yii\widgets\Pjax;

?>
<div class="info-box">
    <div class="box-header with-border">
        <h3 class="box-title">Конфигурация светильника</h3>
        <div class="box-tools pull-right">
            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
            <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
        </div>
    </div>
    <div class="box-body">
        <?php Pjax::begin(['id' => 'options']); ?>
        <?= Html::beginForm(['device/light-config'], 'post',
            ['data-pjax' => '', 'class' => 'form-inline']);
        ?>
        <div class="row">
            <div class="col-md-12" style="margin: 3px">
                <?php
                echo Html::hiddenInput('device', $device['uuid']);
                echo '<label class="control-label">Режим работы светильника</label>';
                $modes = [
                    MtmDevLightConfig::$MTM_DEV_LIGHT_CONFIG_MODE_AUTO => "Автономный режим",
                    MtmDevLightConfig::$MTM_DEV_LIGHT_CONFIG_MODE_ASTRO => "Режим астрономических событий",
                    MtmDevLightConfig::$MTM_DEV_LIGHT_CONFIG_MODE_LIGHT_SENSOR => "Режим с датчиком освещенности"
                ];
                echo Select2::widget(
                    [
                        'name' => 'mode',
                        'data' => $modes,
                        'options' => [
                            'placeholder' => 'Режим работы светильника'
                        ]
                    ]);

                echo '<label class="control-label">Мощность светильника</label>';
                $levels = [
                    MtmDevLightConfig::$LIGHT_POWER_12 => '12%',
                    MtmDevLightConfig::$LIGHT_POWER_40 => '40%',
                    MtmDevLightConfig::$LIGHT_POWER_60 => '60%',
                    MtmDevLightConfig::$LIGHT_POWER_80 => '80%',
                    MtmDevLightConfig::$LIGHT_POWER_100 => '100%',
                    MtmDevLightConfig::$LIGHT_POWER_120 => '120%'
                ];
                echo Select2::widget(
                [
                    'data' => $levels,
                    'name' => 'power',
                    'language' => 'ru',
                    'options' => [
                        'placeholder' => 'Мощность светильника'
                    ],
                    'pluginOptions' => [
                        'allowClear' => true
                    ],
                ]);

                echo '<label class="control-label">Группа</label>';
                $groups = [
                    0 => 'Группа #0', 1 => 'Группа #1', 2 => 'Группа #2', 3 => 'Группа #3',
                    4 => 'Группа #4', 5 => 'Группа #5', 6 => 'Группа #6', 7 => 'Группа #7',
                    8 => 'Группа #8', 9 => 'Группа #9', 10 => 'Группа #10', 11 => 'Группа #11',
                    12 => 'Группа #12', 13 => 'Группа #13', 14 => 'Группа #14', 15 => 'Группа #15'
                ];

                echo Select2::widget(
                [
                    'data' => $groups,
                    'name' => 'group',
                    'language' => 'ru',
                    'options' => [
                        'placeholder' => 'Группа'
                    ],
                    'pluginOptions' => [
                        'allowClear' => true
                    ],
                ]);

                echo '<label class="control-label">Частота выдачи датчиком своего статуса (сек.)</label>';
                echo '<span></br></br></span>';
                echo '<div style="margin: 10px; width: 100%">';
                echo Slider::widget([
                    'name' => 'frequency',
                    'sliderColor' => Slider::TYPE_SUCCESS,
                    'handleColor' => Slider::TYPE_SUCCESS,
                    'pluginOptions' => [
                        'orientation' => 'horizontal',
                        'handle' => 'square',
                        'min' => 5,
                        'max' => 60,
                        'step' => 5,
                        'tooltip' => 'always'
                    ],
                ]);
                echo '</div><div class="modal-footer">';
                echo Html::submitButton('Задать', ['class' => 'btn btn-success', 'name' => 'button']);
                echo '</div>';
                ?>
            </div>
        </div>
        <?php
            echo Html::endForm();
            Pjax::end();
        ?>
    </div>
</div>
