<?php

namespace App\Support;

use App\Models\EvaluadoOrden;

/**
 * Arma las filas de un paquete. Un valor vacío sigue vacío.
 */
class AsistenteRedaccionCampos
{
    /**
     * @param  list<array{clave: string, pregunta: string, tipo?: string, depende_de?: string}>  $definiciones
     * @param  array<string, mixed>  $respuestas
     * @param  list<array{clave: string, pregunta: string, tipo?: string}>  $legado
     * @return list<array{clave: string, pregunta: string, valor: string, texto: string, vacio: bool, incompleto: bool}>
     */
    public static function lista(array $definiciones, array $respuestas, array $legado = []): array
    {
        $campos = [];
        foreach ($definiciones as $definicion) {
            $campos[] = self::campo($definicion, $respuestas, $campos);
        }

        foreach ($legado as $definicion) {
            $armado = self::campo($definicion + ['tipo' => $definicion['tipo'] ?? 'texto'], $respuestas, $campos);
            if ($armado['vacio']) {
                continue;
            }
            $campos[] = $armado;
        }

        return $campos;
    }

    /**
     * @param  list<array<string, mixed>>  $filas
     * @param  list<array{key: string, label: string, options?: array<string, string>}>  $columnas
     * @param  list<string>  $omitir
     * @return list<array{clave: string, pregunta: string, valor: string, texto: string, vacio: bool, incompleto: bool}>
     */
    public static function tabla(string $clave, string $preguntaVacia, array $filas, array $columnas, array $omitir = []): array
    {
        $filasLimpias = [];
        foreach ($filas as $fila) {
            if (! is_array($fila)) {
                continue;
            }
            $partes = [];
            foreach ($columnas as $columna) {
                $key = $columna['key'];
                if (in_array($key, $omitir, true) || self::columnaSensible($key)) {
                    continue;
                }
                $valor = trim((string) ($fila[$key] ?? ''));
                if ($valor === '') {
                    continue;
                }
                $texto = $columna['options'][$valor] ?? $valor;
                $partes[] = $columna['label'].': '.$texto;
            }
            if ($partes !== []) {
                $filasLimpias[] = implode('. ', $partes);
            }
        }

        if ($filasLimpias === []) {
            return [[
                'clave' => $clave,
                'pregunta' => $preguntaVacia,
                'valor' => '',
                'texto' => '',
                'vacio' => true,
                'incompleto' => true,
            ]];
        }

        $campos = [];
        foreach ($filasLimpias as $indice => $texto) {
            $campos[] = [
                'clave' => $clave.'_'.($indice + 1),
                'pregunta' => $preguntaVacia.' '.($indice + 1),
                'valor' => $texto,
                'texto' => $texto,
                'vacio' => false,
                'incompleto' => false,
            ];
        }

        return $campos;
    }

    /**
     * @param  list<array{clave: string, pregunta: string, valor: string, texto: string, vacio: bool, incompleto: bool}>  $campos
     * @return array<string, mixed>
     */
    public static function cerrar(EvaluadoOrden $evaluado, string $seccion, string $titulo, array $campos, ?string $observacion): array
    {
        if ($observacion === null) {
            $notas = EvaluadorNotasSupport::mapaPorSeccion($evaluado->id);
            $observacion = (string) ($notas[$seccion] ?? '');
        }

        $paquete = [
            'seccion' => $seccion,
            'titulo' => $titulo,
            'servicio' => (string) ($evaluado->tipo_servicio ?? ''),
            'formulario' => (string) ($evaluado->tipo_formulario ?: $evaluado->cuestionario?->tipo_formulario ?: ''),
            'campos' => $campos,
            'observacion_evaluador' => trim($observacion),
            'texto_para_modelo' => '',
        ];
        $paquete['texto_para_modelo'] = self::textoParaModelo($paquete);

        return $paquete;
    }

    /**
     * @param  array{titulo?: string, servicio: string, formulario: string, campos: list<array{pregunta: string, texto: string, vacio: bool}>, observacion_evaluador: string}  $paquete
     */
    public static function textoParaModelo(array $paquete): string
    {
        $lineas = [
            'Sección: '.($paquete['titulo'] ?? 'Salud'),
            'Tipo de informe: '.trim($paquete['servicio'].' '.$paquete['formulario']),
            '',
            'Respuestas del formulario:',
        ];

        foreach ($paquete['campos'] as $campo) {
            $lineas[] = '- '.$campo['pregunta'].': '.self::textoDeCampo($campo);
        }

        $lineas[] = '';
        $lineas[] = 'Observación del evaluador:';
        $lineas[] = $paquete['observacion_evaluador'] !== ''
            ? $paquete['observacion_evaluador']
            : '(ninguna)';

        return implode("\n", $lineas);
    }

    /**
     * @param  array{clave: string, pregunta: string, tipo?: string, depende_de?: string}  $definicion
     * @param  array<string, mixed>  $respuestas
     * @param  list<array{clave: string, valor: string}>  $yaArmados
     * @return array{clave: string, pregunta: string, valor: string, texto: string, vacio: bool, incompleto: bool}
     */
    private static function campo(array $definicion, array $respuestas, array $yaArmados): array
    {
        $tipo = $definicion['tipo'] ?? 'texto';
        $crudo = $respuestas[$definicion['clave']] ?? null;
        $valor = self::valorPlano($tipo, $crudo);
        $texto = self::texto($tipo, $valor);
        $vacio = $texto === '';

        return [
            'clave' => $definicion['clave'],
            'pregunta' => $definicion['pregunta'],
            'valor' => $valor,
            'texto' => $texto,
            'vacio' => $vacio,
            'incompleto' => $vacio && ! self::detalleCubiertoPorNo($definicion, $yaArmados),
        ];
    }

    private static function valorPlano(string $tipo, mixed $crudo): string
    {
        if ($tipo === 'sustancias') {
            $lista = SaludHabitosCampos::sustanciasDesdeAlmacenamiento($crudo);

            return $lista === [] ? '' : implode(',', $lista);
        }

        return is_scalar($crudo) ? trim((string) $crudo) : '';
    }

    /**
     * @param  array{depende_de?: string}  $definicion
     * @param  list<array{clave: string, valor: string}>  $yaArmados
     */
    private static function detalleCubiertoPorNo(array $definicion, array $yaArmados): bool
    {
        $padre = $definicion['depende_de'] ?? '';
        if ($padre === '') {
            return false;
        }

        foreach ($yaArmados as $campo) {
            if ($campo['clave'] === $padre) {
                return $campo['valor'] === 'no';
            }
        }

        return false;
    }

    /**
     * @param  array{texto: string, vacio: bool}  $campo
     */
    private static function textoDeCampo(array $campo): string
    {
        if ($campo['vacio']) {
            return '(vacío)';
        }

        $texto = $campo['texto'];
        if (! self::esDenegacion($texto)) {
            return $texto;
        }

        return rtrim($texto, ". \t").'. No lo ha realizado.';
    }

    /**
     * Un No o un N/A escrito por el candidato es una respuesta: no lo hizo.
     * Una frase más larga se deja igual para no recortarla.
     */
    private static function esDenegacion(string $texto): bool
    {
        $normal = mb_strtolower(trim($texto));
        $normal = rtrim($normal, ". \t");
        $compacto = str_replace([' ', '.'], '', $normal);

        return in_array($normal, ['no', 'ninguna', 'ninguno', 'ninguno/a', 'no aplica'], true)
            || in_array($compacto, ['no', 'na', 'n/a'], true);
    }

    private static function texto(string $tipo, string $valor): string
    {
        if ($valor === '') {
            return '';
        }

        return match ($tipo) {
            'si_no' => match ($valor) {
                'si' => 'Sí',
                'no' => 'No',
                default => $valor,
            },
            'estado' => SaludHabitosCampos::ESTADOS_GENERAL[SaludHabitosCampos::normalizarEstadoGeneral($valor)] ?? $valor,
            'sustancias' => implode(', ', array_map(
                fn (string $clave): string => SaludHabitosCampos::SUSTANCIAS[$clave] ?? $clave,
                explode(',', $valor)
            )),
            'vivienda' => match ($valor) {
                'propia_pagada' => 'Propia, pagada',
                'propia_pagando' => 'Propia, pagando',
                'alquilada' => 'Alquilada',
                'prestada' => 'Prestada',
                'familiar' => 'Familiar',
                'otro' => 'Otra',
                default => $valor,
            },
            default => $valor,
        };
    }

    private static function columnaSensible(string $key): bool
    {
        $key = strtolower($key);

        foreach (['telefono', 'dpi', 'foto', 'correo', 'email', 'jefe', 'rrhh'] as $fragmento) {
            if (str_contains($key, $fragmento)) {
                return true;
            }
        }

        return false;
    }
}
