<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaiementLigne extends Model
{
    use HasFactory;

    protected $table = 'paiement_lignes';

    protected $fillable = [
        'paiement_id',
        'article_code',
        'libelle',
        'fait_generateur',
        'montant_ht',
        'montant_ttc',
        'compte_x3',
        'parent_detail_type_service_id',
    ];

    protected $casts = [
        'montant_ht' => 'float',
        'montant_ttc' => 'float',
    ];

    public function r_paiement_ligne_paiement(): BelongsTo
    {
        return $this->belongsTo(Paiement::class, 'paiement_id');
    }

    public function r_paiement_ligne_service(): BelongsTo
    {
        return $this->belongsTo(DetailTypeService::class, 'parent_detail_type_service_id');
    }
}
