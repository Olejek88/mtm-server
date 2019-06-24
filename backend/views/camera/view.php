<?php

use common\models\Camera;
use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $model Camera */

$this->title = "Камеры";
?>
<link href="/css/video-js.min.css" rel="stylesheet">
<script src="/js/video.min.js"></script>

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
                    <h6>
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
                    </h6>
                </div>
                <div>
                    <video
                            id="my-player"
                            class="video-js"
                            controls
                            preload="auto"
                            poster="//vjs.zencdn.net/v/oceans.png"
                            data-setup="{}">
                        <source src="/lightcams/<?= $model->uuid . '.m3u8' ?>" type="application/x-mpegURL"/>
                        <p class="vjs-no-js">
                            To view this video please enable JavaScript, and consider upgrading to a
                            web browser that
                            <a href="https://videojs.com/html5-video-support/" target="_blank">
                                supports HTML5 video
                            </a>
                        </p>
                    </video>
                    <script>
                        v = videojs('my-player');
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
