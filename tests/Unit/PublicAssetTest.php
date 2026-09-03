<?php

namespace Tests\Unit;

use App\Support\PublicAsset;
use Tests\TestCase;

class PublicAssetTest extends TestCase
{
    public function test_version_devuelve_mtime_si_el_archivo_existe(): void
    {
        $this->assertSame(
            (string) filemtime(public_path('js/cuestionario-autosave.js')),
            PublicAsset::version('js/cuestionario-autosave.js')
        );
    }

    public function test_version_no_lanza_si_el_archivo_no_existe(): void
    {
        $version = PublicAsset::version('js/archivo-inexistente-prueba.js');

        $this->assertNotEmpty($version);
        $this->assertMatchesRegularExpression('/^\d+$/', $version);
    }

    public function test_url_incluye_query_version(): void
    {
        $url = PublicAsset::url('js/fecha-nacimiento-mask.js');

        $this->assertStringContainsString('/js/fecha-nacimiento-mask.js?v=', $url);
    }
}
