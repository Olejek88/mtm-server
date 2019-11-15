<?php

use yii\web\JsExpression;

$this->title = 'Расписание работы светильников';

/* @var $events */
/* @var $groupTitle */

if (isset ($_GET["group"]))
    $group = $_GET["group"];
else
    $group = "";
?>

<script type="text/javascript">
    document.addEventListener("keydown", keyDownTextField, false);
    var start;
    var type = 0;
    var group = '<?= $group ?>';

    //$('#calendar').setOption('contentHeight', 450);
    function keyDownTextField(e) {
        window.keyCode = e.keyCode;
    }
</script>

<div class="modal remote fade" id="modalAddProgram">
    <div class="modal-dialog" style="width: 500px">
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
	        var st = $.post("/device/date",{ type: ""+type+"", group: ""+group+"", event_start: ""+start.format()+"", event_end: ""+event.start.format()+"" },	
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
        $JSEventClick = <<<EOF
function(calEvent, jsEvent, view) {
        console.log(calEvent);
        if (calEvent.id % 2 == 1) {
            $.post("/device/set-calendar",
                {
                    date: "" + calEvent.start.format() + "",
                    group: "" + group + ""
                },
                function() {console.log("мутная функция");})
            .done(function(data) {
                  $('#modalAddProgram').modal('show');
                  $('#modalContent').html(data);
    	    });
    	}
}
EOF;
        ?>
        <div width="100%" align="center">
            <h4><?php echo $groupTitle ?></h4>
        </div>
        <?= yii2fullcalendar\yii2fullcalendar::widget(array(
            'id' => 'calendar',
            'options' => [
                'lang' => 'ru',
                'plugins' => ['bootstrap'],
                'themeSystem' => 'bootstrap'
            ],
            'clientOptions' => [
                'selectable' => true,
                'selectHelper' => true,
                'droppable' => true,
                'editable' => false,
                'eventClick' => new JsExpression($JSEventClick),
//                'eventDrop' => new JsExpression($JSDropEvent),
//                'eventDragStart' => new JsExpression($JSDragStartEvent),
                'defaultDate' => date('Y-m-d'),
                'defaultView' => 'month',
                'columnFormat' => 'ddd',
                'header' => [
                    'left' => 'prev,next today month',
                    'right' => 'month',
                    'center' => 'title',
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
<style>
    .fc-time {
        display: none;
    }
    .fc-title {
        cursor: pointer;
    }
</style>
