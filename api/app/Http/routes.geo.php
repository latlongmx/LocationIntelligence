<?php


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


Route::group(['prefix'=>'geo','before' => 'oauth'], function()
{
    Route::get('/status', function(){
      return Response::json(["status"=>"ok"]);
    });

//102.56836 22.59373
//http://localhost:8000/geo/dw/ejes/-102.56836/22.59373/100
    Route::get('/dw/{layer}/{x}/{y}/{meters}', function($layer, $x, $y, $meters){
      return Response::json(["status"=>"ok:".$x]);
    });
});
