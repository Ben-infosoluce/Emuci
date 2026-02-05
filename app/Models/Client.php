<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    protected $guarded = [];

    public function r_client_vehicules()
    {
        return $this->hasMany(Vehicule::class, 'id_client');
    }

    public function r_client_dossiers()
    {
        return $this->hasMany(Dossier::class, 'id_client');
    }
}
