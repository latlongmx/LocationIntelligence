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
  $sql = "SELECT row_to_json(tmp) json
      FROM
      (
        SELECT id_layer, name_layer, creation_dt, pin_url,
          '{}' as data
        FROM users_layers L
        WHERE id_user=$userId
        and is_competence is ".($competence!==""?"true":"false")."
        and is_query is false
        ".($id!=""?" and id_layer=".$id:"")."
        ORDER BY id_layer
      ) tmp;";
  $rs = DB::select($sql,[]);
  $places = array();
  foreach($rs as $r){
    $places[] = json_decode($r->json);
  }


/* -- MEJORAR QUERY BY EXPLAIN
SELECT row_to_json(tmp) json
FROM
(
  SELECT id_layer, name_layer, creation_dt,
    (
      select array_to_json(array_agg(row_to_json(d)))
      from (
        select D.gid id_data, json_agg(json_build_object('nom_estab', D.nom_estab, 'nombre_act', D.nombre_act)) data_values, st_xmax(D.geom) x, st_ymax(D.geom) y
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
        group by D.gid
        limit 10
      ) d
    ) as data
  FROM (
    SELECT *, regexp_split_to_array(bbox_filter,',') bbox  FROM users_layers WHERE id_user=4 and is_competence is true and is_query is true
  ) L
  ORDER BY L.id_layer
) tmp;
*/
  if($competence!==""){

    $sql_denue = "SELECT
          D.gid, D.nom_estab, D.nombre_act,
          st_xmax(D.geom) x, st_ymax(D.geom) y
        from inegi.denue_2016 D,
             inegi.mgn_estados E
        where
            ST_Intersects(E.geom,
                ST_MakeEnvelope( L.bbox[1]::numeric, L.bbox[2]::numeric, L.bbox[3]::numeric, L.bbox[4]::numeric, 4326))
            and E.cve_ent = D.cve_ent
            &FILTER& ";
/*
(
  SELECT array_to_json(array_agg(row_to_json(d)))
    from (
      SELECT
            tt.gid id_data,
            json_agg(
              json_build_object('nom_estab', tt.nom_estab, 'nombre_act', tt.nombre_act)
            ) data_values,
            tt.x, tt.y
      FROM (
        ".str_replace("&FILTER&",
            " and substring(L.query_filter,1,4)<>'cod:'
              and D.tsv @@ to_tsquery(unaccent(L.query_filter)) "
            ,$sql_denue)."
        UNION
        ".str_replace("&FILTER&",
            " and substring(L.query_filter,1,4)='cod:'
             and D.codigo_act like substring(L.query_filter,5) ||'%' "
            ,$sql_denue)."
      ) tt
      group by tt.gid,tt.x, tt.y
  ) d
)
*/
    $sql = "SELECT row_to_json(tmp) json
        FROM
        (
          SELECT id_layer, name_layer, creation_dt, pin_url,
            '{}' as data
          FROM (
            SELECT *, regexp_split_to_array(bbox_filter,',') bbox
            FROM users_layers
            WHERE id_user=$userId and is_competence is true and is_query is true
            ".($id!=""?" and id_layer=".$id:"")."
          ) L
          ORDER BY L.id_layer
        ) tmp;";
    $rs = DB::select($sql,[]);
    foreach($rs as $r){
      $places[] = json_decode($r->json);
    }
  }

  return Response::json(["places"=>$places, "sql" => $sql]);
}]);
