<?php

/**
  * @SWG\Get(
  *     path="/dw?s={schema}&t={table}&c={columns}&w={column_filter}:{value_filter}&lat={latitude}&lng={longitud}&mts={meters}",
  *     summary="Distance Within service analisys",
  *     description="Servicio de analisis que regresa las geometrias con la informacion solicitada",
  *     operationId="dw",
  *     tags={"Distance Within"},
  *     produces={"application/json"},
  *     @SWG\Parameter(
  * 		   	name="schema",
  * 			  in="path",
  * 			  required=true,
  * 			  type="string",
  * 			  description="Schema",
  *     ),
  *     @SWG\Parameter(
  * 		   	name="table",
  * 			  in="path",
  * 			  required=true,
  * 			  type="string",
  * 			  description="table",
  *     ),
  *     @SWG\Parameter(
  * 		   	name="columns",
  * 			  in="path",
  * 			  required=true,
  * 			  type="string",
  * 			  description="columns",
  *     ),
  *     @SWG\Parameter(
  * 		   	name="column_filter",
  * 			  in="path",
  * 			  required=true,
  * 			  type="string",
  * 			  description="column_filter",
  *     ),
  *     @SWG\Parameter(
  * 		   	name="value_filter",
  * 			  in="path",
  * 			  required=true,
  * 			  type="string",
  * 			  description="value_filter",
  *     ),
  *     @SWG\Parameter(
  * 		   	name="latitude",
  * 			  in="path",
  * 			  required=true,
  * 			  type="float",
  * 			  description="latitude",
  *     ),
  *     @SWG\Parameter(
  * 		   	name="longitud",
  * 			  in="path",
  * 			  required=true,
  * 			  type="float",
  * 			  description="longitud",
  *     ),
  *     @SWG\Parameter(
  * 		   	name="meters",
  * 			  in="path",
  * 			  required=true,
  * 			  type="integer",
  * 			  description="meters",
  *     ),
  *     @SWG\Response(
  *         response=200,
  *         description="successful operation",
  *         @SWG\Schema(ref="#/dw")
  *     ),
  * )
  */
Route::get('/dw', function(){
  $response = array();

  $s = Input::get('s', '');
  $t = Input::get('t', '');
  $c = Input::get('c', '');
  $w = Input::get('w', '');
  $lat = Input::get('lat', '');
  $lng = Input::get('lng', '');
  $mts = Input::get('mts', '');

  return Response::json([
    "s"=>$s,
    "t"=>$t,
    "c"=>$c,
    "w"=>$w,
    "lat"=>$lat,
    "lng"=>$lng,
    "mts"=>$mts
  ]);
});
