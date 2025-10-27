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
              'titulaire' => $this->faker->name(),
              'nci' => $this->faker->optional()->numerify('#############'),
              'email' => $this->faker->unique()->safeEmail(),
              'telephone' => $this->faker->phoneNumber(),
              'adresse' => $this->faker->address(),
          ];
      }
}
