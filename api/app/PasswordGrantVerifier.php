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
        'password' => $password,
      ];

      if (Auth::once($credentials)) {
          return Auth::user()->id;
      }

      return false;
  }
}
