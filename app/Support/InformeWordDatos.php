<?php

namespace App\Support;

use App\Models\Cuestionario;
use App\Models\EvaluadoOrden;
use App\Models\Orden;
use Carbon\Carbon;

/** Extrae datos del evaluado/orden/cuestionario para rellenar plantillas Word. */
class InformeWordDatos
{
    private const SECCION_FOTO_CANDIDATO = 'datos_personales';

    public static function seccionFotoCandidato(Cuestionario $cuestionario): string
    {
        return CuestionarioSecciones::slug(1, $cuestionario->tipo_formulario ?? 'preempleo');
    }

    /**
     * @return array<string, string>
     */
    public static function encabezado(Orden $orden, EvaluadoOrden $evaluado): array
    {
        $cuestionario = self::datosCuestionario($evaluado);

        $telefono = trim((string) ($evaluado->telefono ?: $evaluado->celular ?: $cuestionario['telefono_personal'] ?? ''));
        $direccion = trim((string) ($evaluado->direccion ?: $cuestionario['direccion_residencia'] ?? ''));
        $edad = trim((string) ($cuestionario['edad'] ?? ''));
        if ($edad !== '' && ! str_contains($edad, 'año')) {
            $edad .= ' años';
        }

        $agencia = trim((string) (
            $evaluado->sede?->nombre
            ?: $evaluado->sede_region_empresa
            ?: $orden->sede?->nombre
            ?: '—'
        ));

        $observaciones = collect([
            $evaluado->motivo_hecho_evaluacion,
            $evaluado->observaciones,
        ])->filter()->implode("\n\n");

        if ($observaciones === '') {
            $observaciones = '—';
        }

        return [
            'proceso' => self::etiquetaProceso($evaluado),
            'fecha' => self::formatearFecha($evaluado->fecha_realizada ?? now()),
            'nombre' => trim($evaluado->nombre . ' ' . $evaluado->apellidos),
            'puesto' => trim((string) ($evaluado->puesto_evaluar ?: '—')),
            'empresa' => trim((string) ($orden->empresa?->nombre ?: '—')),
            'agencia' => $agencia,
            'dpi' => self::formatearDpi((string) ($evaluado->dpi ?? '')),
            'telefono' => $telefono !== '' ? self::formatearTelefono($telefono) : '—',
            'lugar_fecha_nacimiento' => self::lugarFechaNacimiento($cuestionario),
            'edad' => $edad !== '' ? $edad : '—',
            'direccion' => $direccion !== '' ? $direccion : '—',
            'resultado' => self::resultadoInforme($evaluado),
            'observaciones' => $observaciones,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function tablas(EvaluadoOrden $evaluado): array
    {
        $cuestionario = $evaluado->cuestionario;
        if (! $cuestionario) {
            return [];
        }

        $tipo = $evaluado->tipoFormularioCuestionario();

        if (InformePreempleo::aplicaATipo($tipo)) {
            return InformePreempleo::tablasParaAdmin($cuestionario);
        }

        if (! in_array($tipo, ['periodica', 'especifica'], true)) {
            return [];
        }

        $tablasS3 = $cuestionario->getTablasPorNumeroSeccion(3);
        $tablasS4 = $cuestionario->getTablasPorNumeroSeccion(4);
        $respuestasS3 = $cuestionario->obtenerRespuestasSeccion(3);

        return [
            'familiar' => ResumenFamiliar::compilar($cuestionario),
            'academico' => $tablasS3['formacion_academica'] ?? [],
            'laboral' => self::laboralPeriodica($tablasS3, $evaluado),
            'deudas' => $tablasS4['deudas'] ?? [],
            'labor_complementaria' => self::laborComplementariaPeriodica($respuestasS3, $tipo),
            'complementaria' => [],
        ];
    }

    public static function fotoEvaluadoRuta(EvaluadoOrden $evaluado): ?string
    {
        $cuestionario = $evaluado->cuestionario;
        if (! $cuestionario) {
            return null;
        }

        $seccionFoto = self::seccionFotoCandidato($cuestionario);
        $ruta = CuestionarioFotoCandidato::obtenerRuta($cuestionario->id, $seccionFoto);
        if (! $ruta || ! \Illuminate\Support\Facades\Storage::disk('local')->exists($ruta)) {
            return null;
        }

        return \Illuminate\Support\Facades\Storage::disk('local')->path($ruta);
    }

    public static function fotoEvaluadoBytes(EvaluadoOrden $evaluado): ?string
    {
        $path = self::fotoEvaluadoRuta($evaluado);
        if ($path === null) {
            return null;
        }

        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        if ($extension === 'png') {
            $bytes = file_get_contents($path);

            return $bytes !== false ? $bytes : null;
        }

        if (in_array($extension, ['jpg', 'jpeg'], true)) {
            $png = self::convertirJpegAPng($path);
            if ($png !== null) {
                return $png;
            }

            $bytes = file_get_contents($path);

            return $bytes !== false ? $bytes : null;
        }

        if ($extension === 'webp' && function_exists('imagecreatefromwebp')) {
            $imagen = @imagecreatefromwebp($path);
            if ($imagen === false) {
                return null;
            }

            ob_start();
            imagepng($imagen);
            $bytes = ob_get_clean();
            imagedestroy($imagen);

            return $bytes !== false ? $bytes : null;
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $tablasS3
     * @return list<array<string, mixed>>
     */
    private static function laboralPeriodica(array $tablasS3, EvaluadoOrden $evaluado): array
    {
        $empleo = $tablasS3['empleo_actual'][0] ?? null;
        if (! is_array($empleo)) {
            return [];
        }

        $fechas = trim((string) ($empleo['fechas_laboradas'] ?? ''));
        if ($fechas === '') {
            $ingreso = trim((string) ($empleo['fecha_ingreso'] ?? ''));
            if ($ingreso !== '') {
                try {
                    $fechas = Carbon::parse($ingreso)->format('d/m/Y');
                } catch (\Throwable) {
                    $fechas = $ingreso;
                }
            }
        }

        return [[
            'empresa' => $empleo['empresa'] ?? '',
            'puesto' => $empleo['puesto'] ?? '',
            'fechas' => $fechas,
            'salario' => isset($empleo['salario_actual']) ? (string) $empleo['salario_actual'] : '',
            'motivo' => trim((string) ($evaluado->motivo_hecho_evaluacion ?: '—')),
        ]];
    }

    /**
     * @param  array<string, mixed>  $respuestas
     * @return list<array{pregunta: string, respuesta: string}>
     */
    private static function laborComplementariaPeriodica(array $respuestas, string $tipoFormulario): array
    {
        $filas = [];

        foreach (HistorialLaboralPeriodico::PREGUNTAS as $indice => $pregunta) {
            $valor = trim((string) ($respuestas[$pregunta['key']] ?? ''));
            if ($valor === '') {
                continue;
            }

            $label = $indice === 0 && $tipoFormulario === 'especifica'
                ? HistorialLaboralPeriodico::labelPregunta1(true)
                : $pregunta['label'];

            $filas[] = [
                'pregunta' => $label,
                'respuesta' => $valor,
            ];
        }

        $adicional = trim((string) ($respuestas[HistorialLaboralPeriodico::CAMPO_INFORMACION_ADICIONAL['key']] ?? ''));
        if ($adicional !== '') {
            $filas[] = [
                'pregunta' => HistorialLaboralPeriodico::CAMPO_INFORMACION_ADICIONAL['label'],
                'respuesta' => $adicional,
            ];
        }

        return $filas;
    }

    private static function convertirJpegAPng(string $path): ?string
    {
        if (! function_exists('imagecreatefromjpeg')) {
            return null;
        }

        $imagen = @imagecreatefromjpeg($path);
        if ($imagen === false) {
            return null;
        }

        ob_start();
        imagepng($imagen);
        $bytes = ob_get_clean();
        imagedestroy($imagen);

        return $bytes !== false ? $bytes : null;
    }

    /**
     * @return array<string, mixed>
     */
    private static function datosCuestionario(EvaluadoOrden $evaluado): array
    {
        if (! $evaluado->cuestionario) {
            return [];
        }

        return $evaluado->cuestionario->obtenerRespuestasSeccion(1);
    }

    private static function etiquetaProceso(EvaluadoOrden $evaluado): string
    {
        $servicio = match ($evaluado->tipo_servicio) {
            'poligrafo' => 'Polígrafo',
            'vsa' => 'VSA',
            'socioeconomico' => 'Estudio Socioeconómico',
            default => ucfirst((string) $evaluado->tipo_servicio),
        };

        return match ($evaluado->tipo_formulario) {
            'preempleo' => 'Prueba de ' . $servicio . ' Pre-empleo',
            'periodica' => 'Prueba de ' . $servicio . ' - Periódica',
            'especifica' => 'Prueba de ' . $servicio . ' - Específica',
            default => 'Prueba de ' . $servicio,
        };
    }

    private static function resultadoInforme(EvaluadoOrden $evaluado): string
    {
        if (! $evaluado->resultado || $evaluado->resultado === 'pendiente') {
            return 'PENDIENTE';
        }

        $texto = strtoupper($evaluado->resultado_texto);

        return match ($evaluado->resultado) {
            'aprobado' => 'APROBADO',
            'aprobado_con_obs' => 'APROBADO CON OBSERVACIONES',
            'aprobado_excepcion' => 'APROBADO CON EXCEPCIÓN',
            'no_aprobado' => 'NO APROBADO',
            'inconcluso' => 'INCONCLUSO',
            default => $texto,
        };
    }

    /**
     * @param  array<string, mixed>  $cuestionario
     */
    private static function lugarFechaNacimiento(array $cuestionario): string
    {
        $municipio = trim((string) ($cuestionario['municipio_nacimiento'] ?? ''));
        $fechaRaw = trim((string) ($cuestionario['fecha_nacimiento'] ?? ''));

        if ($municipio === '' && $fechaRaw === '') {
            return '—';
        }

        $fechaTexto = '—';
        if ($fechaRaw !== '') {
            try {
                $fechaTexto = Carbon::parse($fechaRaw)->locale('es')->translatedFormat('j \d\e F \d\e Y');
            } catch (\Throwable) {
                $fechaTexto = $fechaRaw;
            }
        }

        if ($municipio === '') {
            return $fechaTexto;
        }

        if ($fechaTexto === '—') {
            return $municipio;
        }

        return $municipio . ', ' . $fechaTexto;
    }

    private static function formatearFecha(mixed $fecha): string
    {
        if ($fecha instanceof Carbon) {
            return $fecha->format('d/m/Y');
        }

        if ($fecha) {
            try {
                return Carbon::parse($fecha)->format('d/m/Y');
            } catch (\Throwable) {
                return (string) $fecha;
            }
        }

        return now()->format('d/m/Y');
    }

    private static function formatearDpi(string $dpi): string
    {
        $digits = preg_replace('/\D/', '', $dpi) ?? '';

        if (strlen($digits) === 13) {
            return substr($digits, 0, 4) . ' ' . substr($digits, 4, 5) . ' ' . substr($digits, 9, 4);
        }

        return $dpi !== '' ? $dpi : '—';
    }

    private static function formatearTelefono(string $telefono): string
    {
        $digits = preg_replace('/\D/', '', $telefono) ?? '';

        if (strlen($digits) === 8) {
            return substr($digits, 0, 4) . ' ' . substr($digits, 4, 4);
        }

        return $telefono;
    }
}
