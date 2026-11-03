<?php

namespace Tests\Unit;

use App\Support\AsistenteRedaccionCampos;
use App\Support\AsistenteRedaccionInstrucciones;
use Tests\TestCase;

class AsistenteRedaccionNoNaTest extends TestCase
{
    public function test_un_no_o_na_se_incluye_como_no_realizado_y_un_vacio_sigue_vacio(): void
    {
        $texto = AsistenteRedaccionCampos::textoParaModelo([
            'titulo' => 'Aspectos judiciales',
            'servicio' => 'poligrafo',
            'formulario' => 'preempleo',
            'campos' => [
                ['pregunta' => '¿Tiene algún antecedente penal o policiaco?', 'texto' => 'No', 'vacio' => false],
                ['pregunta' => '¿Ha portado armas alguna vez?', 'texto' => 'N/A', 'vacio' => false],
                ['pregunta' => '¿Lo han demandado a usted alguna vez?', 'texto' => 'Sí, en 2019', 'vacio' => false],
                ['pregunta' => '¿Ha robado cualquier objeto con valor superior a Q.200?', 'texto' => '', 'vacio' => true],
            ],
            'observacion_evaluador' => '',
        ]);

        $this->assertStringContainsString('antecedente penal o policiaco?: No. No lo ha realizado.', $texto);
        $this->assertStringContainsString('armas alguna vez?: N/A. No lo ha realizado.', $texto);
        $this->assertStringContainsString('Sí, en 2019', $texto);
        $this->assertStringNotContainsString('Sí, en 2019. No lo ha realizado', $texto);
        $this->assertStringContainsString('Q.200?: (vacío)', $texto);
        $this->assertStringNotContainsString('(vacío). No lo ha realizado', $texto);
    }

    public function test_el_criterio_pide_incluir_el_no_y_el_na(): void
    {
        $sistema = AsistenteRedaccionInstrucciones::sistema('word_judicial', 'poligrafo');
        $pedido = AsistenteRedaccionInstrucciones::pedido('confirmada', 'formal', false);
        $laboral = AsistenteRedaccionInstrucciones::sistema('word_laboral', 'poligrafo');

        $this->assertStringContainsString('no lo ha realizado', $sistema);
        $this->assertStringContainsString('Un No o un N/A no es un dato faltante.', $sistema);
        $this->assertStringContainsString('no se cuenta con declaración', $sistema);
        $this->assertStringContainsString('Incluye cada No y cada N/A', $pedido);
        $this->assertStringContainsString('manejo de efectivo, actas y llamadas de atención', $laboral);
    }
}
