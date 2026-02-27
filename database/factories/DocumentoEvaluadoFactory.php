<?php

namespace Database\Factories;

use App\Models\DocumentoEvaluado;
use App\Models\EvaluadoOrden;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DocumentoEvaluado>
 */
class DocumentoEvaluadoFactory extends Factory
{
    protected $model = DocumentoEvaluado::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $tipos = array_keys(DocumentoEvaluado::tiposDocumento());
        $tipo = $this->faker->randomElement($tipos);

        return [
            'evaluado_orden_id' => EvaluadoOrden::factory(),
            'tipo_documento'    => $tipo,
            'nombre_original'   => $this->faker->word() . '.pdf',
            'ruta_archivo'      => 'documentos_evaluados/' . $this->faker->uuid() . '.pdf',
            'mime_type'         => 'application/pdf',
            'tamano'            => $this->faker->numberBetween(10240, 5242880),
            'subido_por_tipo'   => $this->faker->randomElement(['empresa', 'repro', 'evaluado']),
            'subido_por_user_id' => null,
            'estado_verificacion' => 'pendiente',
        ];
    }

    /**
     * Documento aprobado por REPRO.
     */
    public function aprobado(): static
    {
        return $this->state(fn (array $attrs) => [
            'estado_verificacion' => 'aprobado',
            'verificado_at'      => now(),
        ]);
    }

    /**
     * Documento rechazado por REPRO.
     */
    public function rechazado(): static
    {
        return $this->state(fn (array $attrs) => [
            'estado_verificacion' => 'rechazado',
            'verificado_at'      => now(),
            'notas_verificacion'  => $this->faker->sentence(),
        ]);
    }

    /**
     * Documento subido por evaluado.
     */
    public function subidoPorEvaluado(): static
    {
        return $this->state(fn (array $attrs) => [
            'subido_por_tipo'    => 'evaluado',
            'subido_por_user_id' => null,
        ]);
    }
}
