<?php

namespace Tests\Unit;

use App\Support\CuestionarioPresentacionCandidato;
use Tests\TestCase;

class CuestionarioPresentacionCandidatoTest extends TestCase
{
    public function test_titulo_navbar_por_tipo(): void
    {
        $this->assertSame('Cuestionario de Pre-empleo', CuestionarioPresentacionCandidato::tituloNavbar('preempleo'));
        $this->assertSame('Cuestionario Socioeconómico', CuestionarioPresentacionCandidato::tituloNavbar('socioeconomico'));
        $this->assertSame('Cuestionario Periódico', CuestionarioPresentacionCandidato::tituloNavbar('periodica'));
        $this->assertSame('Cuestionario Específico', CuestionarioPresentacionCandidato::tituloNavbar('especifica'));
    }

    public function test_secciones_preempleo_usan_titulos_pdf(): void
    {
        $cuestionario = new \App\Models\Cuestionario(['tipo_formulario' => 'preempleo']);

        $this->assertSame([
            1 => 'Información Personal',
            2 => 'Información Familiar',
            3 => 'Información Académica y Laboral',
            4 => 'Información Económica',
            5 => 'Salud, Hábitos y Aspectos Complementarios',
        ], $cuestionario->getSeccionesConfig());
    }
}
