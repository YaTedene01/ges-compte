<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Client;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run()
    {
        $email = 'admin@gmail.com';

        $client = Client::firstOrNew(['email' => $email]);
        $client->titulaire = $client->titulaire ?? 'Administrateur';
        $client->telephone = $client->telephone ?? '+221000000000';
        $client->adresse = $client->adresse ?? 'Dakar';
        $client->password = Hash::make('admin');
        $client->save();

        // Optionally attach an 'admin' role if model supports it
        if (property_exists($client, 'role')) {
            $client->role = 'admin';
            $client->save();
        }
    }
}
