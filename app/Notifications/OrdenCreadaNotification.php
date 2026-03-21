<?php

namespace App\Notifications;

use App\Models\Orden;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class OrdenCreadaNotification extends Notification
{
    use Queueable;

    public function __construct(public Orden $orden)
    {
    }

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'tipo' => 'orden_creada',
            'icono' => 'bi-folder-plus',
            'color' => 'primary',
            'mensaje' => 'Nueva orden #' . $this->orden->codigo . ' creada',
            'url' => route('ordenes.show', $this->orden),
            'orden_id' => $this->orden->id,
        ];
    }
}
