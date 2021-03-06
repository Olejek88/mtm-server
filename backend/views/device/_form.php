<?php

use common\components\MainFunctions;
use common\models\DeviceStatus;
use common\models\DeviceType;
use common\models\Node;
use common\models\Objects;
use common\models\User;
use kartik\widgets\Select2;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\Device */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="device-form">

    <?php $form = ActiveForm::begin(
        [
            'id' => 'form-input-documentation',
            'options' => [
                'class' => 'form-horizontal col-lg-12 col-sm-12 col-xs-12',
                'enctype' => 'multipart/form-data'
            ],
        ]
    ); ?>

    <?php
    if (!$model->isNewRecord) {
        echo $form->field($model, 'uuid')->hiddenInput()->label(false);
    } else {
        echo $form->field($model, 'uuid')->hiddenInput(['value' => (new MainFunctions)->GUID()])->label(false);
    }
    ?>
    <?php echo $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

    <?php
    $deviceType = DeviceType::find()->all();
    $items = ArrayHelper::map($deviceType, 'uuid', 'title');
    echo $form->field($model, 'deviceTypeUuid',
        ['template' => MainFunctions::getAddButton("/device-type/create")])->widget(Select2::class,
        [
            'data' => $items,
            'language' => 'ru',
            'options' => [
                'placeholder' => 'Выберите тип..'
            ],
            'pluginOptions' => [
                'allowClear' => true
            ],
        ]);
    ?>

    <?php
    $object = Objects::find()->where(['deleted' => 0])->all();
    $items = ArrayHelper::map($object, 'uuid', function ($model) {
        return $model->getAddress();
    });
    echo $form->field($model, 'objectUuid')->widget(Select2::class,
        [
            'data' => $items,
            'language' => 'ru',
            'options' => [
                'placeholder' => 'Выберите объект..'
            ],
            'pluginOptions' => [
                'allowClear' => true
            ],
        ]);
    ?>

    <?php
    $deviceStatus = DeviceStatus::find()->all();
    $items = ArrayHelper::map($deviceStatus, 'uuid', 'title');
    echo $form->field($model, 'deviceStatusUuid',
        ['template' => MainFunctions::getAddButton("/device-status/create")])->widget(Select2::class,
        [
            'data' => $items,
            'language' => 'ru',
            'options' => [
                'placeholder' => 'Выберите тип..'
            ],
            'pluginOptions' => [
                'allowClear' => true
            ],
        ]);
    ?>

    <?php
    $interfaces = [
            '0' => 'не указан',
            '1' => 'Последовательный порт',
            '2' => 'Zigbee',
            '3' => 'Ethernet'
    ];
    echo $form->field($model, 'interface')->widget(Select2::class,
        [
            'data' => $interfaces,
            'language' => 'ru',
            'options' => [
                'placeholder' => 'Выберите интерфейс'
            ],
            'pluginOptions' => [
                'allowClear' => true
            ],
        ]);
    ?>

    <?php echo $form->field($model, 'port')->textInput(['maxlength' => true]) ?>

    <?php echo $form->field($model, 'address')->textInput(['maxlength' => true]) ?>

    <?php echo $form->field($model, 'serial')->textInput(['maxlength' => true]) ?>

    <?php echo $form->field($model, 'oid')->hiddenInput(['value' => User::getOid(Yii::$app->user->identity)])->label(false); ?>

    <?php
    $nodes = Node::find()->where(['deleted' => 0])->all();
    $items = ArrayHelper::map($nodes, 'uuid', function ($model) {
        return $model['object']['address'].' ['.$model['address'].']';
    });
    echo $form->field($model, 'nodeUuid')->widget(Select2::class,
        [
            'data' => $items,
            'language' => 'ru',
            'options' => [
                'placeholder' => 'Выберите контроллер..'
            ],
            'pluginOptions' => [
                'allowClear' => true
            ],
        ]);

    ?>

    <?php
    $linkTimeoutValue = empty($model->linkTimeout) ? 600 : $model->linkTimeout;
    echo $form->field($model, 'linkTimeout')->textInput(['maxlength' => true, 'value' => $linkTimeoutValue])
        ->label($model->getAttributeLabel('linkTimeout') . '(сек.)');
    ?>

    <div class="form-group text-center">

        <?php
        if ($model->isNewRecord) {
            $buttonText = Yii::t('app', 'Создать');
            $buttonClass = 'btn btn-success';
        } else {
            $buttonText = Yii::t('app', 'Обновить');
            $buttonClass = 'btn btn-primary';
        }

        echo Html::submitButton($buttonText, ['class' => $buttonClass]);
        ?>

    </div>

    <?php ActiveForm::end(); ?>

</div>
