<?php

Route::group(['prefix'=>'ws', 'middleware' => 'cors'], function(){
  require app_path('Http/Routes/Workspace/ws.places.php');
});
