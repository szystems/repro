<?php

namespace App\Notifications;

use App\Models\EvaluadoOrden;
use App\Support\OrdenPortalSupport;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ResultadosDisponiblesNotification extends Notification
{
    use Queueable;

    public function __construct(public EvaluadoOrden $evaluado, public bool $preliminar = false)
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
        $url = OrdenPortalSupport::urlDetalle($notifiable, $this->evaluado->orden_id);

        return [
            'tipo' => 'resultados_disponibles',
            'icono' => 'bi-file-earmark-check',
            'color' => 'info',
            'mensaje' => ($this->preliminar ? 'Resultado preliminar disponible: ' : 'Informe final disponible: ') . $this->evaluado->nombre_completo . ' — Orden #' . ($this->evaluado->orden->codigo_orden ?? $this->evaluado->orden_id),
            'url' => $url,
            'evaluado_id' => $this->evaluado->id,
        ];
    }
}
