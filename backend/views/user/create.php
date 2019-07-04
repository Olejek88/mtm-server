<?php
/* @var $model common\models\User */
/* @var $role Role */

/* @var $roleList array */

use backend\models\Role;
use yii\helpers\Html;

$this->title = 'Новый пользователь';
?>
<div class="user-create box-padding">

    <div class="panel panel-default">
        <div class="panel-heading" style="background: #fff;">
            <h3 class="text-center" style="color: #333;">
                <?= Html::encode($this->title) ?>
            </h3>
        </div>
        <div class="panel-body">

            <div id="myTabContent" class="tab-content">
                <div class="tab-pane fade active in" id="list">
                    <h6>
                        <?= $this->render('_form', [
                            'model' => $model,
                            'role' => $role,
                            'roleList' => $roleList,
                        ]) ?>
                    </h6>
                </div>
            </div>

        </div>
    </div>

</div>
