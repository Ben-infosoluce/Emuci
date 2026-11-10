<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DemandeModification extends Model
{
    use HasFactory;

    protected $table = 'demande_modifications';

    protected $guarded = [];

    protected $casts = [
        'champs_vehicule' => 'array',
        'champs_client' => 'array',
        'champs_entreprise' => 'array',
        'traite_le' => 'datetime',
        'applique_le' => 'datetime',
    ];

    protected $appends = [
        'statut_label',
    ];

    public function dossier()
    {
        return $this->belongsTo(Dossier::class, 'dossier_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function site()
    {
        return $this->belongsTo(Site::class, 'site_id');
    }

    public function traitePar()
    {
        return $this->belongsTo(User::class, 'traite_par');
    }

    public function appliquePar()
    {
        return $this->belongsTo(User::class, 'applique_par');
    }

    public function scopeEnAttente($query)
    {
        return $query->where('statut', 1);
    }

    public function scopeValidee($query)
    {
        return $query->where('statut', 2);
    }

    public function getStatutLabelAttribute(): string
    {
        return match ((int) $this->statut) {
            1 => 'En attente',
            2 => 'Validée',
            3 => 'Rejetée',
            4 => 'Appliquée',
            default => 'Inconnu',
        };
    }
}
