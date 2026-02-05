<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    protected $fillable = [
        'Nom_Permission'
    ];

    public function r_permission_users()
    {
        return $this->belongsToMany(User::class, 'user_permission', 'id_Permission', 'id_user');
    }
}
