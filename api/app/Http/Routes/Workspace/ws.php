<?php

Route::group(['prefix'=>'ws', 'middleware' => 'cors'], function(){
  require app_path('Http/Routes/Workspace/places/ws.places.get.php');
  require app_path('Http/Routes/Workspace/places/ws.places.post.php');
  require app_path('Http/Routes/Workspace/places/ws.places.length.php');
  require app_path('Http/Routes/Workspace/places/ws.places.delete.php');
  require app_path('Http/Routes/Workspace/places/ws.places.put.php');

  require app_path('Http/Routes/Workspace/ws.icon.get.php');
  require app_path('Http/Routes/Workspace/ws.wms.php');

  require app_path('Http/Routes/Workspace/heat/ws.heat.get.php');
  require app_path('Http/Routes/Workspace/heat/ws.heat.post.php');
  require app_path('Http/Routes/Workspace/heat/ws.heat.delete.php');
  require app_path('Http/Routes/Workspace/heat/ws.heat.put.php');

  require app_path('Http/Routes/Workspace/draw/ws.draw.post.php');
  require app_path('Http/Routes/Workspace/draw/ws.draw.put.php');
  require app_path('Http/Routes/Workspace/draw/ws.draw.get.php');
  require app_path('Http/Routes/Workspace/draw/ws.draw.del.php');

  require app_path('Http/Routes/Workspace/rings/ws.rings.post.php');
  require app_path('Http/Routes/Workspace/rings/ws.rings.put.php');
  require app_path('Http/Routes/Workspace/rings/ws.rings.get.php');
  require app_path('Http/Routes/Workspace/rings/ws.rings.del.php');

  require app_path('Http/Routes/Workspace/options/ws.options.get.php');
  require app_path('Http/Routes/Workspace/options/ws.options.put.php');
});
