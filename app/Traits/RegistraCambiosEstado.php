<?php

namespace App\Traits;

use App\Models\AuditoriaEstado;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

trait RegistraCambiosEstado
{
    /**
     * Registra una transición de estado en la tabla de auditoría.
     */
    protected function registrarCambioEstado(
        string $campo,
        ?string $estadoAnterior,
        string $estadoNuevo,
        ?string $observaciones = null
    ): void {
        AuditoriaEstado::create([
            'entidad_tipo' => static::class,
            'entidad_id' => $this->getKey(),
            'campo' => $campo,
            'estado_anterior' => $estadoAnterior,
            'estado_nuevo' => $estadoNuevo,
            'user_id' => Auth::id(),
            'ip' => Request::ip(),
            'observaciones' => $observaciones,
        ]);
    }
}
