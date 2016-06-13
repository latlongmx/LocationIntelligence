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
      $q = "select p.entidad ent, p.mun, max($COL::int) maximo
        from inegi.censo_resageburb_2010 P,
         inegi.mgn_estados E
        where
          ST_Intersects(E.geom,ST_MakeEnvelope($BOX, 4326))
          and E.cve_ent = P.entidad
          and $COL not in('N/D','*') and $COL is not null
          and mun <> '000'
          and loc <> '0000'
          and ageb <> '0000'
          and mza <> '000'
        group by p.entidad, p.mun
        order by p.entidad, p.mun;";
      $rs = DB::select($q,[]);
      foreach($rs as $r){
        $MAXVALS[] = array("ent" => $r->ent, "mun" => $r->mun, "max" =>$r->maximo);
      }
  }

  //HEAT LAYER
  /*$LAY_MZA_HEAT = new \LayerObj($MAP);
  $LAY_MZA_HEAT->set('name', 'Manzanas_Heat');
  $LAY_MZA_HEAT->set('connection', 'Manzanas_Points');
  $LAY_MZA_HEAT->set('type', MS_LAYER_RASTER);
  $LAY_MZA_HEAT->set("status", MS_ON);
  #$LAY_MZA_HEAT->setConnectionType(MS_KERNELDENSITY);
  $LAY_MZA_HEAT->setConnectionType(MS_RASTER);
  $LAY_MZA_HEAT->setMetaData('wms_extent', "-118.407653808594 14.532097816008417 -86.7086486816406 32.71865463170993");
  $LAY_MZA_HEAT->setMetaData('wms_srs', "EPSG:4326");
  $LAY_MZA_HEAT->setMetaData('wms_title', 'Manzanas_Heat');
  $LAY_MZA_HEAT->setProcessing("RANGE_COLORSPACE=HSL");
  $LAY_MZA_HEAT->setProcessing("KERNELDENSITY_RADIUS=20");
  $LAY_MZA_HEAT->setProcessing("KERNELDENSITY_ATTRIBUTE=pbvar");
  $LAY_MZA_HEAT->setProcessing("KERNELDENSITY_COMPUTE_BORDERS=ON");
  $LAY_MZA_HEAT->setProcessing("KERNELDENSITY_NORMALIZATION=AUTO");
  $LAY_MZA_HEAT->offsite->setRGB(0,0,0);
  $HEAT_class = new \ClassObj( $LAY_MZA_HEAT );
  $stl1 = new \StyleObj( $HEAT_class );
  $stl1->set("COLORRANGE",'"#0000ff00"  "#0000ffff"');*/


  //Configurar Layer con puntos de las manzanas (ST_PointOnSurface)
  /*$LAY_MZA = getLayerObjConfig($MAP, 'Manzanas_Points');
  $qry_data = "geom from (select gid, entidad ent, ".
      "(CASE WHEN $COL not in('N/D','*') and $COL is not null THEN $COL::int ELSE 0 END)::int AS pbvar, ".
      " ST_PointOnSurface(geom) geom from inegi.pobviv2010 where ST_Intersects(geom,!BOX!) ) as T using unique gid using srid=4326";
  $LAY_MZA->set('data', $qry_data);
  $LAY_MZA->set('type', MS_LAYER_POINT);*/

  //Layer Manzanas Sin Estilo
  $LAY_SinStyle = getLayerObjConfig($MAP, 'Manzanas_sin');
  $qry_data = "geom from (select gid, entidad ent, mun, ".
      "(CASE WHEN $COL not in('N/D','*') and $COL is not null THEN $COL::int ELSE 0 END)::int AS pbvar, ".
      "geom from inegi.pobviv2010 where ST_Intersects(geom,!BOX!) ) as T using unique gid using srid=4326";
  $LAY_SinStyle->set('data', $qry_data);
  $LAY_SinStyle->set('type', MS_LAYER_POLYGON);
  $LAY_SinStyle->set('labelitem', "pbvar");
  $class = new \ClassObj( $LAY_SinStyle );
  $style = new \StyleObj( $class );
  $style->color->setHex("#ffff99");
  $style->set("opacity",100);
  $label = new \labelObj();
  $label->color->setRGB(0,0,0);
	$label->set("size", 8);
  $class->addLabel($label);
	//$class->label->set("font", "arial");


  //Layer Manzanas
  $LAY = getLayerObjConfig($MAP, 'Manzanas');
  $qry_data = "geom from (select gid, entidad ent, mun, ".
      "(CASE WHEN $COL not in('N/D','*') and $COL is not null THEN $COL::int ELSE 0 END)::int AS pbvar, ".
      "geom from inegi.pobviv2010 where ST_Intersects(geom,!BOX!) ) as T using unique gid using srid=4326";
  $LAY->set('data', $qry_data);
  $LAY->set('type', MS_LAYER_POLYGON);

  //Generar estilos
  foreach ($MAXVALS as $mx){
    $MAXVALUE = (int)$mx["max"];
    $ENT = $mx["ent"];
    $MUN = $mx["mun"];
    $GG = round($MAXVALUE/$GROUPS);

    $r=$MAXVALUE;
    $r2=1;
    $i=1;
    while($i<=$GROUPS){ #$MAXVALUE){
      $r2 = $GG*$i;
      $class = new \ClassObj( $LAY );
      $class->setExpression("([pbvar] < ".$r2." && '[ent]'='".$ENT."' && '[mun]'='".$MUN."')");
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
