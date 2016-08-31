<?php

Route::post('/draw_u/{id}', ['middleware' => 'oauth', function($id) {
  $userId = Authorizer::getResourceOwnerId();
  $nom = Input::get('nom', '');
  $updated = 0;
  if($nom != ""){
    $updated = DB::table('users_draws')
        ->where('id_draw', '=', $id)
        ->where('id_user', '=', $userId)
        ->update( ["name_draw"=>$nom ]);
  }
  return Response::json(["updated"=>$updated]);
}]);
