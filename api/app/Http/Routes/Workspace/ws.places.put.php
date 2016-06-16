<?php

/**
  * @SWG\Put(
  *     path="/ws/places/{id}?nom={nom}&pin={pin}",
  *     summary="Actualiza el icono o nombre de una ubicacion",
  *     description="Elimina la ubicacion que se envia como parametro",
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
  * 		   	name="id",
  * 			  in="path",
  * 			  required=false,
  * 			  type="string",
  * 			  description="Id de la ubicacion a actualizar"
  *     ),
  *     @SWG\Parameter(
  * 		   	name="nom",
  * 			  in="path",
  * 			  required=false,
  * 			  type="string",
  * 			  description="Nombre de la ubicacion a actualizar"
  *     ),
  *     @SWG\Parameter(
  * 		   	name="pin",
  * 			  in="path",
  * 			  required=false,
  * 			  type="file",
  * 			  description="Archivo del icono"
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
Route::put('/places/{id}', ['middleware' => 'oauth', function($id) {
  $userId = Authorizer::getResourceOwnerId();
  $nom = Input::get('nom', '');

  $upd = array();
  if($nom != ""){
    $upd = array_merge($upd, array("name_layer"=>$nom));
  }

  $pinURL = "";
  if(Request::file('pin')->isValid()){
    $pin = Request::file('pin');
    $pinURL = $pin->getClientOriginalName();
    $path = '/var/www/laravel-storage/pins';
    if(!file_exists($path)) {
      $result = File::makeDirectory($path);
    }
    $path = '/var/www/laravel-storage/pins/' . $userId;
    if(!file_exists($path)) {
      $result = File::makeDirectory($path);
    }
    move_uploaded_file( $pin->getRealPath(), $path.'/'.$pin->getClientOriginalName());
    $upd = array_merge($upd, array("pin_url"=>$pinURL));
  }
  $updated = 0;
  if(sizeof($upd)>0){
    $updated = DB::table('users_layers')->where('id_layer', '=', $id)->update($upd);
  }
  return Response::json(["id_layer"=>$id, "updated"=>$updated]);
}]);
