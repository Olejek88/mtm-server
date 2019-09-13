<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\DeviceProgram */

$this->title = 'Update Device Program: ' . $model->title;
$this->params['breadcrumbs'][] = ['label' => 'Device Programs', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->title, 'url' => ['view', 'id' => $model->_id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="device-program-update box-padding">
    <div class="panel panel-default">
        <div class="panel-heading" style="background: #fff;">
            <h3 class="text-center" style="color: #333;">
                <?php echo Html::encode($this->title) ?>
            </h3>
        </div>
        <div class="panel-body">

            <div id="myTabContent" class="tab-content">
                <div class="tab-pane fade active in" id="list">
                    <h6>
                        <?= $this->render('_form', [
                            'model' => $model,
                        ]) ?>
                    </h6>
                </div>
            </div>

        </div>
    </div>
</div>