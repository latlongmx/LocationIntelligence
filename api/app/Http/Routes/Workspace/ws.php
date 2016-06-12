<?php

Route::group(['prefix'=>'ws', 'middleware' => 'cors'], function(){
  require app_path('Http/Routes/Workspace/ws.places.get.php');
  require app_path('Http/Routes/Workspace/ws.places.post.php');
  require app_path('Http/Routes/Workspace/ws.places.delete.php');
  require app_path('Http/Routes/Workspace/ws.icon.get.php');
});
