<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RelicaPrimo extends Model
{
    protected $table = 'relica_primo';

    protected $fillable = [
        'chrono',
        'mt_total_cil',
    ];

}