<?php

namespace App\Support;

use App\Models\Cuestionario;
use App\Models\EvaluadorNota;

/** E3.2 — Compila y persiste tablas del informe Pre-empleo (editables por evaluador). */
class InformePreempleo
{
    public const SECCION_NOTAS = 'informe_preempleo';

    /** @var array<string, string> */
    public const CLAVES_TABLAS = [
        'familiar' => 'Información familiar',
        'academico' => 'Formación académica',
        'laboral' => 'Historial laboral',
        'deudas' => 'Deudas',
        'complementaria' => 'Información complementaria',
    ];

    public static function aplicaATipo(?string $tipoFormulario): bool
    {
        return in_array($tipoFormulario, ['preempleo', 'socioeconomico'], true);
    }

    /**
     * @return array<string, mixed>
     */
    public static function compilarTablas(Cuestionario $cuestionario): array
    {
        $tablasS3 = $cuestionario->getTablasPorNumeroSeccion(3);
        $tablasS4 = $cuestionario->getTablasPorNumeroSeccion(4);
        $respuestasS5 = $cuestionario->obtenerRespuestasSeccion(5);
        $tablas = [
            'familiar' => ResumenFamiliar::compilar($cuestionario),
            'academico' => $tablasS3['formacion_academica'] ?? [],
            'laboral' => $tablasS3['empleos'] ?? [],
            'deudas' => $tablasS4['deudas'] ?? [],
            'complementaria' => self::compilarComplementaria($respuestasS5),
        ];

        if ($cuestionario->tipo_formulario === 'socioeconomico') {
            $tablasS6 = $cuestionario->getTablasPorNumeroSeccion(6);
            $tablas['referencias_familiares'] = $tablasS6['referencias_familiares'] ?? [];
            $tablas['referencias_personales'] = $tablasS6['referencias_personales'] ?? [];
        }

        return $tablas;
    }

    /**
     * @return list<array{pregunta: string, respuesta: string}>
     */
    private static function compilarComplementaria(array $respuestas): array
    {
        $filas = [];

        foreach (InformacionComplementaria::PREGUNTAS as $pregunta) {
            $valor = trim((string) ($respuestas[$pregunta['key']] ?? ''));
            if ($valor === '') {
                continue;
            }

            $filas[] = [
                'pregunta' => $pregunta['label'],
                'respuesta' => $valor,
            ];
        }

        return $filas;
    }

    /**
     * @return array<string, mixed>
     */
    public static function overrides(int $evaluadoOrdenId): array
    {
        $registros = EvaluadorNota::query()
            ->where('evaluado_orden_id', $evaluadoOrdenId)
            ->where('seccion', self::SECCION_NOTAS)
            ->whereIn('campo', array_keys(self::CLAVES_TABLAS))
            ->get();

        $overrides = [];

        foreach ($registros as $nota) {
            if ($nota->contenido === null || $nota->contenido === '') {
                continue;
            }

            $decoded = json_decode($nota->contenido, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $overrides[$nota->campo] = $decoded;
            }
        }

        return $overrides;
    }

    /**
     * @return list<string>
     */
    public static function clavesConOverride(int $evaluadoOrdenId): array
    {
        return array_keys(self::overrides($evaluadoOrdenId));
    }

    /**
     * @return array<string, mixed>
     */
    public static function tablasParaAdmin(Cuestionario $cuestionario): array
    {
        $tablas = self::compilarTablas($cuestionario);
        $overrides = self::overrides($cuestionario->evaluado_orden_id);

        foreach ($overrides as $clave => $datos) {
            if (array_key_exists($clave, $tablas)) {
                $tablas[$clave] = $datos;
            }
        }

        return $tablas;
    }

    /**
     * @param  array<string, mixed>  $input
     * @param  array<string, mixed>  $restaurar
     */
    public static function guardarDesdeRequest(int $evaluadoOrdenId, array $input, array $restaurar, ?int $userId): void
    {
        foreach (self::CLAVES_TABLAS as $clave => $titulo) {
            if (! empty($restaurar[$clave])) {
                EvaluadorNota::query()
                    ->where('evaluado_orden_id', $evaluadoOrdenId)
                    ->where('seccion', self::SECCION_NOTAS)
                    ->where('campo', $clave)
                    ->delete();

                continue;
            }

            if (! array_key_exists($clave, $input)) {
                continue;
            }

            $normalizado = self::normalizarTabla($clave, $input[$clave]);
            $json = json_encode($normalizado, JSON_UNESCAPED_UNICODE);

            EvaluadorNota::guardarNota(
                $evaluadoOrdenId,
                self::SECCION_NOTAS,
                $clave,
                $json !== false ? $json : null,
                $userId
            );
        }
    }

    /**
     * @param  mixed  $datos
     * @return mixed
     */
    private static function normalizarTabla(string $clave, mixed $datos)
    {
        if (! is_array($datos)) {
            return $datos;
        }

        if ($clave === 'familiar') {
            return self::normalizarFamiliar($datos);
        }

        if ($clave === 'complementaria') {
            return self::normalizarComplementaria($datos);
        }

        return array_values(array_filter($datos, fn ($fila) => is_array($fila)));
    }

    /** @param  array<string, mixed>  $datos */
    private static function normalizarFamiliar(array $datos): array
    {
        if (isset($datos['convive_con']) && is_string($datos['convive_con'])) {
            $datos['convive_con'] = array_values(array_filter(array_map('trim', explode(',', $datos['convive_con']))));
        }

        foreach (['hijos', 'hermanos'] as $tabla) {
            if (isset($datos[$tabla]) && is_array($datos[$tabla])) {
                $datos[$tabla] = array_values($datos[$tabla]);
            }
        }

        return $datos;
    }

    /** @param  array<int, mixed>  $datos */
    private static function normalizarComplementaria(array $datos): array
    {
        $filas = [];

        foreach ($datos as $fila) {
            if (! is_array($fila)) {
                continue;
            }

            $pregunta = trim((string) ($fila['pregunta'] ?? ''));
            $respuesta = trim((string) ($fila['respuesta'] ?? ''));

            if ($pregunta === '' && $respuesta === '') {
                continue;
            }

            $filas[] = [
                'pregunta' => $pregunta,
                'respuesta' => $respuesta,
            ];
        }

        return $filas;
    }
}
