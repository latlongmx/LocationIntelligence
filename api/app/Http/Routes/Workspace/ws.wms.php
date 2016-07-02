<?php


Route::get('/ws_wms', ['middleware' => 'oauth', function() {
  $userId = Authorizer::getResourceOwnerId();



  $is_competence =  Input::get('COMPETENCE', '');
  $layer =  Input::get('LAYERS', '');

  $idLayer = str_replace("U","",$layer);


  $map_file = storage_path('USER.map');
  $user_map_file = storage_path("MAPS_TMP/".$layer.".map");

  $file_contents = file_get_contents($map_file);
  $file_contents = str_replace("&ID&", $idLayer, $file_contents);

  $q = "SELECT * FROM users_layers WHERE id_user=".$userId." and id_layer=".$idLayer." and is_competence is ";
  if($is_competence != "" && $is_competence == "1"){
    $q .= "true";
  }else{
    $q .= "false";
  }
  $rs = DB::select($q,[]);
  foreach($rs as $r){
    $img_path = "/var/www/laravel-storage/pins/".$userId."/".$r->pin_url;
    $qry_data = "geom from (".
      "select id_data, geom ".
      "from users_layers_data ".
      "where id_layer=".$id.
      ") as T using unique id_data using srid=4326";

    $file_contents = str_replace("&IMAGE&", $img_path, $file_contents);
    $file_contents = str_replace("&QUERY&", $qry_data, $file_contents);
  }
  file_put_contents($user_map_file, $file_contents);

  $MAP = getMapObjConfig($user_map_file);
  $req = new \Owsrequestobj();
  $req->loadparams();

  ms_ioinstallstdouttobuffer();
  $map_file = storage_path("logs/ms_file_user.map");
  $MAP->save( $map_file );
  $MAP->owsdispatch($req);

  $contenttype = ms_iostripstdoutbuffercontenttype();
  if (!empty($contenttype)){
    error_log($contenttype);
    if ($req->getValueByName("REQUEST") === "GetCapabilities") {
      $buffer = ms_iogetstdoutbufferstring();
      header("Content-type: application/xml");
      echo $buffer;
    }else{
      header("Content-type: $contenttype");
      ms_iogetStdoutBufferBytes();
    }
  }else{
    echo "Fail to render!";
  }

  ms_ioresethandlers();
}]);
