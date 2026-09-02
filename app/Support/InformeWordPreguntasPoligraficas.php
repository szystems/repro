<?php

namespace App\Support;

use App\Models\EvaluadorNota;
use App\Models\EvaluadoOrden;

/** Tabla editable de preguntas poligráficas (última hoja del informe Word). */
class InformeWordPreguntasPoligraficas
{
    public const SECCION_NOTA = 'word_preguntas_poligraficas';

    /** @var list<array{pregunta: string, respuesta: string, resultado: string, puntuacion: string}> */
    public const FILAS_PLANTILLA = [
        [
            'pregunta' => '¿Cometió usted delitos en empleos anteriores?',
            'respuesta' => 'No',
            'resultado' => '',
            'puntuacion' => '',
        ],
        [
            'pregunta' => '¿Realizó usted robos mayores a Q.100.00 en empleos anteriores?',
            'respuesta' => 'No',
            'resultado' => '',
            'puntuacion' => '',
        ],
        [
            'pregunta' => '¿En los últimos 6 meses ha consumido drogas ilegales?',
            'respuesta' => 'No',
            'resultado' => '',
            'puntuacion' => '',
        ],
        [
            'pregunta' => '¿Usted pertenece algún tipo de grupo delictivo?',
            'respuesta' => 'No',
            'resultado' => '',
            'puntuacion' => '',
        ],
        [
            'pregunta' => '¿Está usted presentando algún documento o información falsa en este proceso de contratación?',
            'respuesta' => 'No',
            'resultado' => '',
            'puntuacion' => '',
        ],
    ];

    /** Valores que van a la columna Respuesta del Word (plantillas cliente). */
    public const RESPUESTAS = ['No', 'Sí'];

    public static function aplicaA(EvaluadoOrden $evaluado): bool
    {
        // Polígrafo y VSA muestran tabla editable; VSA no usa puntuación en plantilla.
        return in_array($evaluado->tipo_servicio ?? '', ['poligrafo', 'vsa'], true);
    }

    public static function usaPuntuacion(EvaluadoOrden $evaluado): bool
    {
        return ($evaluado->tipo_servicio ?? '') === 'poligrafo';
    }

    /**
     * FORMATOS: periódica/específica dejan preguntas en blanco (editables por caso).
     */
    public static function preguntasEnBlanco(EvaluadoOrden $evaluado): bool
    {
        return in_array($evaluado->tipo_formulario ?? '', ['periodica', 'especifica'], true);
    }

    /**
     * @return list<array{pregunta: string, respuesta: string, resultado: string, puntuacion: string}>
     */
    public static function filasGuardadas(int $evaluadoOrdenId): array
    {
        $raw = trim((string) (EvaluadorNotasSupport::mapaPorSeccion($evaluadoOrdenId)[self::SECCION_NOTA] ?? ''));

        return self::decodificar($raw);
    }

    public static function esDi(string $resultado): bool
    {
        $n = mb_strtoupper(preg_replace('/\s+/', '', $resultado) ?? '');
        $n = strtr($n, ['Á' => 'A', 'É' => 'E', 'Í' => 'I', 'Ó' => 'O', 'Ú' => 'U']);
        if ($n === '' || str_starts_with($n, 'NDI')) {
            return false;
        }

        return $n === 'DI' || str_starts_with($n, 'DI') || str_contains($n, 'INDICACIONDEMENTIRA');
    }

    /**
     * @param  list<array{pregunta?: string, resultado?: string}>  $filas
     */
    public static function textoConclusionDi(array $filas): string
    {
        $lineas = [];
        foreach (array_values($filas) as $indice => $fila) {
            if (! self::esDi((string) ($fila['resultado'] ?? ''))) {
                continue;
            }
            $pregunta = trim((string) ($fila['pregunta'] ?? ''));
            if ($pregunta === '') {
                continue;
            }
            $lineas[] = 'R'.($indice + 1).'. '.$pregunta;
        }

        return implode("\n", $lineas);
    }

    /**
     * @return list<array{pregunta: string, respuesta: string, resultado: string, puntuacion: string}>
     */
    public static function filas(int $evaluadoOrdenId, ?EvaluadoOrden $evaluado = null): array
    {
        $filas = self::filasGuardadas($evaluadoOrdenId);

        if ($filas !== []) {
            return $filas;
        }

        $evaluado ??= EvaluadoOrden::find($evaluadoOrdenId);
        $resultadoDefault = $evaluado ? self::codigoResultadoDefault($evaluado) : 'NDI';

        if ($evaluado && self::preguntasEnBlanco($evaluado)) {
            return array_map(
                static fn (): array => [
                    'pregunta' => '',
                    'respuesta' => 'No',
                    'resultado' => $resultadoDefault,
                    'puntuacion' => '',
                ],
                range(1, 5)
            );
        }

        return array_map(
            static fn (array $fila): array => array_merge($fila, ['resultado' => $resultadoDefault]),
            self::FILAS_PLANTILLA
        );
    }

    /**
     * @param  array<int, array<string, mixed>>|null  $input
     */
    public static function guardarDesdeRequest(int $evaluadoOrdenId, ?array $input, ?int $userId): void
    {
        if ($input === null) {
            return;
        }

        $filas = [];
        foreach ($input as $fila) {
            if (! is_array($fila)) {
                continue;
            }

            $pregunta = trim((string) ($fila['pregunta'] ?? ''));
            if ($pregunta === '') {
                continue;
            }

            $filas[] = [
                'pregunta' => $pregunta,
                'respuesta' => self::normalizarRespuesta((string) ($fila['respuesta'] ?? '')),
                'resultado' => trim((string) ($fila['resultado'] ?? '')),
                'puntuacion' => trim((string) ($fila['puntuacion'] ?? '')),
            ];
        }

        EvaluadorNota::guardarNota(
            $evaluadoOrdenId,
            self::SECCION_NOTA,
            '',
            $filas === [] ? null : json_encode($filas, JSON_UNESCAPED_UNICODE),
            $userId
        );
    }

    /**
     * @return list<array{pregunta: string, respuesta: string, resultado: string, puntuacion: string}>
     */
    private static function decodificar(string $raw): array
    {
        if ($raw === '') {
            return [];
        }

        $decoded = json_decode($raw, true);
        if (! is_array($decoded)) {
            return [];
        }

        $filas = [];
        foreach ($decoded as $fila) {
            if (! is_array($fila)) {
                continue;
            }

            $pregunta = trim((string) ($fila['pregunta'] ?? ''));
            if ($pregunta === '') {
                continue;
            }

            $filas[] = [
                'pregunta' => $pregunta,
                'respuesta' => self::normalizarRespuesta((string) ($fila['respuesta'] ?? '')),
                'resultado' => trim((string) ($fila['resultado'] ?? '')),
                'puntuacion' => trim((string) ($fila['puntuacion'] ?? '')),
            ];
        }

        return $filas;
    }

    /**
     * M-P4: la plantilla Word ya trae "No"; vacío o "—" no se vuelca (se usa No).
     * "SI"/"NO" del evaluador se normalizan a No/Sí.
     */
    public static function respuestaParaWord(mixed $valor): string
    {
        $texto = self::normalizarRespuesta((string) $valor);

        return $texto === '' ? 'No' : $texto;
    }

    public static function normalizarRespuesta(string $valor): string
    {
        $texto = trim($valor);
        if ($texto === '' || $texto === '—') {
            return '';
        }

        $clave = strtr(mb_strtolower($texto), ['í' => 'i', 'é' => 'e']);

        return match ($clave) {
            'no' => 'No',
            'si', 'yes' => 'Sí',
            default => $texto,
        };
    }

    private static function codigoResultadoDefault(EvaluadoOrden $evaluado): string
    {
        return match ($evaluado->resultado) {
            'no_aprobado' => 'DI',
            'inconcluso' => 'INCONCLUSO',
            default => 'NDI',
        };
    }
}
