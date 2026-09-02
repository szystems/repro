<?php

namespace App\Support;

use App\Models\Cuestionario;
use App\Models\Empresa;
use App\Models\EvaluadoOrden;
use App\Models\Orden;
use App\Models\Sede;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/** Listado compartido admin REPRO + portal empresa (Sprint D 3.2). */
class CuestionariosIndexSupport
{
    /**
     * @return array{
     *     evaluados: \Illuminate\Contracts\Pagination\LengthAwarePaginator,
     *     estadisticas: array<string, mixed>,
     *     empresas: \Illuminate\Support\Collection,
     *     sedes: \Illuminate\Support\Collection,
     *     tiposFormulario: array<string, string>,
     *     portal: string,
     *     indexRoute: string,
     *     ordenesFiltro: \Illuminate\Support\Collection
     * }
     */
    public static function resolver(Request $request): array
    {
        /** @var User $user */
        $user = Auth::user();
        $portal = $user->role_as == 1 ? 'empresa' : 'admin';

        $query = EvaluadoOrden::with(['orden.empresa', 'orden.sede', 'cuestionario'])
            ->whereHas('orden', fn ($q) => $q->activas())
            ->orderBy('created_at', 'desc');

        if ($portal === 'empresa') {
            EmpresaVisibilidadReclutadoresSupport::filtrarQueryEvaluadosEmpresa($query, $user);
        }

        if ($request->filled('empresa_id') && $portal === 'admin') {
            $query->whereHas('orden', function ($q) use ($request) {
                $q->where('empresa_id', $request->empresa_id);
            });
        }

        if ($request->filled('orden_id') && $portal === 'empresa') {
            $query->where('orden_id', $request->orden_id);
        }

        if ($request->filled('tipo_servicio')) {
            $query->where('tipo_servicio', $request->tipo_servicio);
        }

        if ($request->filled('sede_id') && $portal === 'admin') {
            $query->where(function ($q) use ($request) {
                $q->where('sede_id', $request->sede_id)
                    ->orWhereHas('orden', fn ($oq) => $oq->where('sede_id', $request->sede_id));
            });
        }

        if ($request->filled('asignacion_sede') && $portal === 'admin') {
            match ($request->asignacion_sede) {
                'sin_sede' => $query->whereNull('sede_id')
                    ->whereHas('orden', fn ($q) => $q->whereNull('sede_id')),
                'con_sede' => $query->where(function ($q) {
                    $q->whereNotNull('sede_id')
                        ->orWhereHas('orden', fn ($oq) => $oq->whereNotNull('sede_id'));
                }),
                default => null,
            };
        }

        if ($request->filled('tipo_formulario')) {
            $query->where('tipo_formulario', $request->tipo_formulario);
        }

        if ($request->filled('estado')) {
            match ($request->estado) {
                'completado' => $query->where('cuestionario_completado', true),
                'en_progreso' => $query->where('cuestionario_completado', false)
                    ->whereHas('cuestionario', fn ($q) => $q->where('seccion_actual', '>', 1)),
                'pendiente' => $query->where('cuestionario_completado', false)
                    ->where(function ($q) {
                        $q->doesntHave('cuestionario')
                            ->orWhereHas('cuestionario', fn ($c) => $c->where('seccion_actual', '<=', 1));
                    }),
                default => null,
            };
        } elseif ($request->filled('completado')) {
            $query->where('cuestionario_completado', $request->boolean('completado'));
        }

        if ($request->filled('fecha_desde')) {
            $query->whereDate('created_at', '>=', $request->fecha_desde);
        }

        if ($request->filled('fecha_hasta')) {
            $query->whereDate('created_at', '<=', $request->fecha_hasta);
        }

        if ($request->filled('buscar')) {
            $busqueda = $request->buscar;
            $query->where(function ($q) use ($busqueda) {
                $q->where('nombre', 'LIKE', "%{$busqueda}%")
                    ->orWhere('apellidos', 'LIKE', "%{$busqueda}%")
                    ->orWhere('dpi', 'LIKE', "%{$busqueda}%")
                    ->orWhere('telefono', 'LIKE', "%{$busqueda}%")
                    ->orWhere('email', 'LIKE', "%{$busqueda}%");
            });
        }

        if ($request->filled('sort')) {
            $direction = $request->input('direction', 'desc') === 'asc' ? 'asc' : 'desc';
            $query->reorder()->orderBy(
                in_array($request->sort, ['id', 'created_at'], true) ? $request->sort : 'created_at',
                $direction
            );
        }

        $evaluados = $query->paginate($portal === 'empresa' ? 15 : 20)->appends($request->query());

        $baseStats = EvaluadoOrden::query();
        EmpresaVisibilidadReclutadoresSupport::filtrarQueryEvaluadosEmpresa($baseStats, $user);
        if ($portal === 'admin') {
            $baseStats->whereHas('orden', fn ($q) => $q->activas());
        }

        $estadisticas = [
            'total' => (clone $baseStats)->count(),
            'completados' => (clone $baseStats)->where('cuestionario_completado', true)->count(),
            'en_progreso' => (clone $baseStats)->where('cuestionario_completado', false)
                ->whereHas('cuestionario', fn ($q) => $q->where('seccion_actual', '>', 1))->count(),
            'pendientes' => (clone $baseStats)->where('cuestionario_completado', false)
                ->where(function ($q) {
                    $q->doesntHave('cuestionario')
                        ->orWhereHas('cuestionario', fn ($c) => $c->where('seccion_actual', '<=', 1));
                })->count(),
            'iniciados' => (clone $baseStats)->whereHas('cuestionario', fn ($q) => $q->where('seccion_actual', 1))->count(),
            'completados_hoy' => (clone $baseStats)->where('cuestionario_completado', true)
                ->whereDate('completado_at', today())->count(),
            'progreso_promedio' => round((float) Cuestionario::avg('progreso_porcentaje') ?? 0, 1),
            'por_tipo' => (clone $baseStats)->select('tipo_formulario', DB::raw('count(*) as total'))
                ->groupBy('tipo_formulario')
                ->pluck('total', 'tipo_formulario')
                ->toArray(),
            'por_estado' => [],
        ];

        $estadisticas['por_estado'] = [
            'completados' => $estadisticas['completados'],
            'en_progreso' => $estadisticas['en_progreso'],
            'pendientes' => $estadisticas['pendientes'],
            'iniciados' => $estadisticas['iniciados'],
        ];

        $empresas = $portal === 'admin'
            ? Empresa::where('estado', 1)->orderBy('nombre')->get()
            : collect();

        $sedes = $portal === 'admin'
            ? Sede::where('estado', 1)->orderBy('nombre')->get()
            : collect();

        $ordenesFiltro = $portal === 'empresa'
            ? Orden::query()
                ->tap(fn ($q) => EmpresaVisibilidadReclutadoresSupport::filtrarQueryOrdenesEmpresa($q, $user))
                ->activas()
                ->orderByDesc('created_at')
                ->get()
            : collect();

        $tiposFormulario = [
            'preempleo' => 'Pre-empleo',
            'periodica' => 'Periódica',
            'especifica' => 'Específica',
            'socioeconomico' => 'Socioeconómico',
        ];

        return [
            'evaluados' => $evaluados,
            'estadisticas' => $estadisticas,
            'empresas' => $empresas,
            'sedes' => $sedes,
            'tiposFormulario' => $tiposFormulario,
            'portal' => $portal,
            'indexRoute' => $portal === 'empresa' ? 'empresa.cuestionarios' : 'admin.cuestionarios.index',
            'ordenesFiltro' => $ordenesFiltro,
        ];
    }
}
