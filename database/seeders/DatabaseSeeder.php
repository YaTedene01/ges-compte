<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
         // \App\Models\User::factory(10)->create();

         // \App\Models\User::factory()->create([
         //     'name' => 'Test User',
         //     'email' => 'test@example.com',
         // ]);

        // Seeders we intend to run (short names). We'll resolve to FQCN only if the class exists.
        $shortSeeders = [
            'UserSeeder',
            'ClientSeeder',
            'CompteSeeder',
            'TransactionSeeder',
            'AdminSeeder',
        ];

        $available = [];
        foreach ($shortSeeders as $s) {
            $fqcn = "Database\\Seeders\\{$s}";
            if (class_exists($fqcn)) {
                $available[] = $fqcn;
            }
        }

        if (!empty($available)) {
            $this->call($available);
        }
    }
}
