<?php

namespace Tests\Unit;

use App\Support\CuestionarioSecciones;
use Tests\TestCase;

class CuestionarioSeccionesTest extends TestCase
{
    public function test_slug_preempleo_por_numero(): void
    {
        $this->assertSame('datos_personales', CuestionarioSecciones::slug(1, 'preempleo'));
        $this->assertSame('informacion_familiar', CuestionarioSecciones::slug(2, 'preempleo'));
        $this->assertSame('firma_digital', CuestionarioSecciones::slug(6, 'preempleo'));
    }

    public function test_bloques_notas_evaluador_excluyen_firma_digital(): void
    {
        $bloques = CuestionarioSecciones::bloquesNotasEvaluador('preempleo');
        $slugs = array_column($bloques, 'slug');

        $this->assertNotEmpty($bloques);
        $this->assertContains('datos_personales', $slugs);
        $this->assertContains('informacion_familiar', $slugs);
        $this->assertNotContains('firma_digital', $slugs);

        foreach ($bloques as $bloque) {
            $this->assertArrayHasKey('numero', $bloque);
            $this->assertArrayHasKey('slug', $bloque);
            $this->assertArrayHasKey('titulo', $bloque);
        }
    }
}
