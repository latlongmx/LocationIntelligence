<?php

Route::get('/options', ['middleware' => 'oauth', function() {
  $userId = Authorizer::getResourceOwnerId();
  $rs = DB::table('users')->where('id_user', '=', $userId)->get();
  $opts = [];
  foreach($rs as $r){
    $opts = $r->proy_options;
  }
  return Response::json(["places"=>$places]);
}]);
