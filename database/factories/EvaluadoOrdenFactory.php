<?php

namespace Database\Factories;

use App\Models\EvaluadoOrden;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\EvaluadoOrden>
 */
class EvaluadoOrdenFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = EvaluadoOrden::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'orden_id' => null, // Se asigna manualmente o por Orden factory
            'nombre' => $this->faker->firstName(),
            'apellidos' => $this->faker->lastName(),
            'email' => $this->faker->unique()->safeEmail(),
            'telefono' => $this->faker->numerify('2###-####'),
            'dpi' => $this->faker->numerify('#############'), // 13 dígitos
            'tipo_documento' => $this->faker->randomElement(['dpi', 'pasaporte', 'cedula']),
            'token_unico' => EvaluadoOrden::generarToken(),
            'token_expira_at' => now()->addDays(30),
            'cuestionario_completado' => false,
            'completado_at' => null,
            'firma_digital' => null,
            'ip_completado' => null,
            'puesto_evaluar' => $this->faker->optional()->jobTitle(),
            'observaciones' => $this->faker->optional()->paragraph(),
            // Nuevos campos granulares
            'tipo_servicio' => $this->faker->randomElement(['poligrafo', 'vsa', 'socioeconomico']),
            'tipo_formulario' => $this->faker->randomElement(['preempleo', 'periodica', 'especifica']),
            'fecha_programada' => $this->faker->optional()->dateTimeBetween('+1 day', '+30 days'),
            'poligrafista_id' => null,
            'estado_evaluacion' => 'pendiente',
            'resultado' => null,
            'notas_poligrafo' => null,
        ];
    }

    /**
     * State: Cuestionario completado
     */
    public function completado(): static
    {
        return $this->state(fn (array $attributes) => [
            'cuestionario_completado' => true,
            'completado_at' => now()->subDays(rand(1, 10)),
            'firma_digital' => 'data:image/png;base64,iVBORw0KGgo...',
            'ip_completado' => $this->faker->ipv4(),
        ]);
    }

    /**
     * State: Token expirado
     */
    public function expirado(): static
    {
        return $this->state(fn (array $attributes) => [
            'token_expira_at' => now()->subDays(rand(1, 30)),
            'cuestionario_completado' => false,
        ]);
    }

    /**
     * State: En progreso (accedió pero no completó)
     */
    public function enProgreso(): static
    {
        return $this->state(fn (array $attributes) => [
            'cuestionario_completado' => false,
        ]);
    }

    /**
     * State: Notificado (email enviado)
     */
    public function notificado(): static
    {
        return $this->state(fn (array $attributes) => [
            'notificado' => true,
            'notificado_at' => now()->subHours(rand(1, 24)),
        ]);
    }
}
