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
  $sql = "select L.id_layer, name_layer, pin_url, creation_dt, id_data, data_values, st_xmax(geom) x, st_ymax(geom) y
      from users_layers L
      left join users_layers_data D
      on L.id_layer=D.id_layer
      where id_user=$userId and D.id_layer is not null
      ".($id!==""?" and L.id_layer=".$id:"")."
      order by id_layer";
  $rs = DB::select($sql,[]);
  $places = array();
  $places_data = array();
  $last_layer=-1;
  foreach($rs as $r){
    if($last_layer == -1){
      $last_layer = $r->id_layer;
    }
    $places_data[] = [
      "id_data"=>$r->id_data,
      "data_values"=>$r->data_values,
      "pin_url"=>$r->pin_url,
      "x"=>$r->x,
      "y"=>$r->y
    ];
    if($last_layer != $r->id_layer){
      $last_layer = $r->id_layer;
      $places[] = [
        "id_layer"=>$r->id_layer,
        "name_layer"=>$r->name_layer,
        "data"=>$places_data
      ];
      $places_data = array();
    }
  }
  if(sizeof($places)==0 && sizeof($places_data)>0){
    $places[] = [
      "id_layer"=>$r->id_layer,
      "name_layer"=>$r->name_layer,
      "data"=>$places_data
    ];
  }

  return Response::json(["places"=>$places]);
}]);
