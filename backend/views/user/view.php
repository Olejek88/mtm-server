<?php

use common\models\User;
use yii\widgets\DetailView;

/* @var $model User */
/* @var $events */

$this->title = 'Профиль пользователя :: ' . $model->name;
?>
<div class="content-wrapper">
    <section class="content-header">
        <h1>
            Профиль пользователя
        </h1>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-md-3">
                <?= DetailView::widget([
                    'model' => $model,
                    'attributes' => [
                        'id',
                        'username',
                        'name',
//                        'auth_key',
//                        'password_hash',
//                        'password_reset_token',
//                        'email:email',
                        'status',
//                        'created_at',
//                        'updated_at',
                    ],
                ]) ?>
            </div>
            <!-- /.col -->
        </div>
        <!-- /.row -->

    </section>
    <!-- /.content -->
</div>
<!-- /.content-wrapper -->