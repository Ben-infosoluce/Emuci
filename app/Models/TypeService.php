<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TypeService extends Model
{
    protected $fillable = [
        'nom_type_service',
        'id_service'
    ];

    public function r_typeService_service()
    {
        return $this->belongsTo(Service::class, 'id_service');
    }
}
