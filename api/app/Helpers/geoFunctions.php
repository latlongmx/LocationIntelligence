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
