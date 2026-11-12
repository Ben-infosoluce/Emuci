<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dossier extends Model
{
    protected $guarded = [];

    protected static function booted(): void
    {
        static::creating(function (Dossier $dossier) {
            // Clé technique protégée par un index unique : empêche la création
            // de deux dossiers RELICA-PRIMO avec le même numéro chrono.
            if ($dossier->type === 'RELICA-PRIMO' && filled($dossier->num_chrono)) {
                $key = 'RELICA-PRIMO:' . $dossier->num_chrono;

                $dossier->dedup_key = mb_strlen($key) <= 255
                    ? $key
                    : 'RELICA-PRIMO:' . hash('sha256', $dossier->num_chrono);
            }
        });
    }

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

    public function r_dossier_demandes_modification()
    {
        return $this->hasMany(DemandeModification::class, 'dossier_id');
    }

    /**
     * Dernière demande de modification validée par l'administration et non
     * encore appliquée par le PDC.
     */
    public function r_dossier_demande_modification_validee()
    {
        return $this->hasOne(DemandeModification::class, 'dossier_id')
            ->where('statut', 2)
            ->latestOfMany();
    }
}
