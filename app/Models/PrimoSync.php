<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrimoSync extends Model
{
    protected $table = 'primo_syncs';

    protected $fillable = [
        'last_sync_at',
    ];

    protected $casts = [
        'last_sync_at' => 'datetime',
    ];
}
