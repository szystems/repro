<?php

namespace App\Support;

/**
 * Motor de tablas dinámicas (E1.1 + E2) — columnas, normalización y validación.
 */
class TablaDinamica
{
    /**
     * @return array<string, list<array<string, mixed>>>
     */
    public static function camposPorSeccion(int $numero, string $tipoFormulario): array
    {
        if (! in_array($tipoFormulario, ['preempleo', 'periodica', 'especifica', 'socioeconomico'], true)) {
            return [];
        }

        return match ($numero) {
            2 => self::tablasSeccion2($tipoFormulario),
            3 => self::tablasSeccion3($tipoFormulario),
            4 => self::tablasSeccion4($tipoFormulario),
            5 => self::tablasSeccion5($tipoFormulario),
            default => [],
        };
    }

    /** @return array<string, list<array<string, mixed>>> */
    private static function tablasSeccion2(string $tipoFormulario): array
    {
        $tablas = ['hijos' => self::columnasHijos()];

        if ($tipoFormulario !== 'periodica') {
            $tablas['hermanos'] = self::columnasHermanos();
        }

        return $tablas;
    }

    /** @return array<string, list<array<string, mixed>>> */
    private static function tablasSeccion3(string $tipoFormulario): array
    {
        if (! in_array($tipoFormulario, ['preempleo', 'socioeconomico'], true)) {
            return [];
        }

        return [
            'formacion_academica' => self::columnasFormacionAcademica(),
            'empleos' => self::columnasEmpleos(),
        ];
    }

    /** @return array<string, list<array<string, mixed>>> */
    private static function tablasSeccion4(string $tipoFormulario): array
    {
        if (! in_array($tipoFormulario, ['preempleo', 'socioeconomico'], true)) {
            return [];
        }

        return ['deudas' => self::columnasDeudas()];
    }

    /** @return array<string, list<array<string, mixed>>> */
    private static function tablasSeccion5(string $tipoFormulario): array
    {
        if ($tipoFormulario !== 'preempleo') {
            return [];
        }

        return [
            'tatuajes' => self::columnasTatuajes(),
            'perforaciones' => self::columnasPerforaciones(),
        ];
    }

    /** @return list<array<string, mixed>> */
    public static function columnasHijos(): array
    {
        return [
            ['key' => 'nombre', 'label' => 'Nombre completo', 'type' => 'text', 'required' => true, 'max' => 100],
            ['key' => 'edad', 'label' => 'Edad', 'type' => 'number', 'required' => true, 'min' => 0, 'max' => 120],
            ['key' => 'vive_con_candidato', 'label' => '¿Vive con usted?', 'type' => 'select', 'required' => true, 'options' => ['si' => 'Sí', 'no' => 'No']],
            ['key' => 'ocupacion', 'label' => 'Ocupación / lugar de trabajo', 'type' => 'text', 'required' => false, 'max' => 150],
            ['key' => 'telefono', 'label' => 'Teléfono', 'type' => 'tel', 'required' => false, 'max' => 15],
        ];
    }

    /** @return list<array<string, mixed>> */
    public static function columnasHermanos(): array
    {
        return [
            ['key' => 'nombre', 'label' => 'Nombre completo', 'type' => 'text', 'required' => true, 'max' => 100],
            ['key' => 'edad', 'label' => 'Edad', 'type' => 'number', 'required' => true, 'min' => 0, 'max' => 120],
            ['key' => 'direccion', 'label' => 'Dirección', 'type' => 'text', 'required' => true, 'max' => 500],
            ['key' => 'ocupacion', 'label' => 'Ocupación / lugar de trabajo', 'type' => 'text', 'required' => false, 'max' => 150],
            ['key' => 'telefono', 'label' => 'Teléfono', 'type' => 'tel', 'required' => false, 'max' => 15],
        ];
    }

    /** @return list<array<string, mixed>> */
    public static function columnasFormacionAcademica(): array
    {
        return [
            ['key' => 'nivel', 'label' => 'Nivel', 'type' => 'text', 'required' => true, 'readonly' => true, 'options' => HistorialAcademico::NIVELES],
            ['key' => 'estado', 'label' => 'Estado', 'type' => 'select', 'required' => true, 'options' => ['completo' => 'Completo', 'incompleto' => 'Incompleto', 'en_curso' => 'En curso']],
            ['key' => 'carrera', 'label' => 'Carrera / especialidad', 'type' => 'text', 'required' => false, 'max' => 150],
            ['key' => 'institucion', 'label' => 'Institución', 'type' => 'text', 'required' => true, 'max' => 150],
            ['key' => 'anio', 'label' => 'Año', 'type' => 'number', 'required' => true, 'min' => 1950, 'max' => 2100],
            ['key' => 'respaldo', 'label' => '¿Posee respaldo?', 'type' => 'select', 'required' => true, 'options' => ['si' => 'Sí', 'no' => 'No']],
        ];
    }

    /** @return list<array<string, mixed>> */
    public static function columnasEmpleos(): array
    {
        return [
            ['key' => 'empresa', 'label' => 'Empresa', 'type' => 'text', 'required' => true, 'max' => 150],
            ['key' => 'puesto', 'label' => 'Puesto', 'type' => 'text', 'required' => true, 'max' => 100],
            ['key' => 'fecha_ingreso', 'label' => 'Fecha ingreso', 'type' => 'date', 'required' => true],
            ['key' => 'fecha_salida', 'label' => 'Fecha salida', 'type' => 'date', 'required' => false],
            ['key' => 'ultimo_salario', 'label' => 'Último salario (Q.)', 'type' => 'number', 'required' => false, 'min' => 0],
            ['key' => 'motivo_retiro', 'label' => 'Motivo retiro', 'type' => 'text', 'required' => true, 'max' => 200],
            ['key' => 'jefe_inmediato', 'label' => 'Jefe inmediato', 'type' => 'text', 'required' => false, 'max' => 100],
            ['key' => 'contacto_rrhh', 'label' => 'Contacto RRHH', 'type' => 'tel', 'required' => false, 'max' => 15],
            ['key' => 'tiene_constancia', 'label' => '¿Constancia?', 'type' => 'select', 'required' => true, 'options' => ['si' => 'Sí', 'no' => 'No']],
        ];
    }

    /** @return list<array<string, mixed>> */
    public static function columnasDeudas(): array
    {
        return [
            ['key' => 'entidad', 'label' => 'Entidad', 'type' => 'text', 'required' => true, 'max' => 150],
            ['key' => 'monto', 'label' => 'Monto original (Q.)', 'type' => 'number', 'required' => true, 'min' => 0],
            ['key' => 'saldo', 'label' => 'Saldo (Q.)', 'type' => 'number', 'required' => true, 'min' => 0],
            ['key' => 'cuota', 'label' => 'Cuota mensual (Q.)', 'type' => 'number', 'required' => true, 'min' => 0],
            ['key' => 'motivo', 'label' => 'Motivo', 'type' => 'text', 'required' => true, 'max' => 200],
            ['key' => 'antiguedad', 'label' => 'Antigüedad', 'type' => 'text', 'required' => false, 'max' => 50],
            ['key' => 'estatus', 'label' => 'Estatus', 'type' => 'select', 'required' => true, 'options' => ['al_dia' => 'Al día', 'atrasado' => 'Atrasado', 'pagado' => 'Pagado']],
            ['key' => 'meses_atraso', 'label' => 'Meses de atraso', 'type' => 'number', 'required' => false, 'min' => 0, 'max' => 120],
        ];
    }

    /** @return list<array<string, mixed>> */
    public static function columnasTatuajes(): array
    {
        return [
            ['key' => 'ubicacion', 'label' => 'Ubicación', 'type' => 'text', 'required' => true, 'max' => 100],
            ['key' => 'tamano', 'label' => 'Tamaño', 'type' => 'text', 'required' => true, 'max' => 50],
            ['key' => 'descripcion', 'label' => 'Descripción', 'type' => 'text', 'required' => true, 'max' => 200],
            ['key' => 'tiempo', 'label' => 'Tiempo', 'type' => 'text', 'required' => false, 'max' => 50],
            ['key' => 'visible_uniforme', 'label' => '¿Visible con uniforme?', 'type' => 'select', 'required' => true, 'options' => ['si' => 'Sí', 'no' => 'No']],
            ['key' => 'significado', 'label' => 'Significado', 'type' => 'text', 'required' => false, 'max' => 200],
        ];
    }

    /** @return list<array<string, mixed>> */
    public static function columnasPerforaciones(): array
    {
        return [
            ['key' => 'ubicacion', 'label' => 'Ubicación', 'type' => 'text', 'required' => true, 'max' => 100],
            ['key' => 'visible_uniforme', 'label' => '¿Visible con uniforme?', 'type' => 'select', 'required' => true, 'options' => ['si' => 'Sí', 'no' => 'No']],
            ['key' => 'fecha', 'label' => 'Fecha aproximada', 'type' => 'text', 'required' => false, 'max' => 50],
        ];
    }

    /**
     * @return array<string, list<array<string, string>>>
     */
    public static function extraerTablas(array &$datos, int $numero, string $tipoFormulario): array
    {
        $tablas = [];

        foreach (self::camposPorSeccion($numero, $tipoFormulario) as $campo => $columnas) {
            if (! array_key_exists($campo, $datos)) {
                continue;
            }

            $tablas[$campo] = self::normalizarFilas($datos[$campo], $columnas);
            unset($datos[$campo]);
        }

        return $tablas;
    }

    /**
     * @param  mixed  $input
     * @param  list<array<string, mixed>>  $columnas
     * @return list<array<string, string>>
     */
    public static function normalizarFilas(mixed $input, array $columnas): array
    {
        if (! is_array($input)) {
            return [];
        }

        $filas = [];

        foreach ($input as $fila) {
            if (! is_array($fila)) {
                continue;
            }

            $normalizada = [];
            $vacia = true;

            foreach ($columnas as $col) {
                $key = $col['key'];
                $valor = isset($fila[$key]) ? trim((string) $fila[$key]) : '';

                if ($valor !== '') {
                    $vacia = false;
                }

                $normalizada[$key] = $valor;
            }

            if (! $vacia) {
                $filas[] = $normalizada;
            }
        }

        return array_values($filas);
    }

    /** @return array<string, mixed> */
    public static function reglasValidacion(int $numero, string $tipoFormulario): array
    {
        $reglas = [];

        foreach (self::camposPorSeccion($numero, $tipoFormulario) as $campo => $columnas) {
            $gate = self::reglaGateTabla($campo);
            if ($gate !== null) {
                $reglas[$campo] = $gate;
            }

            foreach ($columnas as $col) {
                $field = "{$campo}.*.{$col['key']}";
                $fieldRules = [];

                if ($col['required'] ?? false) {
                    $fieldRules[] = 'required';
                } else {
                    $fieldRules[] = 'nullable';
                }

                $fieldRules[] = match ($col['type']) {
                    'number' => 'numeric',
                    'date' => 'date',
                    'select' => 'string',
                    default => 'string',
                };

                if (isset($col['max'])) {
                    $fieldRules[] = 'max:'.$col['max'];
                }

                if (($col['type'] ?? '') === 'number') {
                    if (isset($col['min'])) {
                        $fieldRules[] = 'min:'.$col['min'];
                    }
                }

                if (($col['type'] ?? '') === 'select' && ! empty($col['options'])) {
                    $fieldRules[] = 'in:'.implode(',', array_keys($col['options']));
                }

                if ($col['key'] === 'nombre') {
                    $fieldRules[] = 'regex:/^[a-zA-ZáéíóúÁÉÍÓÚüÜñÑ\\s]+$/';
                }

                $reglas[$field] = $fieldRules;
            }
        }

        if ($numero === 3 && in_array($tipoFormulario, ['preempleo', 'socioeconomico'], true)) {
            $reglas['formacion_academica'] = 'required_unless:ultimo_nivel_academico,ninguno|array|min:1';
        }

        return $reglas;
    }

    private static function reglaGateTabla(string $campo): ?string
    {
        return match ($campo) {
            'hijos' => 'exclude_unless:tiene_hijos,si|required|array|min:1',
            'hermanos' => 'exclude_unless:tiene_hermanos,si|required|array|min:1',
            'empleos' => 'exclude_unless:experiencia_previa,si|required|array|min:1',
            'deudas' => 'exclude_unless:tiene_deudas,si|required|array|min:1',
            'tatuajes' => 'exclude_unless:tiene_tatuajes,si|required|array|min:1',
            'perforaciones' => 'exclude_unless:tiene_perforaciones,si|required|array|min:1',
            default => null,
        };
    }

    /** @return array<string, string> */
    public static function mensajesValidacion(): array
    {
        return [
            'hijos.required' => 'Debe agregar al menos un hijo en la tabla.',
            'hijos.min' => 'Debe agregar al menos un hijo en la tabla.',
            'hijos.*.nombre.required' => 'El nombre del hijo es obligatorio.',
            'hijos.*.nombre.regex' => 'El nombre solo puede contener letras y espacios.',
            'hijos.*.edad.required' => 'La edad del hijo es obligatoria.',
            'hijos.*.vive_con_candidato.required' => 'Indique si el hijo vive con usted.',
            'hermanos.required' => 'Debe agregar al menos un hermano en la tabla.',
            'hermanos.min' => 'Debe agregar al menos un hermano en la tabla.',
            'hermanos.*.nombre.required' => 'El nombre del hermano es obligatorio.',
            'hermanos.*.direccion.required' => 'La dirección del hermano es obligatoria.',
            'formacion_academica.required' => 'Complete la tabla de formación académica.',
            'empleos.required' => 'Debe agregar al menos un empleo en la tabla.',
            'deudas.required' => 'Debe agregar al menos una deuda en la tabla.',
            'tatuajes.required' => 'Debe agregar al menos un tatuaje en la tabla.',
            'perforaciones.required' => 'Debe agregar al menos una perforación en la tabla.',
        ];
    }
}
