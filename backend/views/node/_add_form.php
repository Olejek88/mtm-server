<?php
/* @var $node */
/* @var $deviceTypeUuid */
/* @var $source */
/* @var $objectUuid */

use common\components\MainFunctions;
use common\models\DeviceStatus;
use common\models\Equipment;
use common\models\EquipmentStatus;
use common\models\EquipmentType;
use common\models\Objects;
use common\models\User;
use common\models\Users;
use kartik\select2\Select2;
use yii\bootstrap\ActiveForm;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

?>

<?php $form = ActiveForm::begin([
    'enableAjaxValidation' => false,
    'action' => '/device/save',
    'options' => [
        'id' => 'form',
        'enctype' => 'multipart/form-data'
    ]]);
?>
<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal">&times;</button>
    <h4 class="modal-title">Контроллер</h4>
</div>
<div class="modal-body">

    <?php
    if ($node['uuid']) {
        echo Html::hiddenInput("nodeUuid", $node['uuid']);
        echo Html::hiddenInput("source", $source);

        echo $form->field($node, 'uuid')
            ->hiddenInput(['value' => $node['uuid']])
            ->label(false);
    } else {
        echo $form->field($node, 'uuid')
            ->hiddenInput(['value' => MainFunctions::GUID()])
            ->label(false);
    }
    echo $form->field($node, 'address')->textInput(['maxlength' => true]);
    echo $form->field($node, 'phone')->textInput(['maxlength' => true]);
    echo $form->field($node, 'software')->textInput(['maxlength' => true]);

    echo $form->field($node, 'oid')->hiddenInput(['value' => User::getOid(Yii::$app->user->identity)])->label(false);
    echo $form->field($node, 'nodeStatusUuid')->hiddenInput(['value' => DeviceStatus::WORK])->label(false);

    if (isset($objectUuid)) {
        echo $form->field($node, 'objectUuid')->hiddenInput(['value' => $objectUuid])->label(false);
    } else {
        $object = Objects::find()->all();
        $items = ArrayHelper::map($object, 'uuid', function ($model) {
            return $model['house']['street']->title . ', ' . $model['house']->number . ', ' . $model['title'];
        });
        echo $form->field($node, 'objectUuid')->widget(Select2::class,
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
    echo $form->field($node, 'deviceStatusUuid')->hiddenInput(['value' => DeviceStatus::WORK])->label(false);

    echo Html::hiddenInput("source", $source);
    echo Html::hiddenInput("type", "node");
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
            data: new FormData( this ),
            processData: false,
            contentType: false
            url: "../device/save",
            success: function () {
                $('#modalAddEquipment').modal('hide');
            },
            error: function () {
            }
        })
    });
</script>
<?php ActiveForm::end(); ?>
