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
  $loc = array();
  $loc[] = [
    "usr_id"=>$userId,
    "id2del"=>$id
  ];
  return Response::json(["places"=>$loc]);
}]);
