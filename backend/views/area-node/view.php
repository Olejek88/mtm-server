<?php

use yii\helpers\Html;
use yii\web\YiiAsset;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\AreaNode */

$this->title = $model->_id;
$this->params['breadcrumbs'][] = ['label' => 'Шкафы по территориям', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
YiiAsset::register($this);
?>
<div class="task-view box-padding">

    <div class="panel panel-default">
        <div class="panel-heading" style="background: #fff;">
            <h3 class="text-center" style="color: #333;">
                <?php echo Html::encode($this->title) ?>
            </h3>
        </div>
        <div class="panel-body">

            <div id="myTabContent" class="tab-content">
                <div class="tab-pane fade active in" id="list">
                    <p class="text-center">
                        <?php
                        echo $this->render('@backend/views/yii2-app/layouts/buttons.php',
                            ['model' => $model]);
                        ?>
                    </p>
                    <?php echo DetailView::widget([
                        'model' => $model,
                        'attributes' => [
                            '_id',
                            [
                                'label' => 'Территория',
                                'value' => $model->area->title,
                            ],
                            [
                                'label' => 'Шкаф',
                                'value' => $model->node->address,
                            ],
//                            'uuid',
//                            'title',
                            'createdAt',
                            'changedAt',
                        ],
                    ]) ?>
                </div>
            </div>

        </div>
    </div>
</div>
