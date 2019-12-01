<?php
/* @var $devices
 * @var $cameras
 * @var $dataProviderSearch
 * @var $tree
 * @var $coordinates
 * @var $categories
 * @var $usersCount
 * @var $currentUser
 * @var $objectsCount
 * @var $objectsTypeCount
 * @var $events
 * @var $reportDataProvider
 * @var $objectsList
 * @var $objectsGroup
 * @var $usersList
 * @var $lightList
 * @var $lightGoodList
 * @var $lightBadList
 * @var $sensorCO2List
 * @var $lightGroup
 * @var $lightGoodGroup
 * @var $lightBadGroup
 * @var $sensorCO2Group
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

<!-- Main row -->
<div class="row">
    <div class="col-md-12">
        <?= $this->render('widget-map', ['coordinates' => $coordinates,
            'lightList' => $lightList, 'lightGoodList' => $lightGoodList,
            'lightBadList' => $lightBadList, 'sensorCO2List' => $sensorCO2List,
            'lightGroup' => $lightGroup, 'lightGoodGroup' => $lightGoodGroup,
            'lightBadGroup' => $lightBadGroup, 'sensorCO2Group' => $sensorCO2Group,
            'nodesGroup' => $nodesGroup, 'nodesList' => $nodesList,
            'camerasGroup' => $camerasGroup, 'camerasList' => $camerasList]); ?>
    </div>
</div>
<div class="row">
    <div class="col-md-12">
        <?= $this->render('widget-equipment-table', ['devices' => $devices]); ?>
    </div>
</div>
<div class="row">
    <div class="col-md-6">
        <?= $this->render('widget-calendar', ['events' => $events]); ?>
    </div>
    <div class="col-md-6">
        <?= $this->render('widget-report', ['reportDataProvider' => $reportDataProvider]); ?>
    </div>
</div>

<footer class="main-footer" style="margin-left: 0 !important;">
    <div class="pull-right hidden-xs" style="vertical-align: middle; text-align: center;">
        <b>Version</b> 1.0.2
    </div>
    <?php echo Html::a('<img src="images/mtm.png">', 'http://www.mtm-smart.com'); ?>
    <strong>Copyright &copy; 2019 <a href="http://www.mtm-smart.com">MTM Смарт</a>.</strong> Все права на
    программный продукт защищены.
</footer>
