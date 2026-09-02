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
use App\Models\Sede;
use App\Models\Cuestionario;
use App\Support\EmpresaVisibilidadReclutadoresSupport;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function index(Request $request)
    {
        $config = Config::first();
        $data = ['config' => $config];

        // Determinar qué layout usar según el rol del usuario
        $user = Auth::user();
        $layout = 'layouts.admin';

        if ($user->role_as == 0) {
            $layout = 'layouts.evaluado';
        } elseif ($user->role_as == 1) {
            $layout = 'layouts.empresa';
            $empresaData = $this->getEmpresaStats($user);

            $buscar = trim((string) $request->input('buscar', ''));
            if ($buscar !== '' && $user->empresa_id) {
                if (!preg_match('/^\d{13}$/', $buscar) && (strlen($buscar) < 2 || strlen($buscar) > 100)) {
                    $empresaData['errorBusqueda'] = 'Ingrese un DPI de 13 dígitos o un nombre/apellido de al menos 2 caracteres.';
                } else {
                    $empresaData['buscar'] = $buscar;
                    $empresaData['resultadosBusqueda'] = EvaluadoOrden::buscarPorEmpresa(
                        (int) $user->empresa_id,
                        $buscar
                    );
                }
            }

            $data = array_merge($data, $empresaData);
        } elseif ($user->role_as >= 2) {
            $layout = 'layouts.admin';
            $data = array_merge($data, $this->getAdminStats());
        }

        // Guardar en sesión para que la vista lo use
        session(['layout' => $layout]);

        return view('admin.index', $data);
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
        $totalOrdenes = Orden::activas()->count();
        $totalEvaluados = EvaluadoOrden::deOrdenesActivas()->count();

        // Órdenes por estado
        $ordenesPorEstado = Orden::activas()->select('estado', DB::raw('count(*) as total'))
            ->groupBy('estado')
            ->pluck('total', 'estado')
            ->toArray();

        // Órdenes del mes actual
        $ordenesEsteMes = Orden::activas()->where('created_at', '>=', $inicioMes)->count();
        $ordenesMesAnterior = Orden::activas()->whereBetween('created_at', [$inicioMesAnterior, $finMesAnterior])->count();
        $variacionOrdenes = $ordenesMesAnterior > 0 
            ? round((($ordenesEsteMes - $ordenesMesAnterior) / $ordenesMesAnterior) * 100, 1) 
            : ($ordenesEsteMes > 0 ? 100 : 0);

        // Evaluados del mes
        $evaluadosEsteMes = EvaluadoOrden::deOrdenesActivas()->where('created_at', '>=', $inicioMes)->count();
        $evaluadosMesAnterior = EvaluadoOrden::deOrdenesActivas()->whereBetween('created_at', [$inicioMesAnterior, $finMesAnterior])->count();
        $variacionEvaluados = $evaluadosMesAnterior > 0 
            ? round((($evaluadosEsteMes - $evaluadosMesAnterior) / $evaluadosMesAnterior) * 100, 1) 
            : ($evaluadosEsteMes > 0 ? 100 : 0);

        // Cuestionarios completados
        $cuestionariosCompletados = EvaluadoOrden::deOrdenesActivas()->where('cuestionario_completado', true)->count();
        $cuestionariosPendientes = EvaluadoOrden::deOrdenesActivas()->where('cuestionario_completado', false)
            ->whereNotNull('token_unico')
            ->count();

        // Evaluados por tipo de servicio
        $evaluadosPorServicio = EvaluadoOrden::deOrdenesActivas()->select('tipo_servicio', DB::raw('count(*) as total'))
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
        $ultimasOrdenes = Orden::with(['empresa', 'creador', 'sede', 'evaluados'])
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
        $ordenesBase = Orden::where('empresa_id', $empresaId)->activas();
        EmpresaVisibilidadReclutadoresSupport::aplicaFiltroTrabajador($ordenesBase, $user);

        $totalOrdenes = (clone $ordenesBase)->count();
        $ordenesEsteMes = (clone $ordenesBase)->where('created_at', '>=', $inicioMes)->count();

        // Órdenes por estado
        $ordenesPorEstado = (clone $ordenesBase)
            ->select('estado', DB::raw('count(*) as total'))
            ->groupBy('estado')
            ->pluck('total', 'estado')
            ->toArray();

        // Evaluados de la empresa
        $evaluadosBase = EvaluadoOrden::query();
        EmpresaVisibilidadReclutadoresSupport::filtrarQueryEvaluadosEmpresa($evaluadosBase, $user);

        $totalEvaluados = (clone $evaluadosBase)->count();

        // Cuestionarios completados
        $cuestionariosCompletados = (clone $evaluadosBase)
            ->where('cuestionario_completado', true)->count();

        // Últimas órdenes
        $ultimasOrdenes = (clone $ordenesBase)
            ->with('evaluados')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Órdenes pendientes
        $ordenesPendientes = (clone $ordenesBase)
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
            // C4: sedes activas con WhatsApp para contacto
            'sedesContacto' => Sede::activas()->whereNotNull('whatsapp')->orderBy('nombre')->get(),
        ];
    }
}
