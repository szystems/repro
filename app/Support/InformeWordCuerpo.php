<?php

namespace App\Support;

use App\Models\Cuestionario;
use App\Models\EvaluadoOrden;
use App\Models\Orden;

/** E7/F7 — Texto del cuerpo del informe Word a partir del cuestionario y notas del evaluador. */
class InformeWordCuerpo
{
    public static function compilar(Orden $orden, EvaluadoOrden $evaluado): string
    {
        $lineas = ['DATOS FAMILIARES:'];

        $cuestionario = $evaluado->cuestionario;
        if ($cuestionario && InformePreempleo::aplicaATipo($cuestionario->tipo_formulario)) {
            $tablas = InformePreempleo::tablasParaAdmin($cuestionario);
            $lineas = array_merge($lineas, self::seccionFamiliar($tablas['familiar'] ?? []));
            $lineas[] = '';
            $lineas[] = 'NIVEL ACADÉMICO:';
            $lineas = array_merge($lineas, self::seccionFilas($tablas['academico'] ?? [], ['nivel', 'institucion', 'titulo', 'anio']));
            $lineas[] = '';
            $lineas[] = 'INFORMACIÓN LABORAL:';
            $lineas = array_merge($lineas, self::seccionFilas($tablas['laboral'] ?? [], ['empresa', 'puesto', 'fecha_inicio', 'fecha_fin', 'salario', 'motivo_retiro']));
            $lineas[] = '';
            $lineas[] = 'INFORMACIÓN COMPLEMENTARIA:';
            $lineas = array_merge($lineas, self::seccionComplementaria($tablas['complementaria'] ?? []));
            $lineas[] = '';
            $lineas[] = 'DEUDAS / ASPECTO ECONÓMICO:';
            $lineas = array_merge($lineas, self::seccionFilas($tablas['deudas'] ?? [], ['entidad', 'tipo', 'monto', 'cuota', 'estado']));
        } else {
            $lineas[] = '[Sin cuestionario completado — complete esta sección manualmente en Word.]';
            $lineas[] = '';
        }

        $lineas[] = '';
        $lineas[] = 'RECOMENDACIONES:';
        $lineas[] = trim((string) ($evaluado->notas_poligrafo ?: '—'));
        $lineas[] = '';
        $lineas[] = 'CONCLUSIONES:';
        $lineas[] = trim((string) ($evaluado->texto_informe_preliminar ?: '—'));
        $lineas[] = '';
        $lineas[] = 'Poligrafista: ' . trim((string) ($evaluado->poligrafista?->name ?: '—'));
        $lineas[] = 'Empresa solicitante: ' . trim((string) ($orden->empresa?->nombre ?: '—'));
        $lineas[] = 'Orden: ' . trim((string) ($orden->codigo_orden ?: '—'));

        return implode("\n", $lineas);
    }

    /**
     * @param  array<string, mixed>  $familiar
     * @return list<string>
     */
    private static function seccionFamiliar(array $familiar): array
    {
        $lineas = [];

        $convive = $familiar['convive_con'] ?? [];
        if (is_array($convive) && $convive !== []) {
            $lineas[] = 'Convive con: ' . implode(', ', $convive);
        }

        foreach (['padre' => 'Padre', 'madre' => 'Madre'] as $clave => $etiqueta) {
            $dato = $familiar[$clave] ?? [];
            if (! is_array($dato) || empty($dato['nombre'])) {
                continue;
            }

            $lineas[] = self::formatearRegistro($etiqueta, $dato, ['nombre', 'edad', 'telefono', 'ocupacion']);
        }

        $pareja = $familiar['pareja'] ?? [];
        if (is_array($pareja) && ($pareja['tiene'] ?? false)) {
            $lineas[] = self::formatearRegistro('Pareja', $pareja, ['tipo', 'nombre', 'edad', 'telefono', 'ocupacion']);
        }

        $lineas = array_merge($lineas, self::seccionFilas($familiar['hijos'] ?? [], ['nombre', 'edad', 'convive_con']));
        $lineas = array_merge($lineas, self::seccionFilas($familiar['hermanos'] ?? [], ['nombre', 'edad', 'telefono', 'ocupacion', 'lugar_trabajo']));

        if ($lineas === []) {
            $lineas[] = '—';
        }

        return $lineas;
    }

    /**
     * @param  list<array<string, mixed>>|array<string, mixed>  $filas
     * @param  list<string>  $campos
     * @return list<string>
     */
    private static function seccionFilas(array $filas, array $campos): array
    {
        if ($filas === []) {
            return ['—'];
        }

        if (! array_is_list($filas)) {
            $filas = [$filas];
        }

        $lineas = [];
        $n = 1;

        foreach ($filas as $fila) {
            if (! is_array($fila)) {
                continue;
            }

            $partes = [];
            foreach ($campos as $campo) {
                $valor = trim((string) ($fila[$campo] ?? ''));
                if ($valor !== '') {
                    $partes[] = $valor;
                }
            }

            if ($partes !== []) {
                $lineas[] = ($n > 1 ? ($n . '. ') : '') . implode(' · ', $partes);
                $n++;
            }
        }

        return $lineas !== [] ? $lineas : ['—'];
    }

    /**
     * @param  list<array{pregunta: string, respuesta: string}>|array<int, mixed>  $filas
     * @return list<string>
     */
    private static function seccionComplementaria(array $filas): array
    {
        if ($filas === []) {
            return ['—'];
        }

        $lineas = [];

        foreach ($filas as $fila) {
            if (! is_array($fila)) {
                continue;
            }

            $pregunta = trim((string) ($fila['pregunta'] ?? ''));
            $respuesta = trim((string) ($fila['respuesta'] ?? ''));
            if ($pregunta === '' && $respuesta === '') {
                continue;
            }

            $lineas[] = ($pregunta !== '' ? $pregunta . ': ' : '') . ($respuesta !== '' ? $respuesta : '—');
        }

        return $lineas !== [] ? $lineas : ['—'];
    }

    /**
     * @param  array<string, mixed>  $dato
     * @param  list<string>  $campos
     */
    private static function formatearRegistro(string $etiqueta, array $dato, array $campos): string
    {
        $partes = [];
        foreach ($campos as $campo) {
            $valor = trim((string) ($dato[$campo] ?? ''));
            if ($valor !== '') {
                $partes[] = $valor;
            }
        }

        return $etiqueta . ': ' . ($partes !== [] ? implode(' · ', $partes) : '—');
    }
}
