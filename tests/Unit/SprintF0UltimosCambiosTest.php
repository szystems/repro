<?php

namespace Tests\Unit;

use App\Support\AutorizacionesLegales;
use App\Support\InformacionFamiliarPadres;
use App\Support\SaludHabitosCampos;
use Tests\TestCase;

/** Sprint F0 — ULTIMOS CAMBIOS.pdf (auth fechas, esclarecimiento, padres, título). */
class SprintF0UltimosCambiosTest extends TestCase
{
    public function test_autorizaciones_tienen_una_sola_fecha_por_plantilla(): void
    {
        $plantillas = config('autorizaciones_legales.plantillas');
        $this->assertIsArray($plantillas);

        foreach ($plantillas as $clave => $plantilla) {
            $cuerpo = (string) ($plantilla['cuerpo'] ?? '');
            $conteo = substr_count($cuerpo, '<strong>Fecha:</strong>');
            $this->assertSame(1, $conteo, "Plantilla {$clave} debe tener exactamente 1 Fecha");
        }

        $infornet = (string) config('autorizaciones_legales.infornet');
        $this->assertSame(1, substr_count($infornet, '<strong>Fecha:</strong>'));
    }

    public function test_autorizacion_especifica_sin_linea_esclarecimiento(): void
    {
        foreach (['poligrafo_especifica', 'vsa_especifica'] as $clave) {
            $cuerpo = (string) config("autorizaciones_legales.plantillas.{$clave}.cuerpo");
            $this->assertStringNotContainsString('esclarecimiento', $cuerpo);
            $this->assertStringContainsString(':motivo_hecho:', $cuerpo);
            $this->assertStringContainsString(':empresa:', $cuerpo);
        }
    }

    public function test_telefono_padres_no_es_obligatorio(): void
    {
        $reglas = InformacionFamiliarPadres::reglasValidacion();
        $this->assertStringNotContainsString('required_if', (string) $reglas['padre_telefono']);
        $this->assertStringNotContainsString('required_if', (string) $reglas['madre_telefono']);
        $this->assertStringStartsWith('nullable', (string) $reglas['padre_telefono']);
    }

    public function test_titulo_sustancias_es_aspectos_varios(): void
    {
        $this->assertSame('ASPECTOS VARIOS', SaludHabitosCampos::TITULO_SUSTANCIAS);
    }

    public function test_render_especifica_incluye_motivo_sin_esclarecimiento(): void
    {
        $evaluado = new \App\Models\EvaluadoOrden([
            'nombre' => 'Ana',
            'apellidos' => 'Prueba',
            'dpi' => '1234567890101',
            'tipo_servicio' => 'vsa',
            'tipo_formulario' => 'especifica',
            'motivo_hecho_evaluacion' => 'Por asalto ocurrido el jueves 15/07/2026',
        ]);
        $evaluado->setRelation('orden', new \App\Models\Orden([
            'empresa_id' => 1,
        ]));
        $evaluado->orden->setRelation('empresa', new \App\Models\Empresa([
            'nombre' => 'PRUEBA 1',
        ]));

        $html = AutorizacionesLegales::renderHtml($evaluado);
        $this->assertStringNotContainsString('esclarecimiento', $html);
        $this->assertStringContainsString('Por asalto ocurrido el jueves 15/07/2026', $html);
        $this->assertSame(1, substr_count($html, '<strong>Fecha:</strong>'));
    }
}
