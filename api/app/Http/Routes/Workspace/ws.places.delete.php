<?php

/**
  * @SWG\Delete(
  *     path="/ws/places/",
  *     summary="Elimina la ubicacion que se envia como parametro",
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
Route::delete('/places/{id}', ['middleware' => 'oauth', function($id) {
  $userId = Authorizer::getResourceOwnerId();
  $delLayDat = DB::table('users_layers_data')->where('id_layer', '=', $id)->delete();
  $delLay = DB::table('users_layers')->where('id_layer', '=', $id)->delete();
  return Response::json(["delLayer"=>$delLay, "delData"=>$delLayDat]);
}]);
