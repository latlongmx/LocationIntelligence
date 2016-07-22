<?php


Route::get('/ws_transp', ['middleware' => 'oauth', function() {
  $userId = Authorizer::getResourceOwnerId();
  $map_file = storage_path('MAPS/mapfile_base.map');

  $MAP = new \mapObj($map_file);
  $req = new \Owsrequestobj();
  $req->loadparams();

  $LAYERS = $req->getValueByName("LAYERS");
  $LAYERS = explode(",", $LAYERS);

  foreach($LAYERS as $layer){
    $is_point = false;
    if (strpos($layer, '_p') !== false) {
      $is_point = true;
    }
    $filter_val = explode("_", $layer);
    $filter_val = $filter_val[0];
    $filter = " and agency_id='".$filter_val."'";

    $tbl = "df_gtfs.vw_lineas";
    $tiplay = MS_LAYER_LINE;

    $symName = "sym_".$layer;

    if($is_point){
      $tbl = "df_gtfs.vw_paradas";
      $tiplay = MS_LAYER_POINT;

      $img_path = storage_path('MAPS/imgs/'.$filter_val.'.png');
      $nId = ms_newsymbolobj($MAP, $symName);
      $sym = $MAP->getsymbolobjectbyid($nId);
      $sym->setImagePath( $img_path );
      $sym->set("inmapfile", MS_TRUE);
      $sym->set("type", MS_SYMBOL_PIXMAP);
      $sym->set("transparent", 0);
    }

    $LAYMAP = getLayerObjConfig($MAP, $layer);

    $qry_data = "geom from (
          select row_number() over() gid,* from $tbl
          where ST_Intersects(geom,!BOX!)
              $filter
        ) as T using unique gid using srid=4326";
    $LAYMAP->set('data', $qry_data);
    $LAYMAP->set('type', $tiplay);

    $class = new \ClassObj( $LAYMAP );
    $style = new \StyleObj( $class );
    if($is_point){
      $style->set("symbolname", $symName);
      $style->set("size", 34);
      $style->set("maxsize", 34);
    }else{
      $style->color->setHex("#A7C9C9");
      $style->set("width",5);
    }
  }

  ms_ioinstallstdouttobuffer();

  $map_file_proccessed = storage_path('MAPS/TMP/transp.map');
  $MAP->save( $map_file_proccessed );
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
