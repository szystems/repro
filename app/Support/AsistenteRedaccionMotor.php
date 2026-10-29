<?php

namespace App\Support;

interface AsistenteRedaccionMotor
{
    public function redactar(string $sistema, string $usuario): string;
}
