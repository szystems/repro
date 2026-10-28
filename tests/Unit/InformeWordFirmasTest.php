<?php

namespace Tests\Unit;

use App\Support\InformeWordFirmas;
use Tests\TestCase;
use ZipArchive;

class InformeWordFirmasTest extends TestCase
{
    public function test_reconoce_al_entrevistador_por_el_nombre(): void
    {
        $this->assertSame('stefanie', InformeWordFirmas::claveDesdeNombre('Stefanie Castro'));
        $this->assertSame('rodrigo', InformeWordFirmas::claveDesdeNombre('Rodrigo Castro'));
        $this->assertSame('narda', InformeWordFirmas::claveDesdeNombre('Licda. Narda Plester'));
        $this->assertNull(InformeWordFirmas::claveDesdeNombre('Aldin Tobar'));
        $this->assertNull(InformeWordFirmas::claveDesdeNombre(''));
    }

    public function test_deja_solo_la_firma_elegida_y_no_toca_la_foto_ni_el_vsa(): void
    {
        foreach ([
            'informe-poligrafo-preempleo-v2.docx',
            'informe-poligrafo-periodica-v2.docx',
            'informe-poligrafo-especifica-v2.docx',
        ] as $archivo) {
            $xml = $this->xmlPlantilla($archivo);
            $this->assertSame(4, preg_match_all('/name="Imagen/', $xml));

            $solo = InformeWordFirmas::dejarSolo($xml, 'narda');

            $this->assertStringContainsString('Narda Plester', $solo);
            $this->assertStringContainsString('Registro APA No. 10296', $solo);
            $this->assertStringContainsString('Poligrafista Certificado', $solo);
            $this->assertStringNotContainsString('Stefanie', $solo);
            $this->assertStringNotContainsString('Rodrigo', $solo);
            $this->assertStringNotContainsString('9245', $solo);
            $this->assertStringNotContainsString('12871', $solo);
            $this->assertSame(2, preg_match_all('/name="Imagen/', $solo));
            $this->assertStringContainsString('cx="2981325"', $solo);
            $this->assertSame(substr_count($solo, '<w:drawing>'), substr_count($solo, '</w:drawing>'));
            $this->assertSame($xml, InformeWordFirmas::dejarSolo($xml, null));
        }

        $vsa = $this->xmlPlantilla('informe-vsa-preempleo-v2.docx');
        $this->assertSame($vsa, InformeWordFirmas::dejarSolo($vsa, 'narda'));
        $this->assertStringContainsString('Aldin', $vsa);
    }

    private function xmlPlantilla(string $archivo): string
    {
        $zip = new ZipArchive();
        $this->assertTrue($zip->open(resource_path('templates/'.$archivo)) === true);
        $xml = $zip->getFromName('word/document.xml');
        $zip->close();
        $this->assertIsString($xml);

        return $xml;
    }
}
