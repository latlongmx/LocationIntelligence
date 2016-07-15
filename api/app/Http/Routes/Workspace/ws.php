<?php

Route::group(['prefix'=>'ws', 'middleware' => 'cors'], function(){
  require app_path('Http/Routes/Workspace/ws.places.get.php');
  require app_path('Http/Routes/Workspace/ws.places.post.php');
  require app_path('Http/Routes/Workspace/ws.places.delete.php');
  require app_path('Http/Routes/Workspace/ws.places.put.php');
  require app_path('Http/Routes/Workspace/ws.icon.get.php');
  require app_path('Http/Routes/Workspace/ws.wms.php');

  require app_path('Http/Routes/Workspace/ws.heat.get.php');
  require app_path('Http/Routes/Workspace/ws.heat.post.php');
});
