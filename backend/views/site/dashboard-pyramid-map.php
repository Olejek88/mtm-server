<?php
/* @var $measures
 * @var $dataProvider
 * @var $searchModel
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

$this->title = Yii::t('app', 'Сводная');
?>

<!-- Main row -->
<div class="row">
    <div class="col-md-7">
        <?php
        if (isset($_GET['type'])) {
            switch ($_GET['type']) {
                case 1 :
                default:
                    echo $this->render('index', [
                        'coordinates' => $coordinates,
                        'devicesGroup' => $devicesGroup,
                        'devicesList' => $devicesList,
                        'nodesGroup' => $nodesGroup,
                        'nodesList' => $nodesList,
                        'camerasGroup' => $camerasGroup,
                        'camerasList' => $camerasList
                    ]);
                    break;
                case 2 :
                    echo $this->render('../device/index', ['searchModel' => $searchModel, 'dataProvider' => $dataProvider]);
                    break;
                case 3 :
                    echo $this->render('../device/tree', ['device' => $tree]);
                    break;
            }
        } else {
            echo $this->render('index', ['coordinates' => $coordinates,
                'devicesGroup' => $devicesGroup, 'devicesList' => $devicesList,
                'nodesGroup' => $nodesGroup, 'nodesList' => $nodesList,
                'camerasGroup' => $camerasGroup, 'camerasList' => $camerasList]);
        }
        ?>
    </div>
    <div class="col-md-5">
        <div class="row">
            <div class="col-md-12">
            <?php
        if (!isset($_GET['type']))
        echo $this->render('../device/index-small', ['searchModel' => $searchModel, 'dataProvider' => $dataProvider]);
        ?>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
            <?php
        if (!isset($_GET['type']))
        echo $this->render('../device/tree-small', ['device' => $tree]);
        ?>
            </div>
        </div>
        </div>
</div>

<!--<footer class="main-footer" style="margin-left: 0 !important;">
    <div class="pull-right hidden-xs" style="vertical-align: middle; text-align: center;">
        <b>Version</b> 0.2.1
    </div>
    <?php /*echo Html::a('<img src="images/mtm.png">', 'http://www.mtm-smart.com'); */ ?>
    <strong>Copyright &copy; 2019 <a href="http://www.mtm-smart.com">MTM Смарт</a>.</strong> Все права на
    программный продукт защищены.
</footer>
-->