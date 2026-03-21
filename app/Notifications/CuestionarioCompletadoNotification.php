<?php

namespace App\Notifications;

use App\Models\EvaluadoOrden;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class CuestionarioCompletadoNotification extends Notification
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
            'tipo' => 'cuestionario_completado',
            'icono' => 'bi-check-circle',
            'color' => 'success',
            'mensaje' => $this->evaluado->nombre_completo . ' completó su cuestionario',
            'url' => route('ordenes.show', $this->evaluado->orden_id),
            'evaluado_id' => $this->evaluado->id,
        ];
    }
}
