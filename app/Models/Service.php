<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $guarded = [];

    public function r_service_types()
    {
        return $this->hasMany(TypeService::class, 'id_service');
    }

    // public function r_service_dossiers()
    // {
    //     return $this->belongsToMany(Dossier::class, 'dossier_services', 'id_service', 'id_dossier');
    // }

    public function r_service_dossiers()
    {
        return $this->hasManyThrough(
            Dossier::class,
            DossierService::class,
            'id_service', // Clé étrangère sur dossier_services
            'id', // Clé primaire sur dossiers
            'id', // Clé primaire sur services
            'id_dossier' // Clé étrangère sur dossier_services
        );
    }
}
