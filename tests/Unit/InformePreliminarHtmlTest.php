<?php

namespace Tests\Unit;

use App\Support\InformePreliminarHtml;
use Tests\TestCase;

class InformePreliminarHtmlTest extends TestCase
{
    public function test_conserva_vinetas_numeros_y_data_list(): void
    {
        $html = '<p>Intro</p>'
            .'<ul><li data-list="bullet">Primera</li><li data-list="bullet">Segunda</li></ul>'
            .'<ol><li data-list="ordered">Uno</li></ol>';

        $limpio = InformePreliminarHtml::sanitizar($html);

        $this->assertIsString($limpio);
        $this->assertStringContainsString('<ul>', $limpio);
        $this->assertStringContainsString('<ol>', $limpio);
        $this->assertStringContainsString('data-list="bullet"', $limpio);
        $this->assertStringContainsString('data-list="ordered"', $limpio);
        $this->assertStringContainsString('Primera', $limpio);
        $this->assertStringContainsString('Uno', $limpio);
    }

    public function test_conserva_color_y_limpia_xss(): void
    {
        $html = '<p><span style="color: rgb(255, 0, 0); font-weight: bold; background-image: url(javascript:alert(1))">Rojo</span></p>'
            .'<p><font color="#e60000"><b>Negrita roja</b></font></p>'
            .'<p><span class="ql-color-#0033cc ql-bg-#ffff00">Clase</span></p>'
            .'<table><tr><td onclick="alert(1)"><b style="font-weight: bold">Celda</b></td></tr></table>'
            .'<img src=x onerror=alert(1)>'
            .'<ul><li data-list="bullet" onclick="alert(1)">Ítem</li></ul>';

        $guardado = InformePreliminarHtml::sanitizar($html);

        $this->assertIsString($guardado);
        $this->assertStringContainsString('<table>', $guardado);
        $this->assertStringContainsString('Celda', $guardado);
        $this->assertStringContainsString('color: rgb(255, 0, 0)', $guardado);
        $this->assertStringContainsString('<b>Negrita roja</b>', $guardado);
        $this->assertStringContainsString('color: #e60000', $guardado);
        $this->assertStringContainsString('color: #0033cc', $guardado);
        $this->assertStringContainsString('background-color: #ffff00', $guardado);
        $this->assertStringContainsString('font-weight: bold', $guardado);
        $this->assertStringContainsString('data-list="bullet"', $guardado);
        $this->assertStringContainsString('Ítem', $guardado);
        $this->assertStringNotContainsString('<font', $guardado);
        $this->assertStringNotContainsString('ql-color', $guardado);
        $this->assertStringNotContainsString('onclick', $guardado);
        $this->assertStringNotContainsString('<img', $guardado);
        $this->assertStringNotContainsString('javascript', $guardado);
        $this->assertStringNotContainsString('background-image', $guardado);
    }
}
