<?php
/* @var $house
 * @var $source
 * @var $streetUuid
 */

use common\components\MainFunctions;
use common\models\DocumentationType;
use common\models\Equipment;
use common\models\EquipmentStatus;
use common\models\HouseStatus;
use common\models\HouseType;
use common\models\User;
use common\models\Users;
use yii\bootstrap\ActiveForm;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

?>

<?php $form = ActiveForm::begin([
    'enableAjaxValidation' => false,
    'action' => '../object/save',
    'options' => [
        'id' => 'form',
        'enctype' => 'multipart/form-data'
    ]]);
?>
<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal">&times;</button>
    <h4 class="modal-title">Дом</h4>
</div>
<div class="modal-body">
    <?php
    if ($house['uuid']) {
        echo Html::hiddenInput("houseUuid", $house['uuid']);
        echo $form->field($house, 'uuid')
            ->hiddenInput(['value' => $house['uuid']])
            ->label(false);
    } else {
        echo $form->field($house, 'uuid')
            ->hiddenInput(['value' => MainFunctions::GUID()])
            ->label(false);
        echo $form->field($house, 'streetUuid')->hiddenInput(['value' => $streetUuid])->label(false);
    }
    echo $form->field($house, 'oid')->hiddenInput(['value' => User::getOid(Yii::$app->user->identity)])->label(false);
    echo $form->field($house, 'number')->textInput(['maxlength' => true]);

    echo Html::hiddenInput("type", "house");
    echo Html::hiddenInput("source", $source);

    $types = HouseType::find()->all();
    $items = ArrayHelper::map($types, 'uuid', 'title');
    echo $form->field($house, 'houseTypeUuid')->widget(\kartik\widgets\Select2::class,
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
            url: "../object/save",
            success: function () {
                $('#modalAdd').modal('hide');
            },
            error: function () {
            }
        })
    });
</script>
<?php ActiveForm::end(); ?>
