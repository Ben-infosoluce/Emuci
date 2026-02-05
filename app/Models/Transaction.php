<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'id_dossier',
        'Montant',
        'date_transaction',
        'type_transaction',
        'id_user',
        'statut'
    ];

    public function r_transaction_dossier()
    {
        return $this->belongsTo(Dossier::class, 'id_dossier');
    }

    public function r_transaction_user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    public function r_transaction_recus()
    {
        return $this->hasMany(Recu::class, 'id_transaction');
    }
}
