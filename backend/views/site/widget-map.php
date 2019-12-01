<?php
/* @var $view */
/* @var $coordinates */
/* @var $lightGoodList */
/* @var $lightBadList */
/* @var $sensorCO2List */
/* @var $lightGoodGroup */
/* @var $lightBadGroup */
/* @var $sensorCO2Group
 * @var $nodesGroup
 * @var $nodesList
 * @var $camerasGroup
 * @var $camerasList
 */

// TODO переделать по уму
//$this->registerJsFile('/js/custom/modules/map/leaflet.js', ['depends' => ['yii\jui\JuiAsset']]);
//$this->registerCssFile('/css/custom/modules/map/leaflet.css');
use yii\helpers\Html; ?>
<script src="https://unpkg.com/leaflet@1.5.1/dist/leaflet.js"
        integrity="sha512-GffPMF3RvMeYyc1LWMHtK8EbPv0iNZ8/oTtHPx9/cc2ILxQ+u905qIwdpULaqDkyBKgOaB57QTMg7ztg8Jm2Og=="
        crossorigin=""></script>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.5.1/dist/leaflet.css"
      integrity="sha512-xwE/Az9zrjBIphAcBb3F6JVqxf46+CDLwfLMHloNu6KEQCAWi6HcDUbeOfBIptF7tcCzusKFjFw2yuvEpDL9wQ=="
      crossorigin=""/>

<div class="box box-success">
    <div class="box-header with-border box-success">
        <h3 class="box-title">Карта объектов и устройств</h3>
        <div class="box-tools pull-right">
            <div class="btn-group">
                <?php echo Html::a("<button type='button' class='btn btn-box-tool'>
                    <i class='fa fa-link'></i></button>", ['/site/index']); ?>
            </div>
            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
            </button>
            <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i>
            </button>
        </div>
    </div>
    <!-- /.box-header -->
    <div class="box-body no-padding">
        <div class="row">
            <div class="col-md-9 col-sm-8" style="width: 100%">
                <div class="pad" style="padding: 1px">
                    <div id="mapid" style="width: 100%; height: 460px"></div>
                </div>
            </div>
        </div>
    </div>
    <!-- /.box-body -->
</div>
<script>
    var houseIcon = L.icon({
        iconUrl: '/images/house_marker_green.png',
        iconSize: [32, 51],
        iconAnchor: [14, 51],
        popupAnchor: [-3, -76]
    });
    var nodeIcon = L.icon({
        iconUrl: '/images/router_marker_green.png',
        iconSize: [32, 51],
        iconAnchor: [14, 51],
        popupAnchor: [-3, -76]
    });
    var nodeIconBad = L.icon({
        iconUrl: '/images/router_marker_red.png',
        iconSize: [32, 51],
        iconAnchor: [14, 51],
        popupAnchor: [-3, -76]
    });
    var cameraIcon = L.icon({
        iconUrl: '/images/camera_marker_green.png',
        iconSize: [32, 51],
        iconAnchor: [14, 51],
        popupAnchor: [-3, -76]
    });
    var cameraIconBad = L.icon({
        iconUrl: '/images/camera_marker_red.png',
        iconSize: [32, 51],
        iconAnchor: [14, 51],
        popupAnchor: [-3, -76]
    });
    var cameraIconSelect = L.icon({
        iconUrl: '/images/camera_marker_blue.png',
        iconSize: [33, 51],
        iconAnchor: [14, 51],
        popupAnchor: [-3, -76]
    });
    var lightIcon = L.icon({
        iconUrl: '/images/light_marker_green.png',
        iconSize: [32, 51],
        iconAnchor: [14, 51],
        popupAnchor: [-3, -76]
    });
    var lightIconBad = L.icon({
        iconUrl: '/images/light_marker_red.png',
        iconSize: [32, 51],
        iconAnchor: [14, 51],
        popupAnchor: [-3, -76]
    });
    var lightIconSelect = L.icon({
        iconUrl: '/images/light_marker_blue.png',
        iconSize: [33, 51],
        iconAnchor: [14, 51],
        popupAnchor: [-3, -76]
    });
    var sensorCO2Icon = L.icon({
        iconUrl: '/images/co2_marker.png',
        iconSize: [33, 51],
        iconAnchor: [14, 51],
        popupAnchor: [-3, -76]
    });


    <?php
    $type = 1;
    $layers = 'lights_good, lights_bad, nodes';
    if (isset($_GET['type']) && $_GET['type'] == 2) {
        $layers = 'cameras, nodes';
        $type = 2;
    }
    if (isset($_GET['type']) && $_GET['type'] == 3) {
        $layers = 'nodes';
        $type = 3;
    }

    if ($type == 1) {
        echo $lightGoodList;
        echo $lightBadList;
        echo $lightGoodGroup;
        echo $lightBadGroup;
    }
    if ($type == 3) {
        echo $sensorCO2List;
        echo $sensorCO2Group;
    }
    echo $nodesList;
    echo $nodesGroup;
    if ($type == 2) {
        echo $camerasList;
        echo $camerasGroup;
    }

    ?>

    var overlayMapsA = {};
    var overlayMapsB = {
        <?php
        if ($type == 1) echo '"Светильники в работе": lights_good,';
        if ($type == 1) echo '"Светильники неисправные": lights_bad,';
        if ($type == 2) echo '"Камеры": cameras,';
        if ($type == 3) echo '"Датчики CO2": sensor1,';
        echo '"Шкафы:": nodes';
        ?>
    };

    var map = L.map('mapid', {
        zoomControl: false,
        layers: [<?= $layers ?>]
    }).setView(<?= $coordinates ?>, 13);
    L.tileLayer('https://api.tiles.mapbox.com/v4/{id}/{z}/{x}/{y}.png?access_token=pk.eyJ1IjoibWFwYm94IiwiYSI6ImNpejY4NXVycTA2emYycXBndHRqcmZ3N3gifQ.rJcFIG214AriISLbB6B5aw', {
        maxZoom: 18,
        id: 'mapbox.streets'
    }).addTo(map);

    L.control.layers(overlayMapsA, overlayMapsB, {
        position: 'bottomleft'
    }).addTo(map);

    L.control.zoom({
        position: 'bottomleft'
    }).addTo(map);

</script>
