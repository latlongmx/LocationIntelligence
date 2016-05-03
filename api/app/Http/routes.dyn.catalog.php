<?php

Route::get('/meta', function(){
  $sql = "select sch, tbl, string_agg(cols, ', ') cols
        from(
          select C1.table_schema sch,C1.table_name tbl,C1.column_name cols
          from information_schema.columns C1
          left join information_schema.columns C2
          on C1.table_schema=C2.table_schema and C1.table_name=C2.table_name
          where C1.column_name not in ('gid','geom')
           and C2.column_name='geom'
          group by C1.table_schema,C1.table_name,C1.column_name
          order by C1.table_schema,C1.table_name,C1.column_name
        )T
        group by sch,tbl;";
  $rs = DB::select($sql,[]);
  $cat = array();
  foreach($rs as $reg){
    $sch = $reg->sch;
    if($sch=="public"){
      $sch = "";
    }
    $cat[] = array("schema"=> $sch, "table"=> $reg->tbl, "columns" => $reg->cols);
  }
  return Response::json(["meta"=>$cat]);
});
