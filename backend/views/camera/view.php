<?php

use common\models\Camera;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\DetailView;

/* @var $model Camera */
/* @var $this View */


$this->registerJsFile('/js/vendor/video.min.js', ['position' => View::POS_BEGIN]);
$this->registerCssFile('/css/vendor/video-js.min.css');
$this->title = "Камеры";
?>

<div class="order-status-view box-padding">

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
                    <?php echo DetailView::widget(
                        [
                            'model' => $model,
                            'attributes' => [
                                'uuid',
                                'title',
                                [
                                    'label' => 'Статус',
                                    'value' => $model['deviceStatus']->title
                                ],
                                [
                                    'label' => 'Контроллер',
                                    'value' => $model['node']->address
                                ],
                                [
                                    'label' => 'Объект',
                                    'value' => $model['node']['object']->getAddress()
                                ],
                                'createdAt',
                                'changedAt',
                            ],
                        ]
                    ) ?>
                </div>
                <div>
                    <video
                            id="my-player<?= $model["_id"] ?>"
                            class="video-js"
                            controls
                            preload="auto"
                            poster="//vjs.zencdn.net/v/oceans.png"
                            data-setup="{}">
                        <source src="/lightcams/<?= $model->uuid . '.m3u8' ?>" type="application/x-mpegURL"/>
                        <p class="vjs-no-js">
                            Для просмотра видео включите JavaScript и обновите браузер для поддержки
                            <a href="https://videojs.com/html5-video-support/" target="_blank">
                                HTML5 видео
                            </a>
                        </p>
                    </video>
                    <script>
                        v = videojs('my-player<?= $model["_id"] ?>');
                        v.on('error', function () {
                            console.log('XXX');
                            console.log(this.error());
                        });
                        v.reloadSourceOnError({
                            getSource: function (reload) {
                                console.log('Reloading because of an error');
                                reload({
                                    src: "/lightcams/<?= $model->uuid . '.m3u8' ?>",
                                    type: 'application/x-mpegURL'
                                });
                            },
                            errorInterval: 5
                        });
                    </script>
                </div>
            </div>
        </div>
    </div>
</div>
