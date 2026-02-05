<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Site extends Model
{
    protected $fillable = [
        'nom_site',
        'type_site',
        'region',
        'heures_ouverture',
        'heures_fermeture',
        'statut'
    ];


    public function r_site_users()
    {
        return $this->hasMany(User::class, 'id_site');
    }

    public function r_site_vehicules()
    {
        return $this->hasMany(Vehicule::class, 'id_site');
    }
}
