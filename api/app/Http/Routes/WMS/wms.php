<?php
Route::get('/ws_wms', ['middleware' => 'oauth', function() {
  $userId = Authorizer::getResourceOwnerId();

  $MAPSERV = env('MAPSERVER_URL','');

  $QUERY = $_SERVER['QUERY_STRING'];
  $WKT = Input::get('WKT', '' );
  $MTS = Input::get('MTS', 0 );
  $MTS = meters2dec($MTS);

  $mapVias = storage_path("MAPS/vias.map");
  $userMapFile = storage_path("MAPS_TMP/V".$userId.".map");

  $mapfile = fopen( $mapVias, "r");
  $vias = fread( $mapfile, filesize($mapVias));

  $GEOM_WKT_SPLIT = " ST_GeomFromText( '$WKT', 4326 ) ";
  //$GEOM_WKT_WHERE = " ST_Intersects( A.geom, S.geom )";
  if (strpos( strtolower($WKT), 'point') !== false) {
    $GEOM_WKT_SPLIT = " ST_buffer( '$WKT' , $MTS)";
    //$GEOM_WKT_WHERE = "ST_DWithin( A.geom, $GEOM_WKT_SPLIT, $mts)";
  }

  $viasUsr = str_replace("%GEOM_WKT_SPLIT%", $GEOM_WKT_SPLIT, $vias);
  //$viasUsr = str_replace("%WKT%", $WKT, $vias);

  file_put_contents($userMapFile, $viasUsr);

  $img = file_get_contents( $MAPSERV . "?map=".$userMapFile. "&" .$QUERY);
  header("content-type: image/png");
  echo $img;
}]);
