<?phpuse yii\helpers\Html;$currentUser = Yii::$app->view->params['currentUser'];$userImage = Yii::$app->view->params['userImage'];$view = '';if (isset($_GET['view']))    $view = $_GET['view'];$type = '';if (isset($_GET['type']))    $type = $_GET['type'];?><style>    .pyramid{        position: relative;        /*background-color: rgba(154, 219, 206, 0.44);*/        background: #3c8dbc;        z-index: 2;        color: #fff;        margin: 2px;        -webkit-clip-path: polygon(100% 100%, 0 100%, 50% 0);        clip-path: polygon(100% 100%, 0 100%, 50% 0);        align-items: center;        position: relative;    }    .pyramid ul {        width: 100%;        position: absolute;        bottom: 0;    }    .pyramid a {        width: 100%;        display: block;        text-align: center;        text-decoration: none;        padding: 3px !important;        margin: 0 !important;        outline: none;        font-size: 14px;        color: #fff;        border-bottom: 1px solid #fff;    }    .pyramid li a{        border-bottom: 1px solid #fff;        color: #fff;    }    /*.pyramid li:last-child a {        border-bottom: none;    }*/    .pyramid li:first-child a {        border-bottom: none;        color: #fff;    }    .pyramid a:hover {        background-color: #fefefe;    }    .pyramid a:hover span {        color: #fff;        border-bottom: 1px dashed #fff;    }    .pyramid a span {        color: #fff;        border-bottom: 1px dashed #423937;    }    #ac .abody{        display: none;        /*background: linear-gradient(#334556,#2C4257),#2A3F54;        background: #ededed;*/        background: #fff;        width: 100%;        border: none;    }    #ac .ahead.active a{        background: green;        color: white;    }    #ac .abody.active a{        background: green;        color: white;    }    .skin-blue .sidebar a {        color: white;    }    .content-wrapper,    .right-side,    .main-footer {        -webkit-transition: -webkit-transform 0.3s ease-in-out, margin 0.3s ease-in-out;        -moz-transition: -moz-transform 0.3s ease-in-out, margin 0.3s ease-in-out;        -o-transition: -o-transform 0.3s ease-in-out, margin 0.3s ease-in-out;        transition: transform 0.3s ease-in-out, margin 0.3s ease-in-out;        margin-left: 280px;        z-index: 820;    }    .main-sidebar {        width: 280px;    }</style><aside class="main-sidebar">    <section class="sidebar">        <!-- Sidebar user panel -->        <div class="user-panel" style="border-color: #fff">            <div class="pull-left image">                <?php                echo '<img src="' . $userImage . '" class="img-circle" alt="User Image">';                ?>            </div>            <div class="pull-left info">                <p><?php if ($currentUser) echo $currentUser['name']; ?> </p>                <a href="#"><i class="fa fa-circle text-success"></i> В сети</a>            </div>        </div>        <ul id="ac" class="nav side-menu pyramid">            <li class="ahead"><br/><br/><br/></li>            <li class="ahead development"><a href="#">Развитие </a></li>            <li class="abody dev-blank"><a href="#"></a></li>            <li class="ahead education"><a href="#">Обучение </a></li>            <li class="abody edu-blank"><a href="#"></a></li>            <li class="ahead transport"><a href="#">Транспорт </a></li>            <li class="abody tran-blank"><a href="#"></a></li>            <li class="ahead communication <?php if ($view=='4') echo 'active' ?>"><?= Html::a('Связь', ['', 'type' => $type, 'view' => '4']); ?>            </li>            <li class="ahead ecology <?php if ($view=='3') echo 'active' ?>"><a href="#">Экология </a></li>            <li class="ahead safety <?php if ($view=='2') echo 'active' ?>"><?= Html::a('Безопасность', ['', 'type' => $type, 'view' => '2']); ?>            </li>            <li class="ahead energy <?php if ($view=='1') echo 'active' ?>"><?= Html::a('Энергия', ['', 'type' => $type, 'view' => '1']); ?>            </li>        </ul>        </br>        <div style="width: 100%; margin: 2px">        <?php        $class1 = 'btn btn-info';        $class2 = 'btn btn-info';        $class3 = 'btn btn-info';        if (isset($_GET['type']) && $_GET['type']==1) $class1 = 'btn btn-success';        if (!isset($_GET['type']) || $_GET['type']=='') $class1 = 'btn btn-success';        if (isset($_GET['type']) && $_GET['type']==2) $class2 = 'btn btn-success';        if (isset($_GET['type']) && $_GET['type']==3) $class3 = 'btn btn-success';        echo Html::a(Yii::t('app', 'Карта'), ['../site/dashboard', 'type' => '1', 'view' => $view], ['class' => $class1, 'style' => 'width: 92px']);        if ($view=='2')            echo Html::a(Yii::t('app', 'Таблица'), ['../camera', 'type' => '2', 'view' => $view], ['class' => $class2, 'style' => 'width: 92px']);        else {            if ($view=='4')                echo Html::a(Yii::t('app', 'Таблица'), ['../sound-file', 'type' => '2', 'view' => $view], ['class' => $class2, 'style' => 'width: 92px']);            else                echo Html::a(Yii::t('app', 'Таблица'), ['../device', 'type' => '2', 'view' => $view], ['class' => $class2, 'style' => 'width: 92px']);        }        if ($view=='2')            echo Html::a(Yii::t('app', 'Дерево'), ['../camera/tree', 'type' => '3', 'view' => $view], ['class' => $class3, 'style' => 'width: 92px']);        else            echo Html::a(Yii::t('app', 'Дерево'), ['../device/tree', 'type' => '3', 'view' => $view], ['class' => $class3, 'style' => 'width: 92px']);        ?>        </div>    </section></aside>