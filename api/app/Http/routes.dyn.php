<?php


Route::group(['prefix'=>'dyn', 'before' => 'oauth', 'middleware' => 'cors'], function(){

  //Catalogos
  require app_path('Http/routes.dyn.catalog.php');

});
