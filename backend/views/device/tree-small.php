<?php

use wbraganca\fancytree\FancytreeWidget;
use yii\web\JsExpression;

/* @var $deviceTypes */

$this->title = 'Дерево оборудования';

?>

    <table id="tree" style="width: 100%">
        <colgroup>
            <col width="*">
            <col width="120px">
        </colgroup>
        <thead style="background-color: #337ab7; color: white">
        <tr>
            <th align="center" colspan="2" style="background-color: #3c8dbc; color: whitesmoke">Оборудование системы
            </th>
        </tr>
        <tr style="background-color: #3c8dbc; color: whitesmoke">
            <th align="center">Оборудование</th>
            <th>Статус</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td></td>
            <td class="alt"></td>
        </tr>
        </tbody>
    </table>
<?php
$this->registerCssFile('/css/custom/modules/list/ui.fancytree.css');

echo FancytreeWidget::widget([
    'options' => [
        'id' => 'tree',
        'source' => $device,
        'checkbox' => true,
        'selectMode' => 3,
        'extensions' => ["glyph", "table"],
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
        'table' => [
            'indentation' => 20,
            "titleColumnIdx" => "1",
            "statusColumnIdx" => "2"
        ],
        'renderColumns' => new JsExpression('function(event, data) {
            var node = data.node;
            $tdList = $(node.tr).find(">td");
            $tdList.eq(1).html(node.data.status);
        }')
    ]
]);
?>
