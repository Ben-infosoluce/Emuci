<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Caisse extends Model
{
    use HasFactory;

    protected $guarded = [];


    // Relations
    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    public function ouvertures()
    {
        return $this->hasMany(CaisseOuverture::class);
    }
}
