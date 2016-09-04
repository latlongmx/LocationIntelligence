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
    return view('welcome');
    //return view('/');
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
  $tu = "uA";
  try {
    $tu = Request::input('tu','uA');
  } catch (Exception $e) {
  }


  $usrMD5 = hash("md5",$usr);

  $existUser = DB::table('users')
                  ->where("username","=","$usr")
                  ->orWhere("email","=","$mail")
                  ->count();
  $existUseroAuth = DB::table('oauth_clients')->where("id","=","$usrMD5")->count();
  if($existUser > 0 || $existUseroAuth > 0){
    return Response::json(["user_exist" => 1]);
  }


  $user = new App\User();
  $user->username=$usr;
  $user->email=$mail;
  $user->user_type=$tu;
  $user->password = \Illuminate\Support\Facades\Hash::make($pwd);
  //$pwd; //\Illuminate\Support\Facades\Hash::make(“password”);
  $user->save();

  $id = DB::table('oauth_clients')->insertGetId(
      array(
        'id' => $usrMD5,
        'secret' => substr(hash("sha256",$pwd),0,40),
        'name' => $usr,
        'created_at' => date('Y-m-d H:i:s')
      )
  );
  return Response::json(["id" => $id, "user_exist" => 0]);
});

Route::post('oa/accesstk', function() {
  $auth = Authorizer::issueAccessToken();
  $usr = Input::get("username", "");
  $rs = DB::select("select user_type, ftue_showed from users where username='".$usr."'",[]);
  $user_type = "";
  $ftue_showed = "";
  foreach($rs as $r){
    $user_type = $r->user_type;
    $ftue_showed = $r->ftue_showed;
  }

  $res = array_merge_recursive( $auth , ["userType"=>"$user_type", "ftueShowed" => "$ftue_showed"] );
  //$resJson = json_encode( $res );
  return Response::json($res);
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
