<?php

use yii\web\JsExpression;

$this->title = 'Расписание работы шкафов';

/* @var $events */
/* @var $nodeTitle */

if (isset ($_GET["node"]))
    $node = $_GET["node"];
else
    $node = "";
?>

<script type="text/javascript">
    document.addEventListener("keydown", keyDownTextField, false);
    var start;
    var type = 0;
    var node = '<?= $node ?>';

    function keyDownTextField(e) {
        window.keyCode = e.keyCode;
    }
</script>

<div class="modal remote fade" id="modalTaskInfo">
    <div class="modal-dialog" style="width: 800px">
        <div class="modal-content loader-lg" id="modalContent">
        </div>
    </div>
</div>

<div class="site-index">
    <div class="body-content">
        <?php
        $JSDragStartEvent = <<<EOF
    function( event, jsEvent, ui, view ) {
        start = event.start;
        console.log(event);
        if (event.title == "выключение")
            type = 0;
        else type = 1;
    }
EOF;
        $JSDropEvent = <<<EOF
    function( event, delta, revertFunc, jsEvent, ui, view ) {
	        var st = $.post("/device/date-node",{ type: ""+type+"", node: ""+node+"", event_start: ""+start.format()+"", event_end: ""+event.start.format()+"" },	
	        function() {
	            //alert( "success" );
	        })
	        .done(function() {
	            //alert( "second success" );
	        })
	        .fail(function() {
	            //alert( "error" );
	        })
	        .always(function() {
	            //$('#calendar').fullCalendar('refetchEvents');
	            //$('#calendar').fullCalendar('rerenderEvents');
	            //window.location.replace("calendar");
	        });  
    }
EOF;
        ?>
        <div width="100%" align="center">
            <h4><?php echo $nodeTitle ?></h4>
        </div>
        <?= yii2fullcalendar\yii2fullcalendar::widget(array(
            'id' => 'calendar',
            'options' => [
                'lang' => 'ru',
                'plugins' => ['bootstrap'],
                'themeSystem' => 'bootstrap',
                //'height' => 550
            ],
            'clientOptions' => [
                'selectable' => true,
                //'height' => 550,
                'selectHelper' => true,
                'droppable' => true,
                'editable' => true,
                'eventDrop' => new JsExpression($JSDropEvent),
                'eventDragStart' => new JsExpression($JSDragStartEvent),
                'defaultDate' => date('Y-m-d'),
                'defaultView' => 'month',
                'columnFormat' => 'ddd',
                'header' => [
                    'left' => 'prev,next today month',
                    'center' => 'title'
                ],
            ],
            'ajaxEvents' => $events,
        ));

        $this->registerJs('$("#modalTask").on("hidden.bs.modal",
            function () {
                window.location.reload();
        })');
        $this->registerJs("$('#calendar').fullCalendar('option', 'height', $(window).height()-50)");
        ?>
    </div>
</div>
