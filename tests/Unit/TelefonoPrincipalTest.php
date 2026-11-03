<?php

namespace Tests\Unit;

use App\Support\TelefonoPrincipal;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class TelefonoPrincipalTest extends TestCase
{
    public function test_exige_entre_ocho_y_quince_digitos(): void
    {
        $reglas = ['telefono' => TelefonoPrincipal::reglas()];
        $mensajes = ['telefono.required' => TelefonoPrincipal::mensajeRequerido()];

        $this->assertTrue(Validator::make(['telefono' => '55551234'], $reglas)->passes());
        $this->assertTrue(Validator::make(['telefono' => '+502 5555-1234'], $reglas)->passes());

        $vacio = Validator::make(['telefono' => ''], $reglas, $mensajes);
        $this->assertTrue($vacio->fails());

        $corto = Validator::make(['telefono' => '12345'], $reglas);
        $this->assertTrue($corto->fails());
        $this->assertSame(
            'El teléfono principal debe tener entre 8 y 15 dígitos.',
            $corto->errors()->first('telefono')
        );

        $largo = Validator::make(['telefono' => '1234567890123456'], $reglas);
        $this->assertTrue($largo->fails());
    }
}
