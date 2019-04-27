<?php

use app\commands\MainFunctions;
use common\models\Device;
use common\models\Users;
use kartik\widgets\Select2;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\PhotoEquipment */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="measure-type-form">

    <?php $form = ActiveForm::begin([
        'id' => 'form-input-documentation',
        'options' => [
            'class' => 'form-horizontal col-lg-11',
            'enctype' => 'multipart/form-data'
        ],
    ]);
    ?>

    <?php
    if (!$model->isNewRecord) {
        echo $form->field($model, 'uuid')->textInput(['maxlength' => true, 'readonly' => true]);
    } else {
        echo $form->field($model, 'uuid')->textInput(['maxlength' => true, 'value' => (new MainFunctions)->GUID()]);
    }
    ?>

    <?php echo $form->field($model, 'longitude')->textInput(['maxlength' => true]) ?>
    <?php echo $form->field($model, 'latitude')->textInput(['maxlength' => true]) ?>

    <?php
    $users = Users::find()->all();
    $items = ArrayHelper::map($users, 'uuid', 'name');
    echo $form->field($model, 'userUuid')->widget(Select2::classname(),
        [
            'data' => $items,
            'language' => 'ru',
            'options' => [
                'placeholder' => 'Выберите пользователя..'
            ],
            'pluginOptions' => [
                'allowClear' => true
            ],
        ]);

    ?>

    <?php
    $equipment = Device::find()->all();
    $items = ArrayHelper::map($equipment, 'uuid', function ($model) {
        return $model['flat']['house']['street']->title . ', ' . $model['flat']['house']->number . ', ' .
            $model['flat']['number'] . ' ' . $model['equipmentType']->title;
    });
    echo $form->field($model, 'equipmentUuid')->widget(Select2::class,
        [
            'data' => $items,
            'language' => 'ru',
            'options' => [
                'placeholder' => 'Выберите оборудование..'
            ],
            'pluginOptions' => [
                'allowClear' => true
            ],
        ]);
    ?>

    <div class="form-group text-center">

        <?= Html::submitButton($model->isNewRecord ? Yii::t('app', 'Создать') : Yii::t('app', 'Обновить'), [
            'class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary'
        ]) ?>

    </div>

    <?php ActiveForm::end(); ?>

</div>
