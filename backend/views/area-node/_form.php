<?php

use common\components\MainFunctions;
use common\models\Area;
use common\models\Node;
use common\models\User;
use kartik\widgets\Select2;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\AreaNode */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="area-node-form">

    <?php $form = ActiveForm::begin(); ?>

    <?php
    if (!$model->isNewRecord) {
        echo $form->field($model, 'uuid')->hiddenInput()->label(false);
    } else {
        echo $form->field($model, 'uuid')->hiddenInput(['value' => (new MainFunctions)->GUID()])->label(false);
    }
    ?>

    <?php echo $form->field($model, 'oid')->hiddenInput(['value' => User::getOid(Yii::$app->user->identity)])->label(false); ?>

    <?php
    $list = Area::find()->all();
    $items = ArrayHelper::map($list, 'uuid', 'title');
    echo $form->field($model, 'areaUuid')->widget(Select2::class,
        [
            'data' => $items,
            'language' => 'ru',
            'options' => [
                'placeholder' => 'Выберите территорию..'
            ],
            'pluginOptions' => [
                'allowClear' => true
            ],
        ])->label('Территория');
    ?>

    <?php
    $list = Node::find()->all();
    $items = ArrayHelper::map($list, 'uuid', 'address');
    echo $form->field($model, 'nodeUuid')->widget(Select2::class, [
        'data' => $items,
        'language' => 'ru',
        'options' => [
            'placeholder' => 'Выберите шкаф..'
        ],
        'pluginOptions' => [
            'allowClear' => true
        ],
    ])->label('Шкаф');

    unset($list);
    unset($items);
    ?>


    <div class="form-group">
        <?php
        echo Html::submitButton(
            $model->isNewRecord ? Yii::t('app', 'Добавить') : Yii::t('app', 'Обновить'),
            [
                'class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary'
            ]
        );
        ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
