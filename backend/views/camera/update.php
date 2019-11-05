<?php

use common\models\Camera;
use yii\helpers\Html;

/* @var $model Camera */

$this->title = 'Обновить камеру';
?>
<div class="equipment-update box-padding">

    <div class="panel panel-default">
        <div class="panel-heading" style="background: #fff;">
            <h3 class="text-center" style="color: #333;">
                <?php echo Html::encode($this->title) ?>
            </h3>
        </div>
        <div class="panel-body">

            <div id="myTabContent" class="tab-content">
                <div class="tab-pane fade active in" id="list">
                    <?php echo $this->render(
                        '_form',
                        [
                            'model' => $model,
                        ]
                    ) ?>
                </div>
            </div>

        </div>
    </div>

</div>
