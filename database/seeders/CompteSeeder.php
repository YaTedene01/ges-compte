<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Compte;
use App\Models\Client;

class CompteSeeder extends Seeder
{
     /**
      * Run the database seeds.
      */
     public function run(): void
     {
         // Create test data
         Compte::factory(10)->create();

         // Create specific example
         $client = Client::firstOrCreate([
             'numeroCompte' => 'C00123456',
             'titulaire' => 'Amadou Diallo Junior',
             'type' => 'epargne',
             'solde' => 1250000.00,
             'devise' => 'FCFA',
             'dateCreation' => '2023-03-15',
             'statut' => 'bloque',
         ]);

         Compte::create([
             'id' => '550e8400-e29b-41d4-a716-446655440000',
             'numeroCompte' => 'C00123456',
             'titulaire' => 'Amadou Diallo Junior',
             'type' => 'epargne',
             'solde' => 1250000.00,
             'devise' => 'FCFA',
             'dateCreation' => '2023-03-15',
             'statut' => 'bloque',
             'metadata' => [
                 'derniereModification' => '2023-03-15T00:00:00Z',
                 'version' => 1,
             ],
             'client_id' => $client->id,
         ]);
     }
}
