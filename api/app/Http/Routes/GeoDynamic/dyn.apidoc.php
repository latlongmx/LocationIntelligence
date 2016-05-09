<?php

Route::get('/apidoc', function(){
  $path = storage_path() . '/api-docs/api-docs.json';
  error_log($path);

    if(!File::exists($path)) abort(404);

    $file = File::get($path);
    $type = File::mimeType($path);

    $response = Response::make($file, 200);
    $response->header("Content-Type", $type);

    return $response;
});
