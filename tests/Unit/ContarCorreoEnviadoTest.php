<?php

namespace Tests\Unit;

use App\Listeners\ContarCorreoEnviado;
use Illuminate\Mail\Events\MessageSent;
use Illuminate\Support\Facades\Cache;
use RuntimeException;
use Tests\TestCase;

class ContarCorreoEnviadoTest extends TestCase
{
    public function test_un_fallo_al_contar_no_rompe_el_envio(): void
    {
        config([
            'mail.default' => 'smtp',
            'mail.alerta.activa' => true,
        ]);

        Cache::shouldReceive('increment')->once()->andThrow(new RuntimeException('cache rota'));

        $listener = new ContarCorreoEnviado();
        $listener->handle(\Mockery::mock(MessageSent::class));

        $this->assertTrue(true);
    }
}
