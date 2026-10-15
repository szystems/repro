<?php

namespace App\Traits;

use App\Models\EstadoHistorial;
use App\Models\Orden;
use Illuminate\Support\Facades\Auth;

trait RegistraCambiosEstado
{
    /**
     * Registra una transición de estado en estado_historial (Fase 18).
     *
     * Detecta automáticamente si el modelo es una Orden o un EvaluadoOrden
     * para asignar el FK correcto.
     */
    protected function registrarCambioEstado(
        string $campo,
        ?string $estadoAnterior,
        string $estadoNuevo,
        ?string $observaciones = null,
        bool $comoSistema = false
    ): void {
        $data = [
            'campo'           => $campo,
            'estado_anterior' => $estadoAnterior,
            'estado_nuevo'    => $estadoNuevo,
            'observacion'     => $observaciones,
            // Null = «Sistema» en el historial. Las pasadas automáticas corren
            // dentro del request de quien esté conectado; no hay que atribuírselas.
            'user_id'         => $comoSistema ? null : Auth::id(),
        ];

        if ($this instanceof Orden) {
            $data['orden_id'] = $this->getKey();
        } else {
            $data['evaluado_orden_id'] = $this->getKey();
        }

        EstadoHistorial::create($data);
    }
}
