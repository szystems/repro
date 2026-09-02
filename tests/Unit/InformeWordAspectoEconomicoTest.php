<?php

namespace Tests\Unit;

use App\Support\InformeWordXml;
use Tests\TestCase;

class InformeWordAspectoEconomicoTest extends TestCase
{
    public function test_elimina_filas_vacias_y_placeholder_xxxxx(): void
    {
        $tabla = <<<'XML'
<w:tbl>
<w:tr><w:tc><w:p><w:r><w:t>ASPECTO ECONÓMICO:</w:t></w:r></w:p></w:tc></w:tr>
<w:tr><w:tc><w:p><w:r><w:t>Deudas:</w:t></w:r></w:p></w:tc></w:tr>
<w:tr><w:tc><w:p><w:r><w:t>Entidad:</w:t></w:r></w:p></w:tc><w:tc><w:p><w:r><w:t>Monto:</w:t></w:r></w:p></w:tc></w:tr>
<w:tr><w:tc><w:p/></w:tc></w:tr>
<w:tr><w:tc><w:p><w:r><w:t>TOTALES: Q. Q. Q.</w:t></w:r></w:p></w:tc></w:tr>
<w:tr><w:tc><w:p><w:r><w:t>xxxxx</w:t></w:r></w:p></w:tc></w:tr>
</w:tbl>
XML;

        $resultado = InformeWordXml::eliminarFilasVaciasOPlaceholder($tabla);

        $this->assertStringNotContainsString('xxxxx', $resultado);
        $this->assertStringContainsString('TOTALES', $resultado);
        $this->assertStringContainsString('Deudas:', $resultado);
        $this->assertSame(4, substr_count($resultado, '<w:tr'));
    }

    public function test_podar_seccion_deudas_vacia_elimina_headers_sin_datos(): void
    {
        $tabla = <<<'XML'
<w:tbl>
<w:tr><w:tc><w:p><w:r><w:t>ASPECTO ECONÓMICO:</w:t></w:r></w:p></w:tc></w:tr>
<w:tr><w:tc><w:p><w:r><w:t>Deudas:</w:t></w:r></w:p></w:tc></w:tr>
<w:tr><w:tc><w:p><w:r><w:t>Entidad:</w:t></w:r></w:p></w:tc><w:tc><w:p><w:r><w:t>Monto:</w:t></w:r></w:p></w:tc></w:tr>
<w:tr><w:tc><w:p/></w:tc></w:tr>
<w:tr><w:tc><w:p><w:r><w:t>TOTALES: Q. Q. Q.</w:t></w:r></w:p></w:tc></w:tr>
<w:tr><w:tc><w:p><w:r><w:t>xxxxx</w:t></w:r></w:p></w:tc></w:tr>
<w:tr><w:tc><w:p><w:r><w:t>Indicó haber sido fiador de un familiar.</w:t></w:r></w:p></w:tc></w:tr>
</w:tbl>
XML;

        $resultado = InformeWordXml::podarSeccionDeudasVacia($tabla);

        $this->assertStringNotContainsString('Deudas:', $resultado);
        $this->assertStringNotContainsString('Entidad:', $resultado);
        $this->assertStringNotContainsString('TOTALES:', $resultado);
        $this->assertStringContainsString('Indicó haber sido fiador', $resultado);
    }
}
