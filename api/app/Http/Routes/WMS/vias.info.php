<?php

Route::get('/vias', ['middleware' => 'oauth', function() {
  $userId = Authorizer::getResourceOwnerId();
  $WKT = Input::get('WKT', '' );
  $MTS = Input::get('MTS', 0 );
  $MTS = meters2dec($MTS);

  $W = " ST_GeomFromText( '$WKT', 4326 ) ";
  if($MTS > 0){
    $W = "ST_DWithin( geom, $W, $MTS)";
  }else{
    $W = "ST_Intersects(geom, $W)";
  }

  $sql = "SELECT * FROM (
      SELECT nomvial, tipovial FROM inegi.inter15_vias WHERE $W
        UNION
      SELECT nombre, tipo_vial FROM inegi.rnc_red_vial_2015 WHERE $W
      ) T
      GROUP BY nomvial, tipovial";
  $rs = DB::select($sql,[]);

  $sql = "SELECT agency_id, route_long_name
  FROM df_gtfs.vw_lineas
  WHERE $W
  GROUP BY agency_id, route_long_name";
  $rsT = DB::select($sql,[]);
  return Response::json(["info"=>$rs, "transp"=>$rsT]);
}]);
