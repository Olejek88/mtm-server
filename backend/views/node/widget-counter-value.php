<?php

use common\models\Node;
use kartik\widgets\DatePicker;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;

/** @var View $this */
/** @var $counterDate */
/** @var $counterValue */
/** @var Node $node */

?>
<div class="box box-success">
    <div class="box-header with-border">
        <h3 class="box-title">Показания счётчика</h3>
    </div>
    <div class="box-body">
        <?php
        Pjax::begin(['enablePushState' => false]);
        $form = ActiveForm::begin([
            'action' => ['/node/counter-value'],
            'method' => 'GET',
            'options' => [
                'data-pjax' => true,
                'class' => 'form-inline',
            ],
        ]);
        echo '<label>Дата: </label>';
        echo DatePicker::widget([
            'name' => 'd',
            'value' => $counterDate,
            'removeButton' => false,
            'options' => [
                'class' => 'add-filter',
                'width' => '140px'
            ],
            'pluginOptions' => [
                'autoclose' => true,
                'format' => 'yyyy-mm-dd',
            ],
            'pluginEvents' => [
                'changeDate' => "function(e) {
                    $(e.target).closest('form').submit();
                }"
            ],
        ]);
        echo 'Значение: ' . $counterValue . '<br/>';
        echo Html::hiddenInput('n', $node->uuid);
        ActiveForm::end();
        Pjax::end();
        ?>
    </div>
</div>
