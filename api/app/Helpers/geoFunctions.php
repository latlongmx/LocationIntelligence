<?php

/**
 * Meters to Decimal coordinates
 *
 * @param number $meters
 * @return number
 */
function meters2dec($mts){
  $m100 = 0.000900804;  // 100m
  return ($mts*$m100)/100;
}

/**
 * Create array with GeoJSON structure
 *
 * @param array $arr
 * @return array
 */
function array2GeoJSON($arr){
  $features = array();
  foreach($arr as $r){
    $properties = array();
    $geometry = null;
    foreach($r as $k=>$v){
      if($k=="geometry"){
        $geometry = json_decode($v);
      }else{
        $properties[$k]=$v;
      }
    }
    $features[] = array("type"=>"Feature","properties"=>$properties,"geometry"=>$geometry);
  }

  $res = array(
    "type"=> "FeatureCollection",
    "features"=> $features
  );
  return $res;
}


/**
 * Crea mapa base lo configura y lo regresa
 *
 * @return object
 */
function getMapObjConfig(){
  $map=new \mapObj(null);

  $map->setFontSet(realpath("lib\\server-side\\fonts.list"));
  #$err_file = storage_path("logs/ms_error.log");
  #$map->setConfigOption("MS_ERRORFILE", "stderr" );
  #$map->set('debug', 5);
  $map->setConfigOption('ows_enable_request','*');
  $map->setConfigOption('size','800 600');
  $map->setConfigOption('extent','-118.407653808594 14.532097816008417 -86.7086486816406 32.71865463170993');
  $map->setExtent(-118.407653808594, 14.532097816008417, -86.7086486816406, 32.71865463170993);

  $map->setMetaData('wms_enable_request','GetCapabilities GetMap GetFeatureInfo');
  $map->setMetaData('wms_getmap_formatlist','image/png,png,png8,png24');
  $map->setMetaData('wms_title','WMS Dynamic LatLong.mx');
  $map->setMetaData('wms_abstract','WMS Dynamic LatLong.mx');
  $map->setMetaData('wms_onlineresource','http://52.8.211.37/test/wms.php?');
  $map->setMetaData('wms_extent', "-118.407653808594 14.532097816008417 -86.7086486816406 32.71865463170993");
  $map->setMetaData('wms_srs', "EPSG:4326");
  $map->setProjection("init=epsg:4326");

  return $map;
}

/**
 * Configura un layer agregando sus metadatos y funcionalidad con postgis
 *
 * @param mapObj $map
 * @param string $layerName
 * @return object
 */
function getLayerObjConfig(&$map, $layerName, $COL){
  $db_hst = env('DB_HOST','');
  $db_dbn = env('DB_DATABASE','');
  $db_usr = env('DB_USERNAME','');
  $db_pwd = env('DB_PASSWORD','');
  $layer = new \LayerObj($map);
  $layer->set('connection',"user=$db_usr dbname=$db_dbn host=$db_hst password=$db_pwd");
  $layer->set('name', $layerName);
  #$layer->set('status', MS_DEFAULT );
  #$layer->set("status", MS_ON);
  #$layer->set("classitem", $COL);
  $layer->setConnectionType(MS_POSTGIS);
  $layer->setProcessing('CLOSE_CONNECTION=DEFER');
  $layer->setProjection("init=epsg:4326");
  $layer->setMetaData('wms_extent', "-118.407653808594 14.532097816008417 -86.7086486816406 32.71865463170993");
  $layer->setMetaData('wms_srs', "EPSG:4326");
  $layer->setMetaData('wms_title', $layerName);
  return $layer;
}

/**
 * Obtiene un color HEX asignando de un color a otro y la posicion a extraer
 *
 * @param string $fromColor
 * @param string $toColor
 * @param number $pos  0.0 - 1.0
 * @return string hexColor
 */
function getColorFromColToCol($from, $to, $pos = 0.5){
    // 1. Grab RGB from each colour
    list($fr, $fg, $fb) = sscanf($from, '%2x%2x%2x');
    list($tr, $tg, $tb) = sscanf($to, '%2x%2x%2x');

    // 2. Calculate colour based on frational position
    $r = (int) ($fr - (($fr - $tr) * $pos));
    $g = (int) ($fg - (($fg - $tg) * $pos));
    $b = (int) ($fb - (($fb - $tb) * $pos));

    // 3. Format to 6-char HEX colour string
    return sprintf('%02x%02x%02x', $r, $g, $b);
}
