<?php

use yii\grid\GridView;
use yii\helpers\Html;

/* @var $searchModel  backend\models\CitySearch */

$this->title = Yii::t('app', 'Город');
?>
<div class="orders-index box-padding-index">

    <div class="panel panel-default">
        <div class="panel-heading" style="background: #fff;">
            <h3 class="text-center" style="color: #333;">
                <?= Html::encode($this->title) ?>
            </h3>

            <ul class="nav nav-tabs" style="width: 305px; margin: 0 auto;">
                <li class="active"><a href="/city">Города</a></li>
                <li class=""><a href="/street">Улицы</a></li>
                <li class=""><a href="/house">Дома</a></li>
                <li class=""><a href="/object">Объекты</a></li>
            </ul>
        </div>
        <div class="panel-body">

            <div id="myTabContent" class="tab-content">
                <div class="tab-pane fade active in" id="list">

                    <p class="text-center">
                        <?= Html::a(Yii::t('app', 'Создать'), ['create'], ['class' => 'btn btn-success']) ?>
                    </p>

                    <?= GridView::widget([
                        'dataProvider' => $dataProvider,
                        'filterModel' => $searchModel,
                        'tableOptions' => [
                            'class' => 'table-striped table table-bordered table-hover table-condensed'
                        ],
                        'columns' => [
                            [
                                'attribute' => '_id',
                                'contentOptions' => [
                                    'class' => 'table_class',
                                    'style' => 'width: 50px; text-align: center'
                                ],
                                'headerOptions' => ['class' => 'text-center'],
                                'content' => function ($data) {
                                    return $data->_id;
                                }
                            ],
                            [
                                'attribute' => 'title',
                                'contentOptions' => [
                                    'class' => 'table_class'
                                ],
                                'headerOptions' => ['class' => 'text-center'],
                                'value' => 'title',
                            ],
                            [
                                'class' => 'yii\grid\ActionColumn',
                                'header' => 'Действия',
                                'headerOptions' => ['class' => 'text-center', 'width' => '70'],
                                'contentOptions' => [
                                    'class' => 'text-center'
                                ],
                                'template' => '{view} {update} {delete}{link}',
                            ],
                        ],
                    ]); ?>
                </div>
            </div>

        </div>
    </div>
</div>
