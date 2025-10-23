<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Client>
 */
class ClientFactory extends Factory
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
             'numeroCompte' => $this->faker->unique()->regexify('C[0-9]{8}'),
             'titulaire' => $this->faker->name(),
             'type' => 'epargne',
             'solde' => $this->faker->numberBetween(100000, 10000000),
             'devise' => 'FCFA',
             'dateCreation' => $this->faker->dateTimeBetween('-2 years', 'now'),
             'statut' => $this->faker->randomElement(['actif', 'bloque', 'suspendu']),
         ];
     }
}
