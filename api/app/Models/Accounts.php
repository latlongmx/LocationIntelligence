<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Accounts extends Model
{
    //
    protected $table = 'sys_accounts';
    protected $primaryKey = 'id_account';
    protected $casts = [
        'name' => 'string',
        'contact' => 'string',
        'mail' => 'string',
        'phone' => 'string',
        'registration_dt' => 'datetime',
        'expiration_dt' => 'datetime',
    ];

    public function users(){
        return $this->hasMany('Users');
    }
}
