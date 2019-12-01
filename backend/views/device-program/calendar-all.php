<?php

use yii\web\JsExpression;

$this->title = 'Расписание работы шкафов';

/* @var $events */
?>

<script type="text/javascript">
    document.addEventListener("keydown", keyDownTextField, false);
    var old;
    var type = 0;

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
        old = event.start;
        console.log(event);
        if (event.title == "выключение") {
            type = 0;
        } else {
            type = 1;
        }
    }
EOF;
        $JSDropEvent = <<<EOF
    function( event, delta, revertFunc, jsEvent, ui, view ) {
        var st = $.post("/device/date-all", {
            type: "" + type + "",
            old_date: "" + old.format() + "",
            new_date: "" + event.start.format() + ""
        },	
        function() {
            //alert( "success" );
        })
        .done(function() {
            //alert( "second success" );
        })
        .fail(function(result) {
            alert(result.statusText);
        })
        .always(function() {
            //$('#calendar').fullCalendar('refetchEvents');
            //$('#calendar').fullCalendar('rerenderEvents');
            //window.location.replace("calendar");
        });  
    }
EOF;
        ?>
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

        $this->registerJs("$('#calendar').fullCalendar('option', 'height', $(window).height()-50)");
        ?>
    </div>
</div>
