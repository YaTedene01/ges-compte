<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use App\Scopes\NonDeletedScope;

class Compte extends Model
{
    use HasFactory, SoftDeletes;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
         'id',
         'numeroCompte',
         'titulaire',
         'type',
         'devise',
         'dateCreation',
         'statut',
         'motifBlocage',
         'dateDebutBlocage',
         'dateFinBlocage',
         'metadata',
         'client_id',
     ];

    protected $casts = [
         'dateCreation' => 'date',
         'dateDebutBlocage' => 'datetime',
         'dateFinBlocage' => 'datetime',
         'metadata' => 'array',
     ];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new NonDeletedScope);

        static::creating(function ($compte) {
            if (empty($compte->id)) {
                $compte->id = Str::uuid();
            }
            if (!$compte->numeroCompte) {
                do {
                    $numero = 'C' . str_pad(rand(1, 99999999), 8, '0', STR_PAD_LEFT);
                } while (self::where('numeroCompte', $numero)->exists());
                $compte->numeroCompte = $numero;
            }
        });
    }

    public function client()
    {
         return $this->belongsTo(Client::class);
    }

    public function transactions()
    {
         return $this->hasMany(Transaction::class, 'compteId');
    }

    public function scopeNumero($query, $numero)
    {
        return $query->where('numeroCompte', $numero);
    }

    public function scopeClient($query, $telephone)
    {
         return $query->whereHas('client', function ($q) use ($telephone) {
             $q->where('telephone', $telephone);
         });
     }

    public function getSoldeAttribute()
    {
         $deposits = $this->transactions()->where('type', 'depot')->sum('montant');
         $withdrawals = $this->transactions()->where('type', 'retrait')->sum('montant');
         return $deposits - $withdrawals;
     }
}
