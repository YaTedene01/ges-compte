<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'numeroCompte',
        'titulaire',
        'type',
        'solde',
        'devise',
        'dateCreation',
        'statut',
    ];

    protected $casts = [
        'solde' => 'decimal:2',
        'dateCreation' => 'datetime',
    ];
}
