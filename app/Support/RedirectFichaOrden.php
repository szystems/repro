<?php

namespace App\Support;

use App\Models\EvaluadoOrden;
use App\Models\Orden;
use Illuminate\Http\RedirectResponse;

/** Tras papelería, informe o cambio de estado, volver a la misma ficha (no al panel). */
class RedirectFichaOrden
{
    public static function evaluado(EvaluadoOrden $evaluado, string $mensaje, string $flash = 'success'): RedirectResponse
    {
        $evaluado->loadMissing('orden');

        return redirect()
            ->to(route('ordenes.show', $evaluado->orden).'#evaluado-'.$evaluado->id)
            ->with($flash, $mensaje);
    }

    public static function orden(Orden $orden, string $mensaje, string $flash = 'success'): RedirectResponse
    {
        return redirect()
            ->to(route('ordenes.show', $orden))
            ->with($flash, $mensaje);
    }
}
