<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model backend\models\AreaSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="area-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, '_id') ?>

    <?= $form->field($model, 'uuid') ?>

    <?= $form->field($model, 'oid') ?>

    <?= $form->field($model, 'title') ?>

    <?= $form->field($model, 'createdAt') ?>

    <?php // echo $form->field($model, 'changedAt') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
