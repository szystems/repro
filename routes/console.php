<?php

use App\Support\CorreoEnvioSupport;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('correo:olvidar-alerta', function () {
    CorreoEnvioSupport::olvidarAlertaPersistente();
    $this->info('Banner persistente de correo eliminado de caché.');
})->purpose('Quita el aviso global de correo/SMTP (sin resetear contador diario)');
