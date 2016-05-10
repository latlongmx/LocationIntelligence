<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Accounts extends Model
{
    //
    protected $table = 'sys_project';
    protected $primaryKey = 'id_project';
    protected $casts = [
        'name' => 'string',
        'description' => 'string',
    ];
}
