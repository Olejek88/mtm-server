<?php
/* @var $cityCount
 * @var $streetCount
 * @var $objectCount
 * @var $nodesCount
 * @var $channelsCount
 * @var $deviceCount
 * @var $deviceTypeCount
 * @var $contragentCount
 * @var $measures
 * @var $devices
 * @var $cameras
 * @var $tree
 * @var $coordinates
 * @var $categories
 * @var $equipments Device[]
 * @var $usersCount
 * @var $currentUser
 * @var $objectsCount
 * @var $objectsTypeCount
 * @var $events
 * @var $users User[]
 * @var $objectsList
 * @var $objectsGroup
 * @var $usersList
 * @var $last_measures
 * @var $complete
 * @var $devicesGroup
 * @var $devicesList
 * @var $camerasGroup
 * @var $camerasList
 * @var $nodesGroup
 * @var $nodesList
 */

use common\models\Device;
use common\models\User;
use yii\helpers\Html;

$this->title = Yii::t('app', 'Сводная');
?>

<br/>
<!-- Main row -->
<div class="row">
    <div class="col-md-8">
        <?= $this->render('widget-map', ['coordinates' => $coordinates,
            'devicesGroup' => $devicesGroup, 'devicesList' => $devicesList,
            'nodesGroup' => $nodesGroup, 'nodesList' => $nodesList,
            'camerasGroup' => $camerasGroup, 'camerasList' => $camerasList]); ?>
    </div>

    <div class="col-md-4">
        <?= $this->render('widget-equipments', ['devices' => $devices]); ?>
    </div>
</div>
<div class="row">
    <div class="col-md-7">
        <?= $this->render('widget-archive'); ?>
    </div>

    <div class="col-md-5">
        <div class="row">
            <?php
            foreach ($cameras as $camera) {
                echo '<div class="col-md-12">';
                echo $this->render('widget-camera',['camera' => $camera]);
                echo '</div>';
            }
            ?>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-12">
        <?= $this->render('widget-equipment-tree', ['devices' => $tree]); ?>
    </div>

<!--    <div class="col-md-4">
        <?/*= $this->render('widget-stats', ['counts' => $counts]);
        */?>
    </div>
--></div>
<!-- /.content-wrapper -->

<footer class="main-footer" style="margin-left: 0 !important;">
    <div class="pull-right hidden-xs" style="vertical-align: middle; text-align: center;">
        <b>Version</b> 1.0.2
    </div>
    <?php echo Html::a('<img src="images/mtm.png">', 'http://www.mtm-smart.com'); ?>
    <strong>Copyright &copy; 2019 <a href="http://www.mtm-smart.com">MTM Смарт</a>.</strong> Все права на
    программный продукт защищены.
</footer>
