<?php

use common\models\User;
use yii\helpers\Html;

/* @var $model User */
/* @var $user_property */
/* @var $orders */
/* @var $events */
/* @var $tree */
/* @var $roleList */

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
                <!-- Profile Image -->
                <div class="box box-primary">
                    <div class="box-body box-profile">
                        <?php
                        $path = '/images/unknown2.png';
                        echo '<img class="profile-user-img img-responsive img-circle" src="' . Html::encode($path) . '">';
                        ?>
                        <h3 class="profile-username text-center"><?php echo $model['name'] ?></h3>
                        <p class="text-muted text-center"><?php echo $model['whoIs'] ?></p>
                    </div>
                </div>

                <!-- About Me Box -->
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Информация</h3>
                    </div>
                    <!-- /.box-header -->
                    <div class="box-body">
                        <strong><i class="fa fa-mobile margin-r-5"></i> Контакт</strong>
                        <span class="text-muted">
                            <?php echo $model['contact'] ?>
                        </span>
                        <hr>
                        <strong><i class="fa fa-check-circle margin-r-5"></i> Статус</strong>
                        <?php
                        echo '<span class="label label-success">Активен</span>';
                        ?>

                        <hr>
                        <strong><i class="fa fa-pencil margin-r-5"></i> Специализация</strong>
                        <p>
                            <?php
                            if (Yii::$app->user->can(User::PERMISSION_ADMIN)) {
                                echo '<span class="label label-danger">Администратор</span>';
                            }
                            ?>
                            <?php
                            if (Yii::$app->user->can(User::PERMISSION_OPERATOR)) {
                                echo '<span class="label label-success">Оператор</span>';
                            }
                            ?>
                            <?php
                            echo '<span class="label label-info">Персонал</span>';
                            ?>
                        </p>

                    </div>
                    <!-- /.box-body -->
                </div>
                <!-- /.box -->
            </div>
            <!-- /.col -->
            <div class="col-md-9">
                <div class="nav-tabs-custom">
                    <ul class="nav nav-tabs">
                        <li class="active" style="margin-right: 0"><a href="#timeline" data-toggle="tab">Журнал</a>
                        </li>
                    </ul>
                    <div class="tab-content">
                        <!-- /.tab-pane -->
                        <div class="active tab-pane" id="timeline">
                            <!-- The timeline -->
                            <ul class="timeline timeline-inverse">
                                <?php
                                foreach ($events as $event) {
                                    echo $event['event'];
                                }
                                ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
