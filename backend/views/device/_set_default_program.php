<?php

use common\models\DeviceProgram;
use common\models\Group;
use common\models\GroupControl;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $form yii\widgets\ActiveForm */
/* @var $date */
/* @var $group */
?>

<div class="equipment-status-form" style="margin: 5px; padding: 5px">
    <h4>Задать программу по-умолчанию</h4>
    <?php $form = ActiveForm::begin([
        'enableAjaxValidation' => false,
        'options' => [
            'id' => 'form'
        ],
    ]);
    ?>

    <?php
    echo Html::hiddenInput("group", $group);

    $program = 0;
    $group = Group::find()
        ->where(['uuid' => $group])
        ->one();
    if ($group)
        $program = 0;
    $programs = DeviceProgram::find()->all();
    $items = ArrayHelper::map($programs, 'uuid', 'title');
    echo Select2::widget(
        [
            'id' => 'deviceProgram',
            'name' => 'deviceProgram',
            'value' => $program,
            'language' => 'ru',
            'data' => $items,
            'options' => ['placeholder' => 'Программа по-умолчанию ...'],
            'pluginOptions' => [
                'allowClear' => true
            ],
        ]);
    ?>
    <span>&nbsp;</span>
    <div class="form-group text-center">
        <?= Html::submitButton(Yii::t('app', 'Сменить'), ['class' => 'btn btn-success']) ?>
    </div>
    <script>
        $(document).on("beforeSubmit", "#dynamic-form", function () {
        }).on('submit', function (e) {
            e.preventDefault();
            $.ajax({
                url: "/device/set-default-program",
                type: "post",
                data: $('form').serialize(),
                success: function () {
                    $('#modalAddProgram').modal('hide');
                },
                error: function () {
                }
            })
        });
    </script>

    <?php ActiveForm::end(); ?>

</div>
