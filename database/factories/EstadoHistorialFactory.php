<?php

namespace Database\Factories;

use App\Models\EstadoHistorial;
use App\Models\EvaluadoOrden;
use App\Models\Orden;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\EstadoHistorial>
 */
class EstadoHistorialFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = EstadoHistorial::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $campo = $this->faker->randomElement([
            'estado_formulario',
            'estado_programacion',
            'estado_evaluacion',
            'estado_orden',
            'modalidad'
        ]);

        return [
            'evaluado_orden_id' => $campo !== 'estado_orden' ? EvaluadoOrden::factory() : null,
            'orden_id' => $campo === 'estado_orden' ? Orden::factory() : null,
            'campo' => $campo,
            'estado_anterior' => $this->faker->word(),
            'estado_nuevo' => $this->faker->word(),
            'observacion' => $this->faker->optional()->sentence(),
            'user_id' => $this->faker->optional()->randomElement([null, User::factory()]),
        ];
    }

    /**
     * State: Para un evaluado específico
     */
    public function paraEvaluado(int $evaluadoOrdenId): static
    {
        return $this->state(fn (array $attributes) => [
            'evaluado_orden_id' => $evaluadoOrdenId,
            'orden_id' => null,
            'campo' => $this->faker->randomElement(['estado_formulario', 'estado_programacion', 'estado_evaluacion', 'modalidad']),
        ]);
    }

    /**
     * State: Para una orden específica
     */
    public function paraOrden(int $ordenId): static
    {
        return $this->state(fn (array $attributes) => [
            'evaluado_orden_id' => null,
            'orden_id' => $ordenId,
            'campo' => 'estado_orden',
        ]);
    }

    /**
     * State: Para campo específico
     */
    public function campo(string $campo): static
    {
        return $this->state(fn (array $attributes) => [
            'campo' => $campo,
        ]);
    }
}
