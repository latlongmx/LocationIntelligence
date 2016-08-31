<?php

/**
  * @SWG\Delete(
  *     path="/ws/heat/{id}",
  *     summary="Elimina el heatmap indicado",
  *     description="Elimina el heatmap con id enviado como parametro",
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
  * 			  required=true,
  * 			  type="integer",
  * 			  description="id del heatmap a elimianr"
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
Route::delete('/heat/{id}', ['middleware' => 'oauth', function($id) {
  $userId = Authorizer::getResourceOwnerId();
  $delLay = DB::table('users_heatmaps')
        ->where('id_heat', '=', $id)
        ->where('id_user', '=', $userId)
        ->delete();
  return Response::json(["delLayer"=>$delLay]);
}]);
