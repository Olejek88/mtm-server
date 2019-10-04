<?php
/* @var $model common\models\SensorChannel */

use common\components\MainFunctions;
use common\models\Device;
use common\models\MeasureType;
use common\models\User;
use kartik\select2\Select2;
use yii\bootstrap\ActiveForm;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

?>
<?php $form = ActiveForm::begin([
    'enableAjaxValidation' => false,
    'action' => '/device/save',
    'options' => [
        'id' => 'form'
    ]]);
?>
<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal">&times;</button>
    <h4 class="modal-title">Добавить канал измерения</h4>
</div>
<div class="modal-body">
    <?php
    if ($model['uuid']) {
        echo Html::hiddenInput("sensorUuid", $model['uuid']);
        echo $form->field($model, 'uuid')->hiddenInput(['value' => $model['uuid']])->label(false);
    } else
        echo $form->field($model, 'uuid')->hiddenInput(['value' => MainFunctions::GUID()])->label(false);
    ?>
    <?php
    echo $form->field($model, 'title')->textInput(['maxlength' => true]);

    $type = MeasureType::find()->all();
    $items = ArrayHelper::map($type, 'uuid', 'title');
    echo $form->field($model, 'measureTypeUuid',
        ['template' => MainFunctions::getAddButton("/measure-type/create")])->widget(Select2::class,
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
    if (isset($deviceUuid)) {
        echo $form->field($model, 'deviceUuid')->hiddenInput(['value' => $deviceUuid])->label(false);
    } else {
        $device = Device::find()->all();
        $items = ArrayHelper::map($device, 'uuid', function ($data) {
            return $data->getFullTitle();
        });
        echo $form->field($model, 'deviceUuid',
            ['template' => MainFunctions::getAddButton("/device/create")])->widget(Select2::class,
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
    }
    ?>

    <?php echo $form->field($model, 'oid')->hiddenInput(['value' => User::getOid(Yii::$app->user->identity)])->label(false); ?>
    <?= $form->field($model, 'register')->textInput() ?>
    <?php echo Html::hiddenInput("type", "channel"); ?>
</div>
<div class="modal-footer">
    <?php echo Html::submitButton(Yii::t('app', 'Отправить'), ['class' => 'btn btn-success']) ?>
    <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
</div>
<script>
    $(document).on("beforeSubmit", "#form", function () {
        $.ajax({
            url: "../device/save",
            type: "post",
            data: $('form').serialize(),
            success: function () {
                $('#modal_request').modal('hide');
            },
            error: function () {
            }
        })
    }).on('submit', function (e) {
        e.preventDefault();
    });
</script>
<?php ActiveForm::end(); ?>
