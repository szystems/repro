<?php

namespace App\Listeners;

use App\Support\CorreoEnvioSupport;
use Illuminate\Queue\Events\JobFailed;

class RegistrarFalloCorreo
{
    public function handle(JobFailed $event): void
    {
        $nombre = $event->job->resolveName();
        if (! self::pareceCorreo($nombre)) {
            return;
        }

        CorreoEnvioSupport::registrarFallo($event->exception, $nombre);
    }

    private static function pareceCorreo(string $nombre): bool
    {
        foreach (['Mail', 'Mailable', 'Notification'] as $marca) {
            if (str_contains($nombre, $marca)) {
                return true;
            }
        }

        return false;
    }
}
