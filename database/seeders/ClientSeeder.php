<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Client;

class ClientSeeder extends Seeder
{
     /**
      * Run the database seeds.
      */
     public function run(): void
     {
         // Create test data
         Client::factory(10)->create();

         // Create specific example
         Client::create([
             'id' => '550e8400-e29b-41d4-a716-446655440000',
             'numeroCompte' => 'C00123456',
             'titulaire' => 'Amadou Diallo Junior',
             'type' => 'epargne',
             'solde' => 1250000.00,
             'devise' => 'FCFA',
             'dateCreation' => '2023-03-15 00:00:00',
             'statut' => 'bloque',
         ]);
     }
}
