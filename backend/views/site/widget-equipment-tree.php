<?php

use wbraganca\fancytree\FancytreeWidget;
use yii\helpers\Html;
use yii\web\JsExpression;

/* @var $devices */
?>

<div class="box box-primary">
    <div class="box-header with-border">
        <h3 class="box-title">Устройства системы</h3>
        <div class="box-tools pull-right">
            <div class="btn-group">
                <button type="button" class="btn btn-box-tool dropdown-toggle" data-toggle="dropdown">
                    <i class="fa fa-bars"></i></button>
                <ul class="dropdown-menu pull-right" role="menu">
                    <li><?php echo Html::a("Все оборудование","/device/table") ?></li>
                    <li><?php echo Html::a("Деревом","/device/tree") ?></li>
                </ul>
            </div>
            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
            </button>
            <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i>
            </button>
        </div>
    </div>
    <!-- /.box-header -->
    <div class="box-body">
        <table id="tree" cellspacing="1" style="width: 100%">
            <colgroup>
                <col width="*">
                <col width="140px">
                <col width="150px">
                <col width="140px">
                <col width="100px">
                <col width="100px">
            </colgroup>
            <thead style="background-color: #337ab7; color: white">
            <tr style="background-color: #3c8dbc; color: whitesmoke">
                <th align="center">Оборудование</th>
                <th>Статус</th>
                <th>Дата</th>
                <th>Интерфейс</th>
                <th>Порт</th>
                <th>Значение</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td></td>
                <td class="alt"></td>
                <td class="center"></td>
                <td class="alt"></td>
                <td class="center"></td>
                <td class="alt"></td>
                <td class="center"></td>
            </tr>
            </tbody>
        </table>
        <?php
            echo FancytreeWidget::widget([
            'options' => [
                'id' => 'tree',
                'source' => $devices,
                'extensions' => ["glyph", "table"],
                'glyph' => 'glyph_opts',
                'table' => [
                    'indentation' => 20,
                    "titleColumnIdx" => "1",
                    "statusColumnIdx" => "2",
                    "dateColumnIdx" => "3",
                    "interfaceColumnIdx" => "4",
                    "portColumnIdx" => "5",
                    "valueColumnIdx" => "6"
                ],
                'renderColumns' => new JsExpression('function(event, data) {
                        var node = data.node;
                        $tdList = $(node.tr).find(">td");
                        $tdList.eq(1).html(node.data.status);
                        $tdList.eq(2).html(node.data.date);
                        $tdList.eq(3).text(node.data.interface);
                        $tdList.eq(4).text(node.data.port);                                                  
                        $tdList.eq(5).text(node.data.value);                                                  
                   }')
            ]
        ]);
        ?>
    </div>
</div>
