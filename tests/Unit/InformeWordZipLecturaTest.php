<?php

namespace Tests\Unit;

use App\Support\InformeWordZip;
use PhpOffice\PhpWord\Settings;
use Tests\TestCase;
use ZipArchive;

/** ZipArchive no relee con getFromName una entrada recién reemplazada. */
class InformeWordZipLecturaTest extends TestCase
{
    public function test_la_lectura_ve_el_xml_reemplazado_y_el_cierre_lo_guarda(): void
    {
        if (! class_exists(ZipArchive::class)) {
            $this->markTestSkipped('Esta instalación no tiene ext-zip.');
        }

        Settings::setZipClass(Settings::ZIPARCHIVE);
        $booted = (new \ReflectionClass(InformeWordZip::class))->getProperty('booted');
        $booted->setAccessible(true);
        $booted->setValue(null, false);

        $dir = storage_path('app/temp');
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $path = $dir.'/ziplectura_'.uniqid('', true).'.docx';

        $origen = new ZipArchive();
        $this->assertTrue($origen->open($path, ZipArchive::CREATE) === true);
        $origen->addFromString('word/document.xml', '<w:document>ORIGINAL</w:document>');
        $origen->close();

        $zip = InformeWordZip::create();
        $this->assertTrue($zip->open($path) === true);
        $this->assertTrue(InformeWordZip::reemplazarEntrada($zip, 'word/document.xml', '<w:document>PRIMERO</w:document>'));
        $this->assertSame('<w:document>PRIMERO</w:document>', InformeWordZip::leerEntrada($zip, 'word/document.xml'));
        $this->assertTrue(InformeWordZip::reemplazarEntrada($zip, 'word/document.xml', '<w:document>DOCUMENTOS ADJUNTOS</w:document>'));
        $this->assertTrue(InformeWordZip::cerrar($zip));

        $cerrado = new ZipArchive();
        $this->assertTrue($cerrado->open($path) === true);
        $this->assertStringContainsString('DOCUMENTOS ADJUNTOS', (string) $cerrado->getFromName('word/document.xml'));
        $cerrado->close();
        @unlink($path);
    }
}
