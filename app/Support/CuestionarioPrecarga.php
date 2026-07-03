<?php

namespace App\Support;

use App\Models\Cuestionario;
use App\Models\EvaluadoOrden;

/**
 * Precarga de datos desde la orden/evaluado y trazabilidad de cambios del candidato (E1.7).
 */
class CuestionarioPrecarga
{
    /** @var list<string> */
    public const CAMPOS_EDITABLES = [
        'nombres_completos',
        'apellidos_completos',
        'telefono_personal',
        'telefono_alternativo',
        'email_personal',
        'direccion_residencia',
    ];

    /** @var list<string> */
    public const CAMPOS_SOLO_LECTURA = [
        'dpi',
        'tipo_identificacion',
        'empresa_solicitante',
        'agencia_region',
        'puesto_evaluar',
    ];

    /**
     * @return array<string, mixed>
     */
    public static function construirDesdeEvaluado(EvaluadoOrden $evaluado): array
    {
        $evaluado->loadMissing(['orden.empresa', 'orden.sede', 'sede']);

        $orden = $evaluado->orden;
        $telefono = trim((string) ($evaluado->telefono ?? ''));

        $agencia = trim((string) ($evaluado->sede_region_empresa ?? ''));
        if ($agencia === '') {
            $agencia = trim((string) ($evaluado->sede?->nombre ?? $orden?->sede?->nombre ?? ''));
        }

        return [
            'nombres_completos' => trim((string) ($evaluado->nombre ?? '')),
            'apellidos_completos' => trim((string) ($evaluado->apellidos ?? '')),
            'dpi' => (string) ($evaluado->dpi ?? ''),
            'tipo_identificacion' => self::mapearTipoDocumento($evaluado->tipo_documento),
            'telefono_personal' => $telefono,
            'telefono_alternativo' => '',
            'email_personal' => trim((string) ($evaluado->email ?? '')),
            'direccion_residencia' => trim((string) ($evaluado->direccion ?? '')),
            'empresa_solicitante' => trim((string) ($orden?->empresa?->nombre ?? '')),
            'agencia_region' => $agencia,
            'puesto_evaluar' => trim((string) ($evaluado->puesto_evaluar ?? '')),
            'capturado_at' => now()->toIso8601String(),
        ];
    }

    /**
     * Garantiza snapshot inmutable de la orden al crear o al primer acceso legacy.
     *
     * @return array<string, mixed>
     */
    public static function asegurarSnapshot(Cuestionario $cuestionario, EvaluadoOrden $evaluado): array
    {
        $existente = $cuestionario->datos_precarga_json;
        if (is_array($existente) && ! empty($existente)) {
            return $existente;
        }

        $snapshot = self::construirDesdeEvaluado($evaluado);
        $cuestionario->update(['datos_precarga_json' => $snapshot]);

        return $snapshot;
    }

    /**
     * Valor a mostrar: respuesta guardada > precarga orden > fallback.
     */
    public static function valorParaCampo(string $campo, array $precarga, array $respuestas, mixed $fallback = ''): mixed
    {
        if (array_key_exists($campo, $respuestas) && $respuestas[$campo] !== null && $respuestas[$campo] !== '') {
            return $respuestas[$campo];
        }

        if (array_key_exists($campo, $precarga) && $precarga[$campo] !== null && $precarga[$campo] !== '') {
            return $precarga[$campo];
        }

        return $fallback;
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function metadataTrazabilidad(string $campo, mixed $valorNuevo, array $snapshot): ?array
    {
        if (! in_array($campo, self::CAMPOS_EDITABLES, true)) {
            return null;
        }

        $valorOrden = $snapshot[$campo] ?? null;

        if ($valorOrden === null || $valorOrden === '') {
            return [
                'precarga' => [
                    'modificado' => false,
                    'sin_valor_orden' => true,
                ],
            ];
        }

        $modificado = self::normalizarParaComparar($campo, $valorNuevo)
            !== self::normalizarParaComparar($campo, $valorOrden);

        return [
            'precarga' => [
                'valor_orden' => $valorOrden,
                'modificado' => $modificado,
                'modificado_at' => $modificado ? now()->toIso8601String() : null,
            ],
        ];
    }

    /**
     * Cambios respecto a la orden registrados en respuestas del cuestionario.
     *
     * @return list<array{campo: string, seccion: string, valor_orden: mixed, valor_actual: mixed, modificado_at: string|null}>
     */
    public static function cambiosRegistrados(Cuestionario $cuestionario): array
    {
        $cambios = [];

        foreach ($cuestionario->respuestas as $respuesta) {
            $meta = $respuesta->metadata['precarga'] ?? null;
            if (! is_array($meta) || empty($meta['modificado'])) {
                continue;
            }

            $cambios[] = [
                'campo' => $respuesta->campo,
                'seccion' => $respuesta->seccion,
                'valor_orden' => $meta['valor_orden'] ?? null,
                'valor_actual' => $respuesta->valor,
                'modificado_at' => $meta['modificado_at'] ?? null,
            ];
        }

        return $cambios;
    }

    /**
     * @return array<string, string>
     */
    public static function etiquetasCampos(): array
    {
        return [
            'nombres_completos' => 'Nombres',
            'apellidos_completos' => 'Apellidos',
            'telefono_personal' => 'Teléfono personal',
            'telefono_alternativo' => 'Teléfono de emergencia',
            'email_personal' => 'Correo electrónico',
            'direccion_residencia' => 'Dirección de residencia',
            'departamento_nacimiento' => 'Departamento de nacimiento',
            'municipio_nacimiento' => 'Municipio de nacimiento',
            'departamento' => 'Departamento de residencia',
            'municipio' => 'Municipio de residencia',
            'igss' => 'IGSS',
            'nit' => 'NIT',
            'licencia_conducir' => 'Licencia de conducir',
            'edad' => 'Edad',
            'dpi' => 'DPI / identificación',
            'tipo_identificacion' => 'Tipo de identificación',
            'empresa_solicitante' => 'Empresa solicitante',
            'agencia_region' => 'Agencia / región',
            'puesto_evaluar' => 'Puesto a evaluar',
        ];
    }

    protected static function mapearTipoDocumento(?string $tipo): string
    {
        return match ($tipo) {
            'pasaporte' => 'pasaporte',
            'documento_extranjero' => 'documento_extranjero',
            'otro' => 'otro',
            default => 'dpi',
        };
    }

    protected static function normalizarParaComparar(string $campo, mixed $valor): string
    {
        $texto = trim((string) $valor);

        if (in_array($campo, ['telefono_personal', 'telefono_alternativo'], true)) {
            return preg_replace('/\D+/', '', $texto) ?? '';
        }

        if ($campo === 'email_personal') {
            return mb_strtolower($texto);
        }

        return mb_strtolower(preg_replace('/\s+/', ' ', $texto) ?? '');
    }
}
