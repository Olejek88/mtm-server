<?php

use yii\web\View;

/* @var $model
 */

$this->title = Yii::t('app', 'Камера');
$this->registerJsFile('/js/vendor/video.min.js', ['position' => View::POS_BEGIN]);
$this->registerCssFile('/css/vendor/video-js.min.css');
?>

<div class="box box-info">
    <div class="box-header with-border">
        <h3 class="box-title">Камера</h3>
        <div class="box-tools pull-right">
            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
            </button>
            <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i>
            </button>
        </div>
    </div>
    <!-- /.box-header -->
    <div class="box-body no-padding" style="min-height: 600px">

        <div class="row">
            <div class="col-md-12" style="width: 800px;">
                <video
                        id="my-player<?= $model["_id"] ?>"
                        class="video-js"
                        width='800'
                        controls
                        preload="auto"
                        poster="/images/camera_view.jpg"
                        data-setup='{"fluid": true}'>
                    <source src="/lightcams/<?= $model['uuid'] . '.m3u8' ?>" type="application/x-mpegURL"/>
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
                                src: "/lightcams/<?= $model['uuid'] . '.m3u8' ?>",
                                type: 'application/x-mpegURL'
                            });
                        },
                        errorInterval: 5
                    });
                </script>
                <div style="text-align: center;">
                    <a class="btn btn-success" onclick="window.history.go(-1); return false;">Назад</a>

                </div>
            </div>
        </div>
    </div>
</div>
