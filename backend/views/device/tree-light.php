<?php

use wbraganca\fancytree\FancytreeWidget;
use yii\web\JsExpression;

/* @var $deviceTypes */

$this->title = 'Дерево светильников';

?>

<table id="tree" style="width: 100%">
    <colgroup>
        <col width="*">
        <col width="120px">
        <col width="140px">
        <col width="150px">
        <col width="150px">
        <col width="150px">
    </colgroup>
    <thead style="background-color: #337ab7; color: white">
    <tr>
        <th align="center" colspan="6" style="background-color: #3c8dbc; color: whitesmoke">Светильники
        </th>
    </tr>
    <tr style="background-color: #3c8dbc; color: whitesmoke">
        <th align="center">Название</th>
        <th>Статус</th>
        <th>Дата</th>
        <th>Адрес</th>
        <th>Каналов</th>
        <th>Конфигурация</th>
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
				data.otherNode.moveTo(node, data.hitMode);
			}'),
        ],
        'contextMenu' => [
            'menu' => [
                'new' => [
                    'name' => 'Добавить новый',
                    'icon' => 'add',
                    'callback' => new JsExpression('function(key, opt) {
                        var node = $.ui.fancytree.getNode(opt.$trigger);
                        if (node.folder==true) {
                            $.ajax({
                                url: "new",
                                type: "post",
                                data: {
                                    selected_node: node.key,
                                    uuid: node.data.uuid,
                                    type: node.type,                                                                        
                                    source: node.data.source                                                                   
                                },
                                success: function (data) { 
                                    $(\'#modalAddEquipment\').modal(\'show\');
                                    $(\'#modalContentEquipment\').html(data);
                                }
                           }); 
                        }                        
                    }')
                ],
                'delete' => [
                    'name' => 'Удалить',
                    'icon' => 'delete',
                    'callback' => new JsExpression('function(key, opt) {
                            var sel = $.ui.fancytree.getTree().getSelectedNodes();
                            if (sel.length > 0) {
                                $.each(sel, function (event, data) {
                                    if (data.type == "device") {
                                        $.ajax({
                                             url: "remove",
                                             type: "post",
                                             data: {
                                                  type: data.type,
                                                  selected_node: data.key,
                                                  uuid: data.data.uuid
                                             },
                                             error: function (result) {
                                                 console.log(result);
                                                 alert(result.statusText);
                                             },
                                             success: function (result) {
                                                 data.remove();
                                             }
                                        });
                                    }
                                });
                            } else {
                                var node = $.ui.fancytree.getNode(opt.$trigger);
                                if (node.type == "device") {
                                    $.ajax({
                                        url: "remove",
                                        type: "post",
                                        data: {
                                            type: node.type,
                                            selected_node: node.key,
                                            uuid: node.data.uuid
                                        },
                                        error: function (result) {
                                            console.log(result);
                                            alert(result.statusText);                                 
                                        },
                                        success: function (result) {
                                            node.remove();            
                                        }                                    
                                    });
                                }
                            }
                    }')
                ],
                'edit' => [
                    'name' => 'Редактировать',
                    'icon' => 'edit',
                    'callback' => new JsExpression('function(key, opt) {
                        var node = $.ui.fancytree.getNode(opt.$trigger);
                        $.ajax({
                            url: "edit",
                            type: "post",
                            data: {
                                selected_node: node.key,
                                uuid: node.data.uuid,
                                deviceTypeUuid: node.data.deviceTypeUuid,
                                type: node.type,
                                source: node.data.source                                                                   
                            },
                            success: function (data) { 
                                $(\'#modalAddEquipment\').modal(\'show\');
                                $(\'#modalContentEquipment\').html(data);
                            }
                       }); 
                    }')
                ],
                'defect' => [
                    'name' => 'Задать конфигурацию',
                    'icon' => 'edit',
                    'callback' => new JsExpression('function(key, opt) {
                        var node = $.ui.fancytree.getNode(opt.$trigger);
                        if (node.folder==false) {
                            $.ajax({
                                url: "set-config",
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
                    }')
                ]
            ]
        ],
        'table' => [
            'indentation' => 20,
            "titleColumnIdx" => "1",
            "statusColumnIdx" => "2",
            "dateColumnIdx" => "3",
            "addressColumnIdx" => "4",
            "channelsColumnIdx" => "5",
            "configColumnIdx" => "6"
        ],
        'renderColumns' => new JsExpression('function(event, data) {
            var node = data.node;
            $tdList = $(node.tr).find(">td");
            $tdList.eq(1).html(node.data.status);
            $tdList.eq(2).html(node.data.date);
            $tdList.eq(3).html(node.data.address);
            $tdList.eq(4).html(node.data.channels);
            $tdList.eq(5).html(node.data.config);
        }')
    ]
]);

$this->registerJs('$("#modalAddEquipment").on("hidden.bs.modal",
function () {
     window.location.replace("tree-light");
})');
$this->registerJs('$("#modalAddConfig").on("hidden.bs.modal",
function () {
})');
?>

<div class="modal remote" id="modalAddEquipment">
    <div class="modal-dialog">
        <div class="modal-content loader-lg" id="modalContentEquipment">
        </div>
    </div>
</div>
<div class="modal remote" id="modalAddConfig">
    <div class="modal-dialog"  style="width: 90%">
        <div class="modal-content loader-lg" id="modalContentConfig">
        </div>
    </div>
</div>
