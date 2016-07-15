<?php
/** @SWG\Get(
  *     path="/ws/heat/{token_id}",
  *     summary="Obtiene las ubicaciones registradas por el usuario",
  *     description="Obtiene los registros incluido las geometrias ingresadas por el usuario",
  *     operationId="catalog",
  *     tags={"Workspace Heatmap"},
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
  * 		   	name="token_id",
  * 			  in="path",
  * 			  required=false,
  * 			  type="integer",
  * 			  description="Si se envia un id retorna la informacion de ese heatmap, en caso contrario regresa el catalogo de heatmaps guardados por el usuario sin datos"
  *     ),
  *   security={{
  *     "access_token":{}
  *   }}
  * )
  */
Route::get('/heat', ['middleware' => 'oauth', function() {
  $userId = Authorizer::getResourceOwnerId();

  $rs = DB::table('users_heatmaps')
            ->where('id_user', '=', $userId)->get();
  $heats = array();
  foreach($rs as $r){
    $rr = $r;
    if (strpos($r->cods, ',') !== false) {
      $rr = array_merge($rr, array("compuest"=>true));
    }
    $heats[] = $rr;
  }

  return Response::json(["heats"=>$heats]);

});


/*CREATE TABLE users_heatmaps
(
  id_heat serial NOT NULL,
  id_user integer,
  name_heat character varying,
  cods character varying,
  bounds text,
  creation_dt timestamp without time zone DEFAULT timezone('America/Mexico_City'::text, now()),
  CONSTRAINT users_heatmaps_pkey PRIMARY KEY (id_heat),
  CONSTRAINT users_heatmaps_id_user_fkey FOREIGN KEY (id_user)
      REFERENCES users (id) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION
)
WITH (
  OIDS=FALSE
);

CREATE INDEX idx_users_heatmaps
  ON users_heatmaps
  USING btree
  (id_user, id_heat);

*/
