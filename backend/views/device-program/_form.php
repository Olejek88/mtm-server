<?php

use common\components\MainFunctions;
use common\models\User;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\widgets\TouchSpin;

/* @var $this yii\web\View */
/* @var $model common\models\DeviceProgram */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="device-program-form">

    <?php $form = ActiveForm::begin([
//        'id' => 'form-input-documentation',
//        'options' => [
//            'class' => 'form-horizontal col-lg-12 col-sm-12 col-xs-12',
//            'enctype' => 'multipart/form-data'
//        ],
    ]); ?>

    <?php
    if (!$model->isNewRecord) {
        echo $form->field($model, 'uuid')->hiddenInput()->label(false);
    } else {
        echo $form->field($model, 'uuid')->hiddenInput(['value' => (new MainFunctions)->GUID()])->label(false);
    }
    ?>

    <?= $form->field($model, 'title')->textInput(['maxlength' => true, 'value' => $model->isNewRecord ? 'Программа' : $model->title]) ?>

    <b><?= $model->attributeLabels()['period_title1'] ?></b>

    <?=
    $form->field($model, 'value1')->widget(TouchSpin::class, [
        'options' => [
            'placeholder' => 'Adjust ...',
        ],
        'pluginOptions' => [
            'initval' => 20,
            'verticalbuttons' => true,
            'verticalup' => '<i class="fa fa-plus"></i>',
            'verticaldown' => '<i class="fa fa-minus"></i>',
//            'buttonup_class' => 'btn btn-outline-secondary bootstrap-touchspin-up',
//            'buttondown_class' => 'btn btn-outline-secondary bootstrap-touchspin-down',
            'min' => 0,
            'max' => 100,
            'postfix' => '%',
            'boostat' => 5,
        ],
    ]);
    ?>

    <b><?= $model->attributeLabels()['period_title2'] ?></b>

    <?=
    $form->field($model, 'time2')->widget(TouchSpin::class, [
        'options' => [
            'placeholder' => 'Adjust ...',
        ],
        'pluginOptions' => [
            'initval' => 20,
            'verticalbuttons' => true,
            'verticalup' => '<i class="fa fa-plus"></i>',
            'verticaldown' => '<i class="fa fa-minus"></i>',
//            'buttonup_class' => 'btn btn-outline-secondary bootstrap-touchspin-up',
//            'buttondown_class' => 'btn btn-outline-secondary bootstrap-touchspin-down',
            'min' => 0,
            'max' => 100,
            'postfix' => '%',
            'boostat' => 5,
        ],
    ]);
    ?>

    <?=
    $form->field($model, 'value2')->widget(TouchSpin::class, [
        'options' => [
            'placeholder' => 'Adjust ...',
        ],
        'pluginOptions' => [
            'initval' => 20,
            'verticalbuttons' => true,
            'verticalup' => '<i class="fa fa-plus"></i>',
            'verticaldown' => '<i class="fa fa-minus"></i>',
//            'buttonup_class' => 'btn btn-outline-secondary bootstrap-touchspin-up',
//            'buttondown_class' => 'btn btn-outline-secondary bootstrap-touchspin-down',
            'min' => 0,
            'max' => 100,
            'postfix' => '%',
            'boostat' => 5,
        ],
    ]);
    ?>

    <b><?= $model->attributeLabels()['period_title3'] ?></b>

    <?=
    $form->field($model, 'time3')->widget(TouchSpin::class, [
        'options' => [
            'placeholder' => 'Adjust ...',
        ],
        'pluginOptions' => [
            'initval' => 20,
            'verticalbuttons' => true,
            'verticalup' => '<i class="fa fa-plus"></i>',
            'verticaldown' => '<i class="fa fa-minus"></i>',
//            'buttonup_class' => 'btn btn-outline-secondary bootstrap-touchspin-up',
//            'buttondown_class' => 'btn btn-outline-secondary bootstrap-touchspin-down',
            'min' => 0,
            'max' => 100,
            'postfix' => '%',
            'boostat' => 5,
        ],
    ]);
    ?>

    <?=
    $form->field($model, 'value3')->widget(TouchSpin::class, [
        'options' => [
            'placeholder' => 'Adjust ...',
        ],
        'pluginOptions' => [
            'initval' => 20,
            'verticalbuttons' => true,
            'verticalup' => '<i class="fa fa-plus"></i>',
            'verticaldown' => '<i class="fa fa-minus"></i>',
//            'buttonup_class' => 'btn btn-outline-secondary bootstrap-touchspin-up',
//            'buttondown_class' => 'btn btn-outline-secondary bootstrap-touchspin-down',
            'min' => 0,
            'max' => 100,
            'postfix' => '%',
            'boostat' => 5,
        ],
    ]);
    ?>

    <b><?= $model->attributeLabels()['period_title4'] ?></b>

    <?=
    $form->field($model, 'time4')->widget(TouchSpin::class, [
        'options' => [
            'placeholder' => 'Adjust ...',
        ],
        'pluginOptions' => [
            'initval' => 20,
            'verticalbuttons' => true,
            'verticalup' => '<i class="fa fa-plus"></i>',
            'verticaldown' => '<i class="fa fa-minus"></i>',
//            'buttonup_class' => 'btn btn-outline-secondary bootstrap-touchspin-up',
//            'buttondown_class' => 'btn btn-outline-secondary bootstrap-touchspin-down',
            'min' => 0,
            'max' => 100,
            'postfix' => '%',
            'boostat' => 5,
        ],
    ]);
    ?>

    <?=
    $form->field($model, 'value4')->widget(TouchSpin::class, [
        'options' => [
            'placeholder' => 'Adjust ...',
        ],
        'pluginOptions' => [
            'initval' => 20,
            'verticalbuttons' => true,
            'verticalup' => '<i class="fa fa-plus"></i>',
            'verticaldown' => '<i class="fa fa-minus"></i>',
//            'buttonup_class' => 'btn btn-outline-secondary bootstrap-touchspin-up',
//            'buttondown_class' => 'btn btn-outline-secondary bootstrap-touchspin-down',
            'min' => 0,
            'max' => 100,
            'postfix' => '%',
            'boostat' => 5,
        ],
    ]);
    ?>

    <b><?= $model->attributeLabels()['period_title5'] ?></b>

    <?=
    $form->field($model, 'value5')->widget(TouchSpin::class, [
        'options' => [
            'placeholder' => 'Adjust ...',
        ],
        'pluginOptions' => [
            'initval' => 20,
            'verticalbuttons' => true,
            'verticalup' => '<i class="fa fa-plus"></i>',
            'verticaldown' => '<i class="fa fa-minus"></i>',
//            'buttonup_class' => 'btn btn-outline-secondary bootstrap-touchspin-up',
//            'buttondown_class' => 'btn btn-outline-secondary bootstrap-touchspin-down',
            'min' => 0,
            'max' => 100,
            'postfix' => '%',
            'boostat' => 5,
        ],
    ]);
    ?>

    <div class="form-group">
        <?= Html::submitButton('Сохранить', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
