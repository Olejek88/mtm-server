<?php

$currentUser = Yii::$app->view->params['currentUser'];
$userImage = Yii::$app->view->params['userImage'];
?>
<aside class="main-sidebar">

    <section class="sidebar">
        <!-- Sidebar user panel -->
        <div class="user-panel">
            <div class="pull-left image">
                <?php
                echo '<img src="' . $userImage . '" class="img-circle" alt="User Image">';
                ?>
            </div>
            <div class="pull-left info">
                <p><?php if ($currentUser) echo $currentUser['name']; ?> </p>
                <a href="#"><i class="fa fa-circle text-success"></i> Online</a>
            </div>
        </div>
        <?= dmstr\widgets\Menu::widget(
            [
                'options' => ['class' => 'sidebar-menu'],
                'items' => [
                    ['label' => 'Login', 'url' => ['site/login'], 'visible' => Yii::$app->user->isGuest],
                    [
                        'label' => 'Карта',
                        'icon' => 'fa fa-map',
                        'url' => '/site/index',
                    ],
                ],
            ]
        ) ?>

        <?= dmstr\widgets\Menu::widget(
            [
                'options' => ['class' => 'sidebar-menu', 'data-widget' => 'tree'],
                'items' => [
                    [
                        "label" => "Оборудование",
                        'icon' => 'glyphicon glyphicon-inbox',
                        "items" => [
                            ["label" => "Таблицей", 'icon' => 'fa fa-table', "url" => ["/device"]],
                            ["label" => "Деревом", 'icon' => 'fa fa-tree', "url" => ["/device/tree"]],
                            ["label" => "Светильники", 'icon' => 'fa fa-tree', "url" => ["/device/tree-light"]],
                            ["label" => "Группы светильников", 'icon' => 'fa fa-tree', "url" => ["/device/tree-group"]],
                            ["label" => "Камеры", 'icon' => 'fa fa-camera', "url" => ["/camera/tree"]],
                            ["label" => "Камеры таблицей", 'icon' => 'fa fa-camera', "url" => ["/camera"]],
                            ["label" => "Программы светильников", 'icon' => 'fa fa-file-text', "url" => ["/device-program"]]
                        ],
                    ],
                ],
            ]
        ) ?>

        <?= dmstr\widgets\Menu::widget(
            [
                'options' => ['class' => 'sidebar-menu', 'data-widget' => 'tree'],
                'items' => [
                    [
                        "label" => "Контроллеры",
                        'icon' => 'fa fa-bar-chart',
                        "items" => [
                            ["label" => "Контроллеры", 'icon' => 'fa fa-table', "url" => ["/node"]],
                            ["label" => "Статус шкафов", 'icon' => 'fa fa-table', "url" => ["/node/status"]],
                            ["label" => "Потоки", 'icon' => 'fa fa-bar-chart', "url" => ["/thread"]],
                            ["label" => "Каналы измерения", 'icon' => 'fa fa-line-chart', "url" => ["/sensor-channel/table"]],
                            ["label" => "Сообщения", 'icon' => 'fa fa-book', "url" => ["/sound-file"]]
                        ],
                    ],
                ],
            ]
        ) ?>

        <?= dmstr\widgets\Menu::widget(
            [
                'options' => ['class' => 'sidebar-menu push-analytics', 'data-widget' => 'tree'],
                'items' => [
                    [
                        "label" => "Отчеты",
                        'icon' => 'glyphicon glyphicon-stats',
                        "items" => [
                            ["label" => "Отчет о потреблении", 'icon' => 'fa fa-table', "url" => ["/device/report"]],
                            ["label" => "Отчет по группам", 'icon' => 'fa fa-table', "url" => ["/device/report-group"]]
                        ],
                    ],
                ],
            ]
        ) ?>

        <?= dmstr\widgets\Menu::widget(
            [
                'options' => ['class' => 'sidebar-menu', 'data-widget' => 'tree'],
                'items' => [
                    [
                        "label" => "Справочники",
                        'icon' => 'glyphicon glyphicon-equalizer',
                        "items" => [
                            ["label" => "Пользователи", 'icon' => 'fa fa-user', "url" => ["/user"]],
                            ["label" => "Тип устройств", 'icon' => 'fa fa-table', "url" => ["/device-type"]],
                        ],
                    ],
                ],
            ]
        ) ?>

        <?= dmstr\widgets\Menu::widget(
            [
                'options' => ['class' => 'sidebar-menu', 'data-widget' => 'tree'],
                'items' => [
                    [
                        "label" => "Объекты",
                        'icon' => 'fa fa-home',
                        "items" => [
                            ["label" => "Города", 'icon' => 'fa fa-table', "url" => ["/city"]],
                            ["label" => "Улицы", 'icon' => 'fa fa-street-view', "url" => ["/street"]],
                            ["label" => "Дома", 'icon' => 'fa fa-home', "url" => ["/house"]],
                            ["label" => "Объекты", 'icon' => 'fa fa-table', "url" => ["/object/table"]],
                        ],
                    ],
                ],
            ]
        ) ?>


    </section>

</aside>
