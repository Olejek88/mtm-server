<?php
/* @var Node $node
 * @var $parameters
 * @var $device
 * @var View $this
 */

use common\models\DeviceStatus;
use common\models\Node;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\Pjax;

?>
<div class="box box-success">
    <div class="box-header with-border">
        <h3 class="box-title">Статус шкафа</h3>
    </div>
    <div class="box-body">
        <table class="kv-grid-table table table-hover table-bordered table-condensed kv-table-wrap">
            <tbody>
            <tr data-key="1">
                <td class="table_class kv-align-middle" data-col-seq="0">Связь</td>
                <?php
                if ($node->deviceStatusUuid == DeviceStatus::WORK)
                    echo '<td class="kv-align-center kv-align-middle" style="background-color: green; color: white">Есть</td>';
                else
                    echo '<td class="kv-align-center kv-align-middle" style="background-color: red; color: white">Нет</td>';
                ?>

            </tr>
            <tr data-key="1">
                <td class="table_class kv-align-middle" data-col-seq="0">Состояние двери</td>
                <?php
                if (isset($parameters['control']['door']) && $parameters['control']['door'])
                    echo '<td class="kv-align-center kv-align-middle" style="background-color: red; color: white">Открыто</td>';
                else
                    echo '<td class="kv-align-center kv-align-middle" style="background-color: green; color: white">Закрыто</td>';
                ?>
            </tr>
            <tr data-key="1">
                <td class="table_class kv-align-middle" data-col-seq="0">Управление контактором</td>
                <td class="kv-align-center kv-align-middle" data-col-seq="1">
                    <?php
                    Pjax::begin(['id' => 'options']);
                    echo Html::beginForm(['dashboard', 'uuid' => $node['uuid'], 'type' => 0], 'post',
                        ['data-pjax' => '', 'class' => 'form-inline']);
                    $options = [];
                    $title = '';
                    $value = 0;
                    if (isset($parameters['control']['relay']) && $parameters['control']['relay']) {
                        $options = ['class' => 'btn btn-danger btn-sm'];
                        $title = 'Выключить';
                        $value = 0;
                    } else {
                        $options = ['class' => 'btn btn-success btn-sm'];
                        $title = 'Включить';
                        $value = 1;
                    }

                    $options = array_merge($options, ['id' => 'contact-button']);

                    $deviceStatuses = [DeviceStatus::WORK];
                    if (!in_array($node->deviceStatusUuid, $deviceStatuses)) {
                        $options = array_merge($options, ['disabled' => 'disabled']);
                    }
                    echo Html::hiddenInput('device', $device['uuid']);
                    echo Html::hiddenInput('type', 'node');
                    echo Html::hiddenInput('on', 0);
                    echo Html::submitButton(Yii::t('app', $title), $options);
                    echo Html::endForm();
                    Pjax::end();

                    $this->registerJs("
$('#contact-button').on('click', function() {
console.log('contact click...');
    $(this).prop('disabled', true).addClass('disabled');
    if($(this).hasClass('btn-success')) {
        $(this).removeClass('btn-success').addClass('btn-danger');
        $('#contact-button').html('Выключить');
        $('#contact-status').css('background-color', 'red').css('color', 'white').html('Включен');
        $('#contact-relay').css('background-color', 'green').css('color', 'white').html('Включено');
    } else {
        $(this).removeClass('btn-danger').addClass('btn-success');
        $('#contact-button').html('Включить');
        $('#contact-status').css('background-color', 'green').css('color', 'white').html('Выключен');
        $('#contact-relay').css('background-color', 'gray').css('color', 'white').html('Отключено');
    }
});
                    ", View::POS_END);
                    ?>
                </td>
            </tr>
            <tr data-key="2">
                <td class="table_class kv-align-middle" data-col-seq="0">Контактор сети</td>
                <?php
                if (isset($parameters['control']['contactor']) && $parameters['control']['contactor'])
                    echo '<td id="contact-status" class="kv-align-center kv-align-middle" style="background-color: red; color: white">Отключен</td>';
                else
                    echo '<td id="contact-status" class="kv-align-center kv-align-middle" style="background-color: green; color: white">Включен</td>';
                ?>
            </tr>
            <tr data-key="2">
                <td class="table_class kv-align-middle" data-col-seq="0">Реле управления контактором</td>
                <?php
                if (isset($parameters['control']['relay']) && $parameters['control']['relay'])
                    echo '<td id="contact-relay" class="kv-align-center kv-align-middle" style="background-color: green; color: white">Включено</td>';
                    else
                        echo '<td id="contact-relay" class="kv-align-center kv-align-middle" style="background-color: gray; color: white">Отключено</td>';
                ?>
            </tr>
            <tr data-key="3">
                <td class="table_class kv-align-middle" data-col-seq="0">Питание</td>
                <?php
                    if ($parameters['u1'] && $parameters['u1'] > 200 && $parameters['u1'] < 251)
                        echo '<td class="kv-align-center kv-align-middle" style="background-color: green; color: white">В норме</td>';
                    else
                        echo '<td class="kv-align-center kv-align-middle" style="background-color: red; color: white">Авария</td>';
                ?>
            </tr>
            <tr data-key="4">
                <td class="table_class kv-align-middle" data-col-seq="0">Напряжение,В</td>
                <td class="table_class kv-align-middle" data-col-seq="1"><?php echo $parameters['voltage'] ?></td>
            </tr>
            <tr data-key="5">
                <td class="table_class kv-align-middle" data-col-seq="0">Ток, А</td>
                <td class="table_class kv-align-middle" data-col-seq="1"><?php echo $parameters['current'] ?></td>
            </tr>
            <tr data-key="6">
                <td class="table_class kv-align-middle" data-col-seq="0">Мощность, кВт</td>
                <td class="table_class kv-align-middle" data-col-seq="1"><?php echo $parameters['power'] ?></td>
            </tr>
            <tr data-key="7">
                <td class="table_class kv-align-middle" data-col-seq="0">Энергия, кВт/ч</td>
                <td class="table_class kv-align-middle" data-col-seq="1"><?php echo $parameters['total'] ?></td>
            </tr>
            <tr data-key="1">
                <td class="table_class kv-align-middle" data-col-seq="0">Сбросить координатор</td>
                <td class="kv-align-center kv-align-middle" data-col-seq="1">
                    <?php
                    Pjax::begin(['id' => 'options']);
                    echo Html::beginForm(['dashboard', 'uuid' => $node['uuid'], 'type' => 0], 'post',
                        ['data-pjax' => '', 'class' => 'form-inline']);
                    $options = ['class' => 'btn btn-info btn-sm', 'id' => 'reset-button'];
                    $deviceStatuses = [DeviceStatus::WORK];
                    if (!in_array($node->deviceStatusUuid, $deviceStatuses)) {
                        $options = array_merge($options, ['disabled' => 'disabled']);
                    }

                    echo Html::submitButton(Yii::t('app', 'Сбросить'), $options);
                    echo Html::hiddenInput('device', $device['uuid']);
                    echo Html::hiddenInput('type','node');
                    echo Html::hiddenInput('reset',1);
                    echo Html::endForm();
                    Pjax::end();

                    $this->registerJs("
$('#reset-button').on('click', function() {
console.log('reset click...');
    $(this).prop('disabled', true).addClass('disabled');
});
                    ", View::POS_END);
                    ?>
                </td>
            </tr>
            </tbody>
        </table>
    </div>
</div>
