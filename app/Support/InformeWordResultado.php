<?php

namespace App\Support;

use App\Models\EvaluadoOrden;

/**
 * Un resultado en el sistema → 1ª y última hoja del Word (M-P3 / M-S3).
 *
 * Poli/VSA: cuadro APROBADO / NO APROBADO / EXCEPCIÓN (colores de plantilla).
 * Socio: tabla Tipo A / A observaciones / A-Condicionado / B / C (no reusa el combo de poli).
 */
class InformeWordResultado
{
    public const NOTA_INDICACION_MENTIRA = 'word_resultado_mentira';

    public const NOTA_ASPECTO_EXCEPCION = 'word_resultado_excepcion';

    public const OPCION_APROBADO = 'aprobado';

    public const OPCION_NO_APROBADO = 'no_aprobado';

    public const OPCION_EXCEPCION = 'excepcion';

    /** Marca histórica `[ X ]`. Poli/VSA/socio: ya no se pone; queda solo la fila de color. */
    public const MARCA = '[ X ] ';

    public const ETIQUETA_MENTIRA = 'Indicación de mentira en las preguntas:';

    public const ETIQUETA_EXCEPCION = 'Aspecto que origina la excepción:';

    /** Texto de la plantilla poli/VSA (última hoja); se sustituye según el resultado. */
    public const FRASE_NO_VERACIDAD_PLANTILLA = 'NO RESPONDIÓ CON VERACIDAD A LAS PREGUNTAS RELEVANTES EVALUADAS.';

    public const FRASE_NO_VERACIDAD = 'NO RESPONDIÓ CON VERACIDAD A LAS SIGUIENTES PREGUNTAS RELEVANTES:';

    public const FRASE_SI_VERACIDAD = 'SÍ RESPONDIÓ CON VERACIDAD A LAS PREGUNTAS RELEVANTES EVALUADAS.';

    public const FRASE_EXCEPCION_VERACIDAD = 'RESPONDIÓ CON VERACIDAD A LAS PREGUNTAS RELEVANTES EVALUADAS, CON EXCEPCIÓN.';

    public const MARCADOR_ULTIMA_HOJA = 'CONFIABLE CON EXCEPCION';

    public const MARCADOR_PREGUNTAS_DI = 'Las siguientes preguntas relevantes presentaron reacciones psicofisiológicas significativas:';

    /** Plantilla poli/VSA: «a la evaluado(a) como» en rojo. Queda fijo para hombre y mujer. */
    public const FRASE_CLASIFICA_PLANTILLA = 'se clasifica a la evaluado(a) como:';

    public const FRASE_CLASIFICA = 'se clasifica al evaluado(a):';

    public const MARCADOR_CLASIFICACION_SOCIO = 'CLASIFICACIÓN';

    public const MARCADOR_CONCLUSIONES_SOCIO = 'se establece la siguiente clasificación';

    /**
     * Opción del cuadro que corresponde al resultado registrado. Devuelve null cuando el informe
     * está pendiente o inconcluso, dejando el cuadro tal como viene en la plantilla original.
     */
    public static function opcionMarcada(EvaluadoOrden $evaluado): ?string
    {
        return match ($evaluado->resultado) {
            'aprobado', 'aprobado_con_obs' => self::OPCION_APROBADO,
            'no_aprobado' => self::OPCION_NO_APROBADO,
            'aprobado_excepcion' => self::OPCION_EXCEPCION,
            default => null,
        };
    }

    /**
     * @return array{mentira: string, excepcion: string}
     */
    public static function detalles(int $evaluadoOrdenId): array
    {
        $notas = EvaluadorNotasSupport::mapaPorSeccion($evaluadoOrdenId);

        return [
            'mentira' => trim((string) ($notas[self::NOTA_INDICACION_MENTIRA] ?? '')),
            'excepcion' => trim((string) ($notas[self::NOTA_ASPECTO_EXCEPCION] ?? '')),
        ];
    }

    public static function esSocio(EvaluadoOrden $evaluado): bool
    {
        return ($evaluado->tipo_servicio ?? '') === 'socioeconomico';
    }

    /**
     * Opciones del campo único en la redacción Word (no incluye pendiente).
     *
     * @return array<string, string>
     */
    public static function opcionesInforme(EvaluadoOrden $evaluado): array
    {
        if (self::esSocio($evaluado)) {
            return [
                'tipo_a' => 'Tipo A — Recomendable',
                'aprobado_con_obs' => 'Tipo A — Presenta observaciones',
                'a_condicionado' => 'A - Condicionado',
                'tipo_b' => 'Tipo B',
                'tipo_c' => 'Tipo C',
            ];
        }

        return [
            'aprobado' => 'Aprobado',
            'no_aprobado' => 'No aprobado',
            'aprobado_excepcion' => 'Aprobado con excepción',
        ];
    }

    public static function guardarEnEvaluado(EvaluadoOrden $evaluado, string $valor): void
    {
        $valor = trim($valor);
        if ($valor === '' || $valor === 'pendiente') {
            $evaluado->update(['resultado' => $valor === 'pendiente' ? 'pendiente' : null]);

            return;
        }

        if (! array_key_exists($valor, self::opcionesInforme($evaluado))) {
            return;
        }

        $evaluado->update(['resultado' => $valor]);
    }

    /** Clasificación socio (1ª hoja CLASIFICACIÓN y última CONCLUSIONES). */
    public static function opcionMarcadaSocio(EvaluadoOrden $evaluado): ?string
    {
        return match ($evaluado->resultado) {
            'tipo_a' => 'tipo_a',
            'aprobado_con_obs' => 'aprobado_con_obs',
            'a_condicionado' => 'a_condicionado',
            'tipo_b' => 'tipo_b',
            'tipo_c' => 'tipo_c',
            default => null,
        };
    }

    /** Fila de la última hoja poli/VSA (NDI / DI / excepción). */
    public static function opcionDeTextoUltimaHoja(string $textoFila): ?string
    {
        $texto = mb_strtoupper(self::sinAcentos(trim($textoFila)));

        if ($texto === '' || str_contains($texto, 'ASPECTO QUE ORIGINA')) {
            return null;
        }

        if (str_contains($texto, 'EXCEPCION') || str_contains($texto, 'CONFIABLE')) {
            return self::OPCION_EXCEPCION;
        }

        if (str_contains($texto, 'NO APROBADO')) {
            return self::OPCION_NO_APROBADO;
        }

        if (str_contains($texto, 'APROBADO')) {
            return self::OPCION_APROBADO;
        }

        return null;
    }

    /** Fila de CLASIFICACIÓN / CONCLUSIONES socio. */
    public static function opcionDeTextoSocio(string $textoFila): ?string
    {
        $texto = mb_strtoupper(self::sinAcentos(trim($textoFila)));

        if ($texto === '' || $texto === 'CLASIFICACION:') {
            return null;
        }

        if (str_contains($texto, 'OBSERVACIONES A CONSIDERAR')) {
            return 'aprobado_con_obs';
        }

        if (str_contains($texto, 'CONDICIONADO')) {
            return 'a_condicionado';
        }

        if (str_contains($texto, 'TIPO C') || str_contains($texto, 'NO RECOMENDABLE')) {
            return 'tipo_c';
        }

        if (str_contains($texto, 'TIPO B')) {
            return 'tipo_b';
        }

        if (str_contains($texto, 'TIPO A') || str_contains($texto, 'RECOMENDABLE')) {
            return 'tipo_a';
        }

        return null;
    }

    public static function fraseVeracidad(?string $opcionMarcada): ?string
    {
        return match ($opcionMarcada) {
            self::OPCION_APROBADO => self::FRASE_SI_VERACIDAD,
            self::OPCION_NO_APROBADO => self::FRASE_NO_VERACIDAD,
            self::OPCION_EXCEPCION => self::FRASE_SI_VERACIDAD,
            default => null,
        };
    }

    /** Opción a la que pertenece una fila del cuadro, según su etiqueta. */
    public static function opcionDeTexto(string $textoFila): ?string
    {
        $texto = mb_strtoupper(self::sinAcentos(trim($textoFila)));

        if ($texto === '') {
            return null;
        }

        // El orden importa: «APROBADO CON EXCEPCION» también contiene «APROBADO».
        if (str_contains($texto, 'EXCEPCION')) {
            return self::OPCION_EXCEPCION;
        }

        if (str_contains($texto, 'NO APROBADO')) {
            return self::OPCION_NO_APROBADO;
        }

        if (str_contains($texto, 'APROBADO')) {
            return self::OPCION_APROBADO;
        }

        return null;
    }

    private static function sinAcentos(string $texto): string
    {
        return strtr($texto, [
            'Á' => 'A', 'É' => 'E', 'Í' => 'I', 'Ó' => 'O', 'Ú' => 'U',
            'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u',
        ]);
    }
}
