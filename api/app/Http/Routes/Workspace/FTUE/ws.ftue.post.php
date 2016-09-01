<?php

Route::post('/ftue', ['middleware' => 'oauth', function() {
  $userId = Authorizer::getResourceOwnerId();
  $prm = ['ftue_etapa', 'ftue_nombre', 'ftue_categoria', 'ftue_subcat1', 'ftue_subcat2', 'ftue_entidad', 'ftue_municipio'];
  $upd = array();
  foreach ($prm as $p) {
    $opt = Input::get($p,'');
    if($opt != ""){
      $upd = array_merge($upd, array("$p"=>$opt));
    }
  }
  $updated = 0;
  if(sizeof($upd)>0){
    $updated = DB::table('users')->where('id', '=', $userId)->update($upd);
  }
  return Response::json([ "res" => "correcto", "upds"=>$updated]);
}]);
