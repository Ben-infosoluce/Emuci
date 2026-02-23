<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Marque extends Model
{
    protected $table = 'marque';
    protected $fillable = ['nom', 'description'];

    public function models(): HasMany
    {
        return $this->hasMany(Model::class, 'marque_id');
    }
}
