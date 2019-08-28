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
            //'time1:datetime',
            //'value1',
            //'period_title2',
            //'time2:datetime',
            //'value2',
            //'period_title3',
            //'time3:datetime',
            //'value3',
            //'period_title4',
            //'time4:datetime',
            //'value4',
            //'createdAt',
            //'changedAt',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>
</div>
