<?php

namespace Tests\Unit;

use App\Support\AntecedentesJudiciales;
use App\Support\HistorialLaboralIntegridad;
use App\Support\HistorialLaboralPeriodico;
use App\Support\InformacionComplementaria;
use App\Support\SaludHabitosCampos;
use Tests\TestCase;

class FormularioLiteralesClienteTest extends TestCase
{
    public function test_integridad_laboral_tiene_19_preguntas_unicas(): void
    {
        $this->assertCount(19, HistorialLaboralIntegridad::PREGUNTAS);
        $this->assertCount(19, array_unique(array_column(HistorialLaboralIntegridad::PREGUNTAS, 'key')));
    }

    public function test_integridad_laboral_coincide_con_formulario_real(): void
    {
        $this->assertSame(
            '¿Ha trabajado en alguna corporación policial o militar? ¿Cuál?',
            HistorialLaboralIntegridad::PREGUNTAS[0]['label']
        );
        $this->assertSame(
            '¿Existe algún empleo que no haya registrado en este formulario? ¿Cuál?',
            HistorialLaboralIntegridad::PREGUNTAS[18]['label']
        );
        $this->assertStringContainsString('corporación policial', HistorialLaboralIntegridad::PREGUNTAS[0]['label']);
        $this->assertStringNotContainsString('currículum', HistorialLaboralIntegridad::PREGUNTAS[0]['label']);
    }

    public function test_labels_transversales_seccion_laboral(): void
    {
        $this->assertStringContainsString('formales, informales', HistorialLaboralIntegridad::LABEL_EXPERIENCIA_PREVIA);
        $this->assertStringContainsString('lagunas de tiempo', HistorialLaboralIntegridad::LABEL_OBSERVACIONES_LABORALES);
        $this->assertSame('Preguntas complementarias laborales', HistorialLaboralIntegridad::TITULO_BLOQUE);
    }

    public function test_todas_las_labels_integridad_son_literales_cliente(): void
    {
        $labels = array_column(HistorialLaboralIntegridad::PREGUNTAS, 'label');
        $this->assertNotContains('¿Ha mentido en algún currículum o entrevista de trabajo?', $labels);
        foreach ($labels as $label) {
            $this->assertGreaterThan(20, strlen($label));
        }
    }

    public function test_judicial_tiene_16_preguntas_literales_cliente(): void
    {
        $this->assertCount(16, AntecedentesJudiciales::PREGUNTAS);
        $this->assertSame(
            '¿Cuándo fue la última vez que tramitó sus antecedentes penales y policiales?',
            AntecedentesJudiciales::PREGUNTAS[0]['label']
        );
        $this->assertSame(
            '¿Considera que su lugar de residencia presenta problemas de delincuencia, pandillas, extorsiones o actividades ilícitas? Explique.',
            AntecedentesJudiciales::PREGUNTAS[15]['label']
        );
        $this->assertStringContainsString('Q.200.00', AntecedentesJudiciales::PREGUNTAS[8]['label']);
    }

    public function test_complementaria_tiene_8_campos_literales_cliente(): void
    {
        $this->assertCount(8, InformacionComplementaria::PREGUNTAS);
        $this->assertSame(
            '¿En qué empleos perteneció a un sindicato? Explique.',
            InformacionComplementaria::PREGUNTAS[1]['label']
        );
        $this->assertSame(
            'Indique los nombres de usuario o perfiles que utiliza en redes sociales actualmente.',
            InformacionComplementaria::PREGUNTAS[7]['label']
        );
        $keys = array_column(InformacionComplementaria::PREGUNTAS, 'key');
        $this->assertNotContains('comp_disponibilidad', $keys);
    }

    public function test_salud_habitos_titulos_y_sustancias_reales(): void
    {
        $this->assertSame('Aspectos de salud', SaludHabitosCampos::TITULO_SALUD);
        $this->assertSame('Hábitos personales', SaludHabitosCampos::TITULO_HABITOS);
        $this->assertSame('Buena', SaludHabitosCampos::ESTADOS_GENERAL['buena']);
        $this->assertArrayHasKey('heroina', SaludHabitosCampos::SUSTANCIAS);
        $this->assertArrayHasKey('lsc', SaludHabitosCampos::SUSTANCIAS);
        $this->assertStringContainsString('90%', SaludHabitosCampos::INTRO_SUSTANCIAS);
        $this->assertSame('buena', SaludHabitosCampos::normalizarEstadoGeneral('bueno'));
    }

    public function test_historial_laboral_periodico_tiene_26_preguntas(): void
    {
        $this->assertCount(26, HistorialLaboralPeriodico::PREGUNTAS);
        $this->assertSame(
            '¿Alguien le ha pedido información confidencial de la empresa?',
            HistorialLaboralPeriodico::PREGUNTAS[25]['label']
        );
        $this->assertSame('periodico_26', HistorialLaboralPeriodico::PREGUNTAS[25]['key']);
    }

    public function test_todas_las_labels_judiciales_son_no_vacias(): void
    {
        foreach (AntecedentesJudiciales::PREGUNTAS as $pregunta) {
            $this->assertNotSame('', trim($pregunta['label']));
            $this->assertMatchesRegularExpression('/\?|Explique|Indique|Describa|Cuándo|Cuál|Nombre|Detalle/i', $pregunta['label']);
        }
    }

    public function test_todas_las_labels_complementaria_son_no_vacias(): void
    {
        foreach (InformacionComplementaria::PREGUNTAS as $pregunta) {
            $this->assertNotSame('', trim($pregunta['label']));
            $this->assertGreaterThan(15, strlen($pregunta['label']));
        }
    }
}
