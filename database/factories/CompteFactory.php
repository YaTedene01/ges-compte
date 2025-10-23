<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Client;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Compte>
 */
class CompteFactory extends Factory
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
             'titulaire' => $this->faker->name(),
             'type' => $this->faker->randomElement(['epargne', 'cheque']),
             'solde' => $this->faker->numberBetween(100000, 10000000),
             'devise' => 'FCFA',
             'dateCreation' => $this->faker->date(),
             'statut' => $this->faker->randomElement(['actif', 'bloque', 'ferme']),
             'metadata' => [
                 'derniereModification' => $this->faker->dateTime(),
                 'version' => $this->faker->numberBetween(1, 10),
             ],
             'client_id' => Client::factory(),
         ];
     }
}
