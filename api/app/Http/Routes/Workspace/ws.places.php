<?php

/**
  * @SWG\Post(
  *     path="ws/places/",
  *     summary="Subir archivo para agregar a mis ubicaciones",
  *     description="Sube un archivo csv a la seccion de mis ubicaciones del usuario registrado a traves del access_token",
  *     operationId="catalog",
  *     tags={"Workspace"},
  *     produces={"application/json"},
  *     @SWG\Parameter(
  * 		   	name="nm",
  * 			  in="path",
  * 			  required=true,
  * 			  type="string",
  * 			  description="Nombre de mis hubicaciones",
  *     ),
  *     @SWG\Parameter(
  * 		   	name="pin",
  * 			  in="path",
  * 			  required=true,
  * 			  type="string",
  * 			  description="Url del PIN a utilizar",
  *     ),
  *     @SWG\Parameter(
  * 		   	name="file",
  * 			  in="path",
  * 			  required=true,
  * 			  type="file",
  * 			  description="Archivo csv",
  *     ),
  *     @SWG\Parameter(
  * 		   	name="lat",
  * 			  in="path",
  * 			  required=true,
  * 			  type="string",
  * 			  description="Columna con la latitud",
  *     ),
  *     @SWG\Parameter(
  * 		   	name="lng",
  * 			  in="path",
  * 			  required=true,
  * 			  type="string",
  * 			  description="Columna con la longitud",
  *     ),
  *     @SWG\Response(
  *         response=400,
  *         description="Bad request falta access_token",
  *         @SWG\Schema(
  *           type="object",
  *           additionalProperties={
  *             "type":"integer",
  *             "format":"int32"
  *           }
  *         )
  *     ),
  *     @SWG\Response(
  *         response=200,
  *         description="successful operation",
  *         @SWG\Schema(ref="#/ws/up")
  *     ),
  *   security={{
  *     "access_token":{}
  *   }}
  * )
  */
Route::post('/places', ['middleware' => 'oauth', function() {
  $userId = Authorizer::getResourceOwnerId();
  $lat = Input::get('lat');
  if(!isset($lat)){
    return Response::json([ "error" => "Falta parametro 'lat'"]);
  }
  $lng = Input::get('lng');
  if(!isset($lng)){
    return Response::json([ "error" => "Falta parametro 'lng'"]);
  }
  $NAME = Input::get('nm');
  if(!isset($NAME)){
    return Response::json([ "error" => "Falta parametro 'nm'"]);
  }
  $pin = Input::get('pin');
  if(!isset($pin)){
    return Response::json([ "error" => "Falta parametro 'pin'"]);
  }
  if(Request::file('file')->isValid()){
    $f = Request::file('file')->openFile();
    $f->setFlags(SplFileObject::READ_CSV);
    $f->setCsvControl(',');
    $res = array();
    $read_csv_header = false;
    $HEAD = array();
    $latF=false;
    $lngF=false;
    $idLayer = 0;
    foreach ($f as $row) {
      if(isset($row) && $row != null){
        if(sizeof($row)>=3){
          if($read_csv_header==false){
            $read_csv_header=true;
            $latF = array_search($lat, $row);
            $lngF = array_search($lng, $row);
            if($latF===false || $lngF===false){
              return Response::json([ "error" => "Falta parametro 'lat' o 'lng'"]);
            }
            unset($row[$latF]);
            unset($row[$lngF]);
            $HEAD = $row;
            $idLayer = DB::table('users_layers')->insertGetId(
                ['id_user' => $userId, 'name_layer' => $NAME, 'pin_url' => $pin],
                'id_layer'
            );
          }else{
            $la = $row[$latF];
            $ln = $row[$lngF];
            unset($row[$latF]);
            unset($row[$lngF]);
            $desc = array();
            foreach($HEAD as $k=>$v){
              $desc[] = array($HEAD[$k] => $row[$k]);
            }
            DB::table('users_layers_data')->insert(
                ['id_layer' => $idLayer, 'data_values' => json_encode($desc), 'geom' => DB::raw("ST_SetSRID(ST_Point($ln, $la),4326)::geometry")]
            );
          }
        }
      }
    }
    return Response::json([ "res" => "correcto"]);
  }else{
    return Response::json([ "error" => "File no valido"]);
  }
}]);



/**
  * @SWG\Get(
  *     path="ws/places/",
  *     summary="Obtiene las ubicaciones registradas por el usuario",
  *     description="Obtiene los registros incluido las geometrias ingresadas por el usuario",
  *     operationId="catalog",
  *     tags={"Workspace"},
  *     produces={"application/json"},
  *     @SWG\Response(
  *         response=400,
  *         description="Bad request falta access_token",
  *         @SWG\Schema(
  *           type="object",
  *           additionalProperties={
  *             "type":"integer",
  *             "format":"int32"
  *           }
  *         )
  *     ),
  *     @SWG\Response(
  *         response=200,
  *         description="successful operation",
  *         @SWG\Schema(ref="#/ws/up")
  *     ),
  *   security={{
  *     "access_token":{}
  *   }}
  * )
  */
Route::get('/places', ['middleware' => 'oauth', function() {
  $userId = Authorizer::getResourceOwnerId();
  $sql = "select L.id_layer, name_layer, pin_url, creation_dt, id_data, data_values, st_xmax(geom) x, st_ymax(geom) y
      from users_layers L
      left join users_layers_data D
      on L.id_layer=D.id_layer
      where id_user=$userId and D.id_layer is not null
      order by id_layer";
  $rs = DB::select($sql,[]);
  $places = array();
  $places_data = array();
  $last_layer=-1;
  foreach($rs as $r){
    if($last_layer == -1){
      $last_layer = $r->id_layer;
    }
    $places_data[] = [
      "id_data"=>$r->id_data,
      "data_values"=>$r->data_values,
      "pin_url"=>$r->pin_url,
      "x"=>$r->x,
      "y"=>$r->y
    ];
    if($last_layer != $r->id_layer){
      $last_layer = $r->id_layer;
      $places[] = [
        "id_layer"=>$r->id_layer,
        "name_layer"=>$r->name_layer,
        "data"=>$places_data
      ];
      $places_data = array();
    }
  }
  if(sizeof($places)==0 && sizeof($places_data)>0){
    $places[] = [
      "id_layer"=>$r->id_layer,
      "name_layer"=>$r->name_layer,
      "data"=>$places_data
    ];
  }

  return Response::json(["places"=>$places]);
}]);
