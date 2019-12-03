<?php

use common\models\AreaNode;
use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\AreaNodeSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Шкафы по территориям';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="area-node-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a('Добавить шкаф к территории', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            '_id',
//            'uuid',
//            'oid',
//            'areaUuid',
            [
                'label' => 'Территория',
                'content' => function ($model) {
                    /** @var AreaNode $model */
                    return $model->area->title;
                }
            ],
            [
                'label' => 'Шкаф',
                'content' => function ($model) {
                    /** @var AreaNode $model */
                    return $model->node->address;
                }
            ],
//            'nodeUuid',
            //'createdAt',
            //'changedAt',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>
</div>
