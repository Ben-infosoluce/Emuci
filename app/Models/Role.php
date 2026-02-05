<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $fillable = [
        'nom_role'
    ];

    public function r_role_users()
    {
        return $this->belongsToMany(User::class, 'user_role', 'id_role', 'id_user');
    }
}
