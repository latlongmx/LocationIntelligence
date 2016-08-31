<?php

/**
  * @SWG\Get(
  *     path="/ws/places?p={id_ubicacion}",
  *     summary="Obtiene las ubicaciones registradas por el usuario",
  *     description="Obtiene los registros incluido las geometrias ingresadas por el usuario",
  *     operationId="catalog",
  *     tags={"Workspace"},
  *     produces={"application/json"},
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
  *     @SWG\Parameter(
  * 		   	name="id_layer",
  * 			  in="path",
  * 			  required=false,
  * 			  type="integer",
  * 			  description="id de la ubicacion a actualizar si no se manda se obtendran todos"
  *     ),
  *     @SWG\Parameter(
  * 		   	name="competence",
  * 			  in="path",
  * 			  required=false,
  * 			  type="integer",
  * 			  description="si se requiere traer todos las competencias competence=1"
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
Route::get('/places', ['middleware' => 'oauth', function() {
  $userId = Authorizer::getResourceOwnerId();
  $id = Input::get('id_layer', '');
  $competence = Input::get('competence', '');

  $sql = "";
  $places = array();

  //if($competence===""){
    $sql = "SELECT row_to_json(tmp) json
        FROM
        (
          SELECT id_layer, name_layer, creation_dt, pin_url, extend, num_features,
            '{}' as data
          FROM users_layers L
          WHERE id_user=$userId
          and is_competence is ".($competence!==""?"true":"false")."
          ".($id!=""?" and id_layer=".$id:"")."
          ORDER BY id_layer
        ) tmp;";
    $rs = DB::select($sql,[]);
    foreach($rs as $r){
      $places[] = json_decode($r->json);
    }

  return Response::json(["places"=>$places]);
}]);
