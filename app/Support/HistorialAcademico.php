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

    /**
     * Cada último grado incluye ese nivel y los anteriores (primaria … seleccionado).
     * Así, universitario también muestra diversificado. Las filas en blanco no se guardan.
     *
     * @return array<string, list<string>>
     */
    public static function mapaNivelesVisibles(): array
    {
        $claves = array_keys(self::NIVELES);
        $mapa = [];
        foreach ($claves as $indice => $clave) {
            $mapa[$clave] = array_slice($claves, 0, $indice + 1);
        }

        return $mapa;
    }

    /**
     * @return list<string>
     */
    public static function nivelesVisibles(?string $ultimoNivel): array
    {
        if ($ultimoNivel === null || $ultimoNivel === '' || $ultimoNivel === 'ninguno') {
            return [];
        }

        $mapa = self::mapaNivelesVisibles();

        if (isset($mapa[$ultimoNivel])) {
            return $mapa[$ultimoNivel];
        }

        return [$ultimoNivel];
    }

    public static function textoAyudaFilas(): string
    {
        return 'Incluya los niveles anteriores que correspondan (por ejemplo, diversificado si el último grado es universitario). Puede dejar en blanco los que no apliquen; complete el último grado.';
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
     * Reconstruye filas del POST. Exige el último grado y conserva los anteriores solo si traen datos.
     *
     * @param  list<array<string, string>>  $filasInput
     * @return list<array<string, string>>
     */
    public static function filasParaValidacion(?string $ultimoNivel, array $filasInput): array
    {
        $filas = self::filasParaFormulario($ultimoNivel, $filasInput);
        if ($filas === []) {
            return [];
        }

        $ultimoIndice = count($filas) - 1;

        return array_values(array_filter(
            $filas,
            static function (array $fila, int $indice) use ($ultimoIndice): bool {
                return $indice === $ultimoIndice || ! self::filaVacia($fila);
            },
            ARRAY_FILTER_USE_BOTH
        ));
    }

    /** @param  array<string, string>  $fila */
    public static function filaVacia(array $fila): bool
    {
        foreach (['estado', 'carrera', 'institucion', 'anio', 'respaldo'] as $campo) {
            if (trim((string) ($fila[$campo] ?? '')) !== '') {
                return false;
            }
        }

        return true;
    }

    public static function etiquetaNivel(?string $nivel): string
    {
        return self::NIVELES[$nivel ?? ''] ?? (string) $nivel;
    }

    /** @return array<string, mixed> */
    public static function reglasValidacion(): array
    {
        return array_merge([
            'ultimo_nivel_academico' => 'required|in:ninguno,'.implode(',', array_keys(self::NIVELES)),
        ], self::reglasEstudiaActualmente());
    }

    /** @return array<string, mixed> */
    public static function reglasEstudiaActualmente(): array
    {
        return [
            'estudia_actualmente' => 'required|in:si,no',
        ];
    }

    /** @return array<string, string> */
    public static function mensajesValidacion(): array
    {
        return [
            'ultimo_nivel_academico.required' => 'Seleccione su último nivel académico.',
            'estudia_actualmente.required' => 'Indique si estudia actualmente.',
            'estudia_actualmente.in' => 'Seleccione si estudia actualmente.',
        ];
    }
}
