<?php

namespace App\Support;

/** E2.8 — Formación académica autogenerada según último nivel. */
class HistorialAcademico
{
    /** @var array<string, string> */
    public const NIVELES = [
        'primaria' => 'Primaria',
        'basico' => 'Básico',
        'diversificado' => 'Diversificado',
        'tecnico' => 'Técnico',
        'universitario' => 'Universitario',
        'postgrado' => 'Postgrado',
    ];

    /** @return list<string> */
    public static function nivelesVisibles(?string $ultimoNivel): array
    {
        if ($ultimoNivel === null || $ultimoNivel === '' || $ultimoNivel === 'ninguno') {
            return [];
        }

        $orden = array_keys(self::NIVELES);
        $indice = array_search($ultimoNivel, $orden, true);

        if ($indice === false) {
            return [$ultimoNivel];
        }

        return array_slice($orden, 0, $indice + 1);
    }

    /**
     * @param  list<array<string, string>>  $filasExistentes
     * @return list<array<string, string>>
     */
    public static function filasParaFormulario(?string $ultimoNivel, array $filasExistentes = []): array
    {
        $indexadas = [];
        foreach ($filasExistentes as $fila) {
            if (! empty($fila['nivel'])) {
                $indexadas[$fila['nivel']] = $fila;
            }
        }

        $filas = [];
        foreach (self::nivelesVisibles($ultimoNivel) as $clave) {
            $filas[] = array_merge([
                'nivel' => $clave,
                'estado' => '',
                'carrera' => '',
                'institucion' => '',
                'anio' => '',
                'respaldo' => '',
            ], $indexadas[$clave] ?? []);
        }

        return $filas;
    }

    /**
     * @param  list<array<string, string>>  $filas
     * @return list<array<string, string>>
     */
    public static function filasParaAlmacenamiento(?string $ultimoNivel, array $filas): array
    {
        $visibles = self::nivelesVisibles($ultimoNivel);
        if ($visibles === []) {
            return [];
        }

        $indexadas = [];
        foreach ($filas as $fila) {
            $nivel = $fila['nivel'] ?? '';
            if ($nivel !== '' && in_array($nivel, $visibles, true)) {
                $indexadas[$nivel] = $fila;
            }
        }

        $guardadas = [];
        foreach ($visibles as $clave) {
            if (! isset($indexadas[$clave]) || ! self::filaCompleta($indexadas[$clave])) {
                continue;
            }
            $guardadas[] = array_merge(['nivel' => $clave], $indexadas[$clave]);
        }

        return $guardadas;
    }

    /** @param  array<string, string>  $fila */
    public static function filaCompleta(array $fila): bool
    {
        if (($fila['nivel'] ?? '') === '') {
            return false;
        }

        foreach (['estado', 'institucion', 'anio', 'respaldo'] as $campo) {
            if (($fila[$campo] ?? '') === '') {
                return false;
            }
        }

        return true;
    }

    /**
     * Reconstruye filas enviadas por el POST para validación (una por nivel visible).
     *
     * @param  list<array<string, string>>  $filasInput
     * @return list<array<string, string>>
     */
    public static function filasParaValidacion(?string $ultimoNivel, array $filasInput): array
    {
        return self::filasParaFormulario($ultimoNivel, $filasInput);
    }

    public static function etiquetaNivel(?string $nivel): string
    {
        return self::NIVELES[$nivel ?? ''] ?? (string) $nivel;
    }

    /** @return array<string, mixed> */
    public static function reglasValidacion(): array
    {
        return [
            'ultimo_nivel_academico' => 'required|in:ninguno,'.implode(',', array_keys(self::NIVELES)),
        ];
    }

    /** @return array<string, string> */
    public static function mensajesValidacion(): array
    {
        return [
            'ultimo_nivel_academico.required' => 'Seleccione su último nivel académico.',
        ];
    }
}
