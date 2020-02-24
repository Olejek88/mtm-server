<?php

use common\models\Node;
use kartik\widgets\SwitchInput;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $model Node */
/* @var $zbcMode integer|null */

$this->title = 'Обновить контроллеры';
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
                    <h6>
                        <?php echo $this->render(
                            '_form',
                            [
                                'model' => $model,
                            ]
                        ) ?>
                    </h6>
                </div>
            </div>

            <div id="myTabContent" class="tab-content">
                <div class="tab-pane fade active in" id="list">
                    <h6>
                        <?php
                        if ($zbcMode !== null) {
                            $form = ActiveForm::begin(
                                [
                                    'id' => 'form-input-zbcoordinator-mode',
                                    'action' => '/node/set-manual-mode?id=' . $model->_id,
                                    'options' => [
                                        'class' => 'form-horizontal col-lg-12 col-sm-12 col-xs-12',
                                        'enctype' => 'multipart/form-data'
                                    ],
                                ]
                            );
                            echo '<label for="zbcmode">Ручной режим</label>';
                            echo SwitchInput::widget([
                                'name' => 'zbcmode',
                                'value' => $zbcMode,
                            ]);
                            echo Html::submitButton('Установить', ['class' => 'btn btn-primary']);
                            ActiveForm::end();
                        }
                        ?>
                    </h6>
                </div>
            </div>

        </div>
    </div>

</div>
