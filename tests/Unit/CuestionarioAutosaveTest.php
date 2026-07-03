<?php

namespace Tests\Unit;

use App\Support\CuestionarioAutosave;
use Tests\TestCase;

class CuestionarioAutosaveTest extends TestCase
{
    public function test_reglas_permisivas_seccion_2_no_exigen_campos_obligatorios(): void
    {
        $reglas = CuestionarioAutosave::reglasPermisivas(2, 'preempleo');

        $this->assertArrayHasKey('estado_civil_detalle', $reglas);
        $this->assertStringContainsString('nullable', (string) $reglas['estado_civil_detalle']);
        $this->assertStringNotContainsString('required', (string) $reglas['estado_civil_detalle']);
        $this->assertSame('nullable|array', $reglas['hijos']);
    }
}
