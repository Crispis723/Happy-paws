<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Mascota>
 */
class MascotaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
           'user_id' => \App\Models\User::factory(),
           'nombre' => $this->faker->firstName,
           'especie' => $this->faker->randomElement(['Perro','Gato']),
           'raza' => $this->faker->word,
        ];
    }
}
