<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vehicule extends Model
{
    protected $guarded = [];

    public function r_vehicule_site(): BelongsTo
    {
        return $this->belongsTo(Site::class, 'id_site');
    }

    public function r_vehicule_client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'id_client');
    }

    public function r_vehicule_dossiers(): HasMany
    {
        return $this->hasMany(Dossier::class, 'id_vehicule');
    }

    //relation vehicule entreprise
    public function r_vehicule_entreprise(): BelongsTo
    {
        return $this->belongsTo(Entreprise::class, 'entreprise_id');
    }
}
