<?php
/* @var $device
 * @var $parameters
 */

use yii\helpers\Html;
use yii\widgets\Pjax;

?>
<div class="box box-success">
    <div class="box-header with-border">
        <h3 class="box-title">Управление</h3>
    </div>
    <div class="box-body">
        <div id="requests-table-container" class="panel table-responsive kv-grid-container" style="overflow: auto">
            <table class="kv-grid-table table table-hover table-bordered table-condensed kv-table-wrap">
                <thead>
                <tr class="kartik-sheet-style" style="height: 20px">
                    <th class="text-center kv-align-middle" data-col-seq="0" style="width: 60%;" colspan="2">Статус</th>
                    <th class="text-center kv-align-middle" data-col-seq="1" style="width: 40%;" colspan="2">Управление</th>
                </tr>
                </thead>
                <tbody>
                <tr data-key="1">
                    <td class="kv-align-center kv-align-middle" data-col-seq="0">Сигнализация</td>
                    <?php
                        if (isset($parameters['control']['signal']) && $parameters['control']['signal'])
                            echo '<td class="kv-align-center kv-align-middle" style="background-color: green">Закрыто</td>';
                        else
                            echo '<td class="kv-align-center kv-align-middle" style="background-color: red">Открыто</td>';
                        ?>
                    <td class="kv-align-center kv-align-middle" data-col-seq="1">
                            <?php
                            Pjax::begin(['id' => 'options']);
                            echo Html::beginForm(['dashboard', 'uuid' => $device['uuid'], 'type' => 0], 'post',
                                ['data-pjax' => '', 'class' => 'form-inline']);
                            if (isset($parameters['control']['contact']) && $parameters['control']['contact']) {
                                echo Html::submitButton(Yii::t('app', 'Выключить'),
                                    ['class' => 'btn btn-danger']);
                                echo Html::hiddenInput('uuid',$device['uuid']);
                                echo Html::hiddenInput('type','node');
                                echo Html::hiddenInput('on',0);

                            } else {
                                echo Html::submitButton(Yii::t('app', 'Включить'),
                                    ['class' => 'btn btn-success']);
                                echo Html::hiddenInput('uuid',$device['uuid']);
                                echo Html::hiddenInput('type','node');
                                echo Html::hiddenInput('on',1);
                            }
                            echo Html::endForm();
                            Pjax::end();
                         ?>
                    </td>
                    <?php
                        if (isset($parameters['control']['contact']) && $parameters['control']['contact'])
                            echo '<td class="kv-align-center kv-align-middle" style="background-color: green">Включены</td>';
                        else
                            echo '<td class="kv-align-center kv-align-middle" style="background-color: gray">Отключены</td>';
                        ?>
                </tr>
                <tr data-key="1">
                    <td class="kv-align-center kv-align-middle" data-col-seq="0">Напряжение, В</td>
                    <td class="kv-align-center kv-align-middle" data-col-seq="1"><?= $parameters['control']['u'] ?></td>
                    <td class="kv-align-center kv-align-middle" data-col-seq="2"></td>
                    <td class="kv-align-center kv-align-middle" data-col-seq="3"></td>
                </tr>
                <tr data-key="1">
                    <td class="kv-align-center kv-align-middle" data-col-seq="0">Мощность, кВт</td>
                    <td class="kv-align-center kv-align-middle" data-col-seq="1"><?= $parameters['control']['w'] ?></td>
                    <td class="kv-align-center kv-align-middle" data-col-seq="2"></td>
                    <td class="kv-align-center kv-align-middle" data-col-seq="3"></td>
                </tr>
                <tr data-key="1">
                    <td class="kv-align-center kv-align-middle" data-col-seq="0">Ток, А</td>
                    <td class="kv-align-center kv-align-middle" data-col-seq="1"><?= $parameters['control']['i'] ?></td>
                    <td class="kv-align-center kv-align-middle" data-col-seq="2"></td>
                    <td class="kv-align-center kv-align-middle" data-col-seq="3"></td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
