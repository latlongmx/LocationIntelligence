<?php

/**
  * @SWG\Get(
  *     path="/catalog",
  *     summary="Catalogo del API Geo Dinamico",
  *     description="Regresa el catalogo de las tablas disponibles para analsis",
  *     operationId="catalog",
  *     tags={"catalog"},
  *     produces={"application/json"},
  *     @SWG\Parameter(
  *         t="Tipo de catalogo, (vacio|denue_cods)"
  *     ),
  *     @SWG\Response(
  *         response=200,
  *         description="successful operation",
  *         @SWG\Schema(ref="#/catalog")
  *     ),
  * )
  */
Route::get('/catalog?t={tip_catalog}', function($tip_catalog){
  $cat = array();

  if( $tip_catalog == "denue_cods" ){
    $sql = "select codigo_act, codigo_act from inegi.cat_denue_cod_act where codigo_act like '46%' or codigo_act like '43%';";
    $rs = DB::select($sql,[]);
    foreach($rs as $r){
      $sch = $reg->sch;
      $cat[] = array("cod"=> $r->codigo_act, "nom"=> $r->nombre_act);
    }
  } else {
    $sql = "select sch, tbl, string_agg(cols, ', ') cols
          from(
            select C1.table_schema sch,C1.table_name tbl,C1.column_name cols
            from information_schema.columns C1
            left join information_schema.columns C2
            on C1.table_schema=C2.table_schema and C1.table_name=C2.table_name
            where C1.column_name not in ('gid','geom')
             and C2.column_name='geom'
             and C1.table_schema not in ('catastro')
            group by C1.table_schema,C1.table_name,C1.column_name
            order by C1.table_schema,C1.table_name,C1.column_name
          )T
          group by sch,tbl;";
    $rs = DB::select($sql,[]);
    foreach($rs as $reg){
      $sch = $reg->sch;
      if($sch=="public"){
        $sch = "";
      }
      $cat[] = array("schema"=> $sch, "table"=> $reg->tbl, "columns" => $reg->cols);
    }
  }

  return Response::json(["meta"=>$cat]);
});
