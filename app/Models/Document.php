<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    protected $guarded = [];
    public function r_document_dossier()
    {
        return $this->belongsTo(Dossier::class, 'id_dossier');
    }
}
