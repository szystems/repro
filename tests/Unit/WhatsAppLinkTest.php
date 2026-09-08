<?php

namespace Tests\Unit;

use App\Support\WhatsAppLink;
use PHPUnit\Framework\TestCase;

class WhatsAppLinkTest extends TestCase
{
    public function test_agrega_502_a_ocho_digitos(): void
    {
        $this->assertSame('https://wa.me/50245464545', WhatsAppLink::url('45464545'));
    }

    public function test_respeta_numero_con_codigo_pais(): void
    {
        $this->assertSame('https://wa.me/50277637811', WhatsAppLink::url('+502 7763-7811'));
    }

    public function test_vacio_devuelve_null(): void
    {
        $this->assertNull(WhatsAppLink::url(''));
        $this->assertNull(WhatsAppLink::url(null));
    }
}
