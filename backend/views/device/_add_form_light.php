<?php
/* @var $device Device */
/* @var $object Objects */
/* @var $latitude */

/* @var $longitude */

use common\components\MainFunctions;
use common\models\Device;
use common\models\DeviceStatus;
use common\models\DeviceType;
use common\models\Node;
use common\models\Objects;
use common\models\ObjectType;
use common\models\Street;
use common\models\User;
use kartik\select2\Select2;
use yii\bootstrap\ActiveForm;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

?>

<?php $form = ActiveForm::begin([
    'enableAjaxValidation' => false,
    'action' => '/device/save-light',
    'options' => [
        'id' => 'form',
        'enctype' => 'multipart/form-data'
    ]]);
?>
<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal">&times;</button>
    <h4 class="modal-title">Добавить светильник</h4>
</div>
<div class="modal-body">
    <?php
    echo $form->field($device, 'uuid')
        ->hiddenInput(['value' => MainFunctions::GUID()])
        ->label(false);
    $objectUuid = MainFunctions::GUID();
    echo $form->field($object, 'uuid')
        ->hiddenInput(['value' => $objectUuid])
        ->label(false);

    echo $form->field($device, 'name')->textInput(['maxlength' => true, 'value' => 'Неуправляемый светильник'])->label("Название устройства");
    echo $form->field($object, 'title')->textInput(['maxlength' => true, 'value' => 'Столб освещения'])->label("Название объекта");

    $street = Street::find()->all();
    $items = ArrayHelper::map($street, 'uuid', function ($model) {
        return $model['title'];
    });
    echo Select2::widget([
            'id' => 'street',
            'name' => 'street',
            'data' => $items,
            'language' => 'ru',
            'options' => [
                'placeholder' => 'Улица..'
            ],
            'pluginOptions' => [
                'allowClear' => true
            ],
        ]);

    echo $form->field($object, 'latitude')->hiddenInput(['value' => $latitude])->label(false);
    echo $form->field($object, 'longitude')->hiddenInput(['value' => $longitude])->label(false);
    echo $form->field($object, 'oid')->hiddenInput(['value' => User::getOid(Yii::$app->user->identity)])->label(false);
    echo $form->field($object, 'objectTypeUuid')->hiddenInput(['value' => ObjectType::OBJECT_TYPE_PILLAR])->label(false);

    $nodes = Node::find()->all();
    $items = ArrayHelper::map($nodes, 'uuid', function ($model) {
        return $model['object']['address'] . ' [' . $model['address'] . ']';
    });
    echo $form->field($device, 'nodeUuid')->widget(Select2::class,
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
    echo $form->field($device, 'port')->textInput(['maxlength' => true, 'value' => '/dev/zigbee']);
    echo $form->field($device, 'interface')->hiddenInput(['value' => 2])->label(false);
    echo $form->field($device, 'address')->textInput(['maxlength' => true]);
    echo $form->field($device, 'serial')->textInput(['maxlength' => true]);
    echo $form->field($device, 'oid')->hiddenInput(['value' => User::getOid(Yii::$app->user->identity)])->label(false);
    echo $form->field($device, 'deviceStatusUuid')->hiddenInput(['value' => DeviceStatus::WORK])->label(false);
    echo Html::hiddenInput("type", "device");

    echo $form->field($device, 'deviceTypeUuid')->hiddenInput(['value' => DeviceType::DEVICE_LIGHT])->label(false);
    echo $form->field($device, 'objectUuid')->hiddenInput(['value' => $objectUuid])->label(false);
    ?>

</div>
<div class="modal-footer">
    <?php echo Html::submitButton(Yii::t('app', 'Отправить'), ['class' => 'btn btn-success']) ?>
    <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
</div>
<script>
    $(document).on("beforeSubmit", "#form", function (e) {
        e.preventDefault();
    }).on('submit', function (e) {
        e.preventDefault();
        $.ajax({
            type: "post",
            data: new FormData(this),
            processData: false,
            contentType: false
            url: "../device/save-light",
            success: function () {
                $('#modalAddEquipment').modal('hide');
            },
            error: function () {
            }
        })
    });
</script>
<?php ActiveForm::end(); ?>
