<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\Measure */

$this->title = Yii::t('app', 'Обновить {modelClass}: ', [
        'modelClass' => 'Измеренные значения',
    ]) . $model->_id;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Измеренные значения'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->_id, 'url' => ['view', 'id' => $model->_id]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Обновить');
?>
<div class="measured-value-update box-padding">

    <div class="box box-default">
        <div class="box-header with-border">
            <h2><?= Html::encode($this->title) ?></h2>
            <div class="box-tools pull-right">
                <span class="label label-default"></span>
            </div>
        </div>
        <div class="box-body" style="padding: 30px;">
            <?= $this->render('_form', [
                'model' => $model,
            ]) ?>
        </div>
    </div>

</div>
