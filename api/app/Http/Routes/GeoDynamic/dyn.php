<?php

/**
 * @SWG\Swagger(
 *     schemes={"http"},
 *     host="52.8.211.37",
 *     basePath="/api.walmex.latlong.mx/dyn",
 *     @SWG\Info(
 *         version="1.0.0",
 *         title="LATLONG API GEO",
 *         description="LATLONG API Dinamico para analisis geografíco",
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

Route::group(['prefix'=>'dyn', 'before' => 'oauth', 'middleware' => 'cors'], function(){

  //API Documentacion
  require app_path('Http/Routes/GeoDynamic/dyn.apidoc.php');
  //Catalogos
  require app_path('Http/Routes/GeoDynamic/dyn.catalog.php');

});
