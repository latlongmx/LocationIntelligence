<?php

/**
  * @SWG\Get(
  *     path="/ws/icon?nm={name}&access_token={token_id}",
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
  * 		   	name="nm",
  * 			  in="path",
  * 			  required=true,
  * 			  type="integer",
  * 			  description="nombre del icono guardado en el perfile del usuario"
  *     ),
  *     @SWG\Response(
  *         response=200,
  *         description="icono seleccionado",
  *         @SWG\Schema(ref="#/ws/up")
  *     ),
  *   security={{
  *     "access_token":{}
  *   }}
  * )
  */ //?nm={nm}&access_token={token_id}
Route::get('/icon', ['middleware' => 'oauth', function() {
  $userId = Authorizer::getResourceOwnerId();
  $icoName = Input::get('nm', '');
  $path = storage_path() . '/pins/'.$userId.'/'.$icoName;

  /*if(!File::exists($path)) abort(404);

  $file = File::get($path);
  $type = File::mimeType($path);

  $response = Response::make($file, 200);
  $response->header("Content-Type", $type);
  return $response;*/

  return Response::json(["icoName"=>$icoName, "path" => $path]);
}]);
