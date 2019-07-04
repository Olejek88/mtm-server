<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use backend\models\Role;

/* @var $this yii\web\View */
/* @var $model backend\models\User */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $role Role */
/* @var $roleList array */
?>

<div class="user-form">
    <?php
    $fieldOptionsLock = [
        'options' => ['class' => 'form-group has-feedback'],
        'inputTemplate' => "{input}<span class='glyphicon glyphicon-lock form-control-feedback'></span>"
    ];
    ?>

    <?php $form = ActiveForm::begin([
        'id' => 'form-input-documentation',
        'options' => [
            'class' => 'form-horizontal col-lg-12 col-sm-12 col-xs-12',
            'enctype' => 'multipart/form-data'
        ],
    ]);
    ?>

    <?php

    //    if (!$model->isNewRecord) {
    //        echo $form->field($model, 'username')->textInput(['maxlength' => true]);
    //    } else {
        echo $form->field($model, 'username')->textInput(['maxlength' => true, 'value' => $model->username]);
    //    }

    ?>

    <?php echo $form->field($model, 'name')->textInput(['maxlength' => true, 'value' => $model->name]); ?>

    <?= $form->field($model, 'password', $fieldOptionsLock)->passwordInput() ?>

    <?php echo $form->field($role, 'role')
        ->label(Yii::t('app', 'Права пользователя в системе'))
        ->dropDownList($roleList);
    ?>

    <div class="form-group text-center">

        <?= Html::submitButton($model->username == '' ? Yii::t('app', 'Создать') : Yii::t('app', 'Обновить'), [
            'class' => $model->username == '' ? 'btn btn-success' : 'btn btn-primary'
        ]) ?>

    </div>

    <?php ActiveForm::end(); ?>

</div>
