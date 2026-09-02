<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Empresa;
use App\Models\EvaluadoOrden;
use App\Models\Orden;
use App\Models\Sede;
use App\Support\EmpresaVisibilidadReclutadoresSupport;
use App\Support\ExportacionesSupport;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class ReportesController extends Controller
{
    /**
     * Construir query base de evaluaciones con filtros aplicados.
     *
     * @param  array<string, mixed>  $filters
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function buildEvaluacionesQuery(array $filters): \Illuminate\Database\Eloquent\Builder
    {
        $query = EvaluadoOrden::with(['orden.empresa', 'cuestionario'])->deOrdenesActivas();

        // Cliente (empresa): ver todos los evaluados de sus órdenes, independiente del estado.
        // La vista/columnas de resultados se condicionan por separado usando
        // `$orden->resultadosDisponiblesParaEmpresa()`.
        if (Auth::user()->role_as == 1) {
            EmpresaVisibilidadReclutadoresSupport::filtrarQueryEvaluadosEmpresa($query, Auth::user());
        }

        if (!empty($filters['fecha_inicio'])) {
            $query->whereDate('created_at', '>=', $filters['fecha_inicio']);
        }
        if (!empty($filters['fecha_fin'])) {
            $query->whereDate('created_at', '<=', $filters['fecha_fin']);
        }
        if (!empty($filters['empresa_id']) && Auth::user()->role_as >= 2) {
            $query->whereHas('orden', function ($q) use ($filters) {
                $q->where('empresa_id', $filters['empresa_id']);
            });
        }
        if (!empty($filters['tipo_servicio'])) {
            $query->where('tipo_servicio', $filters['tipo_servicio']);
        }
        if (!empty($filters['sede_id'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('sede_id', $filters['sede_id'])
                    ->orWhereHas('orden', fn ($oq) => $oq->where('sede_id', $filters['sede_id']));
            });
        }
        if (!empty($filters['estado'])) {
            if ($filters['estado'] == 'completado') {
                $query->where('cuestionario_completado', true);
            } elseif ($filters['estado'] == 'pendiente') {
                $query->where('cuestionario_completado', false);
            }
        }

        return $query;
    }

    /**
     * Reporte de Evaluaciones/Cuestionarios
     */
    public function evaluaciones(Request $request)
    {
        $query = $this->buildEvaluacionesQuery($request->all());

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

        // Sedes para filtro
        $sedes = Sede::activas()->orderBy('nombre')->get();

        return view('admin.reportes.evaluaciones', compact('evaluados', 'stats', 'empresas', 'sedes'));
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
        if ($request->filled('empresa_id')) {
            $query->where('id', $request->empresa_id);
        }
        // A7: filtro por sede
        if ($request->filled('sede_id')) {
            $query->whereHas('ordenes.evaluados', function ($q) use ($request) {
                $q->where('sede_id', $request->sede_id);
            });
        }

        // Clonar para estadísticas
        $statsQuery = Empresa::query();
        $stats = [
            'total_empresas'   => $statsQuery->count(),
            'empresas_activas' => (clone $statsQuery)->where('estado', 1)->count(),
            'total_ordenes'    => Orden::activas()->count(),
            'total_evaluados'  => EvaluadoOrden::deOrdenesActivas()->count(),
        ];

        // A7: ranking top 5 empresas por órdenes entregadas
        $ranking = Empresa::withCount([
            'ordenes as ordenes_entregadas' => fn ($q) => $q->where('estado', 'entregado'),
        ])
        ->having('ordenes_entregadas', '>', 0)
        ->orderByDesc('ordenes_entregadas')
        ->limit(5)
        ->get();

        $empresas      = $query->orderBy('nombre')->paginate(15)->withQueryString();
        $todasEmpresas = Empresa::orderBy('nombre')->pluck('nombre', 'id');
        $todasSedes    = Sede::activas()->orderBy('nombre')->pluck('nombre', 'id');

        return view('admin.reportes.empresas', compact('empresas', 'stats', 'todasEmpresas', 'todasSedes', 'ranking'));
    }

    /**
     * Exportar reporte de evaluaciones a PDF
     */
    public function evaluacionesPdf(Request $request)
    {
        $query = $this->buildEvaluacionesQuery($request->all());

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

        // Obtener logo de REPRO desde configuración
        $config = \App\Models\Config::first();
        $imagen = null;
        if ($config && $config->logo && file_exists(public_path('assets/imgs/logos/'.$config->logo))) {
            $imagen = public_path('assets/imgs/logos/'.$config->logo);
        }

        $pdf = Pdf::loadView('admin.reportes.pdf.evaluaciones', compact('evaluados', 'stats', 'filtros', 'imagen'));
        $pdf->setPaper('letter', 'landscape');

        return $pdf->download('reporte-evaluaciones-' . now()->format('Y-m-d') . '.pdf');
    }

    /**
     * Construir query base de empresas para reportes con filtros.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  array<string>  $extraCounts  Relaciones withCount adicionales
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function buildEmpresasReportQuery(Request $request, array $extraCounts = []): \Illuminate\Database\Eloquent\Builder
    {
        $counts = array_merge(['ordenes'], $extraCounts);
        $query = Empresa::withCount($counts);

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

        return $query->orderBy('nombre');
    }

    /**
     * Exportar reporte de empresas a PDF
     */
    public function empresasPdf(Request $request)
    {
        $empresas = $this->buildEmpresasReportQuery($request, [
            'ordenes as ordenes_completadas_count' => function ($q) {
                $q->where('estado', 'entregado');
            },
        ])->get();

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
        ExportacionesSupport::asegurarPuedeExportarInformes(Auth::user());

        $evaluados = $this->buildEvaluacionesQuery($request->all())
            ->orderBy('created_at', 'desc')
            ->get();

        $export = new \App\Exports\EvaluacionesExport($evaluados);
        $base = 'reporte-evaluaciones-' . now()->format('Y-m-d');

        return ExportacionesSupport::descargarExcel($export, $base);
    }

    /**
     * Exportar reporte de empresas a Excel
     */
    public function empresasExcel(Request $request)
    {
        ExportacionesSupport::asegurarPuedeExportarInformes(Auth::user());

        $empresas = $this->buildEmpresasReportQuery($request)->get();
        $export = new \App\Exports\EmpresasExport($empresas);
        $base = 'reporte-empresas-' . now()->format('Y-m-d');

        return ExportacionesSupport::descargarExcel($export, $base);
    }
}
