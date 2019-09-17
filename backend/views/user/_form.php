<?php

use backend\models\User;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\models\User */
/* @var $form yii\bootstrap\ActiveForm */
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

    <?php echo $form->field($model, 'username')->textInput(['maxlength' => true, 'value' => $model->username]); ?>

    <?php echo $form->field($model, 'name')->textInput(['maxlength' => true, 'value' => $model->name]); ?>

    <?= $form->field($model, 'password', $fieldOptionsLock)->passwordInput() ?>

    <?php echo $form->field($model, 'role')
        ->label(Yii::t('app', 'Права пользователя в системе'))
        ->dropDownList($roleList);
    ?>

    <?php
    $statusList = [
        User::STATUS_DELETED => 'Заблокирован',
        User::STATUS_ACTIVE => 'Активен',
    ];
    echo $form->field($model, 'status')
        ->label(Yii::t('app', 'Состояние пользователя'))
        ->dropDownList($statusList);
    ?>

    <div class="form-group text-center">

        <?= Html::submitButton($model->username == '' ? Yii::t('app', 'Создать') : Yii::t('app', 'Обновить'), [
            'class' => $model->username == '' ? 'btn btn-success' : 'btn btn-primary'
        ]) ?>

    </div>

    <?php ActiveForm::end(); ?>

</div>
