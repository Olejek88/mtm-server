<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Device Programs';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="device-program-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create Device Program', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            '_id',
            'uuid',
            'oid',
            'title',
            //'period_title1',
            //'value1',
            //'period_title2',
            //'time2',
            //'value2',
            //'period_title3',
            //'time3',
            //'value3',
            //'period_title4',
            //'time4',
            //'value4',
            //'period_title5',
            //'value5',
            //'createdAt',
            //'changedAt',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>
</div>
