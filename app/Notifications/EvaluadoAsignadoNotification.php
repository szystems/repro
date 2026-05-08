<?php

namespace App\Notifications;

use App\Models\EvaluadoOrden;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class EvaluadoAsignadoNotification extends Notification
{
    use Queueable;

    public function __construct(public EvaluadoOrden $evaluado)
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
        $url = $notifiable->role_as === 1
            ? route('empresa.ordenes.show', $this->evaluado->orden_id)
            : route('ordenes.show', $this->evaluado->orden_id);

        return [
            'tipo' => 'evaluado_asignado',
            'icono' => 'bi-person-check',
            'color' => 'warning',
            'mensaje' => $this->evaluado->nombre_completo . ' ha sido asignado para evaluación',
            'url' => $url,
            'evaluado_id' => $this->evaluado->id,
        ];
    }
}
