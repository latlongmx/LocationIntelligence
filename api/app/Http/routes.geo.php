<?php

/**
 * @SWG\Swagger(
 *     schemes={"http"},
 *     host="52.8.211.37",
 *     basePath="/api.walmex.latlong.mx/geo",
 *     @SWG\Info(
 *         version="1.0.0",
 *         title="LATLONG API GEO",
 *         description="LATLONG API con diferentes analisis",
 *         termsOfService="http://helloreverb.com/terms/",
 *         @SWG\Contact(
 *             email="admin@latlong.mx"
 *         ),
 *         @SWG\License(
 *             name="Apache 2.0",
 *             url="http://www.apache.org/licenses/LICENSE-2.0.html"
 *         )
 *     ),
 *     @SWG\ExternalDocumentation(
 *         description="Find out more about Swagger",
 *         url="http://swagger.io"
 *     )
 * )
 */



Route::group(['prefix'=>'geo','before' => 'oauth', 'middleware' => 'cors'], function()
{

  /**
     * @SWG\Get(
     *     path="/status/",
     *     summary="Estatus de la API",
     *     description="Returns a single pet",
     *     operationId="getPetById",
     *     tags={"status"},
     *     produces={"application/json"},
     *     @SWG\Response(
    *         response=200,
    *         description="successful operation",
    *         @SWG\Schema(ref="#/status")
    *     ),
     * )
     */
    Route::get('/status', function(){
      return Response::json(["status"=>"ok"]);
    });

//102.56836 22.59373
//http://localhost:8000/geo/dw/LAYER/21.85996530350067/-102.2827363014221/100
    Route::get('/dw/{layer}/{lat}/{lng}/{meters}', function($layer, $lat, $lng, $meters){
        $mts = meters2dec($meters);

        $TBL = "";
        $INFO = "";
        $GEOM = "";
        $SPLIT = "";
        $GEOM_CUT_LINE = "ST_AsGeoJSON( ( (ST_Dump(ST_Intersection(S.geom, A.geom))).geom )::geometry)::json As geometry";
        $GEOM_INTERSECT = "ST_AsGeoJSON(A.geom)::json As geometry";
        $WHERE = "";
        switch ($layer) {
          case 'rnc':
            $SPLIT = " WITH split AS ( SELECT (st_buffer(ST_SetSRID(ST_Point($lng, $lat),4326) , $mts))::geometry geom ) ";
            $TBL = "inegi.rnc_red_vial_2015 As A, split S";
            $INFO = "'rnc' tip_lay, id_red, tipo_vial, nombre, codigo, cond_pav, recubri, carriles, estatus, condicion,
                nivel, peaje, administra, jurisdi,circula, escala_vis, velocidad, union_ini, union_fin, longitud,
                ancho,fecha_act, calirepr,";
            $GEOM = $GEOM_CUT_LINE;
            break;
          case 'denue':
            $SPLIT = "";
            $TBL = "inegi.denue_2016 As A";
            $INFO = "'denue' tip_lay, nom_estab, raz_social, codigo_act, nombre_act, per_ocu, tipo_vial, nom_vial, tipo_v_e_1, nom_v_e_1,
                tipo_v_e_2, nom_v_e_2, tipo_v_e_3, nom_v_e_3, numero_ext, letra_ext, edificio, edificio_e, numero_int,
                letra_int, tipo_asent, nomb_asent, tipocencom, nom_cencom, num_local, cod_postal, cve_ent, entidad,
                cve_mun, municipio, cve_loc, localidad, ageb, manzana, telefono, correoelec, www, tipounieco, fecha_alta, ";
            $GEOM = $GEOM_INTERSECT;
            break;
          case 'mza':
            $SPLIT = "";
            $TBL = "inegi.inter15_manzanas As A
                LEFT JOIN  inegi.censo_resageburb_2010 CR
                ON A.CVEGEO=(CR.entidad||CR.mun||CR.loc||CR.ageb||CR.mza) ";
            $INFO = "'mza' tip_lay, CR.entidad,CR.nom_ent,CR.mun,CR.nom_mun,CR.loc,CR.nom_loc,CR.ageb,CR.mza,CR.pobtot,CR.pobmas,CR.pobfem,
                CR.p_0a2,CR.p_0a2_m,CR.p_0a2_f,CR.p_3ymas,CR.p_3ymas_m,CR.p_3ymas_f,CR.p_5ymas,CR.p_5ymas_m,CR.p_5ymas_f,
                CR.p_12ymas,CR.p_12ymas_m,CR.p_12ymas_f,CR.p_15ymas,CR.p_15ymas_m,CR.p_15ymas_f,CR.p_18ymas,CR.p_18ymas_m,
                CR.p_18ymas_f,CR.p_3a5,CR.p_3a5_m,CR.p_3a5_f,CR.p_6a11,CR.p_6a11_m,CR.p_6a11_f,CR.p_8a14,CR.p_8a14_m,
                CR.p_8a14_f,CR.p_12a14,CR.p_12a14_m,CR.p_12a14_f,CR.p_15a17,CR.p_15a17_m,CR.p_15a17_f,CR.p_18a24,CR.p_18a24_m,
                CR.p_18a24_f,CR.p_15a49_f,CR.p_60ymas,CR.p_60ymas_m,CR.p_60ymas_f,CR.rel_h_m,CR.pob0_14,CR.pob15_64,CR.pob65_mas,
                CR.prom_hnv,CR.pnacent,CR.pnacent_m,CR.pnacent_f,CR.pnacoe,CR.pnacoe_m,CR.pnacoe_f,CR.pres2005,CR.pres2005_m,
                CR.pres2005_f,CR.presoe05,CR.presoe05_m,CR.presoe05_f,CR.p3ym_hli,CR.p3ym_hli_m,CR.p3ym_hli_f,CR.p3hlinhe,
                CR.p3hlinhe_m,CR.p3hlinhe_f,CR.p3hli_he,CR.p3hli_he_m,CR.p3hli_he_f,CR.p5_hli,CR.p5_hli_nhe,CR.p5_hli_he,CR.phog_ind,CR.pcon_lim,CR.pclim_mot,CR.pclim_vis,CR.pclim_leng,CR.pclim_aud,CR.pclim_mot2,CR.pclim_men,CR.pclim_men2,CR.psin_lim,CR.p3a5_noa,CR.p3a5_noa_m,CR.p3a5_noa_f,CR.p6a11_noa,CR.p6a11_noam,CR.p6a11_noaf,CR.p12a14noa,CR.p12a14noam,CR.p12a14noaf,CR.p15a17a,CR.p15a17a_m,CR.p15a17a_f,CR.p18a24a,CR.p18a24a_m,CR.p18a24a_f,CR.p8a14an,CR.p8a14an_m,CR.p8a14an_f,CR.p15ym_an,CR.p15ym_an_m,CR.p15ym_an_f,CR.p15ym_se,CR.p15ym_se_m,CR.p15ym_se_f,CR.p15pri_in,CR.p15pri_inm,CR.p15pri_inf,CR.p15pri_co,CR.p15pri_com,CR.p15pri_cof,CR.p15sec_in,CR.p15sec_inm,CR.p15sec_inf,CR.p15sec_co,CR.p15sec_com,CR.p15sec_cof,CR.p18ym_pb,CR.p18ym_pb_m,CR.p18ym_pb_f,CR.graproes,CR.graproes_m,CR.graproes_f,CR.pea,CR.pea_m,CR.pea_f,CR.pe_inac,CR.pe_inac_m,CR.pe_inac_f,CR.pocupada,CR.pocupada_m,CR.pocupada_f,CR.pdesocup,CR.pdesocup_m,CR.pdesocup_f,CR.psinder,CR.pder_ss,CR.pder_imss,CR.pder_iste,CR.pder_istee,CR.pder_segp,CR.p12ym_solt,CR.p12ym_casa,CR.p12ym_sepa,CR.pcatolica,CR.pncatolica,CR.potras_rel,CR.psin_relig,CR.tothog,CR.hogjef_m,CR.hogjef_f,CR.pobhog,CR.phogjef_m,CR.phogjef_f,CR.vivtot,CR.tvivhab,CR.tvivpar,CR.vivpar_hab,CR.tvivparhab,CR.vivpar_des,CR.vivpar_ut,CR.ocupvivpar,CR.prom_ocup,CR.pro_ocup_c,CR.vph_pisodt,CR.vph_pisoti,CR.vph_1dor,CR.vph_2ymasd,CR.vph_1cuart,CR.vph_2cuart,CR.vph_3ymasc,CR.vph_c_elec,CR.vph_s_elec,CR.vph_aguadv,CR.vph_aguafv,CR.vph_excsa,CR.vph_drenaj,CR.vph_nodren,CR.vph_c_serv,CR.vph_snbien,CR.vph_radio,CR.vph_tv,CR.vph_refri,CR.vph_lavad,CR.vph_autom,CR.vph_pc,CR.vph_telef,CR.vph_cel,CR.vph_inter, ";
            $GEOM = $GEOM_INTERSECT;
            $WHERE = " and CR.entidad is not null";
            break;

          default:
            $TBL = "inegi.rnc_red_vial_2015 As A, split S";
            $INFO = "'rnc' tip_lay, id_red, tipo_vial, nombre, codigo, cond_pav, recubri, carriles, estatus, condicion, nivel, peaje, administra, jurisdi,circula, escala_vis, velocidad, union_ini, union_fin, longitud, ancho,fecha_act, calirepr,";
            $GEOM = $GEOM_CUT_LINE;
            break;
        }

        $sql = " $SPLIT
              SELECT $INFO $GEOM
              FROM $TBL
              WHERE ST_DWithin(A.geom, ST_SetSRID(ST_Point($lng, $lat),4326), $mts)
                $WHERE";
        $rs = DB::select($sql,[]);
        $geo = array2GeoJSON($rs);
        return Response::json([
          "info"=>"",
          "geojson"=>$geo,
          "sql" => "$sql"
        ]);
    });
});
