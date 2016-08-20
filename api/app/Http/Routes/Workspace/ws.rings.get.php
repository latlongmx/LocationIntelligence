<?php

Route::get('/rings', ['middleware' => 'oauth', function() {
  $userId = Authorizer::getResourceOwnerId();
  $id = Input::get('id', '');
  $filter = "";
  if($id!=""){
    $filter = " and id_ring="+$id;
  }
  $sql = "SELECT row_to_json(tmp) json
      FROM
      ( SELECT *
        FROM users_rings L
        WHERE id_user=$userId $filter
        ORDER BY id_ring
      ) tmp;";
  $rs = DB::select($sql,[]);
  $draws = [];
  foreach($rs as $r){
    $draws[] = json_decode($r->json);
  }
  return Response::json(["rings"=>$draws]);
}]);
