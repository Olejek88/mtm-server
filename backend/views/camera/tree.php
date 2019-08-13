<?php

use wbraganca\fancytree\FancytreeWidget;
use yii\web\JsExpression;

$this->title = 'Дерево оборудования';

/* @var $registers common\models\DeviceRegister */

?>
<script type="text/javascript">
    $("modalDefects").on("click", function (e) {
        //var detailId;

        e.preventDefault();

        // we can use jQuery's built in .data() method to retrieve the detail-id
        //detailId = $(this).data("data-id");
        $('#modalDefects').load('index.php?id=1', function () {
            // call some kind of overlay code that displays the popup?
            // this way the popup will show then the content from popup.php has
            // finished loading.
        });
    });
</script>

<table id="tree" style="width: 100%">
    <colgroup>
        <col width="*">
        <col width="120px">
        <col width="140px">
        <col width="*">
    </colgroup>
    <thead style="background-color: #337ab7; color: white">
    <tr>
        <th align="center" colspan="5" style="background-color: #3c8dbc; color: whitesmoke">Оборудование</th>
    </tr>
    <tr style="background-color: #3c8dbc; color: whitesmoke">
        <th align="center">Оборудование</th>
        <th>Статус</th>
        <th>Дата</th>
        <th>Регистр</th>
    </tr>
    </thead>
    <tbody>
    <tr>
        <td></td>
        <td class="alt"></td>
        <td class="center"></td>
        <td class="alt"></td>
    </tr>
    </tbody>
</table>
<?php echo FancytreeWidget::widget([
    'options' => [
        'id' => 'tree',
        'source' => $device,
        'extensions' => ['dnd', "glyph", "table"],
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
            "statusColumnIdx" => "2",
            "dateColumnIdx" => "3",
            "registerColumnIdx" => "4"
        ],
        'renderColumns' => new JsExpression('function(event, data) {
            var node = data.node;
            $tdList = $(node.tr).find(">td");
            $tdList.eq(1).html(node.data.status);
            $tdList.eq(2).html(node.data.date);
            $tdList.eq(3).html(node.data.register);
        }')
    ]
]);
?>
