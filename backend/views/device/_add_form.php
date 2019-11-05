<?php
/* @var $device Device */
/* @var $deviceTypeUuid */
/* @var $source */
/* @var $objectUuid */

use common\components\MainFunctions;
use common\models\Device;
use common\models\DeviceStatus;
use common\models\DeviceType;
use common\models\Node;
use common\models\Objects;
use common\models\User;
use kartik\select2\Select2;
use yii\bootstrap\ActiveForm;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

?>

<?php $form = ActiveForm::begin([
    'enableAjaxValidation' => false,
    'options' => [
        'id' => 'form',
        'enctype' => 'multipart/form-data'
    ]]);
?>
<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal">&times;</button>
    <h4 class="modal-title">Оборудование</h4>
</div>
<div class="modal-body">

    <?php
    if ($device['uuid']) {
        echo Html::hiddenInput("deviceUuid", $device['uuid']);
        echo Html::hiddenInput("source", $source);

        echo $form->field($device, 'uuid')
            ->hiddenInput(['value' => $device['uuid']])
            ->label(false);
    } else {
        echo $form->field($device, 'uuid')
            ->hiddenInput(['value' => MainFunctions::GUID()])
            ->label(false);
    }

    echo $form->field($device, 'name')->textInput(['maxlength' => true]);

    if (isset($deviceTypeUuid) && ($deviceTypeUuid."" == DeviceType::DEVICE_LIGHT)) {
        echo $form->field($device, 'deviceTypeUuid')->hiddenInput(['value' => DeviceType::DEVICE_LIGHT])->label(false);
    } else {
        $deviceType = DeviceType::find()->all();
        $items = ArrayHelper::map($deviceType, 'uuid', 'title');
        echo $form->field($device, 'deviceTypeUuid')->widget(Select2::class,
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
    }
    if (isset($objectUuid)) {
        echo $form->field($device, 'objectUuid')->hiddenInput(['value' => $objectUuid])->label(false);
    } else {
        $object = Objects::find()->where(['deleted' => 0])->all();
        $items = ArrayHelper::map($object, 'uuid', function ($model) {
            return $model['house']['street']->title . ', ' . $model['house']->number . ', ' . $model['title'];
        });
        echo $form->field($device, 'objectUuid')->widget(Select2::class,
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
    }

    if (isset($nodeUuid)) {
        echo $form->field($device, 'nodeUuid')->hiddenInput(['value' => $nodeUuid])->label(false);
    } else {
        $nodes = Node::find()->where(['deleted' => 0])->all();
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
    }
    echo $form->field($device, 'port')->textInput(['maxlength' => true]);

    $interfaces = [
        '0' => 'не указан',
        '1' => 'Последовательный порт',
        '2' => 'Zigbee',
        '3' => 'Ethernet'
    ];
    echo $form->field($device, 'interface')->widget(Select2::class,
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

    echo $form->field($device, 'address')->textInput(['maxlength' => true]);
    echo $form->field($device, 'serial')->textInput(['maxlength' => true]);
    echo $form->field($device, 'oid')->hiddenInput(['value' => User::getOid(Yii::$app->user->identity)])->label(false);
    echo $form->field($device, 'deviceStatusUuid')->hiddenInput(['value' => DeviceStatus::WORK])->label(false);

    $linkTimeoutValue = empty($device->linkTimeout) ? 600 : $device->linkTimeout;
    echo $form->field($device, 'linkTimeout')->textInput(['maxlength' => true, 'value' => $linkTimeoutValue])
        ->label($device->getAttributeLabel('linkTimeout') . '(сек.)');

    echo Html::hiddenInput("source", $source);
    echo Html::hiddenInput("type", "device");
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
            data: $('form').serialize(),
            url: "../device/save",
            success: function () {
                $('#modalAddEquipment').modal('hide');
            }
        })
    });
</script>
<?php ActiveForm::end(); ?>
