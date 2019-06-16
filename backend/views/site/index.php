<?php

use yii\helpers\Html;
use yii\widgets\Pjax;

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
/* @var  $camerasList */
/* @var  $camerasGroup */

$this->title = Yii::t('app', 'Карта объектов и светильников');

?>

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

<div class="box-relative">
    <div id="mapid" style="width: 100%; height: 800px"></div>

    <script>
        var houseIcon = L.icon({
            iconUrl: '/images/house_marker_green.png',
            iconSize: [32, 51],
            iconAnchor: [22, 94],
            popupAnchor: [-3, -76]
        });
        var nodeIcon = L.icon({
            iconUrl: '/images/router_marker_green.png',
            iconSize: [32, 51],
            iconAnchor: [22, 94],
            popupAnchor: [-3, -76]
        });
        var cameraIcon = L.icon({
            iconUrl: '/images/camera_marker_green.png',
            iconSize: [32, 51],
            iconAnchor: [22, 94],
            popupAnchor: [-3, -76]
        });

        <?php
        echo $devicesList;
        echo $devicesGroup;
        echo $nodesList;
        echo $nodesGroup;
        echo $camerasList;
        echo $camerasGroup;
        ?>

        var overlayMapsA = {};
        var overlayMapsB = {
            "Светильники": devices,
            "Камеры": cameras,
            "Шкафы:": nodes
        };

        var map = L.map('mapid', {zoomControl: false, layers: [devices, cameras, nodes]}).setView(<?= $coordinates ?>, 13);
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
