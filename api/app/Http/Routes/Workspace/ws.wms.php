<?php


Route::get('/ws_wms', ['middleware' => 'oauth', function() {
  $userId = Authorizer::getResourceOwnerId();
  $layer =  Input::get('layers', '');
  $idLayer = str_replace("U","",$layer);

  $map_file = storage_path('USER.map');
  $user_map_file = storage_path("MAPS_TMP/".$layer.".map");

  $file_contents = file_get_contents($map_file);
  $file_contents = str_replace("&ID&", $idLayer, $file_contents);

  $q = "SELECT * FROM users_layers WHERE id_user=".$userId." and id_layer=".$idLayer;
  $rs = DB::select($q,[]);
  foreach($rs as $r){
    $qry_data = "geom from (".
      "select id_data, geom ".
      "from users_layers_data ".
      "where id_layer=".$idLayer.
      ") as T using unique id_data using srid=4326";
    if($r->is_competence=="t"){
      Log::info("competencia");
      $qf = $r->query_filter;
      $filter = "";
      if (strpos($qf, "cod:") !== false) {
        $filter = "and D.codigo_act like '".str_replace("cod:","",$qf)."%'";
      }else{
        $filter = "and D.tsv @@ to_tsquery(unaccent('$qf'))";
      }
      $qry_data = "geom from (
            SELECT
              D.gid, D.nom_estab, D.nombre_act, D.geom
            from inegi.denue_2016 D,
                 inegi.mgn_estados E
            where
                ST_Intersects(E.geom,
                    ST_MakeEnvelope(".$r->bbox_filter.", 4326)
                )
                and E.cve_ent = D.cve_ent
                ".$filter."
        ) as T using unique gid using srid=4326";
    }
    Log::info($qry_data);
    $file_contents = str_replace("&QUERY&", $qry_data, $file_contents);


    if($r->pin_url === null || $r->pin_url === ""){
      $file_contents = str_replace("&IMAGE&", "", $file_contents);
      $file_contents = str_replace("&SYMBOL&", "point", $file_contents);
      $file_contents = str_replace("&EXTRAS&",
        "COLOR 53 110 127
         SIZE 10", $file_contents);
    }else{
      $img_path = "/var/www/laravel-storage/pins/".$userId."/".$r->pin_url;
      $file_contents = str_replace("&IMAGE&", $img_path, $file_contents);
      $file_contents = str_replace("&EXTRAS&",
      "SIZE 34
       MAXSIZE 34", $file_contents);
      $file_contents = str_replace("&SYMBOL&", "symbol_".$idLayer, $file_contents);

    }

  }
  file_put_contents($user_map_file, $file_contents);

  $MAP = new \mapObj($user_map_file);
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
