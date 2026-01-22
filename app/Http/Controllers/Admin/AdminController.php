<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Config;
use App\Models\User;
use App\Models\Empresa;
use App\Models\Orden;
use App\Models\EvaluadoOrden;
use App\Models\Cuestionario;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use PDF;

class AdminController extends Controller
{
    public function index()
    {
        $config = Config::first();
        $data = ['config' => $config];

        // Determinar qué layout usar según el rol del usuario
        $user = Auth::user();
        $layout = 'admin';

        if ($user->role_as == 0) {
            $layout = 'evaluado';
        } elseif ($user->role_as == 1) {
            $layout = 'empresa';
            $data = array_merge($data, $this->getEmpresaStats($user));
        } elseif ($user->role_as >= 2) {
            $layout = 'admin';
            $data = array_merge($data, $this->getAdminStats());
        }

        return view('admin.index', $data)->with('layout', $layout);
    }

    /**
     * Obtener estadísticas para Admin y REPRO
     */
    private function getAdminStats(): array
    {
        $hoy = Carbon::today();
        $inicioMes = Carbon::now()->startOfMonth();
        $inicioMesAnterior = Carbon::now()->subMonth()->startOfMonth();
        $finMesAnterior = Carbon::now()->subMonth()->endOfMonth();

        // Contadores principales
        $totalEmpresas = Empresa::where('estado', 1)->count();
        $totalUsuarios = User::where('estado', 1)->count();
        $totalOrdenes = Orden::count();
        $totalEvaluados = EvaluadoOrden::count();

        // Órdenes por estado
        $ordenesPorEstado = Orden::select('estado', DB::raw('count(*) as total'))
            ->groupBy('estado')
            ->pluck('total', 'estado')
            ->toArray();

        // Órdenes del mes actual
        $ordenesEsteMes = Orden::where('created_at', '>=', $inicioMes)->count();
        $ordenesMesAnterior = Orden::whereBetween('created_at', [$inicioMesAnterior, $finMesAnterior])->count();
        $variacionOrdenes = $ordenesMesAnterior > 0 
            ? round((($ordenesEsteMes - $ordenesMesAnterior) / $ordenesMesAnterior) * 100, 1) 
            : ($ordenesEsteMes > 0 ? 100 : 0);

        // Evaluados del mes
        $evaluadosEsteMes = EvaluadoOrden::where('created_at', '>=', $inicioMes)->count();
        $evaluadosMesAnterior = EvaluadoOrden::whereBetween('created_at', [$inicioMesAnterior, $finMesAnterior])->count();
        $variacionEvaluados = $evaluadosMesAnterior > 0 
            ? round((($evaluadosEsteMes - $evaluadosMesAnterior) / $evaluadosMesAnterior) * 100, 1) 
            : ($evaluadosEsteMes > 0 ? 100 : 0);

        // Cuestionarios completados
        $cuestionariosCompletados = EvaluadoOrden::where('cuestionario_completado', true)->count();
        $cuestionariosPendientes = EvaluadoOrden::where('cuestionario_completado', false)
            ->whereNotNull('token_unico')
            ->count();

        // Evaluados por tipo de servicio
        $evaluadosPorServicio = EvaluadoOrden::select('tipo_servicio', DB::raw('count(*) as total'))
            ->groupBy('tipo_servicio')
            ->pluck('total', 'tipo_servicio')
            ->toArray();

        // Órdenes por mes (últimos 6 meses)
        $ordenesPorMes = [];
        for ($i = 5; $i >= 0; $i--) {
            $mes = Carbon::now()->subMonths($i);
            $ordenesPorMes[] = [
                'mes' => $mes->translatedFormat('M'),
                'total' => Orden::whereYear('created_at', $mes->year)
                    ->whereMonth('created_at', $mes->month)
                    ->count()
            ];
        }

        // Últimas órdenes
        $ultimasOrdenes = Orden::with(['empresa', 'evaluados'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Órdenes pendientes (no entregadas ni canceladas)
        $ordenesPendientes = Orden::whereNotIn('estado', ['entregado', 'cancelado'])
            ->count();

        // Top empresas por órdenes
        $topEmpresas = Empresa::withCount('ordenes')
            ->orderBy('ordenes_count', 'desc')
            ->limit(5)
            ->get();

        return [
            'totalEmpresas' => $totalEmpresas,
            'totalUsuarios' => $totalUsuarios,
            'totalOrdenes' => $totalOrdenes,
            'totalEvaluados' => $totalEvaluados,
            'ordenesPorEstado' => $ordenesPorEstado,
            'ordenesEsteMes' => $ordenesEsteMes,
            'variacionOrdenes' => $variacionOrdenes,
            'evaluadosEsteMes' => $evaluadosEsteMes,
            'variacionEvaluados' => $variacionEvaluados,
            'cuestionariosCompletados' => $cuestionariosCompletados,
            'cuestionariosPendientes' => $cuestionariosPendientes,
            'evaluadosPorServicio' => $evaluadosPorServicio,
            'ordenesPorMes' => $ordenesPorMes,
            'ultimasOrdenes' => $ultimasOrdenes,
            'ordenesPendientes' => $ordenesPendientes,
            'topEmpresas' => $topEmpresas,
        ];
    }

    /**
     * Obtener estadísticas para usuarios de empresa
     */
    private function getEmpresaStats(User $user): array
    {
        $empresaId = $user->empresa_id;
        
        if (!$empresaId) {
            return ['empresa' => null];
        }

        $empresa = Empresa::find($empresaId);
        $hoy = Carbon::today();
        $inicioMes = Carbon::now()->startOfMonth();

        // Órdenes de la empresa
        $totalOrdenes = Orden::where('empresa_id', $empresaId)->count();
        $ordenesEsteMes = Orden::where('empresa_id', $empresaId)
            ->where('created_at', '>=', $inicioMes)
            ->count();

        // Órdenes por estado
        $ordenesPorEstado = Orden::where('empresa_id', $empresaId)
            ->select('estado', DB::raw('count(*) as total'))
            ->groupBy('estado')
            ->pluck('total', 'estado')
            ->toArray();

        // Evaluados de la empresa
        $totalEvaluados = EvaluadoOrden::whereHas('orden', function($q) use ($empresaId) {
            $q->where('empresa_id', $empresaId);
        })->count();

        // Cuestionarios completados
        $cuestionariosCompletados = EvaluadoOrden::whereHas('orden', function($q) use ($empresaId) {
            $q->where('empresa_id', $empresaId);
        })->where('cuestionario_completado', true)->count();

        // Últimas órdenes
        $ultimasOrdenes = Orden::where('empresa_id', $empresaId)
            ->with('evaluados')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Órdenes pendientes
        $ordenesPendientes = Orden::where('empresa_id', $empresaId)
            ->whereNotIn('estado', ['entregado', 'cancelado'])
            ->count();

        return [
            'empresa' => $empresa,
            'totalOrdenes' => $totalOrdenes,
            'ordenesEsteMes' => $ordenesEsteMes,
            'ordenesPorEstado' => $ordenesPorEstado,
            'totalEvaluados' => $totalEvaluados,
            'cuestionariosCompletados' => $cuestionariosCompletados,
            'ultimasOrdenes' => $ultimasOrdenes,
            'ordenesPendientes' => $ordenesPendientes,
        ];
    }
}
