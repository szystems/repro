<?php

namespace Tests\Unit;

use App\Support\MatrizFormularioServicio;
use App\Support\MensajesInformacionImportante;
use PHPUnit\Framework\TestCase;

class MatrizFormularioServicioTest extends TestCase
{
    public function test_socioeconomico_solo_permite_preempleo(): void
    {
        $this->assertSame(['preempleo'], MatrizFormularioServicio::tiposFormularioPermitidos('socioeconomico'));
        $this->assertTrue(MatrizFormularioServicio::combinacionValida('socioeconomico', 'preempleo'));
        $this->assertFalse(MatrizFormularioServicio::combinacionValida('socioeconomico', 'periodica'));
        $this->assertFalse(MatrizFormularioServicio::combinacionValida('socioeconomico', 'especifica'));
    }

    public function test_poligrafo_y_vsa_permiten_tres_formularios(): void
    {
        foreach (['poligrafo', 'vsa'] as $servicio) {
            $this->assertSame(
                ['preempleo', 'periodica', 'especifica'],
                MatrizFormularioServicio::tiposFormularioPermitidos($servicio)
            );
            $this->assertTrue(MatrizFormularioServicio::combinacionValida($servicio, 'periodica'));
        }
    }

    public function test_tipo_formulario_orden_y_cuestionario(): void
    {
        $this->assertSame('preempleo', MatrizFormularioServicio::tipoFormularioParaOrden('socioeconomico', 'periodica'));
        $this->assertSame('socioeconomico', MatrizFormularioServicio::tipoFormularioCuestionario('socioeconomico', 'preempleo'));
        $this->assertSame('periodica', MatrizFormularioServicio::tipoFormularioCuestionario('poligrafo', 'periodica'));
        $this->assertSame('especifica', MatrizFormularioServicio::tipoFormularioCuestionario('vsa', 'especifica'));
    }

    public function test_modalidad_sugerida(): void
    {
        $this->assertSame('virtual', MatrizFormularioServicio::modalidadSugerida('vsa'));
        $this->assertSame('presencial', MatrizFormularioServicio::modalidadSugerida('poligrafo'));
        $this->assertSame('presencial', MatrizFormularioServicio::modalidadSugerida('socioeconomico'));
    }

    public function test_mensajes_informacion_importante_por_tipo(): void
    {
        $pre = MensajesInformacionImportante::parrafo('preempleo');
        $per = MensajesInformacionImportante::parrafo('periodica');
        $esp = MensajesInformacionImportante::parrafo('especifica');
        $soc = MensajesInformacionImportante::parrafo('socioeconomico');

        $this->assertStringContainsString('30 días', $pre);
        $this->assertStringContainsString('DPI', $per);
        $this->assertSame($per, $esp);
        $this->assertStringContainsString('constancia laboral', $soc);

        $viñetas = MensajesInformacionImportante::viñetasCompletado('preempleo');
        $this->assertGreaterThanOrEqual(5, count($viñetas));
        $this->assertStringContainsString('papelería', implode(' ', $viñetas));
    }
}
