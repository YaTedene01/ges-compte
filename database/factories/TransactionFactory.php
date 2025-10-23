<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Compte;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Transaction>
 */
class TransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
         return [
             'id' => $this->faker->uuid(),
             'compteId' => Compte::factory(),
             'type' => $this->faker->randomElement(['depot', 'retrait', 'virement', 'frais']),
             'montant' => $this->faker->numberBetween(1000, 100000),
             'devise' => 'FCFA',
             'description' => $this->faker->sentence(),
             'dateTransaction' => $this->faker->dateTimeBetween('-1 month', 'now'),
             'statut' => $this->faker->randomElement(['en_attente', 'validee', 'annulee']),
         ];
     }
}
