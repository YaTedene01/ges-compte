<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
    use HasFactory, SoftDeletes;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'compteId',
        'type',
        'montant',
        'devise',
        'description',
        'dateTransaction',
        'statut',
    ];

    protected $casts = [
        'montant' => 'decimal:2',
        'dateTransaction' => 'datetime',
    ];

    public function compte()
    {
        return $this->belongsTo(Compte::class, 'compteId');
    }
}
