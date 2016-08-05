<?php

Route::post('/places_c', ['middleware' => 'oauth', function() {
  $userId = Authorizer::getResourceOwnerId();
  $NAME = Input::get('nm');
  $BBOX = Input::get('qb','');
  if(!isset($NAME)){
    return Response::json([ "error" => "Falta parametro 'nm'"]);
  }

  $qden_count = "SELECT
    count(D.*) cnts
  from inegi.denue_2016 D,
       inegi.mgn_estados E
  where
      ST_Intersects(E.geom,
          ST_MakeEnvelope(".$BBOX.", 4326)
      )
      and E.cve_ent = D.cve_ent
      and D.tsv @@ plainto_tsquery(unaccent(lower('$NAME')))";

  $rs = DB::select($qden_count,[]); 
  $counted = 0;
  foreach($rs as $r){
    $counted =$r->cnts;
  }
  return Response::json([ "regs" => $counted]);
}]);
