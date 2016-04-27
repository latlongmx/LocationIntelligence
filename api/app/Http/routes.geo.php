<?php

function meters2dec($mts){
  $m100 = 0.000900804;  // 100m
  return ($mts*$m100)/100;
}

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


Route::group(['prefix'=>'geo','before' => 'oauth', 'middleware' => 'cors'], function()
{
    Route::get('/status', function(){
      return Response::json(["status"=>"ok"]);
    });

//102.56836 22.59373
//http://localhost:8000/geo/dw/LAYER/21.85996530350067/-102.2827363014221/100
    Route::get('/dw/{layer}/{lat}/{lng}/{meters}', function($layer, $lat, $lng, $meters){
        $mts = meters2dec($meters);
        //ST_Buffer(ST_GeomFromText('POINT(100 90)'), 50) As circle)
        //ST_Split(circle, line)
        $split = " ( SELECT (st_buffer(ST_SetSRID(ST_Point($lng, $lat),4326) , $mts))::geometry geom ) ";
        $sql = "
              WITH split AS $split
              SELECT 'rnc' tip_lay, id_red, tipo_vial, nombre, codigo, cond_pav, recubri, carriles, estatus, condicion, nivel, peaje, administra, jurisdi,circula, escala_vis, velocidad, union_ini, union_fin, longitud, ancho,fecha_act, calirepr,
                      ST_AsGeoJSON( ( (ST_Dump(ST_Intersection(S.geom, A.geom))).geom )::geometry)::json As geometry
              FROM inegi.rnc_red_vial_2015 As A, split S
              WHERE ST_DWithin(A.geom, ST_SetSRID(ST_Point($lng, $lat),4326), $mts)
              ";
        $rs = DB::select($sql,[]);
        $geo = array2GeoJSON($rs);
        return Response::json([
          "info"=>"",
          "geojson"=>$geo,
          "sql" => "$sql"
        ]);
    });
});
