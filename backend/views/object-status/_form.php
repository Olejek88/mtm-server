<?php

use app\commands\MainFunctions;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\ObjectStatus */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="stage-status-form">

    <?php $form = ActiveForm::begin(
        [
            'id' => 'form-input',
            'options' => [
                'class' => 'form-horizontal col-lg-12 col-sm-12 col-xs-12',
                'enctype' => 'multipart/form-data'
            ],
        ]
    );
    ?>

    <?php
    if (!$model->isNewRecord) {
        echo $form->field($model, 'uuid')
            ->textInput(['maxlength' => true, 'readonly' => true]);
    } else {
        echo $form->field($model, 'uuid')->hiddenInput(['value' => (new MainFunctions)->GUID()])->label(false);
    }
    ?>

    <?php echo $form->field($model, 'title')->textInput(['maxlength' => true]) ?>

    <div class="form-group text-center">

        <?php echo Html::submitButton(
            $model->isNewRecord
                ? Yii::t('app', 'Создать') : Yii::t('app', 'Обновить'),
            ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']
        ) ?>

    </div>

    <?php ActiveForm::end(); ?>

</div>
