<?php

Route::group(['prefix'=>'api','before' => 'oauth'], function()
{
    Route::get('/status', function(){
      return Response::json(["status"=>"ok"]);
    });
});
