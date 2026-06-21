<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReplicaPrimo extends Model
{
    use HasFactory;

    protected $table = 'relica_primo';

    protected $fillable = [
        'chrono',
        'mt_total_cil',
    ];

    protected $casts = [
        'mt_total_cil' => 'decimal:2',
    ];
}
