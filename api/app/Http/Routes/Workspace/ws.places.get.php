<?php

/**
  * @SWG\Get(
  *     path="/ws/places?p={id_ubicacion}",
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
  *     @SWG\Parameter(
  * 		   	name="id_layer",
  * 			  in="path",
  * 			  required=false,
  * 			  type="integer",
  * 			  description="id de la ubicacion a actualizar si no se manda se obtendran todos"
  *     ),
  *     @SWG\Parameter(
  * 		   	name="competence",
  * 			  in="path",
  * 			  required=false,
  * 			  type="integer",
  * 			  description="si se requiere traer todos las competencias competence=1"
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
  $id = Input::get('id_layer', '');
  $competence = Input::get('competence', '');
  /*$sql = "select L.id_layer, name_layer, pin_url, creation_dt, id_data, data_values, st_xmax(geom) x, st_ymax(geom) y
      from users_layers L
      left join users_layers_data D
      on L.id_layer=D.id_layer
      where id_user=$userId and D.id_layer is not null
      ".($id!==""?" and L.id_layer=".$id:"")."is_Query
      and L.is_competence is ".($competence!==""?"true":"false")."
      order by id_layer";*/
  $sql = "SELECT row_to_json(tmp) json
      FROM
      (
        SELECT id_layer, name_layer, creation_dt,
          (
            select array_to_json(array_agg(row_to_json(d)))
            from (
              select id_data, data_values, L.pin_url, st_xmax(geom) x, st_ymax(geom) y
              from users_layers_data
              where id_layer=L.id_layer
              order by id_data
            ) d
          ) as data
        FROM users_layers L
        WHERE id_user=$userId
        and is_competence is ".($competence!==""?"true":"false")."
        and is_query is false
        ORDER BY id_layer
      ) tmp;";
  $rs = DB::select($sql,[]);
  $places = array();
  foreach($rs as $r){
    $places[] = json_decode($r->json);
    /*$places_data[] = [
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
    }*/
  }
  /*if(sizeof($places)==0 && sizeof($places_data)>0){
    $places[] = [
      "id_layer"=>$r->id_layer,
      "name_layer"=>$r->name_layer,
      "data"=>$places_data
    ];
  }*/



  if($competence!==""){
    $sql = "SELECT row_to_json(tmp) json
        FROM
        (
          SELECT id_layer, name_layer, creation_dt,
            (
              select array_to_json(array_agg(row_to_json(d)))
              from (
                select D.gid id_data, json_build_object('nom_estab', D.nom_estab, 'nombre_act', D.nombre_act) data_values, st_xmax(D.geom) x, st_ymax(D.geom) y
                from inegi.denue_2016 D,
                     inegi.mgn_estados E
                where
                    ST_Intersects(E.geom,
                        ST_MakeEnvelope(
                            L.bbox[1]::numeric,
                            L.bbox[2]::numeric,
                            L.bbox[3]::numeric,
                            L.bbox[4]::numeric,
                        4326))
                    and E.cve_ent = D.cve_ent
                    and D.nom_estab ilike '%'|| L.query_filter ||'%'
                order by gid
              ) d
            ) as data
          FROM (
            SELECT *, regexp_split_to_array(bbox_filter,',') bbox
            FROM users_layers
            WHERE id_user=$userId and is_competence is true and is_query is true
          ) L
          ORDER BY L.id_layer
        ) tmp;";
    $rs = DB::select($sql,[]);
    foreach($rs as $r){
      $places[] = json_decode($r->json);
    }
  }

  return Response::json(["places"=>$places]);
}]);
