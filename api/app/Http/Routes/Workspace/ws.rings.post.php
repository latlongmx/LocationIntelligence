<?php

Route::post('/rings', ['middleware' => 'oauth', function() {
  $userId = Authorizer::getResourceOwnerId();

  $nm = Input::get('nm','');
  $type = Input::get('ty','');
  $time = Input::get('ti','');
  $geo = Input::get('geo','');

  $id_ring = DB::table('users_rings')->insertGetId( [
    'id_user'=> $userId,
    'name_ring'=> $nm,
    'type_ring'=> $typ,
    'time_ring'=> $typ,
    'geo'=> DB::raw("ST_GeomFromText( '$geo', 4326 )")
  ], 'id_ring' );

  return Response::json([ "res" => "correcto", "id_ring"=>$id_ring]);
}]);
