<?php

Route::post('/rings_u/{id}', ['middleware' => 'oauth', function($id) {
  $userId = Authorizer::getResourceOwnerId();
  $nom = Input::get('nom', '');
  $updated = 0;
  if($nom != ""){
    $updated = DB::table('users_rings')
        ->where('id_ring', '=', $id)
        ->where('id_user', '=', $userId)
        ->update( ["name_ring"=>$nom ]);
  }
  return Response::json(["updated"=>$updated]);
}]);
