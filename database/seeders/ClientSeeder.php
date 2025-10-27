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
             'titulaire' => 'Amadou Diallo Junior',
             'nci' => '1234567890123',
             'email' => 'amadou.diallo@example.com',
             'telephone' => '+221771234567',
             'adresse' => 'Dakar, Sénégal',
         ]);
     }
}
