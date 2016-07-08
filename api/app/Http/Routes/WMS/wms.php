<?php
Route::get('/ws_wms', ['middleware' => 'oauth', function() {
  $userId = Authorizer::getResourceOwnerId();

  $MAPSERV = env('MAPSERVER_URL','');

  $QUERY = $_SERVER['QUERY_STRING'];
  $WKT =  Input::get('WKT', '' );

  $mapVias = storage_path("MAPS/vias.map");
  $userMapFile = storage_path("MAPS_TMP/V".$userId.".map");

  $mapfile = fopen( $mapVias, "r");
  $vias = fread( $mapfile, filesize($mapVias));
  $viasUsr = str_replace("%WKT%", $WKT, $vias);

  file_put_contents($userMapFile, $viasUsr);

  $img = file_get_contents( $MAPSERV . "?map=".$userMapFile. "&" .$QUERY);
  header("content-type: image/png");
  echo $img;
}]);
