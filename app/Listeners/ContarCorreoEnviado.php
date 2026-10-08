<?php

namespace App\Listeners;

use App\Support\CorreoEnvioSupport;
use Illuminate\Mail\Events\MessageSent;
use Illuminate\Support\Facades\Log;
use Throwable;

class ContarCorreoEnviado
{
    public function handle(MessageSent $event): void
    {
        try {
            CorreoEnvioSupport::registrarEnvio();
        } catch (Throwable $e) {
            // El mensaje ya salió. Un fallo al contarlo no debe marcar el envío como fallido.
            Log::warning('No se pudo contar el correo enviado', [
                'error' => $e->getMessage(),
            ]);
        }
    }
}
