<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Empresa;
use App\Models\EvaluadoOrden;
use App\Models\Orden;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class ReportesController extends Controller
{
    /**
     * Reporte de Evaluaciones/Cuestionarios
     */
    public function evaluaciones(Request $request)
    {
        $query = EvaluadoOrden::with(['orden.empresa']);

        // Filtrar por empresa si el usuario es de empresa
        if (Auth::user()->role_as == 1) {
            $query->whereHas('orden', function ($q) {
                $q->where('empresa_id', Auth::user()->empresa_id);
            });
        }

        // Filtros
        if ($request->filled('fecha_inicio')) {
            $query->whereDate('created_at', '>=', $request->fecha_inicio);
        }
        if ($request->filled('fecha_fin')) {
            $query->whereDate('created_at', '<=', $request->fecha_fin);
        }
        if ($request->filled('empresa_id') && Auth::user()->role_as >= 2) {
            $query->whereHas('orden', function ($q) use ($request) {
                $q->where('empresa_id', $request->empresa_id);
            });
        }
        if ($request->filled('tipo_servicio')) {
            $query->where('tipo_servicio', $request->tipo_servicio);
        }
        if ($request->filled('estado')) {
            if ($request->estado == 'completado') {
                $query->where('cuestionario_completado', true);
            } elseif ($request->estado == 'pendiente') {
                $query->where('cuestionario_completado', false);
            }
        }

        // Clonar query para estadísticas
        $statsQuery = clone $query;

        // Estadísticas
        $stats = [
            'total' => $statsQuery->count(),
            'completados' => (clone $statsQuery)->where('cuestionario_completado', true)->count(),
            'pendientes' => (clone $statsQuery)->where('cuestionario_completado', false)->count(),
            'por_servicio' => (clone $statsQuery)
                ->select('tipo_servicio', DB::raw('count(*) as total'))
                ->groupBy('tipo_servicio')
                ->pluck('total', 'tipo_servicio')
                ->toArray(),
        ];

        // Datos para la tabla
        $evaluados = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();

        // Empresas para filtro
        $empresas = Auth::user()->role_as >= 2 
            ? Empresa::where('estado', 1)->orderBy('nombre')->get() 
            : collect();

        return view('admin.reportes.evaluaciones', compact('evaluados', 'stats', 'empresas'));
    }

    /**
     * Reporte de Empresas y sus evaluaciones
     */
    public function empresas(Request $request)
    {
        $query = Empresa::withCount([
            'ordenes',
            'ordenes as ordenes_completadas_count' => function ($q) {
                $q->where('estado', 'entregado');
            },
            'ordenes as ordenes_pendientes_count' => function ($q) {
                $q->whereNotIn('estado', ['entregado', 'cancelado']);
            },
        ])->with(['ordenes' => function ($q) {
            $q->withCount('evaluados');
        }]);

        // Filtros
        if ($request->filled('fecha_inicio')) {
            $query->whereHas('ordenes', function ($q) use ($request) {
                $q->whereDate('created_at', '>=', $request->fecha_inicio);
            });
        }
        if ($request->filled('fecha_fin')) {
            $query->whereHas('ordenes', function ($q) use ($request) {
                $q->whereDate('created_at', '<=', $request->fecha_fin);
            });
        }
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        // Clonar para estadísticas
        $statsQuery = Empresa::query();
        $stats = [
            'total_empresas' => $statsQuery->count(),
            'empresas_activas' => (clone $statsQuery)->where('estado', 1)->count(),
            'total_ordenes' => Orden::count(),
            'total_evaluados' => EvaluadoOrden::count(),
        ];

        $empresas = $query->orderBy('nombre')->paginate(15)->withQueryString();

        return view('admin.reportes.empresas', compact('empresas', 'stats'));
    }

    /**
     * Exportar reporte de evaluaciones a PDF
     */
    public function evaluacionesPdf(Request $request)
    {
        $query = EvaluadoOrden::with(['orden.empresa']);

        if (Auth::user()->role_as == 1) {
            $query->whereHas('orden', function ($q) {
                $q->where('empresa_id', Auth::user()->empresa_id);
            });
        }

        // Aplicar mismos filtros
        if ($request->filled('fecha_inicio')) {
            $query->whereDate('created_at', '>=', $request->fecha_inicio);
        }
        if ($request->filled('fecha_fin')) {
            $query->whereDate('created_at', '<=', $request->fecha_fin);
        }
        if ($request->filled('empresa_id') && Auth::user()->role_as >= 2) {
            $query->whereHas('orden', function ($q) use ($request) {
                $q->where('empresa_id', $request->empresa_id);
            });
        }
        if ($request->filled('tipo_servicio')) {
            $query->where('tipo_servicio', $request->tipo_servicio);
        }
        if ($request->filled('estado')) {
            if ($request->estado == 'completado') {
                $query->where('cuestionario_completado', true);
            } elseif ($request->estado == 'pendiente') {
                $query->where('cuestionario_completado', false);
            }
        }

        $evaluados = $query->orderBy('created_at', 'desc')->get();

        $stats = [
            'total' => $evaluados->count(),
            'completados' => $evaluados->where('cuestionario_completado', true)->count(),
            'pendientes' => $evaluados->where('cuestionario_completado', false)->count(),
        ];

        $filtros = [
            'fecha_inicio' => $request->fecha_inicio,
            'fecha_fin' => $request->fecha_fin,
            'empresa' => $request->filled('empresa_id') ? Empresa::find($request->empresa_id)?->nombre : 'Todas',
            'tipo_servicio' => $request->tipo_servicio ?? 'Todos',
            'estado' => $request->estado ?? 'Todos',
        ];

        $pdf = Pdf::loadView('admin.reportes.pdf.evaluaciones', compact('evaluados', 'stats', 'filtros'));
        $pdf->setPaper('letter', 'landscape');

        return $pdf->download('reporte-evaluaciones-' . now()->format('Y-m-d') . '.pdf');
    }

    /**
     * Exportar reporte de empresas a PDF
     */
    public function empresasPdf(Request $request)
    {
        $query = Empresa::withCount([
            'ordenes',
            'ordenes as ordenes_completadas_count' => function ($q) {
                $q->where('estado', 'entregado');
            },
        ]);

        if ($request->filled('fecha_inicio')) {
            $query->whereHas('ordenes', function ($q) use ($request) {
                $q->whereDate('created_at', '>=', $request->fecha_inicio);
            });
        }
        if ($request->filled('fecha_fin')) {
            $query->whereHas('ordenes', function ($q) use ($request) {
                $q->whereDate('created_at', '<=', $request->fecha_fin);
            });
        }
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        $empresas = $query->orderBy('nombre')->get();

        $stats = [
            'total_empresas' => $empresas->count(),
            'total_ordenes' => $empresas->sum('ordenes_count'),
        ];

        $filtros = [
            'fecha_inicio' => $request->fecha_inicio,
            'fecha_fin' => $request->fecha_fin,
            'estado' => $request->estado ?? 'Todos',
        ];

        $pdf = Pdf::loadView('admin.reportes.pdf.empresas', compact('empresas', 'stats', 'filtros'));
        $pdf->setPaper('letter', 'landscape');

        return $pdf->download('reporte-empresas-' . now()->format('Y-m-d') . '.pdf');
    }

    /**
     * Exportar reporte de evaluaciones a Excel
     */
    public function evaluacionesExcel(Request $request)
    {
        $query = EvaluadoOrden::with(['orden.empresa']);

        if (Auth::user()->role_as == 1) {
            $query->whereHas('orden', function ($q) {
                $q->where('empresa_id', Auth::user()->empresa_id);
            });
        }

        if ($request->filled('fecha_inicio')) {
            $query->whereDate('created_at', '>=', $request->fecha_inicio);
        }
        if ($request->filled('fecha_fin')) {
            $query->whereDate('created_at', '<=', $request->fecha_fin);
        }
        if ($request->filled('empresa_id') && Auth::user()->role_as >= 2) {
            $query->whereHas('orden', function ($q) use ($request) {
                $q->where('empresa_id', $request->empresa_id);
            });
        }
        if ($request->filled('tipo_servicio')) {
            $query->where('tipo_servicio', $request->tipo_servicio);
        }
        if ($request->filled('estado')) {
            if ($request->estado == 'completado') {
                $query->where('cuestionario_completado', true);
            } elseif ($request->estado == 'pendiente') {
                $query->where('cuestionario_completado', false);
            }
        }

        $evaluados = $query->orderBy('created_at', 'desc')->get();

        return Excel::download(
            new \App\Exports\EvaluacionesExport($evaluados),
            'reporte-evaluaciones-' . now()->format('Y-m-d') . '.xlsx'
        );
    }

    /**
     * Exportar reporte de empresas a Excel
     */
    public function empresasExcel(Request $request)
    {
        $query = Empresa::withCount(['ordenes']);

        if ($request->filled('fecha_inicio')) {
            $query->whereHas('ordenes', function ($q) use ($request) {
                $q->whereDate('created_at', '>=', $request->fecha_inicio);
            });
        }
        if ($request->filled('fecha_fin')) {
            $query->whereHas('ordenes', function ($q) use ($request) {
                $q->whereDate('created_at', '<=', $request->fecha_fin);
            });
        }
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        $empresas = $query->orderBy('nombre')->get();

        return Excel::download(
            new \App\Exports\EmpresasExport($empresas),
            'reporte-empresas-' . now()->format('Y-m-d') . '.xlsx'
        );
    }
}
