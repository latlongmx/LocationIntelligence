<?php

/**
  * @SWG\Post(
  *     path="/ws/places/",
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
  * 			  required=false,
  * 			  type="file",
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
  *     @SWG\Parameter(
  * 		   	name="competence",
  * 			  in="path",
  * 			  required=false,
  * 			  type="integer",
  * 			  description="Si es competencia se requiere el parametro competence=1 si no se manda el parametro por default es false",
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

  $NAME = Input::get('nm');
  if(!isset($NAME)){
    return Response::json([ "error" => "Falta parametro 'nm'"]);
  }

  //Guardar PIN
  $pinURL = '';
  if(Request::file('pin') != null && Request::file('pin')->isValid()){
    $pin = Request::file('pin');
    $pinURL = $pin->getClientOriginalName();
    $path = '/var/www/laravel-storage/pins';
    if(!file_exists($path)) {
      $result = File::makeDirectory($path);
    }
    $path = '/var/www/laravel-storage/pins/' . $userId;
    if(!file_exists($path)) {
      $result = File::makeDirectory($path);
    }
    move_uploaded_file( $pin->getRealPath(), $path.'/'.$pin->getClientOriginalName());
  }

  $idLayer = 0;
  if( Input::get('qf','')!='' && Input::get('qb','')!='' && Input::get('competence','') == "1" ){
    /*LAYER BY QUERY*/
    /*LAYER BY QUERY*/
    /*LAYER BY QUERY*/
    $BBOX = Input::get('qb');
    $FILTER = Input::get('qf');

    $data = [
      'id_user' => $userId,
      'name_layer' => $NAME,
      'pin_url' => $pinURL,
      'is_competence'=>true,
      'is_query'=>true,
      'query_filter'=> $BBOX,
      'bbox_filter'=> $FILTER
    ];
    $idLayer = DB::table('users_layers')->insertGetId( $data, 'id_layer' );
    return Response::json([ "res" => "correcto", "id_layer"=>$idLayer]);

  }else{
    /*LAYER BY FILE*/
    /*LAYER BY FILE*/
    /*LAYER BY FILE*/
    $lat = Input::get('lat');
    if(!isset($lat)){
      return Response::json([ "error" => "Falta parametro 'lat'"]);
    }
    $lng = Input::get('lng');
    if(!isset($lng)){
      return Response::json([ "error" => "Falta parametro 'lng'"]);
    }

    $data = [];
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
              $data = ['id_user' => $userId, 'name_layer' => $NAME, 'pin_url' => $pinURL];

              //es competencia
              $competence = Input::get('competence','');
              if($competence == "1"){
                $data = array_merge($data, array("is_competence"=>true));
              }
              $idLayer = DB::table('users_layers')->insertGetId( $data, 'id_layer' );
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
                ['id_layer' => $idLayer, 'data_values' => json_encode($desc),
                 'geom' => DB::raw("ST_SetSRID(ST_Point($ln, $la),4326)::geometry")]
              );
            }
          }
        }
      }
      return Response::json([ "res" => "correcto", "id_layer"=>$idLayer]);
    }else{
      return Response::json([ "error" => "File no valido"]);
    }
  }


}]);
