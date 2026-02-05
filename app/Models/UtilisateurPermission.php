<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserPermission extends Model
{
    protected $table = 'user_permission';
    public $timestamps = false;

    protected $fillable = [
        'id_user',
        'id_Permission'
    ];
}
