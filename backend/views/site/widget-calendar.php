<?php

/* @var $events
 */

use yii\helpers\Html; ?>

<script type="text/javascript">
</script>

<div class="box box-primary">
    <div class="box-header with-border">
        <h3 class="box-title">Расписание работы</h3>
        <div class="box-tools pull-right">
            <div class="btn-group">
                <?php echo Html::a("<button type='button' class='btn btn-box-tool'>
                    <i class='fa fa-link'></i></button>", ['/device-program/calendar']); ?>
            </div>
            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
            </button>
            <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i>
            </button>
        </div>
    </div>

    <div class="box-body">
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
                'editable' => true,
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
        $this->registerJs("$('#calendar').fullCalendar('option', 'height', 700)");
        ?>

    </div>
</div>
<style>
    .grid-view td {
        white-space: pre-line;
    }
</style>
