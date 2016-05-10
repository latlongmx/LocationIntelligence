<?php

/**
  * @SWG\Get(
  *     path="/dyn/intersect?s={schema}&t={table}&c={columns}&w={column_filter}:{value_filter}&wkt={wkt}&mts={meters}",
  *     summary="Obtener lo que intersecte",
  *     description="Servicio de analisis que regresa las geometrias con la informacion solicitada segun el WKT enviado como parametro",
  *     operationId="intersect",
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
  *         default="inter15_vias",
  *     ),
  *     @SWG\Parameter(
  * 		   	name="columns",
  * 			  in="path",
  * 			  required=false,
  * 			  type="string",
  * 			  description="Columna a agrupar",
  *         default="tipovial",
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
  * 		   	name="wkt",
  * 			  in="path",
  * 			  required=true,
  * 			  type="string",
  * 			  description="WKT geometry format",
  *         default="POINT(-102.2827363014221 21.85996530350067)",
  *     ),
  *     @SWG\Parameter(
  * 		   	name="meters",
  * 			  in="path",
  * 			  required=false,
  * 			  type="integer",
  * 			  description="Metros solo si wkt es POINT",
  *         default="100",
  *     ),
  *     @SWG\Response(
  *         response=200,
  *         description="successful operation",
  *         @SWG\Schema(ref="#/intersect")
  *     ),
  * )
  */
Route::get('/intersect', function(){
  $response = array();

  $s = Input::get('s', 'public');
  $t = Input::get('t', '');
  $c = Input::get('c', 'gid');
  $w = Input::get('w', '');
  $wkt = Input::get('wkt', '');
  $mts = Input::get('mts', 0);


  $mts = meters2dec($mts);

  $TBL = $s.".".$t;
  $INFO = array();
  $GEOM = "";
  $SPLIT = "";
  $GEOM_CUT_LINE = "ST_AsGeoJSON( ( (ST_Dump(ST_Intersection(S.geom, A.geom))).geom )::geometry)::json As geometry";
  $GEOM_INTERSECT = "ST_AsGeoJSON(A.geom)::json As geometry";
  $WHERE = "";
  $GEOM_WKT_SPLIT = " ST_GeomFromText( '$wkt', 4326 ) ";
  $GEOM_WKT_WHERE = " ST_intersects( A.geom, ST_GeomFromText('$wkt', 4326 ) )";
  if (strpos( strtolower($wkt), 'point') !== false) {
    $GEOM_WKT_SPLIT = "st_buffer( ST_GeomFromText( '$wkt', 4326 ) , $mts)";
    $GEOM_WKT_WHERE = "ST_DWithin( A.geom, ST_GeomFromText( '$wkt', 4326 ), $mts)";
  }

  switch ($TBL) {
    case 'inegi.rnc_red_vial_2015':
        $SPLIT = " WITH split AS ( SELECT ($GEOM_WKT_SPLIT)::geometry geom ) ";
        $TBL = "inegi.rnc_red_vial_2015 As A, split S";
        $GEOM = $GEOM_CUT_LINE;
      break;
    case 'inegi.inter15_vias':
        $SPLIT = " WITH split AS ( SELECT ($GEOM_WKT_SPLIT)::geometry geom ) ";
        $TBL = "inegi.inter15_vias As A, split S";
        $GEOM = $GEOM_CUT_LINE;
      break;
    case 'inegi.denue_2016':
        $c = "nom_estab, raz_social, codigo_act, nombre_act, per_ocu, tipo_vial, nom_vial, tipo_v_e_1, nom_v_e_1,
              tipo_v_e_2, nom_v_e_2, tipo_v_e_3, nom_v_e_3, numero_ext, letra_ext, edificio, edificio_e, numero_int,
              letra_int, tipo_asent, nomb_asent, tipocencom, nom_cencom, num_local, cod_postal, cve_ent, entidad,
              cve_mun, municipio, cve_loc, localidad, ageb, manzana, telefono, correoelec, www, tipounieco, fecha_alta, ";
      break;
    default:
        $TBL = $TBL." As A";
        $GEOM = $GEOM_INTERSECT;
      break;
  }

  if($w!="" && strpos($w, ':') !== false && strpos($w, '{') === false){
    $ww = explode(":", $w);
    $WHERE = " and ".$ww[0]."='".$ww[1]."'";
  }


  $sql = " $SPLIT
        SELECT $c , $GEOM
        FROM $TBL
        WHERE $GEOM_WKT_WHERE
        $WHERE";
  $rs = DB::select($sql,[]);
  $geo = array2GeoJSON($rs);

  if($c !== 'gid' && $TBL!='inegi.denue_2016'){
    foreach($rs as $ro){
      $r = (array) $ro;
      if(isset($INFO[$r[$c]])){
        $INFO[ $r[$c] ] = $INFO[ $r[$c] ]+1;
      }else{
        $INFO[ $r[$c] ] = 1;
      }
    }
  }

  return Response::json([
    "info" => $INFO,
    "geojson" => $geo,
    "sql" => $sql
  ]);

});
