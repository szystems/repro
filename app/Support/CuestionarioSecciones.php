<?php

namespace App\Support;

/**
 * Slugs y metadatos de secciones del cuestionario (compartido E1.8+).
 */
class CuestionarioSecciones
{
    /**
     * @return array<string, array<int, string>>
     */
    public static function slugsPorTipo(): array
    {
        return [
            'preempleo' => [
                1 => 'datos_personales',
                2 => 'informacion_familiar',
                3 => 'historial_laboral',
                4 => 'situacion_economica',
                5 => 'antecedentes',
                6 => 'firma_digital',
            ],
            'periodica' => [
                1 => 'actualizacion_datos',
                2 => 'cambios_familiares',
                3 => 'situacion_laboral',
                4 => 'antecedentes_recientes',
                5 => 'firma_digital',
            ],
            'especifica' => [
                1 => 'datos_basicos',
                2 => 'situacion_especifica',
                3 => 'antecedentes_relevantes',
                4 => 'firma_digital',
            ],
            'socioeconomico' => [
                1 => 'datos_personales',
                2 => 'informacion_familiar',
                3 => 'historial_laboral',
                4 => 'situacion_economica',
                5 => 'antecedentes',
                6 => 'informacion_socioeconomica_complementaria',
            ],
        ];
    }

    public static function slug(int $numero, string $tipo): string
    {
        $slugs = self::slugsPorTipo();

        return $slugs[$tipo][$numero] ?? 'seccion_'.$numero;
    }

    /**
     * Bloques para notas internas del evaluador (excluye firma digital).
     *
     * @return list<array{numero: int, slug: string, titulo: string}>
     */
    public static function bloquesNotasEvaluador(string $tipo): array
    {
        $cuestionario = new \App\Models\Cuestionario(['tipo_formulario' => $tipo]);
        $secciones = $cuestionario->getSeccionesConfig();
        $slugs = self::slugsPorTipo()[$tipo] ?? [];
        $bloques = [];

        foreach ($secciones as $numero => $titulo) {
            $slug = $slugs[$numero] ?? 'seccion_'.$numero;
            if ($slug === 'firma_digital') {
                continue;
            }
            $bloques[] = [
                'numero' => $numero,
                'slug' => $slug,
                'titulo' => $titulo,
            ];
        }

        return $bloques;
    }
}
