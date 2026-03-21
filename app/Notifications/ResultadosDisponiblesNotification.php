<?php

namespace App\Notifications;

use App\Models\EvaluadoOrden;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ResultadosDisponiblesNotification extends Notification
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
        return [
            'tipo' => 'resultados_disponibles',
            'icono' => 'bi-file-earmark-check',
            'color' => 'info',
            'mensaje' => 'Resultados disponibles para ' . $this->evaluado->nombre_completo,
            'url' => route('ordenes.show', $this->evaluado->orden_id),
            'evaluado_id' => $this->evaluado->id,
        ];
    }
}
