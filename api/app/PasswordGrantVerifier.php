<?php

namespace App;

use Illuminate\Support\Facades\Auth;
use Log;

class PasswordGrantVerifier
{
  public function verify($username, $password)
  {
      Log::info('user: '.$username);
      $credentials = [
        'name'    => $username,
        'password' => bcrypt($password),
      ];

      if (Auth::once($credentials)) {
          return Auth::user()->id;
          /*Log::info('user: '.Auth::user()->id);
          $id = DB::table('oauth_clients')->where('name', Auth::user()->name )->first()->id;
          Log::info('oauth_clients: '.$id);
          return $id;*/ //Auth::user()->id;
      }

      return false;
  }
}
