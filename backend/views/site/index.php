<?php

/* @var $accountUser */
/* @var $equipment */
/* @var $js */
/* @var $js2 */
/* @var $coordinates */
/* @var $objectsList */
/* @var $objectsGroup */
/* @var $devicesList */
/* @var $devicesGroup */
/* @var  $nodesList */
/* @var  $nodesGroup */
/* @var  $polylineList */
/* @var  $camerasList */
/* @var  $camerasGroup */
/* @var  $define */

$this->title = Yii::t('app', 'Карта объектов и светильников');
$this->registerJs('$(window).on("resize", function () { $("#mapid").height($(window).height()-40); map.invalidateSize(); }).trigger("resize");');
?>
<script src="https://unpkg.com/leaflet@1.5.1/dist/leaflet.js"
        integrity="sha512-GffPMF3RvMeYyc1LWMHtK8EbPv0iNZ8/oTtHPx9/cc2ILxQ+u905qIwdpULaqDkyBKgOaB57QTMg7ztg8Jm2Og=="
        crossorigin=""></script>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.5.1/dist/leaflet.css"
      integrity="sha512-xwE/Az9zrjBIphAcBb3F6JVqxf46+CDLwfLMHloNu6KEQCAWi6HcDUbeOfBIptF7tcCzusKFjFw2yuvEpDL9wQ=="
      crossorigin=""/>

<div id="page-preloader">
    <div class="cssload-preloader cssload-loading">
        <span class="cssload-slice"></span>
        <span class="cssload-slice"></span>
        <span class="cssload-slice"></span>
        <span class="cssload-slice"></span>
        <span class="cssload-slice"></span>
        <span class="cssload-slice"></span>
    </div>
</div>

<style>
.leaflet-popup-content {
    margin: 10px 10px;
    line-height: 1.6;
}
</style>
<div class="box-relative">
    <div id="mapid" style="height: 800px; width: 100%"></div>

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

        <?php
        echo $define;

        echo $devicesList;
        echo $devicesGroup;
        echo $nodesList;
        echo $nodesGroup;
        if (!isset($_GET['view']) || (isset($_GET['view']) && $_GET['view'] == '2')) {
            echo $camerasList;
            echo $camerasGroup;
        }
        ?>

        var overlayMapsA = {};
        var overlayMapsB = {
            <?php if (!isset($_GET['view']) || (isset($_GET['view']) && $_GET['view'] == '1')) echo '"Светильники": devices,'; ?>
            <?php if (!isset($_GET['view']) || (isset($_GET['view']) && $_GET['view'] == '2')) echo '"Камеры": cameras,'; ?>
            <?php if (!isset($_GET['view']) || (isset($_GET['view']) && $_GET['view'] == '1')) echo '"Шкафы:": nodes'; ?>        };

        <?php
            if (!isset($_GET['view'])) {
                echo "var map = L.map('mapid', {zoomControl: false, layers: [devices, cameras, nodes]}).setView(".$coordinates.", 13);";
            } else {
                if ($_GET['view']=='4')
                    echo "var map = L.map('mapid', {zoomControl: false, layers: []}).setView(".$coordinates.", 13);";
                if ($_GET['view']=='2')
                    echo "var map = L.map('mapid', {zoomControl: false, layers: [cameras]}).setView(".$coordinates.", 13);";
                if ($_GET['view']=='1')
                    echo "var map = L.map('mapid', {zoomControl: false, layers: [devices, nodes]}).setView(".$coordinates.", 13);";
            }
        ?>
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

        <?php
        //echo $polylineList;
        ?>
    </script>

</div>