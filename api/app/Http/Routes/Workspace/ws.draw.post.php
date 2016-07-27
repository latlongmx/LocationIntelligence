<?php

Route::post('/draw', ['middleware' => 'oauth', function() {
  $userId = Authorizer::getResourceOwnerId();

  $nm = Input::get('nm','');
  $typ = Input::get('typ','');
  $geo = Input::get('geo','');

  $id_draw = DB::table('users_draws')->insertGetId( [
    'id_user'=> $userId,
    'name_draw'=> $nm,
    'type_draw'=> $typ,
    'gjson'=> $geo
  ], 'id_draw' );

  return Response::json([ "res" => "correcto", "id_draw"=>$id_draw]);
}]);
