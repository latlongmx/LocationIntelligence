<?php
#sudo git pull https://joystor@bitbucket.org/joystor/walmex-test.git master
$dbconn = pg_connect("host=walmex.cdpzlqvniluk.us-west-1.rds.amazonaws.com port=5432 dbname=walmex user=walmex password=,F'ZB4}<+tsD<75*")
       or die('connection failed');

$lat=$_POST['lat'];
$lng=$_POST['lng'];
$buf=$_POST['buf'];

/*
| places | degrees    | distance |
| ------ | ---------- | -------- |
| 0      | 1.0        | 111 km   |
| 1      | 0.1        | 11.1 km  |
| 2      | 0.01       | 1.11 km  |
| 3      | 0.001      | 111 m    |
| 4      | 0.0001     | 11.1 m   |
| 5      | 0.00001    | 1.11 m   |
| 6      | 0.000001   | 0.111 m  |
| 7      | 0.0000001  | 1.11 cm  |
| 8      | 0.00000001 | 1.11 mm  |*/

function array2GeoJSON($arr){
  $features = array();
  foreach($arr as $r){
    $properties = array();
    $geometry = null;
    foreach($r as $k=>$v){
      if($k=="geometry"){
        $geometry = json_decode($v);
      }else{
        $properties[$k]=$v;
      }
    }
    $features[] = array("type"=>"Feature","properties"=>$properties,"geometry"=>$geometry);
  }

  $res = array(
    "type"=> "FeatureCollection",
    "features"=> $features
  );
  return $res;
}


$sql="";
if(!isset($_POST['vr'])){
  /*"SELECT row_to_json(fc)
   FROM (
     SELECT 'FeatureCollection' As type, array_to_json(array_agg(f)) As features
     FROM (SELECT 'Feature' As type
      , ST_AsGeoJSON(lg.geom)::json As geometry
      , row_to_json((SELECT l FROM (SELECT id_red, tipo_vial, nombre, codigo, cond_pav, recubri, carriles, estatus, condicion, nivel, peaje, administra, jurisdi,circula, escala_vis, velocidad, union_ini, union_fin, longitud, ancho,fecha_act, calirepr) As l
        )) As properties
     FROM inegi.rnc_red_vial_2015 As lg WHERE ST_DWithin(geom, ST_SetSRID(ST_Point($lng, $lat),4326),$buf) ) As f
  )  As fc;"*/
  ##RNC
  $sql = "SELECT 'rnc' tip_lay, id_red, tipo_vial, nombre, codigo, cond_pav, recubri, carriles, estatus, condicion, nivel, peaje, administra, jurisdi,circula, escala_vis, velocidad, union_ini, union_fin, longitud, ancho,fecha_act, calirepr,
          ST_AsGeoJSON(lg.geom)::json As geometry
  FROM inegi.rnc_red_vial_2015 As lg WHERE ST_DWithin(geom, ST_SetSRID(ST_Point($lng, $lat),4326),$buf)";
  $rs = pg_query($sql) or die('Query failed: ' . pg_last_error());
  $arr = array();
  while ($row = pg_fetch_assoc($rs)) {
    $arr[] = $row;
  }

  #EJES viales
  $sql = "SELECT 'ejes' tip_lay, cvegeo, cvevial, cveseg, nomvial, tipovial, cve_ent, cve_loc, cve_mun, ambito, sentido,
          ST_AsGeoJSON(lg.geom)::json As geometry
      FROM inegi.inter15_vias As lg WHERE ST_DWithin(geom, ST_SetSRID(ST_Point($lng, $lat),4326),$buf)";
  $rs = pg_query($sql) or die('Query failed: ' . pg_last_error());
  while ($row = pg_fetch_assoc($rs)) {
    $arr[] = $row;
  }

  #DENUE
  $sql = "SELECT 'denue' tip_lay, id, nom_estab, raz_social, codigo_act, nombre_act, per_ocu, tipo_vial, nom_vial, tipo_v_e_1, nom_v_e_1, tipo_v_e_2, nom_v_e_2, tipo_v_e_3, nom_v_e_3, numero_ext, letra_ext, edificio, edificio_e, numero_int, letra_int, tipo_asent, nomb_asent, tipocencom, nom_cencom, num_local, cod_postal, cve_ent, entidad, cve_mun, municipio, cve_loc, localidad, ageb, manzana, telefono, correoelec, www, tipounieco, latitud, longitud, fecha_alta,
          ST_AsGeoJSON(geom)::json As geometry
      FROM inegi.denue_2016 WHERE ST_DWithin(geom, ST_SetSRID(ST_Point($lng, $lat),4326),$buf)";
  $rs = pg_query($sql) or die('Query failed: ' . pg_last_error());
  while ($row = pg_fetch_assoc($rs)) {
    $arr[] = $row;
  }

  $res = array2GeoJSON($arr);

}else{
  if($_POST['vr']==="mza"){
    $sql = "SELECT CR.entidad,CR.nom_ent,CR.mun,CR.nom_mun,CR.loc,CR.nom_loc,CR.ageb,CR.mza,CR.pobtot,CR.pobmas,CR.pobfem,
        CR.p_0a2,CR.p_0a2_m,CR.p_0a2_f,CR.p_3ymas,CR.p_3ymas_m,CR.p_3ymas_f,CR.p_5ymas,CR.p_5ymas_m,CR.p_5ymas_f,
        CR.p_12ymas,CR.p_12ymas_m,CR.p_12ymas_f,CR.p_15ymas,CR.p_15ymas_m,CR.p_15ymas_f,CR.p_18ymas,CR.p_18ymas_m,
        CR.p_18ymas_f,CR.p_3a5,CR.p_3a5_m,CR.p_3a5_f,CR.p_6a11,CR.p_6a11_m,CR.p_6a11_f,CR.p_8a14,CR.p_8a14_m,
        CR.p_8a14_f,CR.p_12a14,CR.p_12a14_m,CR.p_12a14_f,CR.p_15a17,CR.p_15a17_m,CR.p_15a17_f,CR.p_18a24,CR.p_18a24_m,
        CR.p_18a24_f,CR.p_15a49_f,CR.p_60ymas,CR.p_60ymas_m,CR.p_60ymas_f,CR.rel_h_m,CR.pob0_14,CR.pob15_64,CR.pob65_mas,
        CR.prom_hnv,CR.pnacent,CR.pnacent_m,CR.pnacent_f,CR.pnacoe,CR.pnacoe_m,CR.pnacoe_f,CR.pres2005,CR.pres2005_m,
        CR.pres2005_f,CR.presoe05,CR.presoe05_m,CR.presoe05_f,CR.p3ym_hli,CR.p3ym_hli_m,CR.p3ym_hli_f,CR.p3hlinhe,
        CR.p3hlinhe_m,CR.p3hlinhe_f,CR.p3hli_he,CR.p3hli_he_m,CR.p3hli_he_f,CR.p5_hli,CR.p5_hli_nhe,CR.p5_hli_he,CR.phog_ind,CR.pcon_lim,CR.pclim_mot,CR.pclim_vis,CR.pclim_leng,CR.pclim_aud,CR.pclim_mot2,CR.pclim_men,CR.pclim_men2,CR.psin_lim,CR.p3a5_noa,CR.p3a5_noa_m,CR.p3a5_noa_f,CR.p6a11_noa,CR.p6a11_noam,CR.p6a11_noaf,CR.p12a14noa,CR.p12a14noam,CR.p12a14noaf,CR.p15a17a,CR.p15a17a_m,CR.p15a17a_f,CR.p18a24a,CR.p18a24a_m,CR.p18a24a_f,CR.p8a14an,CR.p8a14an_m,CR.p8a14an_f,CR.p15ym_an,CR.p15ym_an_m,CR.p15ym_an_f,CR.p15ym_se,CR.p15ym_se_m,CR.p15ym_se_f,CR.p15pri_in,CR.p15pri_inm,CR.p15pri_inf,CR.p15pri_co,CR.p15pri_com,CR.p15pri_cof,CR.p15sec_in,CR.p15sec_inm,CR.p15sec_inf,CR.p15sec_co,CR.p15sec_com,CR.p15sec_cof,CR.p18ym_pb,CR.p18ym_pb_m,CR.p18ym_pb_f,CR.graproes,CR.graproes_m,CR.graproes_f,CR.pea,CR.pea_m,CR.pea_f,CR.pe_inac,CR.pe_inac_m,CR.pe_inac_f,CR.pocupada,CR.pocupada_m,CR.pocupada_f,CR.pdesocup,CR.pdesocup_m,CR.pdesocup_f,CR.psinder,CR.pder_ss,CR.pder_imss,CR.pder_iste,CR.pder_istee,CR.pder_segp,CR.p12ym_solt,CR.p12ym_casa,CR.p12ym_sepa,CR.pcatolica,CR.pncatolica,CR.potras_rel,CR.psin_relig,CR.tothog,CR.hogjef_m,CR.hogjef_f,CR.pobhog,CR.phogjef_m,CR.phogjef_f,CR.vivtot,CR.tvivhab,CR.tvivpar,CR.vivpar_hab,CR.tvivparhab,CR.vivpar_des,CR.vivpar_ut,CR.ocupvivpar,CR.prom_ocup,CR.pro_ocup_c,CR.vph_pisodt,CR.vph_pisoti,CR.vph_1dor,CR.vph_2ymasd,CR.vph_1cuart,CR.vph_2cuart,CR.vph_3ymasc,CR.vph_c_elec,CR.vph_s_elec,CR.vph_aguadv,CR.vph_aguafv,CR.vph_excsa,CR.vph_drenaj,CR.vph_nodren,CR.vph_c_serv,CR.vph_snbien,CR.vph_radio,CR.vph_tv,CR.vph_refri,CR.vph_lavad,CR.vph_autom,CR.vph_pc,CR.vph_telef,CR.vph_cel,CR.vph_inter,
        ST_AsGeoJSON(lg.geom)::text As geometry
      FROM inegi.inter15_manzanas As lg
      LEFT JOIN  inegi.censo_resageburb_2010 CR
      ON lg.CVEGEO=(CR.entidad||CR.mun||CR.loc||CR.ageb||CR.mza)
      WHERE ST_DWithin(lg.geom, ST_SetSRID(ST_Point($lng, $lat),4326),$buf) and CR.entidad is not null";
  }else if($_POST['vr']==="denue"){
  }

  $rs = pg_query($sql) or die('Query failed: ' . pg_last_error());
  $arr = array();
  while ($row = pg_fetch_assoc($rs)) {
    $arr[] = $row;
  }
  $res = array2GeoJSON($arr);
}



//curl -X POST -d "b=feature/db-testing" http://52.8.211.37/deploy/d.php
// http://52.8.211.37/deploy/walmex/
header('Content-Type: application/json');
echo json_encode($res);
?>
