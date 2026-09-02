<?php

namespace Tests\Unit;

use App\Support\InformeWordPdfPaginas;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class InformeWordPdfPaginasTest extends TestCase
{
    public function test_archivo_inexistente_devuelve_lista_vacia(): void
    {
        $this->assertSame([], InformeWordPdfPaginas::paginasComoPng('/tmp/no-existe-' . uniqid() . '.pdf'));
    }

    public function test_convierte_pdf_simple_a_png_si_hay_herramienta_disponible(): void
    {
        Storage::fake('local');
        $ruta = 'tests/fixture_demo.pdf';
        Storage::disk('local')->put($ruta, $this->pdfMinimoValido());

        $paginas = InformeWordPdfPaginas::paginasComoPng(Storage::disk('local')->path($ruta));

        if ($paginas === []) {
            $this->markTestSkipped('Sin Imagick/pdftoppm/gs en el entorno de pruebas.');
        }

        $this->assertNotEmpty($paginas);
        $this->assertStringStartsWith("\x89PNG", $paginas[0]['bytes']);
        $this->assertGreaterThan(0, $paginas[0]['widthPx']);
        $this->assertGreaterThan(0, $paginas[0]['heightPx']);
    }

    private function pdfMinimoValido(): string
    {
        return <<<'PDF'
%PDF-1.4
1 0 obj<</Type/Catalog/Pages 2 0 R>>endobj
2 0 obj<</Type/Pages/Kids[3 0 R]/Count 1>>endobj
3 0 obj<</Type/Page/Parent 2 0 R/MediaBox[0 0 300 300]/Contents 4 0 R/Resources<</Font<</F1 5 0 R>>>>>>endobj
4 0 obj<</Length 44>>stream
BT /F1 24 Tf 50 150 Td (REPRO) Tj ET
endstream
endobj
5 0 obj<</Type/Font/Subtype/Type1/BaseFont/Helvetica>>endobj
xref
0 6
0000000000 65535 f 
0000000009 00000 n 
0000000058 00000 n 
0000000115 00000 n 
0000000274 00000 n 
0000000367 00000 n 
trailer<</Size 6/Root 1 0 R>>
startxref
444
%%EOF
PDF;
    }
}
