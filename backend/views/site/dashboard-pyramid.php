<?php
/* @var $measures
 * @var $devices
 * @var $cameras
 * @var $tree
 * @var $coordinates
 * @var $categories
 * @var $equipments Device[]
 * @var $usersCount
 * @var $currentUser
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
    <div class="col-md-12">
        <?php
        if (!isset($_GET['type']) || (isset($_GET['type']) && $_GET['type'] == '1')) {
            echo $this->render('widget-map', ['coordinates' => $coordinates,
                'devicesGroup' => $devicesGroup, 'devicesList' => $devicesList,
                'nodesGroup' => $nodesGroup, 'nodesList' => $nodesList,
                'camerasGroup' => $camerasGroup, 'camerasList' => $camerasList]);
        }
        if (isset($_GET['type']) && ($_GET['type'] == '2'))
            echo $this->render('widget-equipment-table', ['devices' => $devices]);
        if (isset($_GET['type']) && ($_GET['type'] == '3'))
            echo $this->render('widget-equipment-tree', ['devices' => $devices]);
        ?>
    </div>
</div>

<footer class="main-footer" style="margin-left: 0 !important;">
    <div class="pull-right hidden-xs" style="vertical-align: middle; text-align: center;">
        <b>Version</b> 0.2.1
    </div>
    <?php echo Html::a('<img src="images/mtm.png">', 'http://www.mtm-smart.com'); ?>
    <strong>Copyright &copy; 2019 <a href="http://www.mtm-smart.com">MTM Смарт</a>.</strong> Все права на
    программный продукт защищены.
</footer>
