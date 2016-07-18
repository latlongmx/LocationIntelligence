<?php

/**
  * @SWG\Post(
  *     path="/ws/heat/{id}?nom={nom}",
  *     summary="Actualiza el nombre de un heatmap",
  *     description="Actualiza el nombre de un heatmap",
  *     operationId="catalog",
  *     tags={"Workspace Heatmap"},
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
Route::post('/heat_u/{id}', ['middleware' => 'oauth', function($id) {
  $userId = Authorizer::getResourceOwnerId();
  $nom = Input::get('nom', '');
  $updated = 0;
  if($nom != ""){
    $updated = DB::table('users_heatmaps')
        ->where('id_heat', '=', $id)
        ->update( ["name_heat"=>$nom ]);
  }
  return Response::json(["id_layer"=>$id, "updated"=>$updated]);
}]);
