<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DossierService extends Model
{
    protected $table = 'dossier_services';
    public $timestamps = false;

    protected $fillable = [
        'id_dossier',
        'id_service'
    ];
}
