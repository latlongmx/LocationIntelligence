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
      ( SELECT id_ring, name_ring, type_ring, time_ring, st_xmax(geom) x, st_ymax(geom) y
        FROM users_rings L
        WHERE id_user=$userId $filter
        ORDER BY id_ring
      ) tmp;";
  $rs = DB::select($sql,[]);
  $rings = [];
  foreach($rs as $r){
    $rings[] = json_decode($r->json);
  }
  return Response::json(["rings"=>$rings]);
}]);
