<?php

namespace Tests\Unit;

use App\Support\PerfilImagenSupport;
use Tests\Support\FakeImage;
use Tests\TestCase;

class PerfilImagenSupportTest extends TestCase
{
    public function test_guarda_en_public_assets_y_puede_borrar(): void
    {
        $file = FakeImage::jpeg('foto.jpg');
        $nombre = PerfilImagenSupport::guardar($file, 'users');

        $this->assertNotSame('foto.jpg', $nombre);
        $this->assertFileExists(public_path('assets/imgs/users/'.$nombre));

        PerfilImagenSupport::borrar('users', $nombre);
        $this->assertFileDoesNotExist(public_path('assets/imgs/users/'.$nombre));
    }
}
