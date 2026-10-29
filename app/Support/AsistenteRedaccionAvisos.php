<?php

namespace App\Support;

/**
 * Avisos de vacíos y de diferencias. No modifican las respuestas del candidato.
 */
class AsistenteRedaccionAvisos
{
    /**
     * @param  array{campos: list<array{clave: string, pregunta: string, valor: string, vacio: bool, incompleto: bool}>, observacion_evaluador: string}  $paquete
     * @return list<string>
     */
    public static function de(array $paquete, bool $restoSinCambios): array
    {
        $avisos = [];
        $incompletos = [];
        foreach ($paquete['campos'] as $campo) {
            if ($campo['incompleto']) {
                $incompletos[] = $campo['pregunta'];
            }
        }

        foreach (array_slice($incompletos, 0, 8) as $pregunta) {
            $avisos[] = 'Sin respuesta: '.$pregunta.'. Un vacío no es un no.';
        }
        if (count($incompletos) > 8) {
            $avisos[] = 'Hay '.(count($incompletos) - 8).' campos vacíos más. Un vacío no es un no.';
        }
        if ($restoSinCambios && $incompletos !== []) {
            $avisos[] = 'Confirmar el resto sin cambios no convierte un campo vacío en una respuesta negativa.';
        }

        $observacion = mb_strtolower($paquete['observacion_evaluador']);
        if ($observacion === '') {
            return $avisos;
        }

        foreach (self::pistas($paquete['campos']) as $pista) {
            $campo = self::buscar($paquete['campos'], $pista['clave']);
            if ($campo === null) {
                continue;
            }
            $menciona = false;
            foreach ($pista['terminos'] as $termino) {
                if (str_contains($observacion, $termino)) {
                    $menciona = true;
                    break;
                }
            }
            if (! $menciona) {
                continue;
            }
            if ($campo['vacio'] || $campo['valor'] === 'no') {
                $avisos[] = 'La observación menciona «'.$pista['terminos'][0].'» y «'.$campo['pregunta'].'» está '
                    .($campo['vacio'] ? 'vacío' : 'en No')
                    .'. Revíselo antes de aceptar.';
            }
        }

        return $avisos;
    }

    /**
     * @param  list<array{clave: string}>  $campos
     * @return list<array{clave: string, terminos: list<string>}>
     */
    private static function pistas(array $campos): array
    {
        $claves = array_column($campos, 'clave');
        $posibles = [
            ['clave' => 'salud_alergias', 'terminos' => ['alerg']],
            ['clave' => 'salud_tratamiento_medico', 'terminos' => ['medicament', 'tratamiento méd']],
            ['clave' => 'salud_hospitalizaciones', 'terminos' => ['hospital', 'cirug', 'operad']],
            ['clave' => 'salud_atencion_psicologica', 'terminos' => ['psicolog', 'psiquiatr']],
            ['clave' => 'tiene_deudas', 'terminos' => ['deuda', 'préstamo', 'prestamo']],
            ['clave' => 'integridad_05', 'terminos' => ['faltante']],
            ['clave' => 'judicial_02', 'terminos' => ['antecedente penal', 'antecedentes penales']],
            ['clave' => 'sustancias_usadas', 'terminos' => ['marihuana', 'cocaína', 'cocaina', 'droga']],
        ];

        return array_values(array_filter(
            $posibles,
            fn (array $pista): bool => in_array($pista['clave'], $claves, true)
        ));
    }

    /**
     * @param  list<array{clave: string, pregunta: string, valor: string, vacio: bool}>  $campos
     * @return array{clave: string, pregunta: string, valor: string, vacio: bool}|null
     */
    private static function buscar(array $campos, string $clave): ?array
    {
        foreach ($campos as $campo) {
            if ($campo['clave'] === $clave) {
                return $campo;
            }
        }

        return null;
    }
}
