<?php

namespace App\Listeners;

use App\Support\CorreoEnvioSupport;
use Illuminate\Mail\Events\MessageSent;

class ContarCorreoEnviado
{
    public function handle(MessageSent $event): void
    {
        CorreoEnvioSupport::registrarEnvio();
    }
}
