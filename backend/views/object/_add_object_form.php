<?php
/* @var $object
 * @var $source
 * @var $houseUuid
 */

use common\components\MainFunctions;
use common\models\ObjectType;
use common\models\User;
use dosamigos\leaflet\layers\Marker;
use dosamigos\leaflet\layers\TileLayer;
use dosamigos\leaflet\LeafLet;
use dosamigos\leaflet\plugins\geocoder\GeoCoder;
use dosamigos\leaflet\plugins\geocoder\ServiceNominatim;
use dosamigos\leaflet\types\LatLng;
use dosamigos\leaflet\widgets\Map;
use kartik\select2\Select2;
use yii\bootstrap\ActiveForm;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

?>

<?php $form = ActiveForm::begin([
    'enableAjaxValidation' => false,
    'action' => '../object/save',
    'options' => [
        'id' => 'form',
        'enctype' => 'multipart/form-data'
    ]]);
?>
<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal">&times;</button>
    <h4 class="modal-title">Объект</h4>
</div>
<div class="modal-body">
    <?php
    $latDefault = 55.160374;
    $lngDefault = 61.402738;

    if ($object['uuid']) {
        echo Html::hiddenInput("objectUuid", $object['uuid']);
        echo $form->field($object, 'uuid')
            ->hiddenInput(['value' => $object['uuid']])
            ->label(false);
    } else {
        echo $form->field($object, 'uuid')
            ->hiddenInput(['value' => MainFunctions::GUID()])
            ->label(false);
        echo $form->field($object, 'houseUuid')->hiddenInput(['value' => $houseUuid])->label(false);
    }
    echo $form->field($object, 'oid')->hiddenInput(['value' => User::getOid(Yii::$app->user->identity)])->label(false);
    echo $form->field($object, 'title')->textInput(['maxlength' => true]);

    echo Html::hiddenInput("type", "object");
    echo Html::hiddenInput("source", $source);

    $types = ObjectType::find()->all();
    $items = ArrayHelper::map($types, 'uuid', 'title');
    echo $form->field($object, 'objectTypeUuid')->widget(Select2::class,
        [
            'data' => $items,
            'language' => 'ru',
            'options' => [
                'placeholder' => 'Выберите тип..'
            ],
            'pluginOptions' => [
                'allowClear' => true
            ],
        ]);

    echo $form->field($object, 'latitude')->textInput(['maxlength' => true, 'value' => $latDefault]);
    echo $form->field($object, 'longitude')->textInput(['maxlength' => true, 'value' => $lngDefault]);

    // lets use nominating service
    $nominatim = new ServiceNominatim();

    // create geocoder plugin and attach the service
    $geoCoderPlugin = new GeoCoder([
        'service' => $nominatim,
        'clientOptions' => [
            // we could leave it to allocate a marker automatically
            // but I want to have some fun
            'showMarker' => false,
        ]
    ]);

    // first lets setup the center of our map
    $center = new LatLng(['lat' => $latDefault, 'lng' => $lngDefault]);

    // now lets create a marker that we are going to place on our map
    $marker = new Marker([
        'latLng' => $center,
//        'popupContent' => 'Hi!',
        'name' => 'geoMarker',
        'clientOptions' => ['draggable' => true],
        'clientEvents' => [
            'dragend' => 'function(e){
//                console.log(e.target._latlng.lat, e.target._latlng.lng);
                $("#objects-latitude").val(e.target._latlng.lat);
                $("#objects-longitude").val(e.target._latlng.lng);
            }'
        ],
    ]);
    // The Tile Layer (very important)
    $tileLayer = new TileLayer([
//        'urlTemplate' => 'http://a.tile.openstreetmap.org/{z}/{x}/{y}.png',
        'urlTemplate' => 'http://{s}.tiles.mapbox.com/v4/mapquest.streets-mb/{z}/{x}/{y}.{ext}?access_token=pk.eyJ1IjoibWFwcXVlc3QiLCJhIjoiY2Q2N2RlMmNhY2NiZTRkMzlmZjJmZDk0NWU0ZGJlNTMifQ.mPRiEubbajc6a5y9ISgydg',
        'clientOptions' => [
            'attribution' => 'Tiles &copy; <a href="http://www.osm.org/copyright" target="_blank">OpenStreetMap contributors</a> />',
            'subdomains' => '1234',
//            'id' => 'mapbox.streets',
            'type' => 'osm',
            's' => 'a',
            'ext' => 'png',

        ]
    ]);

    // now our component and we are going to configure it
    $leafLet = new LeafLet([
        'name' => 'geoMap',
        'center' => $center,
        'tileLayer' => $tileLayer,
        'clientEvents' => [
            'geocoder_showresult' => 'function(e){
                // set markers position
                geoMarker.setLatLng(e.Result.center);
                $("#objects-latitude").val(e.Result.center.lat);
                $("#objects-longitude").val(e.Result.center.lng);
            }'
        ],
    ]);
    // Different layers can be added to our map using the `addLayer` function.
    $leafLet->addLayer($marker);      // add the marker
    //    $leafLet->addLayer($tileLayer);  // add the tile layer

    // install the plugin
    $leafLet->installPlugin($geoCoderPlugin);

    // finally render the widget
    try {
        echo Map::widget(['leafLet' => $leafLet]);
    } catch (Exception $exception) {
        echo '<div id="map"/>';
    }
    ?>
</div>
<div class="modal-footer">
    <?php echo Html::submitButton(Yii::t('app', 'Отправить'), ['class' => 'btn btn-success']) ?>
    <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
</div>
<script>
    $(document).on("beforeSubmit", "#form", function (e) {
        e.preventDefault();
    }).on('submit', function (e) {
        e.preventDefault();
        $.ajax({
            type: "post",
            data: new FormData(this),
            processData: false,
            contentType: false
            url: "../object/save",
            success: function () {
                $('#modalAdd').modal('hide');
            },
            error: function () {
            }
        })
    });
</script>
<?php ActiveForm::end(); ?>
