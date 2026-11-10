<?php

namespace Tests\Unit;

use App\Support\AsistenteRedaccionFormato;
use Tests\TestCase;

class AsistenteRedaccionFormatoTest extends TestCase
{
    public function test_quita_los_renglones_vacios_y_las_vinetas_de_un_apartado(): void
    {
        $texto = AsistenteRedaccionFormato::limpiar("Primer párrafo.\n\n• Segundo párrafo.\n\n", 'word_salud');

        $this->assertSame("Primer párrafo.\nSegundo párrafo.", $texto);
        $this->assertStringNotContainsString('•', $texto);
    }

    public function test_recomendaciones_llevan_una_vineta_por_linea_y_el_sin_riesgo_no(): void
    {
        $texto = AsistenteRedaccionFormato::limpiar("Seguimiento laboral\n\nVerificar referencias", 'word_recomendaciones');

        $this->assertSame("• Seguimiento laboral\n• Verificar referencias", $texto);

        $sinRiesgo = AsistenteRedaccionFormato::limpiar(
            'Durante el proceso de evaluación no se identificaron factores de riesgo que requieran seguimiento adicional.',
            'word_recomendaciones'
        );

        $this->assertStringNotContainsString('•', $sinRiesgo);
        $this->assertStringContainsString('factores de riesgo', $sinRiesgo);
    }

    public function test_el_preliminar_conserva_vinetas_y_las_vuelve_html(): void
    {
        $html = AsistenteRedaccionFormato::html("Observación general.\n• Primera\n• Segunda");

        $this->assertStringContainsString('<p>Observación general.</p>', $html);
        $this->assertStringContainsString('<ul><li>Primera</li><li>Segunda</li></ul>', $html);
    }
}
