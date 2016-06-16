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
/*
SELECT st_xmax(D.geom) lat, st_ymax(D.geom) lng, 1.0 val_data, D.cve_ent
FROM inegi.denue_2016 D
LEFT JOIN inegi.mgn_estados E
ON E.cve_ent = D.cve_ent
WHERE
ST_Intersects(E.geom, ST_GeomFromText( 'POLYGON((-99.15652513504028 19.416957838501798,-99.13919806480408 19.416957838501798,-99.12187099456787 19.416957838501798,-99.12187099456787 19.4253255113588,-99.12187099456787 19.4336931842158,-99.13919806480408 19.4336931842158,-99.15652513504028 19.4336931842158,-99.15652513504028 19.4253255113588,-99.15652513504028 19.416957838501798))', 4326 ) )
and D.nom_estab ilike '%oxxo%'
limit 10;
*/
  $sql = "SELECT st_xmax(D.geom) lat, st_ymax(D.geom) lng, 1.0 val_data, D.cve_ent
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
