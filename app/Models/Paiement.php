<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Paiement extends Model
{
    use HasFactory;
    protected $guarded = [];

    // 🔗 Relations
    public function dossier()
    {
        return $this->belongsTo(Dossier::class, 'id_dossier');
    }

    public function caisseOuverture()
    {
        return $this->belongsTo(CaisseOuverture::class, 'caisse_ouverture_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Ventilation du paiement (source de vérité pour Sage X3).
     */
    public function lignes()
    {
        return $this->hasMany(PaiementLigne::class, 'paiement_id');
    }
}
