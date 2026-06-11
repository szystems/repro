<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // Enviar recordatorios de cuestionarios próximos a expirar
        // Se ejecuta diariamente a las 8:00 AM
        $schedule->command('notificaciones:recordatorios --dias=3,1')
            ->dailyAt('08:00')
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/recordatorios.log'));

        // Fase 18: Auto-transiciones de estado_formulario (cada hora)
        //  - link_enviado +24h sin abrir → pendiente_de_llenar
        //  - cualquier estado incompleto +30 días → vencido
        $schedule->command('formulario:auto-transiciones')
            ->hourly()
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/auto-transiciones.log'));
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
