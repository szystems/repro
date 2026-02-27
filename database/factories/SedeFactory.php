<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Sede>
 */
class SedeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nombre'    => 'Sede ' . fake()->unique()->word(),
            'direccion' => fake()->address(),
            'telefono'  => fake()->numerify('2###-####'),
            'capacidad' => fake()->numberBetween(1, 10),
            'estado'    => 1,
            'notas'     => null,
        ];
    }

    /** Estado: inactiva. */
    public function inactiva(): static
    {
        return $this->state(['estado' => 0]);
    }
}
