<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BillingArticle extends Model
{
    use HasFactory;

    protected $table = 'billing_articles';

    protected $fillable = [
        'detail_type_service_id',
        'code',
        'libelle',
        'fait_generateur',
        'montant_ht',
        'montant_ttc',
        'compte_x3',
        'actif',
    ];

    protected $casts = [
        'montant_ht' => 'float',
        'montant_ttc' => 'float',
        'actif' => 'boolean',
    ];

    public function r_billing_article_service(): BelongsTo
    {
        return $this->belongsTo(DetailTypeService::class, 'detail_type_service_id');
    }
}
