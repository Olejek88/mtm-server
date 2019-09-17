<?php
/* @var $node
 * @var $parameters
 * @var $device
 */

use yii\helpers\Html;
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
                if (strtotime($node['lastDate']) + 50000 > time())
                    echo '<td class="kv-align-center kv-align-middle" style="background-color: green; color: white">Есть</td>';
                else
                    echo '<td class="kv-align-center kv-align-middle" style="background-color: red; color: white">Нет</td>';
                ?>

            </tr>
            <tr data-key="1">
                <td class="table_class kv-align-middle" data-col-seq="0">Состояние двери</td>
                <?php
                if (isset($parameters['control']['signal']) && $parameters['control']['signal'])
                    echo '<td class="kv-align-center kv-align-middle" style="background-color: green; color: white">Закрыто</td>';
                else
                    echo '<td class="kv-align-center kv-align-middle" style="background-color: red; color: white">Открыто</td>';
                ?>
            </tr>
            <tr data-key="1">
                <td class="table_class kv-align-middle" data-col-seq="0">Управление контактором</td>
                <td class="kv-align-center kv-align-middle" data-col-seq="1">
                    <?php
                    Pjax::begin(['id' => 'options']);
                    echo Html::beginForm(['dashboard', 'uuid' => $node['uuid'], 'type' => 0], 'post',
                        ['data-pjax' => '', 'class' => 'form-inline']);
                    if (isset($parameters['control']['contact']) && $parameters['control']['contact']) {
                        echo Html::submitButton(Yii::t('app', 'Выключить'),
                            ['class' => 'btn btn-danger btn-sm']);
                        echo Html::hiddenInput('device', $device['uuid']);
                        echo Html::hiddenInput('type','node');
                        echo Html::hiddenInput('on',0);

                    } else {
                        echo Html::submitButton(Yii::t('app', 'Включить'),
                            ['class' => 'btn btn-success btn-sm']);
                        echo Html::hiddenInput('device', $device['uuid']);
                        echo Html::hiddenInput('type','node');
                        echo Html::hiddenInput('on',1);
                    }
                    echo Html::endForm();
                    Pjax::end();
                    ?>
                </td>
            </tr>
            <tr data-key="2">
                <td class="table_class kv-align-middle" data-col-seq="0">Контактор сети</td>
                <?php
                    if (isset($parameters['control']['contact']) && $parameters['control']['contact'])
                        echo '<td class="kv-align-center kv-align-middle" style="background-color: green; color: white">Включены</td>';
                    else
                        echo '<td class="kv-align-center kv-align-middle" style="background-color: gray; color: white">Отключены</td>';
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
                    echo Html::submitButton(Yii::t('app', 'Сбросить'),
                           ['class' => 'btn btn-info btn-sm']);
                    echo Html::hiddenInput('device', $device['uuid']);
                    echo Html::hiddenInput('type','node');
                    echo Html::hiddenInput('reset',1);
                    echo Html::endForm();
                    Pjax::end();
                    ?>
                </td>
            </tr>
            </tbody>
        </table>
    </div>
</div>
