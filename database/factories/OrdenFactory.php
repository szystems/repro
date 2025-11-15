<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Orden;
use App\Models\Empresa;
use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Orden>
 */
class OrdenFactory extends Factory
{
    protected $model = Orden::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'empresa_id' => Empresa::factory(),
            'creado_por' => User::factory(),
            'estado' => 'solicitud',
            'fecha_solicitud' => $this->faker->dateTimeBetween('-30 days', '+30 days'),
            'observaciones' => $this->faker->optional()->paragraph(),
            'instrucciones_generales' => $this->faker->optional()->paragraph(),
            'prioridad' => $this->faker->randomElement(['baja', 'normal', 'alta', 'urgente']),
            'fecha_limite' => $this->faker->optional()->dateTimeBetween('+1 day', '+60 days'),
        ];
    }

    /**
     * Estado en proceso
     */
    public function enProceso()
    {
        return $this->state(function (array $attributes) {
            return [
                'estado' => 'en_proceso',
            ];
        });
    }

    /**
     * Estado entregado
     */
    public function entregado()
    {
        return $this->state(function (array $attributes) {
            return [
                'estado' => 'entregado',
            ];
        });
    }

    /**
     * Con alta prioridad
     */
    public function altaPrioridad()
    {
        return $this->state(function (array $attributes) {
            return [
                'prioridad' => 'alta',
            ];
        });
    }
}
