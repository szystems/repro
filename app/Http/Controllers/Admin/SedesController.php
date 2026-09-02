<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SedeFormRequest;
use App\Models\EvaluadoOrden;
use App\Models\Orden;
use App\Models\Sede;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SedesController extends Controller
{
    /** Listar sedes con búsqueda y filtro de estado. */
    public function index(Request $request): View
    {

        $searchTerm = $request->input('search');
        $estado     = $request->input('estado');

        $query = Sede::query();

        if ($searchTerm) {
            $query->where(function ($q) use ($searchTerm) {
                $q->where('nombre', 'like', "%{$searchTerm}%")
                  ->orWhere('direccion', 'like', "%{$searchTerm}%")
                  ->orWhere('telefono', 'like', "%{$searchTerm}%");
            });
        }

        if ($estado !== null && $estado !== '') {
            $query->where('estado', $estado);
        } else {
            $query->where('estado', 1);
        }

        $sedes = $query->withCount('evaluados')->orderBy('nombre')->paginate(20);

        return view('admin.sedes.index', compact('sedes', 'searchTerm', 'estado'));
    }

    /** Formulario para crear nueva sede. */
    public function create(): View
    {
        return view('admin.sedes.create');
    }

    /** Guardar nueva sede. */
    public function store(SedeFormRequest $request): RedirectResponse
    {
        Sede::create($request->validated());

        return redirect('sedes')->with('success', 'Sede creada correctamente.');
    }

    /** Mostrar detalle de una sede con panel de procesos. */
    public function show(int $id, Request $request): View
    {
        $sede = Sede::withCount('evaluados')->findOrFail($id);

        // Búsqueda por nombre o DPI
        $search = $request->input('search');

        $estadosActivos    = ['orden_recibida', 'en_proceso'];
        $estadosRealizados = ['entregado'];
        $estadosPendientes = ['orden_recibida'];

        // Candidatos de esta sede (a través de evaluados_orden.sede_id)
        $baseQuery = EvaluadoOrden::deOrdenesActivas()->where('sede_id', $sede->id)
            ->with(['orden.empresa']);

        if ($search) {
            $baseQuery->where(function ($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                  ->orWhere('apellidos', 'like', "%{$search}%")
                  ->orWhere('dpi', 'like', "%{$search}%");
            });
        }

        // Estadísticas de procesos en esta sede
        $stats = [
            'actuales'   => (clone $baseQuery)->whereHas('orden', fn ($q) => $q->whereIn('estado', $estadosActivos))->count(),
            'realizados' => (clone $baseQuery)->whereHas('orden', fn ($q) => $q->whereIn('estado', $estadosRealizados))->count(),
            'pendientes' => (clone $baseQuery)->whereHas('orden', fn ($q) => $q->whereIn('estado', $estadosPendientes))->count(),
            'total'      => (clone $baseQuery)->count(),
        ];

        // Tabla paginada de candidatos con su estado
        $candidatos = (clone $baseQuery)
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.sedes.show', compact('sede', 'stats', 'candidatos', 'search'));
    }

    /** Formulario para editar una sede. */
    public function edit(int $id): View
    {
        $sede = Sede::findOrFail($id);

        return view('admin.sedes.edit', compact('sede'));
    }

    /** Guardar cambios de una sede. */
    public function update(SedeFormRequest $request, int $id): RedirectResponse
    {
        $sede = Sede::findOrFail($id);
        $sede->update($request->validated());

        return redirect('sedes')->with('success', 'Sede actualizada correctamente.');
    }

    /** Activar o desactivar una sede. */
    public function cambiarEstado(int $id, int $estado): RedirectResponse
    {
        $sede = Sede::findOrFail($id);
        $sede->update(['estado' => $estado]);

        $mensaje = $estado ? 'Sede activada.' : 'Sede desactivada.';

        return redirect()->back()->with('success', $mensaje);
    }

    /** Eliminar una sede (solo si no tiene evaluados asignados). */
    public function destroy(int $id): RedirectResponse
    {
        $sede = Sede::withCount('evaluados')->findOrFail($id);

        if ($sede->evaluados_count > 0) {
            return redirect()->back()->with('error', 'No se puede eliminar la sede porque tiene evaluados asignados.');
        }

        $sede->delete();

        return redirect('sedes')->with('success', 'Sede eliminada correctamente.');
    }
}
