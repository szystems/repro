<?php

namespace Tests\Doubles;

use App\Support\AsistenteRedaccionMotor;

class AsistenteRedaccionMotorFijo implements AsistenteRedaccionMotor
{
    public string $sistema = '';

    public string $usuario = '';

    public int $llamadas = 0;

    public function redactar(string $sistema, string $usuario): string
    {
        $this->llamadas++;
        $this->sistema = $sistema;
        $this->usuario = $usuario;

        return 'PROPUESTA DE PRUEBA';
    }
}
