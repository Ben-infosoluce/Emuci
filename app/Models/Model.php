<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Model extends EloquentModel
{
    protected $table = 'model';
    protected $fillable = ['marque_id', 'nom', 'description'];

    public function marque(): BelongsTo
    {
        return $this->belongsTo(Marque::class, 'marque_id');
    }
}
