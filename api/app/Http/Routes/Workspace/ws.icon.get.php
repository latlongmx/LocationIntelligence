<?php

/**
  * @SWG\Get(
  *     path="/ws/icon/{name}",
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
  * 		   	name="name",
  * 			  in="path",
  * 			  required=true,
  * 			  type="integer",
  * 			  description="nombre del icono guardado en el perfile del usuario"
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
Route::get('/icon/{icoName}', ['middleware' => 'oauth', function($icoName) {
  $userId = Authorizer::getResourceOwnerId();
  $path = storage_path() . '/pins/'.$userId.'/'.$icoName;
  
  if(!File::exists($path)) abort(404);

  $file = File::get($path);
  $type = File::mimeType($path);

  $response = Response::make($file, 200);
  $response->header("Content-Type", $type);
  return $response;
}]);
