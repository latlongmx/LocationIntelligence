<?php


Route::get('/ws_wms', ['middleware' => 'oauth', function() {
  $userId = Authorizer::getResourceOwnerId();

  $MAP = getMapObjConfig();
  $req = new \Owsrequestobj();
  $req->loadparams();

  $is_competence = $req->getValueByName("competence");

  $q = "SELECT * FROM users_layers WHERE id_user=".$userId." and is_competence is ";
  if($is_competence != "" && $is_competence == "1"){
    $q .= "true";
  }else{
    $q .= "false";
  }
  $rs = DB::select($q,[]);
  foreach($rs as $r){
    $id = $r->id_layer;
    $layer = getLayerObjConfig($MAP, 'U'.$id);
    $layer->set('type', MS_LAYER_POINT);

    $qry_data = "geom from (".
      "select id_data, geom ".
      "from users_layers_data ".
      "where id_layer=".$id.
      ") as T using unique id_data using srid=4326";
    $layer->set('data', $qry_data);

    $class = new \ClassObj( $layer );
    $style = new \StyleObj( $class );

    $symbol = new \SymbolObj($MAP, "symbol_".$id);
    $img_path = "/var/www/laravel-storage/pins/".$userId."/".$r->pin_url;
    Log:info($img_path);
    $symbol->setImagePath($img_path);
    $symbol->set("sizex", 32);
    $symbol->set("sizey", 32);
    $symbol->set("transparent", MS_TRUE);
    $symbol->set("inmapfile", MS_TRUE);

    $style->set("symbolname", "symbol_".$id);
  }


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
