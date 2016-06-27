<?php

/**
  * @SWG\Get(
  *     path="/dyn/heat?filter={filter}&wkt={wkt}",
  *     summary="Obtiene datos para usar en un leafelt usando heatmap",
  *     description="Genera los datos necesarios para utilizar en un heatmap de leaflet se requiere mandar el codigo y si se quiere que contenga una palabra mandar filter",
  *     operationId="intersect",
  *     tags={"HeatMap data"},
  *     produces={"application/json"},
  *     @SWG\Parameter(
  * 		   	name="filter",
  * 			  in="path",
  * 			  required=false,
  * 			  type="string",
  * 			  description="Filtro para obtener los datos",
  *     ),
  *     @SWG\Parameter(
  * 		   	name="cod",
  * 			  in="path",
  * 			  required=false,
  * 			  type="string",
  * 			  description="Codigo a filtrar",
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
  *         @SWG\Schema(ref="#/heat")
  *     ),
  *   security={{
  *     "access_token":{}
  *   }}
  * )
  */
Route::get('/heat', ['middleware' => 'oauth', function() {

  $filter = Input::get('filter', '');
  $cod = Input::get('cod', '');
  $wkt = Input::get('wkt', '');
  $w_cod = "";
  if($cod != ""){
    $w_cod = "and (";
    $ar = explode(",",$cod);
    foreach ($ar as $c) {
      if($c != ""){
        $w_cod .= "D.codigo_act like '$cod%' or ";
      }
    }
    $w_cod = substr($w_cod, 0, -4);
    $w_cod .= ")";
  }

  $sql = "SELECT st_ymax(D.geom) lat, st_xmax(D.geom) lng, 1.0 val_data, D.cve_ent
    FROM inegi.denue_2016 D
    LEFT JOIN inegi.mgn_estados E
    ON E.cve_ent = D.cve_ent
    WHERE
      ST_Intersects(E.geom, ST_GeomFromText( '$wkt', 4326 ) )
      ".($filter==''?'':"and D.nom_estab ilike '%$filter%'")."
      ".$w_cod."
    ";
  $rs = DB::select($sql,[]);
  $data = array();
  $ents = array();
  foreach($rs as $r){
    $data[] = [$r->lat,$r->lng,$r->val_data];
    if(!in_array($r->cve_ent, $ents)){
      array_push($ents, $r->cve_ent);
    }
  }
/*json_encode
  return Response::json([
    "data" => $data,
    "ents" => $ents,
  ]);*/

  return response()->json([
    "data" => $data,
    "ents" => $ents,
  ], 200, [], JSON_NUMERIC_CHECK);

}]);
