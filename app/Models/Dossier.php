<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dossier extends Model
{
    protected $guarded = [];
    public function r_dossier_vehicule()
    {
        return $this->belongsTo(Vehicule::class, 'id_vehicule');
    }

    public function r_dossier_user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    public function r_dossier_client()
    {
        return $this->belongsTo(Client::class, 'id_client');
    }

    public function r_dossier_documents()
    {
        return $this->hasMany(Document::class, 'id_dossier');
    }

    // public function r_dossier_services()
    // {
    //     return $this->belongsToMany(Service::class, 'dossier_services', 'id_dossier', 'id_service');
    // }
    public function r_dossier_services()
    {
        return $this->belongsTo(Service::class, 'id_service');
    }

    public function r_dossier_transactions()
    {
        return $this->hasMany(Transaction::class, 'id_dossier');
    }
}
