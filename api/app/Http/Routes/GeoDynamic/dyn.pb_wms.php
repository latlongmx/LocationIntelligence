<?php


Route::get('/pb_wms', //['middleware' => 'oauth', function() {
function(){

  //$userId = Authorizer::getResourceOwnerId();
  $MAP = getMapObjConfig();

  $req = new \Owsrequestobj();
  $req->loadparams();

  $COL = $req->getValueByName("col");
  $BOX = $req->getValueByName("bbox"); //-99.1461181640625,19.45105402980001,-99.140625,19.456233596018

  $VALUES = array();
  $MAXVALS = array();

  //Grupos de colores
  $GROUPS = 100;
  if($BOX!= ""){

      //MAXIMOS
      $q = "select p.entidad ent, max($COL) maximo
        from inegi.censo_resageburb_2010 P,
         inegi.mgn_estados E
        where
          ST_Intersects(E.geom,ST_MakeEnvelope($BOX, 4326))
          and E.cve_ent = P.entidad
          and $COL not in('N/D','*') and $COL is not null
        group by p.entidad;";
      $rs = DB::select($q,[]);
      foreach($rs as $r){
        $MAXVALS[] = array("ent" => $r->ent, "max" =>$r->maximo);
      }
  }

  $LAY_MZA_HEAT = new \LayerObj($MAP);
  $LAY_MZA_HEAT->set('name', 'Manzanas_Heat');
  $LAY_MZA_HEAT->set('connection', 'Manzanas_Points');
  $LAY_MZA_HEAT->setProcessing("RANGE_COLORSPACE=HSL");
  $LAY_MZA_HEAT->setProcessing("KERNELDENSITY_RADIUS=20");
  $LAY_MZA_HEAT->setProcessing("KERNELDENSITY_ATTRIBUTE=pbvar");
  $LAY_MZA_HEAT->setProcessing("KERNELDENSITY_COMPUTE_BORDERS=ON");
  $LAY_MZA_HEAT->setProcessing("KERNELDENSITY_NORMALIZATION=AUTO");


  //Configurar Layer con puntos de las manzanas (ST_PointOnSurface)
  $LAY_MZA = getLayerObjConfig($MAP, 'Manzanas_Points');
  $qry_data = "geom from (select gid, entidad ent, ".
      "(CASE WHEN $COL not in('N/D','*') and $COL is not null THEN $COL::int ELSE 0 END)::int AS pbvar, ".
      " ST_PointOnSurface(geom) geom from inegi.pobviv2010 where ST_Intersects(geom,!BOX!) ) as T using unique gid using srid=4326";
  $LAY_MZA->set('data', $qry_data);
  $LAY_MZA->set('type', MS_LAYER_POINT);


  //Layer Manzanas
  $LAY = getLayerObjConfig($MAP, 'Manzanas');
  $qry_data = "geom from (select gid, entidad ent, ".
      "(CASE WHEN $COL not in('N/D','*') and $COL is not null THEN $COL::int ELSE 0 END)::int AS pbvar, ".
      "geom from inegi.pobviv2010 where ST_Intersects(geom,!BOX!) ) as T using unique gid using srid=4326";
  $LAY->set('data', $qry_data);
  $LAY->set('type', MS_LAYER_POLYGON);

  //Generar estilos
  foreach ($MAXVALS as $mx){
    $MAXVALUE = (int)$mx["max"];
    $ENT = $mx["ent"];
    $GG = round($MAXVALUE/$GROUPS);

    $r=$MAXVALUE;
    $r2=1;
    $i=1;
    while($i<=$GROUPS){ #$MAXVALUE){
      $r2 = $GG*$i;
      $class = new \ClassObj( $LAY );
      $class->setExpression("([pbvar] < ".$r2." && '[ent]'='".$ENT."')");
      $style = new \StyleObj( $class );
      $ncol = ((($i*100)/$GROUPS)*0.01);
      $col = getColorFromColToCol("ffff99", "ff0000", $ncol );
      $style->color->setHex( "#".$col );
      $style->set("opacity",100);

      $r = $GG*$i;
      $i++;
    }
  }

/*  $class = new \ClassObj( $LAY );
  $style = new \StyleObj( $class );
  $style->color->setHex("#ffff99");
  $style->set("opacity",100);
*/

  ms_ioinstallstdouttobuffer();
  $map_file = storage_path("logs/ms_file.map");
  $MAP->save( $map_file );
  $MAP->owsdispatch($req);

  $contenttype = ms_iostripstdoutbuffercontenttype();
  if (!empty($contenttype)){
    error_log($contenttype);
    if ($req->getValueByName("REQUEST") === "GetCapabilities") {
      $buffer = ms_iogetstdoutbufferstring();
      header("Content-type: application/xml");
      echo $buffer;
    }else{
      header("Content-type: $contenttype");
      ms_iogetStdoutBufferBytes();
    }
  }
  else
      echo "Fail to render!";
  ms_ioresethandlers();

});
