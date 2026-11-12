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
        $this->assertStringContainsString('como máximo tres verbos', $sistema);
        $this->assertStringContainsString('no está registrado', $sistema);
        $this->assertStringContainsString('Los montos de dinero son quetzales.', $sistema);
        $this->assertStringContainsString('3000 queda Q3,000.00', $sistema);
        $this->assertStringContainsString('No le pongas Q a edades', $sistema);
        $this->assertStringContainsString('no se cuenta con declaración', $sistema);
        $this->assertStringContainsString('Nunca inventes verificaciones.', $sistema);
        $this->assertStringContainsString('no escribas una oración por cada una', $sistema);
        $this->assertStringContainsString('«haber» se usa como máximo dos veces por párrafo', $sistema);
        $this->assertStringContainsString('tres o cuatro respuestas por oración', $sistema);
        $this->assertStringContainsString('Esto incluye «haber», «no», «ni», «que»', $sistema);
        $this->assertStringContainsString('No omitas ninguna respuesta al agrupar', $sistema);
        $this->assertStringContainsString('haber alterado documentos o facturas, haber aceptado sobornos', $sistema);
        $this->assertStringContainsString('antecedentes de alteración de documentos o facturas, aceptación de sobornos', $sistema);
        $this->assertStringContainsString('recomendaciones. El ejemplo usa el aspecto laboral', $sistema);
        $this->assertStringNotContainsString('con una frase completa según la pregunta', $sistema);
        $this->assertStringContainsString('Incluye cada No y cada N/A', $pedido);
        $this->assertStringContainsString('«haber» se usa como máximo dos veces por párrafo', $pedido);
        $this->assertStringContainsString('manejo de efectivo, actas y llamadas de atención', $laboral);
        $this->assertStringContainsString('no escribas «haber» al inicio de cada pregunta', $laboral);

        $observaciones = AsistenteRedaccionInstrucciones::sistema('word_observaciones', 'poligrafo');
        $this->assertStringContainsString('Solo corriges el texto que ya está escrito', $observaciones);
        $this->assertStringContainsString('«• »', $observaciones);
        $this->assertStringNotContainsString('No uses viñetas', $observaciones);

        $salud = AsistenteRedaccionInstrucciones::sistema('word_salud', 'poligrafo');
        $this->assertStringContainsString('no enumeres cada uno', $salud);
        $this->assertStringContainsString('Si Sexo es mujer', $salud);
        $this->assertStringContainsString('Si Sexo es hombre, no redactes el embarazo', $salud);

        $recomendaciones = AsistenteRedaccionInstrucciones::sistema('word_recomendaciones', 'poligrafo');
        $this->assertStringContainsString('Se recomienda', $recomendaciones);
        $this->assertStringContainsString('factores de riesgo', $recomendaciones);
        $this->assertStringContainsString('«• »', $recomendaciones);
        $this->assertStringContainsString('un solo salto de línea', $sistema);
        $this->assertStringContainsString('No uses viñetas', $sistema);
    }
}
