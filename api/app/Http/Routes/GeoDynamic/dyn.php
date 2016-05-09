<?php

Route::group(['prefix'=>'dyn', 'before' => 'oauth', 'middleware' => 'cors'], function(){

  //API Documentacion
  require app_path('Http/Routes/GeoDynamic/dyn.apidoc.php');
  //Catalogos
  require app_path('Http/Routes/GeoDynamic/dyn.catalog.php');
  //Distance within
  require app_path('Http/Routes/GeoDynamic/dyn.dw.php');

});
