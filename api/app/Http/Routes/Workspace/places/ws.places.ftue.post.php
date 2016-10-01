<?php

/**
  * @SWG\Post(
  *     path="/ws/places/",
  *     summary="Subir archivo para agregar a mis ubicaciones",
  *     description="Sube un archivo csv a la seccion de mis ubicaciones del usuario registrado a traves del access_token",
  *     operationId="catalog",
  *     tags={"Workspace"},
  *     produces={"application/json"},
  *     @SWG\Parameter(
  * 		   	name="nm",
  * 			  in="path",
  * 			  required=true,
  * 			  type="string",
  * 			  description="Nombre de mis hubicaciones",
  *     ),
  *     @SWG\Parameter(
  * 		   	name="pin",
  * 			  in="formData",
  * 			  required=false,
  * 			  type="file",
  * 			  description="Url del PIN a utilizar",
  *     ),
  *     @SWG\Parameter(
  * 		   	name="file",
  * 			  in="formData",
  * 			  required=true,
  * 			  type="file",
  * 			  description="Archivo csv",
  *     ),
  *     @SWG\Parameter(
  * 		   	name="lat",
  * 			  in="path",
  * 			  required=true,
  * 			  type="string",
  * 			  description="Columna con la latitud",
  *     ),
  *     @SWG\Parameter(
  * 		   	name="lng",
  * 			  in="path",
  * 			  required=true,
  * 			  type="string",
  * 			  description="Columna con la longitud",
  *     ),
  *     @SWG\Parameter(
  * 		   	name="competence",
  * 			  in="path",
  * 			  required=false,
  * 			  type="integer",
  * 			  description="Si es competencia se requiere el parametro competence=1 si no se manda el parametro por default es false",
  *     ),
  *     @SWG\Response(
  *         response=400,
  *         description="Bad request falta access_token",
  *         @SWG\Schema(
  *           type="object",
  *           additionalProperties={
  *             "type":"integer",
  *             "format":"int32"
  *           }
  *         )
  *     ),
  *     @SWG\Response(
  *         response=200,
  *         description="successful operation",
  *         @SWG\Schema(ref="#/ws/up")
  *     ),
  *   security={{
  *     "access_token":{}
  *   }}
  * )
  */
Route::post('/places.ftue', ['middleware' => 'oauth', function() {
  $userId = Authorizer::getResourceOwnerId();

  $NAME = Input::get('nm');
  if(!isset($NAME)){
    return Response::json([ "error" => "Falta parametro 'nm'"]);
  }

  $ents_org = Input::get('ents');
  if($ents_org != ""){
    $ents_org = str_split($ents_org, ",");
  }
  $ents = "";
  for( $ents_org as $ent ){
    $ents .= "'".trim($ent)."',";
  }
  $ents = substr($ents,0,-1);


  //Guardar PIN
  $pinURL = "POINT.FTUE";
  $idLayer = 0;
  if( Input::get('qf','')!='' && Input::get('qb','')!='' && Input::get('competence','') == "1" ){
    /*LAYER BY QUERY*/
    /*LAYER BY QUERY*/
    /*LAYER BY QUERY*/
    $BBOX = Input::get('qb','');
    $FILTER = Input::get('qf','');

    $data = [
      'id_user' => $userId,
      'name_layer' => $NAME,
      'pin_url' => $pinURL,
      'is_competence'=>true,
      'is_query'=>true,
      'query_filter'=> $FILTER,
      'bbox_filter'=> $BBOX
    ];
    $idLayer = DB::table('users_layers')->insertGetId( $data, 'id_layer' );

    $fl = "";
    if (strpos($FILTER, "cod:") !== false) {
      $fl .= "and D.codigo_act like '".str_replace("cod:","",$FILTER)."%'";
    }else{
      $fl .= "and D.tsv @@ plainto_tsquery(unaccent(lower('$FILTER')))";
    }
    $qden_extend = "(SELECT
      ST_Extent(D.geom)::varchar extend
    from inegi.denue_2016 D
    where D.cve_ent in ($ents)
        ".$fl.")";

    $qden_count = "SELECT
      count(D.*) cnts
    from inegi.denue_2016 D
    where D.cve_ent in ($ents)
        ".$fl."";

    $rs = DB::select($qden_count,[]);
    $counted = 0;
    foreach($rs as $r){
      $counted =$r->cnts;
    }

    if($counted!=0){
      DB::table('users_layers')
          ->where('id_layer', $idLayer)
          ->update([
            'extend' => DB::raw($qden_extend),
            'num_features' => $counted //DB::raw($qden_count)
          ]);
      return Response::json([ "res" => "correcto", "id_layer"=>$idLayer]);
    }else{
      DB::table('users_layers')->where('id_layer', $idLayer)->delete();
      return Response::json([ "error" => "Sin registros", "found"=>$counted]);
    }

  }


}]);
