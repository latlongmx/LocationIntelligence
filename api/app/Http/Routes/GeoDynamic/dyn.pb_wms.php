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
  if($BOX!= ""){

/*
select p.entidad, max(pea)
from inegi.pobviv2010 P,
 inegi.mgn_estados E
where
  ST_Intersects(E.geom,ST_MakeEnvelope(-99.1461181640625,19.45105402980001,-99.140625,19.456233596018, 4326))
  and E.cve_ent = P.entidad
GROUP BY p.entidad, pea;

select p.entidad ent, max(pea)
from inegi.censo_resageburb_2010 P,
 inegi.mgn_estados E
where
  ST_Intersects(E.geom,ST_MakeEnvelope(-99.1461181640625,19.45105402980001,-99.140625,19.456233596018, 4326))
  and E.cve_ent = P.entidad
  and pea not in('N/D','*') and pea is not null
group by p.entidad;
*/
      //$q = "SELECT $COL FROM inegi.pobviv2010 where ST_Intersects(geom,ST_MakeEnvelope('$WKT', 4326))";

      //MAXIMOS
      $q = "select p.entidad ent, max($COL) maximo
      from inegi.censo_resageburb_2010 P,
       inegi.mgn_estados E
      where
        ST_Intersects(E.geom,ST_MakeEnvelope($BOX, 4326))
        and E.cve_ent = P.entidad
        and pea not in('N/D','*') and pea is not null
      group by p.entidad;";
      $rs = DB::select($q,[]);
      foreach($rs as $r){
        $MAXVALS[] = array($r-ent => $r->maximo);
      }


      $q = "select E.cvegeo cvegeo, $COL variab
      from inegi.censo_resageburb_2010 P,
       inegi.inter15_manzanas E
      where
        ST_Intersects(E.geom,ST_MakeEnvelope($BOX, 4326))
        and E.cvegeo = P.p.entidad || p.mun || p.loc || p.ageb || p.mza;";
      $rs = DB::select($q,[]);
      foreach($rs as $r){
        $VALUES[] = array($r->cvegeo => $r->variab);
      }
  }

  $LAY = getLayerObjConfig($MAP, 'Manzanas', $COL);
  $LAY->set('data', "geom from (select gid, cvegeo, geom from inegi.inter15_manzanas where ST_Intersects(geom,!BOX!)) as T using unique gid using srid=4326");
  $LAY->set("classitem", "cvegeo");

  foreach ($VALUES as $key => $val){
    $class = new \ClassObj( $LAY );
    $class->setExpression("(\"[cvegeo]\" = \"".$key."\")");
    $style = new \StyleObj( $class );
    if(is_numeric($val)){
      $MAXVAL = array_search( substr($key,0,2), $MAXVALS);
      $v = (((int)$val)*100)/$MAXVAL;
      $v = $v/100;
      $style->color->setHex( '#'.getColorFromColToCol('ffff99', 'ff0000', $v ) );
    }else{
      $style->color->setHex('#ffff99');
    }
    $style->set('opacity',100);
  }

  ms_ioinstallstdouttobuffer();
  #$map->save('map_exmp.map');
  $MAP->owsdispatch($req);

  $contenttype = ms_iostripstdoutbuffercontenttype();
  if (!empty($contenttype)){
      error_log($contenttype);
      if ($req->getValueByName('REQUEST') === 'GetCapabilities') {
          $buffer = ms_iogetstdoutbufferstring();
          header('Content-type: application/xml');
          echo $buffer;
      }else{
          header('Content-type: $contenttype');
          ms_iogetStdoutBufferBytes();
      }
  }
  else
      echo "Fail to render!";
  ms_ioresethandlers();

});
