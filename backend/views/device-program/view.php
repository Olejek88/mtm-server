<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\web\YiiAsset;

/* @var $this yii\web\View */
/* @var $model common\models\DeviceProgram */

$this->title = $model->title;
$this->params['breadcrumbs'][] = ['label' => 'Программы', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
YiiAsset::register($this);
?>
<div class="device-program-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Изменить', ['update', 'id' => $model->_id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Удалить', ['delete', 'id' => $model->_id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Вы уверены что хотите удалить эту программу?',
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
            'value5',
            'createdAt',
            'changedAt',
        ],
    ]) ?>

</div>
