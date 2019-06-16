<?php

/* @var $counts
 */
?>

<div class="box box-success">
    <div class="box-header with-border">
        <h3 class="box-title">Статистика</h3>
        <div class="box-tools pull-right">
            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
            </button>
            <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i>
            </button>
        </div>
    </div>

    <div class="box box-primary">
        <div class="col-md-6 col-sm-6 col-xs-12">
            <div class="info-box">
                <a href="/city"><span class="info-box-icon bg-aqua"><i class="fa fa-calendar"></i></span></a>

                <div class="info-box-content">
                    <span>Городов <?= $counts['city']; ?> / Улиц <?= $counts['street']; ?></span><br/>
                    <span>Объектов <?= $counts['objects']; ?> /  Шкафов <?= $counts['node']; ?></span><br/>
                    <span>Каналов <?= $counts['channel']; ?></span><br/>
                </div>
                <!-- /.info-box-content -->
            </div>
            <!-- /.info-box -->
        </div>
        <!-- /.col -->
        <div class="col-md-6 col-sm-6 col-xs-12">
            <div class="info-box">
                <a href="/device"><span class="info-box-icon bg-red"><i class="fa fa-plug"></i></span></a>

                <div class="info-box-content">
                    <span><a href="/device/type">Электросчетчиков <?= $counts['elektro']; ?></a></span><br/>
                    <span><a href="/device/type">Светильников <?= $counts['light']; ?></a></span><br/>
                </div>
                <!-- /.info-box-content -->
            </div>
            <!-- /.info-box -->
        </div>
    </div>
</div>
