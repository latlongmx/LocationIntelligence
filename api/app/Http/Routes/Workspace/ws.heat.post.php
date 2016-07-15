<?php
/** @SWG\Post(
  *     path="/ws/heat?nm={name}&access_token={token_id}&cod={codigos}&bnd={bounds}",
  *     summary="Obtiene las ubicaciones registradas por el usuario",
  *     description="Obtiene los registros incluido las geometrias ingresadas por el usuario",
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
  * 		   	name="nm",
  * 			  in="path",
  * 			  required=true,
  * 			  type="integer",
  * 			  description="nombre del alyer heatmap a guardar"
  *     ),
  *   security={{
  *     "access_token":{}
  *   }}
  * )
  */
Route::post('/heat', ['middleware' => 'oauth', function() {
  $userId = Authorizer::getResourceOwnerId();

  $nm = Input::get('nm','');
  $cod = Input::get('cod','');
  $bnd = Input::get('bnd','');

  $id_heat = DB::table('users_heatmaps')->insertGetId( [
    'id_user'=> $userId,
    'name_heat'=> $nm,
    'cods'=> $cod,
    'bounds'=> $bnd
  ], 'id_heat' );

  return Response::json([ "res" => "correcto", "id_heat"=>$id_heat]);
});
