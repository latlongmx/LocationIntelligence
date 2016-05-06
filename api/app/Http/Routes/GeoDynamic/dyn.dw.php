<?php

/**
  * @SWG\Get(
  *     path="/dw?s={schema}&t={table}&c={columns}&w={column_filter}:{value_filter}&lat={latitude}&lng={longitud}&mts={meters}",
  *     summary="Distance Within service analisys",
  *     description="Servicio de analisis que regresa las geometrias con la informacion solicitada",
  *     operationId="dw",
  *     tags={"Distance Within"},
  *     produces={"application/json"},
  *     @SWG\Parameter(
  * 		   	name="schema",
  * 			  in="path",
  * 			  required=true,
  * 			  type="string",
  * 			  description="Schema",
  *         default="inegi",
  *     ),
  *     @SWG\Parameter(
  * 		   	name="table",
  * 			  in="path",
  * 			  required=true,
  * 			  type="string",
  * 			  description="table",
  *         default="rnc_red_vial_2015",
  *     ),
  *     @SWG\Parameter(
  * 		   	name="columns",
  * 			  in="path",
  * 			  required=false,
  * 			  type="string",
  * 			  description="Columna a agrupar",
  *         default="tipo_vial",
  *     ),
  *     @SWG\Parameter(
  * 		   	name="column_filter",
  * 			  in="path",
  * 			  required=false,
  * 			  type="string",
  * 			  description="Columna para usar en un filtro (where)",
  *         default="",
  *     ),
  *     @SWG\Parameter(
  * 		   	name="value_filter",
  * 			  in="path",
  * 			  required=false,
  * 			  type="string",
  * 			  description="valor que tendra la columna a filtrar",
  *         default="",
  *     ),
  *     @SWG\Parameter(
  * 		   	name="latitude",
  * 			  in="path",
  * 			  required=true,
  * 			  type="number",
  * 			  description="latitude",
  *         default="21.85996530350067",
  *     ),
  *     @SWG\Parameter(
  * 		   	name="longitud",
  * 			  in="path",
  * 			  required=true,
  * 			  type="number",
  * 			  description="longitud",
  *         default="-102.2827363014221",
  *     ),
  *     @SWG\Parameter(
  * 		   	name="meters",
  * 			  in="path",
  * 			  required=true,
  * 			  type="integer",
  * 			  description="meters",
  *         default="100",
  *     ),
  *     @SWG\Response(
  *         response=200,
  *         description="successful operation",
  *         @SWG\Schema(ref="#/dw")
  *     ),
  * )
  */
Route::get('/dw', function(){
  $response = array();

  $s = Input::get('s', 'public');
  $t = Input::get('t', '');
  $c = Input::get('c', 'gid');
  $w = Input::get('w', '');
  $lat = Input::get('lat', '');
  $lng = Input::get('lng', '');
  $mts = Input::get('mts', 0);



  $mts = meters2dec($mts);

  $TBL = $s.".".$t;
  $INFO = array();
  $GEOM = "";
  $SPLIT = "";
  $GEOM_CUT_LINE = "ST_AsGeoJSON( ( (ST_Dump(ST_Intersection(S.geom, A.geom))).geom )::geometry)::json As geometry";
  $GEOM_INTERSECT = "ST_AsGeoJSON(A.geom)::json As geometry";
  $WHERE = "";

  switch ($TBL) {
    case 'inegi.rnc_red_vial_2015':
        $SPLIT = " WITH split AS ( SELECT (st_buffer(ST_SetSRID(ST_Point($lng, $lat),4326) , $mts))::geometry geom ) ";
        $TBL = "inegi.rnc_red_vial_2015 As A, split S";
        $GEOM = $GEOM_CUT_LINE;
      break;
    case 'inegi.inter15_vias':
        $SPLIT = " WITH split AS ( SELECT (st_buffer(ST_SetSRID(ST_Point($lng, $lat),4326) , $mts))::geometry geom ) ";
        $TBL = "inegi.inter15_vias As A, split S";
        $GEOM = $GEOM_CUT_LINE;
    default:
        $TBL = $TBL." As A";
        $GEOM = $GEOM_INTERSECT;
      break;
  }


  $sql = " $SPLIT
        SELECT $c , $GEOM
        FROM $TBL
        WHERE ST_DWithin(A.geom, ST_SetSRID(ST_Point($lng, $lat),4326), $mts)
          $WHERE";
  $rs = DB::select($sql,[]);
  $geo = array2GeoJSON($rs);

  if($c !== 'gid'){
    foreach($rs as $r){
      if(isset($INFO[$rs[$c]])){
        $INFO[ $rs[$c] ] = $INFO[ $rs[$c] ]+1;
      }else{
        $INFO[ $rs[$c] ] = 0;
      }
    }
  }

  return Response::json([
    "info" => $INFO,
    "geojson" => $geo,
    "sql" => "$sql"
  ]);



  /*return Response::json([
    "s"=>$s,
    "t"=>$t,
    "c"=>$c,
    "w"=>$w,
    "lat"=>$lat,
    "lng"=>$lng,
    "mts"=>$mts
  ]);*/
});
