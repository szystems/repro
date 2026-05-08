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
        $url = $notifiable->role_as === 1
            ? route('empresa.ordenes.show', $this->orden)
            : route('ordenes.show', $this->orden);

        $servicios = $this->orden->evaluados->pluck('tipo_servicio')->filter()->unique()->map(fn($s) => ucfirst($s))->join(', ');
        $empresa = $this->orden->empresa?->nombre ?? 'Sin empresa';
        $mensaje = "Nueva orden #{$this->orden->codigo_orden} — {$empresa}";
        if ($servicios) {
            $mensaje .= " | {$servicios}";
        }

        return [
            'tipo' => 'orden_creada',
            'icono' => 'bi-folder-plus',
            'color' => 'primary',
            'mensaje' => $mensaje,
            'url' => $url,
            'orden_id' => $this->orden->id,
        ];
    }
}
