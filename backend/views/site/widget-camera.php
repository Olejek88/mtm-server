<?php
/* @var $camera */

use common\models\DeviceStatus;

?>

<div class="box box-success">
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
    <div class="box-body no-padding">
        <div class="col-sm-7 invoice-col">
            <div class="product-img">
                <div>
                    <video
                            id="my-player"
                            class="video-js"
                            controls
                            preload="auto"
                            poster="/images/camera_view.jpg"
                            data-setup="{}">
                        <source src="/lightcams/<?= $camera['uuid'] . '.m3u8' ?>" type="application/x-mpegURL"/>
                        <p class="vjs-no-js">
                            Для просмотра видео включите JavaScript и обновите браузер для поддержки
                            <a href="https://videojs.com/html5-video-support/" target="_blank">
                                HTML5 видео
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
                                    src: "/lightcams/<?= $camera['uuid'] . '.m3u8' ?>",
                                    type: 'application/x-mpegURL'
                                });
                            },
                            errorInterval: 5
                        });
                    </script>
                </div>
                <?php /*echo Html::img('@web/images/camera_view.jpg')*/ ?>
            </div>
        </div>
        <!-- /.col -->
        <div class="col-sm-5">
                <?php
                if ($camera) {
                    $color = 'background-color: white';
                    if ($camera['deviceStatusUuid'] == DeviceStatus::UNKNOWN ||
                        $camera['deviceStatusUuid'] == DeviceStatus::NOT_MOUNTED)
                        $color = 'background-color: gray';
                    if ($camera['deviceStatusUuid'] == DeviceStatus::NOT_WORK)
                        $color = 'background-color: lightred';
                    if ($camera['deviceStatusUuid'] == DeviceStatus::WORK)
                        $color = 'background-color: green';
                    $status = "<span class='badge' style='" . $color . "; height: 12px; margin-top: -3px'> </span>&nbsp;"
                        .$camera['deviceStatus']['title'];

                    echo '<strong>&nbsp;&nbsp;' . $camera['object']->getAddress() . '</strong> <br>';
                    echo '<strong>Статус</strong>&nbsp;&nbsp;' . $status . '<br>';
                    echo '<strong>Адрес</strong>&nbsp;&nbsp;' . $camera['address'] . '<br>';
                }
                ?>
        </div>
    </div>
</div>
