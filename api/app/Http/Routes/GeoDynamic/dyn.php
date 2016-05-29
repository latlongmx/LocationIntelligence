<?php

Route::group(['prefix'=>'dyn', 'before' => 'oauth', 'middleware' => 'cors'], function(){

  /*if (!extension_loaded('MapScript')) {
    dl('php_mapscript.soo');
  }*/

  //API Documentacion
  require app_path('Http/Routes/GeoDynamic/dyn.apidoc.php');
  //Catalogos
  require app_path('Http/Routes/GeoDynamic/dyn.catalog.php');
  //Intersect
  require app_path('Http/Routes/GeoDynamic/dyn.intersect.php');
  //PobViv WMS
  require app_path('Http/Routes/GeoDynamic/dyn.pb_wms.php');

});
