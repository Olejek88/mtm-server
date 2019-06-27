<?php
/* @var $model
 * @var $source
 * @var $streetUuid
 */

use common\components\MainFunctions;
use common\models\Node;
use common\models\User;
use kartik\file\FileInput;
use kartik\widgets\Select2;
use yii\bootstrap\ActiveForm;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

?>

<?php $form = ActiveForm::begin([
    'enableAjaxValidation' => false,
    'action' => '../sound-file/save',
    'options' => [
        'id' => 'form',
        'enctype' => 'multipart/form-data'
    ]]);
?>
<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal">&times;</button>
    <h4 class="modal-title">Добавить сообщение</h4>
</div>
<div class="modal-body">
    <?php
    echo $form->field($model, 'uuid')
        ->hiddenInput(['value' => MainFunctions::GUID()])
        ->label(false);
    echo $form->field($model, 'oid')->hiddenInput(['value' => User::getOid(Yii::$app->user->identity)])->label(false);

    echo $form->field($model, 'title')->textInput(['maxlength' => true]);

    $nodes = Node::find()->all();
    $items = ArrayHelper::map($nodes, 'uuid', function ($model) {
        return $model['object']['address'] . ' [' . $model['address'] . ']';
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

    echo $form->field($model, 'sFile')
        ->widget(FileInput::class, [
            'options' => ['accept' => 'audio/*', 'allowEmpty' => true],
            'pluginOptions' => ['allowedFileExtensions' => ['ogg', 'mp3']],
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
            url: "../sound-file/save",
            success: function () {
                $('#modalAdd').modal('hide');
            },
            error: function () {
            }
        })
    });
</script>
<?php ActiveForm::end(); ?>
