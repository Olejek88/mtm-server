<?php

use wbraganca\fancytree\FancytreeWidget;
use yii\web\JsExpression;

/* @var $deviceTypes */

$this->title = 'Группы светильников';

?>

<table id="tree" style="width: 100%">
    <colgroup>
        <col width="*">
        <col width="120px">
        <col width="140px">
        <col width="150px">
        <col width="*">
        <col width="150px">
    </colgroup>
    <thead style="background-color: #337ab7; color: white">
    <tr>
        <th align="center" colspan="6" style="background-color: #3c8dbc; color: whitesmoke">Светильники</th>
    </tr>
    <tr style="background-color: #3c8dbc; color: whitesmoke">
        <th align="center">Название</th>
        <th>Адрес</th>
        <th>Статус</th>
        <th>Дата</th>
        <th>Адрес/Программа</th>
        <th>Каналов</th>
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
    </tr>
    </tbody>
</table>
<?php
$this->registerJsFile('/js/custom/modules/list/jquery.fancytree.contextMenu.js',
    ['depends' => ['wbraganca\fancytree\FancytreeAsset']]);
$this->registerJsFile('/js/custom/modules/list/jquery.contextMenu.min.js',
    ['depends' => ['yii\jui\JuiAsset']]);
$this->registerCssFile('/css/custom/modules/list/ui.fancytree.css');
$this->registerCssFile('/css/custom/modules/list/jquery.contextMenu.min.css');

echo FancytreeWidget::widget([
    'options' => [
        'id' => 'tree',
        'source' => $device,
        'checkbox' => true,
        'selectMode' => 3,
        'extensions' => ['dnd', "glyph", "table", "contextMenu"],
        'glyph' => 'glyph_opts',
        'dnd' => [
            'preventVoidMoves' => true,
            'preventRecursiveMoves' => true,
            'autoExpandMS' => 400,
            'dragStart' => new JsExpression('function(node, data) {
				return true;
			}'),
            'dragEnter' => new JsExpression('function(node, data) {
				return true;
			}'),
            'dragDrop' => new JsExpression('function(node, data) {
				data.otherNode. copyTo(node, data.hitMode);
				console.log(data.otherNode.data.uuid);
				console.log(node.data.uuid);
				$.ajax({
                     url: "group-move",
                     type: "post",
                     data: {
				        dev: data.otherNode.data.uuid,
				        grp: node.data.uuid                        
                     }
                });				
			}'),
        ],
        'contextMenu' => [
            'menu' => [
                'edit' => [
                    'name' => 'Задать конфигурацию',
                    'icon' => 'edit',
                    'callback' => new JsExpression('function(key, opt) {
                        var node = $.ui.fancytree.getNode(opt.$trigger);
                        if (node.folder==false) {
                            $.ajax({
                                url: "set-calendar",
                                type: "post",
                                data: {
                                    selected_node: node.key,
                                    uuid: node.data.uuid,
                                    type: node.data.type                                    
                                },
                                success: function (data) { 
                                    $(\'#modalAddConfig\').modal(\'show\');
                                    $(\'#modalContentConfig\').html(data);
                                }
                            });
                        }
                        else {
                            window.location.replace("../device-program/calendar?group="+node.data.uuid);
                        }
                    }')
                ],
                'default' => [
                    'name' => 'Задать программу по-умолчанию',
                    'icon' => 'edit',
                    'callback' => new JsExpression('function(key, opt) {
                        var node = $.ui.fancytree.getNode(opt.$trigger);
                        if (node.folder==true) {
                            $.ajax({
                                url: "set-default",
                                type: "post",
                                data: {
                                    selected_node: node.key,
                                    uuid: node.data.uuid,
                                    type: node.data.type                                    
                                },
                                success: function (data) { 
                                    $(\'#modalAddProgram\').modal(\'show\');
                                    $(\'#modalContent\').html(data);
                                }
                            });
                        }
                    }')
                ]
            ]
        ],
        'table' => [
            'indentation' => 20,
            "titleColumnIdx" => "1",
            "nodesColumnIdx" => "2",
            "statusColumnIdx" => "3",
            "dateColumnIdx" => "4",
            "addressColumnIdx" => "5",
            "channelsColumnIdx" => "6"
        ],
        'renderColumns' => new JsExpression('function(event, data) {
            var node = data.node;
            $tdList = $(node.tr).find(">td");
            $tdList.eq(1).html(node.data.nodes);
            $tdList.eq(2).html(node.data.status);
            $tdList.eq(3).html(node.data.date);
            $tdList.eq(4).html(node.data.address);
            $tdList.eq(5).html(node.data.channels);
        }')
    ]
]);

$this->registerJs('$("#modalAddConfig").on("hidden.bs.modal",
function () {
})');
?>

<div class="modal remote fade" id="modalAddConfig">
    <div class="modal-dialog"  style="width: 90%">
        <div class="modal-content loader-lg" id="modalContentConfig">
        </div>
    </div>
</div>

<div class="modal remote fade" id="modalNewGroup">
    <div class="modal-dialog"  style="width: 300px">
        <div class="modal-content loader-lg" id="modalContentNewGroup">
        </div>
    </div>
</div>

<div class="modal remote fade" id="modalAddProgram">
    <div class="modal-dialog" style="width: 500px">
        <div class="modal-content loader-lg" id="modalContent">
        </div>
    </div>
</div>
