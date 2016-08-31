<?php

Route::delete('/rings/{id}', ['middleware' => 'oauth', function($id) {
  $userId = Authorizer::getResourceOwnerId();
  $delLay = DB::table('users_rings')
        ->where('id_ring', '=', $id)
        ->where('id_user', '=', $userId)
        ->delete();
  return Response::json(["delLayer"=>$delLay]);
}]);
