<?php

namespace App\Notifications;

use App\Models\EvaluadoOrden;
use App\Support\OrdenPortalSupport;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * Campana del portal al guardar un comentario nuevo. No gasta correo.
 */
class ComentarioAgregadoNotification extends Notification
{
    use Queueable;

    public function __construct(public EvaluadoOrden $evaluado)
    {
        $this->evaluado->loadMissing('orden');
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
        $orden = $this->evaluado->orden;
        $codigo = $orden->codigo_orden ?? $this->evaluado->orden_id;
        $url = OrdenPortalSupport::urlDetalle($notifiable, $orden ?? $this->evaluado->orden_id);

        return [
            'tipo' => 'comentario_agregado',
            'icono' => 'bi-chat-left-text',
            'color' => 'info',
            'mensaje' => 'Nuevo comentario sobre '.$this->evaluado->nombre_completo.' — Orden #'.$codigo,
            'url' => $url,
            'evaluado_id' => $this->evaluado->id,
            'orden_id' => $orden->id ?? $this->evaluado->orden_id,
        ];
    }
}
