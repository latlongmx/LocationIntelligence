<?php

/**
  * @SWG\Get(
  *     path="/dyn/heat?filter={filter}&wkt={wkt}",
  *     summary="Obtiene datos para usar en un leafelt usando heatmap",
  *     description="Genera los datos necesarios para utilizar en un heatmap de leaflet",
  *     operationId="intersect",
  *     tags={"Get geometry as GeoJSON by WKT"},
  *     produces={"application/json"},
  *     @SWG\Parameter(
  * 		   	name="filter",
  * 			  in="path",
  * 			  required=true,
  * 			  type="string",
  * 			  description="Filtro para obtener los datos",
  *     ),
  *     @SWG\Parameter(
  * 		   	name="wkt",
  * 			  in="path",
  * 			  required=true,
  * 			  type="string",
  * 			  description="WKT para filtro geometrico",
  *     ),
  *     @SWG\Response(
  *         response=200,
  *         description="successful operation",
  *         @SWG\Schema(ref="#/intersect")
  *     ),
  *   security={{
  *     "access_token":{}
  *   }}
  * )
  */
Route::get('/heat', ['middleware' => 'oauth', function() {

  $filter = Input::get('filter', '');
  $wkt = Input::get('wkt', '');

  $sql = "SELECT st_xmax(geom) lat, st_ymax(geom) lng, 1.0 value, D.cve_ent
    FROM inegi.denue_2016 D
    LEFT JOIN inegi.mgn_estados E
    ON E.cve_ent = D.cve_ent
    WHERE
      ST_Intersects(E.geom, ST_GeomFromText( '?', 4326 ) )
      and D.nom_estab ilike '%?%'
    ";
  $rs = DB::select($sql,[$filter, $wkt]);

  return Response::json([
    "data" => $rs,
    "sql"=>$sql
  ]);

}]);
