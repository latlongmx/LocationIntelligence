<?php

Route::post('/heat', ['middleware' => 'oauth', function() {
  $userId = Authorizer::getResourceOwnerId();
  $opts = Input::get('options','');
  $updated = DB::table('users')->where('id_user', '=', $userId)->update([
    'options'=> DB::raw($opts)
  ]);
  return Response::json([ "res" => "correcto", "upds"=>$updated]);
}]);
