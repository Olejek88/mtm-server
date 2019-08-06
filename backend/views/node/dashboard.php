<?php
/* @var $node
 * @var $camera
 */

$this->title = Yii::t('app', 'Контроллер');
$this->registerJsFile('/js/vendor/video.min.js');
$this->registerCssFile('/css/vendor/video-js.min.css');
?>

<br/>
<!-- Info boxes -->
<div class="row">
    <div class="col-md-12">
    <?= $this->render('widget-node-info', ['node' => $node, 'type' => $type]); ?>
    </div>
</div>
<!-- /.row -->

<!-- Main row -->
<div class="row">
    <!-- Left col -->
    <div class="col-md-5">
        <?= $this->render('widget-archive', ['node' => $node]); ?>
    </div>

    <div class="col-md-7">
        <?= $this->render('widget-power', ['node' => $node]); ?>
    </div>
</div>
<div class="row">
    <div class="col-md-7">
        <?= $this->render('widget-thread', ['node' => $node]); ?>
    </div>

    <div class="col-md-5">
        <?= $this->render('widget-camera', ['camera' => $camera]); ?>
    </div>
</div>

<!-- Main row -->
<div class="row">
    <!-- Left col -->
    <div class="col-md-7">
        <?= $this->render('widget-devices', ['node' => $node]); ?>
    </div>

    <div class="col-md-5">
        <?= $this->render('widget-device-register', ['node' => $node]); ?>
    </div>

    <div class="col-md-8">
        <?= $this->render('widget-sensor-channel', ['node' => $node]); ?>
    </div>

    <div class="col-md-4">

        <?= $this->render('widget-status', ['node' => $node]); ?>
    </div>
</div>

<!-- /.content-wrapper -->
