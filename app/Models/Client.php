<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Observers\ClientObserver;
use Laravel\Passport\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Client extends Authenticatable
{
    use HasFactory;
    use HasApiTokens;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
         'id',
         'titulaire',
         'nci',
         'email',
         'telephone',
         'adresse',
         'password',
         'code',
     ];

    protected $hidden = [
         'password',
         'code',
     ];

    protected $casts = [
         'dateCreation' => 'datetime',
     ];

    protected static function boot()
    {
         parent::boot();

         static::observe(ClientObserver::class);

         static::creating(function ($client) {
             if (empty($client->id)) {
                 $client->id = Str::uuid();
             }
             if (!$client->password) {
                 $client->password = Hash::make(Str::random(8));
             }
             if (!$client->code) {
                 $client->code = strtoupper(Str::random(6));
             }
         });
     }
}
