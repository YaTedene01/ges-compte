<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Transaction;

class TransactionSeeder extends Seeder
{
     /**
      * Run the database seeds.
      */
     public function run(): void
     {
         // Create test data
         Transaction::factory(10)->create();
     }
}
