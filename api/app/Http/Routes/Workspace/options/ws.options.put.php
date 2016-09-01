<?php

Route::post('/options', ['middleware' => 'oauth', function() {
  $userId = Authorizer::getResourceOwnerId();
  $opts = Input::get('options','');
  $updated = DB::table('users')->where('id', '=', $userId)->update([
    'proy_options'=> DB::raw("'$opts'")
  ]);
  return Response::json([ "res" => "correcto", "upds"=>$updated]);
}]);
