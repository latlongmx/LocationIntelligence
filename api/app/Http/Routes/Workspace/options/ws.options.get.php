<?php

Route::get('/options', ['middleware' => 'oauth', function() {
  $userId = Authorizer::getResourceOwnerId();
  $rs = DB::table('users')->where('id', '=', $userId)->get();
  $opts = [];
  foreach($rs as $r){
    $opts = $r->proy_options;
  }
  return Response::json(["options"=>$opts]);
}]);
