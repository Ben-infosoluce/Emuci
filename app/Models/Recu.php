<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Recu extends Model
{
    protected $fillable = [
        'id_transaction',
        'date_emission',
        'Format'
    ];
    public function r_recu_transaction()
    {

        return $this->belongsTo(Transaction::class, 'id_transaction');
    }
}
