<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\web\YiiAsset;

/* @var $this yii\web\View */
/* @var $model common\models\DeviceProgram */

$this->title = $model->title;
$this->params['breadcrumbs'][] = ['label' => 'Device Programs', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
YiiAsset::register($this);
?>
<div class="device-program-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Update', ['update', 'id' => $model->_id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'id' => $model->_id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
//            '_id',
//            'uuid',
//            'oid',
            'title',
            'period_title1',
            'time1',
            'value1',
            'period_title2',
            'time2',
            'value2',
            'period_title3',
            'time3',
            'value3',
            'period_title4',
            'time4',
            'value4',
            'period_title5',
            'time5',
            'value5',
            'createdAt',
            'changedAt',
        ],
    ]) ?>

</div>
