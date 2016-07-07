<?php
Route::get('/ws_wms', ['middleware' => 'oauth', function() {
  $userId = Authorizer::getResourceOwnerId();

  $MAPSERV = env('MAPSERVER_URL','');

}]);
