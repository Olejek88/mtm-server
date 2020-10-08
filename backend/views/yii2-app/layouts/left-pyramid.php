<?php/* @var $counts [] *//* @var $org */use yii\helpers\Html;$view = '';if (isset($_GET['view']))    $view = $_GET['view'];$type = '';if (isset($_GET['type']))    $type = $_GET['type'];$this->registerCssFile('/css/custom/modules/list/pyramid.css', ['depends' => ['yii\bootstrap\BootstrapAsset']]);?><aside class="main-sidebar">    <section class="sidebar">        <br/>        <div style="width: 100%; margin: 3px">            <?php            $class1 = 'btn btn-info xbtn';            $class2 = 'btn btn-info xbtn';            $class3 = 'btn btn-info xbtn ';            echo Html::a(Yii::t('app', 'Умный город'), ['#'], ['class' => $class1, 'style' => 'width: 290px']);            echo Html::a(Yii::t('app', 'Умное предприятие'), ['#'], ['class' => $class2, 'style' => 'width: 290px']);            echo Html::a(Yii::t('app', 'Умный дом'), ['#'], ['class' => $class3, 'style' => 'width: 290px']);            ?>        </div>        <br/>        <ul id="ac" class="nav side-menu pyramid">            <li class="ahead"><br/><br/><br/></li>            <li class="ahead development"><a href="#">РАЗВИТИЕ </a></li>            <li class="abody dev-blank"><a href="#"></a></li>            <li class="ahead education"><a href="#">ОБУЧЕНИЕ </a></li>            <li class="abody edu-blank"><a href="#"></a></li>            <li class="ahead transport"><a href="#">ТРАНСПОРТ </a></li>            <li class="abody tran-blank"><a href="#"></a></li>            <li class="ahead communication <?php if ($type == '4') echo 'active' ?>"><?= Html::a('СВЯЗЬ', ['/sound-file', 'type' => '4']); ?>            </li>            <li class="ahead ecology <?php if ($type == '3') echo 'active' ?>"><?= Html::a('ЭКОЛОГИЯ', ['/', 'type' => '3']); ?>            </li>            <li class="ahead safety <?php if ($type == '2') echo 'active' ?>"><?= Html::a('БЕЗОПАСНОСТЬ', ['/', 'type' => '2', 'view' => '2']); ?>            </li>            <li class="ahead energy <?php if ($type == '1') echo 'active' ?>"><?= Html::a('ЭНЕРГИЯ', ['/', 'type' => '1', 'view' => '1']); ?>            </li>        </ul>        <br/>        <div style="width: 100%; margin: 3px">            <?php            echo '<div class="org_title" style="text-align: center; background-color: #03DA01">' . $org["title"] . '</div>';            echo '<span class="stat">Улиц: ' . $counts["street"] . '</span></br>';            echo '<span class="stat">Шкафов управления: ' . $counts["node"] . '</span></br>';            echo '<span class="stat">Электросчетчиков: ' . $counts["elektro"] . '</span></br>';            echo '<span class="stat">Камер: ' . $counts["camera"] . '</span></br>';            echo '<span class="stat">Управляемых светильников: ' . $counts["light"] . '</span></br>';            echo '<span class="stat">Простых светильников: ' . $counts["light2"] . '</span></br>';            echo '<span class="stat">Датчиков: ' . $counts["sensors"] . '</span></br>';            ?>        </div>        <br/>        <div style="width: 100%; margin: 3px">            <?php            echo '<a href="http://mtm-com.ru/"><img style="width: 140px" border="0" src="/images/mtm.png" /></a>';            echo '<img border="0" style="width: 140px" src="/images/skolkovo.png" />';            ?>        </div>    </section></aside>