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
     * Preempleo y socioeconómico: filas de la regla de Stephany.
     * Periódica y específica: solo el último grado cursado.
     *
     * @return array<string, list<string>>
     */
    public static function mapaNivelesVisibles(?string $tipoFormulario = null): array
    {
        if (self::soloUltimoGrado($tipoFormulario)) {
            $mapa = [];
            foreach (array_keys(self::NIVELES) as $clave) {
                $mapa[$clave] = [$clave];
            }

            return $mapa;
        }

        return [
            'primaria' => ['primaria'],
            'basico' => ['primaria', 'basico'],
            'diversificado' => ['basico', 'diversificado'],
            'tecnico' => ['diversificado', 'tecnico'],
            'universitario' => ['diversificado', 'universitario'],
            'postgrado' => ['diversificado', 'universitario', 'postgrado'],
        ];
    }

    public static function soloUltimoGrado(?string $tipoFormulario): bool
    {
        return in_array($tipoFormulario, ['periodica', 'especifica'], true);
    }

    /**
     * @return list<string>
     */
    public static function nivelesVisibles(?string $ultimoNivel, ?string $tipoFormulario = null): array
    {
        if ($ultimoNivel === null || $ultimoNivel === '' || $ultimoNivel === 'ninguno') {
            return [];
        }

        $mapa = self::mapaNivelesVisibles($tipoFormulario);

        if (isset($mapa[$ultimoNivel])) {
            return $mapa[$ultimoNivel];
        }

        return [$ultimoNivel];
    }

    public static function textoAyudaFilas(?string $tipoFormulario = null): string
    {
        if (self::soloUltimoGrado($tipoFormulario)) {
            return 'Complete solo el último grado que seleccionó arriba.';
        }

        return 'Complete las filas de la regla para ese último grado. Universitario incluye diversificado; posgrado incluye universitario y diversificado. Puede dejar en blanco la fila que no aplique; complete el último grado.';
    }

    /**
     * @param  list<array<string, string>>  $filasExistentes
     * @return list<array<string, string>>
     */
    public static function filasParaFormulario(?string $ultimoNivel, array $filasExistentes = [], ?string $tipoFormulario = null): array
    {
        $indexadas = [];
        foreach ($filasExistentes as $fila) {
            if (! empty($fila['nivel'])) {
                $indexadas[$fila['nivel']] = $fila;
            }
        }

        $filas = [];
        foreach (self::nivelesVisibles($ultimoNivel, $tipoFormulario) as $clave) {
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
    public static function filasParaAlmacenamiento(?string $ultimoNivel, array $filas, ?string $tipoFormulario = null): array
    {
        $visibles = self::nivelesVisibles($ultimoNivel, $tipoFormulario);
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
    public static function filasParaValidacion(?string $ultimoNivel, array $filasInput, ?string $tipoFormulario = null): array
    {
        $filas = self::filasParaFormulario($ultimoNivel, $filasInput, $tipoFormulario);
        if ($filas === []) {
            return [];
        }

        return array_values(array_filter(
            $filas,
            static function (array $fila) use ($ultimoNivel): bool {
                return ($fila['nivel'] ?? '') === $ultimoNivel || ! self::filaVacia($fila);
            }
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
