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
    /** @var list<string> */
    private const INTEGRIDAD_PDF = [
        '¿Cuál fue el problema más serio que tuvo en sus empleos? ¿Cómo lo resolvió?',
        '¿Ha trabajado en alguna corporación policial o militar? ¿Cuál?',
        '¿En el último año, cuantas veces estuvo ausente en su empleo?',
        '¿Manejó efectivo en sus empleos? ¿Cuánto fue el monto máximo?',
        '¿Cuál fue el faltante más grande que tuvo? ¿Cómo lo resolvió?',
        '¿Cuál fue el sobrante más grande que tuvo en sus empleos?',
        '¿Cuántas veces alteró documentos o facturas en sus empleos?',
        '¿Cuándo llamemos a pedir referencias en sus empleos ¿cree que alguien vaya a recomendarlo mal?',
        '¿Cuál ha sido la cantidad máxima que se ha quedado de producto sobrante o promocional de sus empleos?',
        '¿Cuál fue el soborno más grande que aceptó en sus empleos?',
        '¿En qué empleo le acusaron de deshonestidad?',
        '¿Con justificación tomó sin autorización dinero, producto en sus empleos?',
        '¿Cuánto tendría que pagar por lo que ha tomado en sus empleos?',
        '¿Cuántas actas administrativas le fueron impuestas en sus empleos? y ¿Cuál fue el motivo?',
        '¿Algún compañero le enseñó a como robar en sus empleos?',
        '¿Cuántas veces no reportó a algún compañero por pena o por no meterse en problemas?',
        '¿Alguna vez abandonó algún empleo sin previo aviso? ¿cuál fue?',
        '¿Tuvo necesidad alguna vez de prestar dinero sin autorización o sin permiso en sus empleos?',
        '¿Qué empleo está omitiendo porque pudiera afectar su proceso de contratación actual?',
    ];

    /** @var list<string> */
    private const JUDICIAL_PDF = [
        '¿Cuándo fue la última vez que tramitó sus antecedentes penales y policiacos?',
        '¿Tiene algún antecedente penal o policiaco?',
        '¿Alguna vez tuvo que limpiar algun antecedentepenal o policial ¿Por qué motivo?',
        '¿Alguna vez estuvo detenido en cárceles o delegaciones? ¿Por qué motivo?',
        '¿Ha demandado alguna vez a alguien o a alguna empresa por cualquier motivo?',
        '¿Lo han demandado a usted alguna vez? ¿Por qué motivo?',
        '¿Alguna vez tuvo necesidad de ocultar su identidad por cualquier motivo?',
        '¿Ha portado armas alguna vez? ¿Por qué motivo?',
        '¿Ha robado cualquier objeto con valor superior a Q.200?',
        '¿Ha robado cualquier objeto con valor menor a Q.200?',
        '¿Ha tenido la necesidad de alguna vez falsificar, alterar o utilizar documentos falsos?',
        '¿Usted o algún familiar involuntariamente ha estado involucrado en extorsiones o alguna actividad delictiva?',
        '¿Algún amigo o familiar está privado de libertad? Por qué motivo?',
        '¿Cuándo fue la última vez que lo visitó?',
        '¿Alguna vez usted involuntariamente ha estado involucrado en alguna actividad ilicita?',
        '¿Su lugar de residencia es considerado zona roja?',
    ];

    /** @var list<string> */
    private const COMPLEMENTARIA_PDF = [
        'Tipo de Licencia de conducir/ Vigencia:',
        '¿En qué empleos perteneció a un sindicato?',
        'Tiene algún familiar o amigo laborando en la empresa contratante:',
        '¿Cómo se enteró del empleo?',
        '¿Está de acuerdo con las condiciones laborales que le ofrece la empresa?',
        '¿Cuales son sus metas personales y laborales a corto, mediano y largo plazo?',
        'Mencione sus cualidades y defectos:',
        'Usuario en redes sociales:',
    ];

    public function test_integridad_laboral_tiene_19_preguntas_unicas(): void
    {
        $this->assertCount(19, HistorialLaboralIntegridad::PREGUNTAS);
        $this->assertCount(19, array_unique(array_column(HistorialLaboralIntegridad::PREGUNTAS, 'key')));
    }

    public function test_integridad_laboral_coincide_con_formulario_real(): void
    {
        $labels = array_column(HistorialLaboralIntegridad::PREGUNTAS, 'label');
        $this->assertSame(self::INTEGRIDAD_PDF, $labels);
        $this->assertSame('integridad_01', HistorialLaboralIntegridad::PREGUNTAS[0]['key']);
        $this->assertSame('integridad_19', HistorialLaboralIntegridad::PREGUNTAS[18]['key']);
        $this->assertStringNotContainsString('currículum', implode(' ', $labels));
        $this->assertStringNotContainsString('inventario', implode(' ', $labels));
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
        $labels = array_column(AntecedentesJudiciales::PREGUNTAS, 'label');
        $this->assertCount(16, AntecedentesJudiciales::PREGUNTAS);
        $this->assertSame(self::JUDICIAL_PDF, $labels);
        $this->assertStringContainsString('Q.200', AntecedentesJudiciales::PREGUNTAS[8]['label']);
        $this->assertStringContainsString('zona roja', AntecedentesJudiciales::PREGUNTAS[15]['label']);
    }

    public function test_complementaria_tiene_8_campos_literales_cliente(): void
    {
        $labels = array_column(InformacionComplementaria::PREGUNTAS, 'label');
        $this->assertCount(8, InformacionComplementaria::PREGUNTAS);
        $this->assertSame(self::COMPLEMENTARIA_PDF, $labels);
        $keys = array_column(InformacionComplementaria::PREGUNTAS, 'key');
        $this->assertNotContains('comp_disponibilidad', $keys);
    }

    public function test_salud_habitos_titulos_y_sustancias_reales(): void
    {
        $this->assertSame('Aspectos de salud', SaludHabitosCampos::TITULO_SALUD);
        $this->assertSame('Hábitos personales', SaludHabitosCampos::TITULO_HABITOS);
        $this->assertSame('Buena', SaludHabitosCampos::ESTADOS_GENERAL['buena']);
        $this->assertSame('Heroina', SaludHabitosCampos::SUSTANCIAS['heroina']);
        $this->assertArrayHasKey('lsc', SaludHabitosCampos::SUSTANCIAS);
        $this->assertStringContainsString('acercamientocon', SaludHabitosCampos::INTRO_SUSTANCIAS);
        $this->assertSame('¿Cual es el problema personal mas serio que tiene actualmente?', SaludHabitosCampos::LABEL_PREOCUPACIONES);
        $this->assertSame('buena', SaludHabitosCampos::normalizarEstadoGeneral('bueno'));
    }

    public function test_historial_laboral_periodico_tiene_31_preguntas(): void
    {
        $this->assertCount(31, HistorialLaboralPeriodico::PREGUNTAS);
        $this->assertSame(
            '¿Usted ha brindado información confidencial de la empresa?',
            HistorialLaboralPeriodico::PREGUNTAS[30]['label']
        );
        $this->assertSame('periodico_31', HistorialLaboralPeriodico::PREGUNTAS[30]['key']);
    }

    public function test_todas_las_labels_judiciales_son_no_vacias(): void
    {
        foreach (AntecedentesJudiciales::PREGUNTAS as $pregunta) {
            $this->assertNotSame('', trim($pregunta['label']));
            $this->assertMatchesRegularExpression('/\?|Explique|Indique|Describa|Cuándo|Cuál|Nombre|Detalle|Por qué|motivo|Q\./i', $pregunta['label']);
        }
    }

    public function test_todas_las_labels_complementaria_son_no_vacias(): void
    {
        foreach (InformacionComplementaria::PREGUNTAS as $pregunta) {
            $this->assertNotSame('', trim($pregunta['label']));
            $this->assertGreaterThan(10, strlen($pregunta['label']));
        }
    }
}
