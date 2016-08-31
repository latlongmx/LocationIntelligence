<?php

Route::delete('/draw/{id}', ['middleware' => 'oauth', function($id) {
  $userId = Authorizer::getResourceOwnerId();
  $delLay = DB::table('users_draws')
        ->where('id_draw', '=', $id)
        ->where('id_user', '=', $userId)
        ->delete();
  return Response::json(["delLayer"=>$delLay]);
}]);
