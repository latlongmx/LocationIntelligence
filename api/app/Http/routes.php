<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('/', function () {
    return view('api.latlong.mx');
});

/*
$app->post('login', function() use($app) {
    $credentials = app()->make('request')->input("credentials");
    return $app->make('App\Auth\Proxy')->attemptLogin($credentials);
});

$app->post('refresh-token', function() use($app) {
    return $app->make('App\Auth\Proxy')->attemptRefresh();
});
*/
Route::post('oa/register',function(){
  $usr = Request::input('u');
  $pwd = Request::input('p');
  $mail = Request::input('ml');

  $user = new App\User();
  $user->name=$usr;
  $user->email=$mail;
  $user->password = \Illuminate\Support\Facades\Hash::make($pwd);
  //$pwd; //\Illuminate\Support\Facades\Hash::make(“password”);
  $user->save();

  $id = DB::table('oauth_clients')->insertGetId(
      array(
        'id' => hash("md5",$usr),
        'secret' => substr(hash("sha256",$pwd),0,40),
        'name' => $usr,
        'created_at' => date('Y-m-d H:i:s')
      )
  );
  return Response::json(["id" => $id]);
});

Route::post('oa/accesstk', function() {
    return Response::json(Authorizer::issueAccessToken());
});


/*$app->group(['prefix' => 'api', 'middleware' => 'oauth'], function($app)
{
    $app->get('resource', function() {
        return response()->json([
            "id" => 1,
            "name" => "A resource"
        ]);
    });
});
*/

Route::group(['prefix'=>'api','before' => 'oauth'], function()
{
    Route::get('/status', function(){
      return Response::json(["status"=>"ok"]);
    });
});
