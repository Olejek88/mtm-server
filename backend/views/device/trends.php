<?php

use yii\web\View;

/* @var $device
 * @var $parameters1
 * @var $parameters2
 * @var $parameters3
 * @var $parameters4
 */

$this->title = Yii::t('app', 'Тренды');
$this->registerJsFile('/js/vendor/video.min.js', ['position' => View::POS_BEGIN]);
$this->registerCssFile('/css/vendor/video-js.min.css');

?>

<br/>
<div class="row">
    <div class="col-md-12">
        <?= $this->render('widget-electro-trends', ['device' => $device, 'parameters' => $parameters1]); ?>
    </div>
</div>
<div class="row">
    <div class="col-md-12">
        <?= $this->render('widget-electro-trends', ['device' => $device, 'parameters' => $parameters2]); ?>
    </div>
</div>
<div class="row">
    <div class="col-md-12">
        <?= $this->render('widget-electro-trends', ['device' => $device, 'parameters' => $parameters3]); ?>
    </div>
</div>
