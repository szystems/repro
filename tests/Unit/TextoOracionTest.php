<?php

namespace Tests\Unit;

use App\Support\TextoOracion;
use PHPUnit\Framework\TestCase;

class TextoOracionTest extends TestCase
{
    public function test_baja_un_texto_entero_en_mayusculas_y_no_toca_lo_mezclado(): void
    {
        $this->assertSame('Reorganizacion', TextoOracion::aplicar('REORGANIZACION'));
        $this->assertSame('Gerente pilas', TextoOracion::aplicar('GERENTE PILAS'));
        $this->assertSame('Encargado de bodega', TextoOracion::aplicar('ENCARGADO DE BODEGA'));
        $this->assertSame('Walter Pablo Lopez Pérez', TextoOracion::aplicar('Walter Pablo Lopez Pérez'));
        $this->assertSame('USAC', TextoOracion::aplicar('USAC'));
        $this->assertSame('4750', TextoOracion::aplicar('4750'));
        $this->assertSame('10/2021', TextoOracion::aplicar('10/2021'));
    }

    public function test_una_clave_de_formulario_vuelve_a_minusculas(): void
    {
        $this->assertSame('si', TextoOracion::aplicar('SI'));
        $this->assertSame('no', TextoOracion::aplicar('NO'));
        $this->assertSame('soltero', TextoOracion::aplicar('SOLTERO'));
    }

    public function test_las_filas_de_una_tabla_bajan_solo_el_texto(): void
    {
        $filas = TextoOracion::filas([[
            'empresa' => 'GERENTE PILAS',
            'puesto' => 'VERIFICADOR',
            'ultimo_salario' => '4750',
        ]]);

        $this->assertSame('Gerente pilas', $filas[0]['empresa']);
        $this->assertSame('Verificador', $filas[0]['puesto']);
        $this->assertSame('4750', $filas[0]['ultimo_salario']);
    }
}
