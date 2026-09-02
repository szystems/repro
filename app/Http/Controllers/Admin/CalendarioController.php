<?php

namespace App\Http\Controllers\Admin;

use App\Exports\CalendarioExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\ProgramarCitaRequest;
use App\Models\Empresa;
use App\Models\EvaluadoOrden;
use App\Models\Sede;
use App\Models\User;
use App\Support\ExportacionesSupport;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CalendarioController extends Controller
{
    /** Slots de 30 min de 08:00 a 18:00 (última cita empieza a 17:30). */
    public const HORA_INICIO = 8;
    public const HORA_FIN = 18;
    public const INTERVALO_MINUTOS = 30;

    /**
     * Vista mensual del calendario.
     */
    public function index(Request $request)
    {
        $filtros = $this->filtrosDesdeRequest($request);
        $fechaDesde = $filtros['fechaDesde'];
        $fechaHasta = $filtros['fechaHasta'];
        if ($fechaDesde) {
            $diaElegido = Carbon::parse($fechaDesde);
            $mes = $diaElegido->month;
            $anio = $diaElegido->year;
        } else {
            $mes  = $request->input('mes', now()->month);
            $anio = $request->input('anio', now()->year);
        }

        $fecha = Carbon::createFromDate($anio, $mes, 1);
        $inicioMes = $fecha->copy()->startOfMonth();
        $finMes    = $fecha->copy()->endOfMonth();

        $sedeId         = $filtros['sedeId'];
        $poligrafistaId = $filtros['poligrafistaId'];
        $encargadoId    = $filtros['encargadoId'];
        $tipoServicio   = $filtros['tipoServicio'];
        $empresaId      = $filtros['empresaId'];

        // Contar citas por día del mes
        $query = EvaluadoOrden::query()
            ->deOrdenesActivas()
            ->programados()
            ->whereBetween('fecha_programada', [$inicioMes, $finMes->copy()->endOfDay()]);
        $this->aplicarFiltrosCitas($query, $filtros);

        $citas = $query->get();

        // Agrupar por día → conteo + desglose por tipo
        $citasPorDia = [];
        foreach ($citas as $cita) {
            $dia = Carbon::parse($cita->fecha_programada)->format('Y-m-d');
            if (!isset($citasPorDia[$dia])) {
                $citasPorDia[$dia] = ['total' => 0, 'poligrafo' => 0, 'vsa' => 0, 'socioeconomico' => 0];
            }
            $citasPorDia[$dia]['total']++;
            $citasPorDia[$dia][$cita->tipo_servicio]++;
        }

        // Contar también reprogramados por su fecha original (registro histórico)
        $reprogOriginalQuery = EvaluadoOrden::query()
            ->deOrdenesActivas()
            ->where('estado_programacion', 'reprogramado')
            ->whereNotNull('fecha_programada_original')
            ->whereBetween('fecha_programada_original', [$inicioMes, $finMes->copy()->endOfDay()]);
        $this->aplicarFiltrosCitas($reprogOriginalQuery, $filtros);
        foreach ($reprogOriginalQuery->get() as $cita) {
            $dia = Carbon::parse($cita->fecha_programada_original)->format('Y-m-d');
            if (!isset($citasPorDia[$dia])) {
                $citasPorDia[$dia] = ['total' => 0, 'poligrafo' => 0, 'vsa' => 0, 'socioeconomico' => 0];
            }
            $citasPorDia[$dia]['total']++;
            $citasPorDia[$dia][$cita->tipo_servicio]++;
        }

        // Datos para filtros
        $sedes         = Sede::activas()->orderBy('nombre')->get();
        $poligrafistas = User::poligrafistas()->get();
        $empresas      = Empresa::query()->where('estado', 1)->orderBy('nombre')->get();

        // P-P2: el historial lista todos los programados del periodo (no solo informe final),
        // para que el filtro encuentre la orden en proceso.
        [$inicioHist, $finHist] = $this->periodoDesdeFiltros(
            $filtros,
            $inicioMes,
            $finMes->copy()->endOfDay()
        );

        $historialQuery = EvaluadoOrden::query()
            ->deOrdenesActivas()
            ->whereNotNull('fecha_programada')
            ->whereBetween('fecha_programada', [$inicioHist, $finHist])
            ->with(['poligrafo', 'responsable', 'sede', 'orden.empresa']);
        $this->aplicarFiltrosCitas($historialQuery, $filtros);

        $historialReprogQuery = EvaluadoOrden::query()
            ->deOrdenesActivas()
            ->where('estado_programacion', 'reprogramado')
            ->whereNotNull('fecha_programada_original')
            ->whereBetween('fecha_programada_original', [$inicioHist, $finHist])
            ->with(['poligrafo', 'responsable', 'sede', 'orden.empresa']);
        $this->aplicarFiltrosCitas($historialReprogQuery, $filtros);

        $historial = $historialQuery->get()
            ->merge($historialReprogQuery->get())
            ->unique('id')
            ->sortByDesc('fecha_programada')
            ->values();

        return view('admin.calendario.index', compact(
            'fecha', 'inicioMes', 'finMes', 'citasPorDia',
            'sedes', 'poligrafistas', 'empresas',
            'sedeId', 'poligrafistaId', 'encargadoId', 'tipoServicio', 'empresaId',
            'fechaDesde', 'fechaHasta', 'inicioHist', 'finHist',
            'mes', 'anio',
            'historial'
        ));
    }

    /** P-X1 / P-P1: Excel del mes (filtros) + total por poligrafista. */
    public function excel(Request $request)
    {
        ExportacionesSupport::asegurarPuedeExportarInformes(Auth::user());

        $filtros = $this->filtrosDesdeRequest($request);
        $mes = (int) $request->input('mes', now()->month);
        $anio = (int) $request->input('anio', now()->year);
        $fecha = Carbon::createFromDate($anio, $mes, 1);
        [$inicio, $fin] = $this->periodoDesdeFiltros(
            $filtros,
            $fecha->copy()->startOfMonth(),
            $fecha->copy()->endOfMonth()
        );
        $citas = $this->queryCitasPeriodo($request, $inicio, $fin)
            ->orderBy('fecha_programada')->get();

        $export = new CalendarioExport($citas);
        $base = ($filtros['fechaDesde'] || $filtros['fechaHasta'])
            ? 'calendario-'.$inicio->format('Y-m-d').'_'.$fin->format('Y-m-d')
            : 'calendario-'.$fecha->format('Y-m');

        return ExportacionesSupport::descargarExcel($export, $base);
    }

    public function excelDia(Request $request, string $fecha)
    {
        ExportacionesSupport::asegurarPuedeExportarInformes(Auth::user());

        $dia = Carbon::parse($fecha);
        $citas = $this->queryCitasPeriodo($request, $dia->copy()->startOfDay(), $dia->copy()->endOfDay())
            ->orderBy('fecha_programada')
            ->get();

        $export = new CalendarioExport($citas);
        $base = 'calendario-'.$dia->format('Y-m-d');

        return ExportacionesSupport::descargarExcel($export, $base);
    }

    /**
     * Vista diaria del calendario (slots por hora).
     */
    public function dia(Request $request, string $fecha)
    {
        $fechaCarbon = Carbon::parse($fecha);
        $filtros = $this->filtrosDesdeRequest($request);

        $sedeId         = $filtros['sedeId'];
        $poligrafistaId = $filtros['poligrafistaId'];
        $encargadoId    = $filtros['encargadoId'];
        $tipoServicio   = $filtros['tipoServicio'];
        $empresaId      = $filtros['empresaId'];

        $query = EvaluadoOrden::query()
            ->deOrdenesActivas()
            ->programados()
            ->enDia($fecha)
            ->with(['poligrafo', 'responsable', 'sede', 'orden.empresa']);
        $this->aplicarFiltrosCitas($query, $filtros);

        $citas = $query->orderBy('fecha_programada')->get();

        // Citas históricas: reprogramados que tenían cita este día (por fecha original)
        $citasHistoricas = EvaluadoOrden::query()
            ->deOrdenesActivas()
            ->where('estado_programacion', 'reprogramado')
            ->whereNotNull('fecha_programada_original')
            ->whereDate('fecha_programada_original', $fecha)
            ->with(['poligrafo', 'responsable', 'sede', 'orden.empresa']);
        $this->aplicarFiltrosCitas($citasHistoricas, $filtros);
        $citasHistoricas = $citasHistoricas
            ->orderBy('fecha_programada_original')
            ->get();

        // Generar slots de 30 min
        $slots = $this->generarSlots($fecha);

        // Datos para filtros y modal de programación
        $sedes         = Sede::activas()->orderBy('nombre')->get();
        $poligrafistas = User::poligrafistas()->get();
        $empresas      = Empresa::query()->where('estado', 1)->orderBy('nombre')->get();

        // Evaluados disponibles para programar: excluye solo estados terminales (no filtra por fecha_programada,
        // ya que un evaluado puede reprogramarse o aún no tener fecha asignada)
        // Fase 18: excluir evaluados en estados terminales de evaluacion Y de programacion
        $evaluadosPendientes = EvaluadoOrden::query()
            ->deOrdenesActivas()
            ->whereNotIn('estado_evaluacion', ['cancelado', 'desistio', 'informe_final_enviado'])
            ->whereNotIn('estado_programacion', ['cancelado', 'desistio'])
            ->with('orden.empresa')
            ->orderBy('created_at', 'desc')
            ->limit(200)
            ->get();

        return view('admin.calendario.dia', compact(
            'fechaCarbon', 'fecha', 'citas', 'slots',
            'sedes', 'poligrafistas', 'empresas', 'evaluadosPendientes',
            'sedeId', 'poligrafistaId', 'encargadoId', 'tipoServicio', 'empresaId',
            'citasHistoricas'
        ));
    }

    /**
     * Programar una cita (POST).
     */
    public function programar(ProgramarCitaRequest $request)
    {
        $evaluado = EvaluadoOrden::findOrFail($request->evaluado_orden_id);
        $sede     = Sede::findOrFail($request->sede_id);
        $inicio   = $request->getInicio();
        $fin      = $request->getFin();

        // Validar capacidad de la sede (Fase 19: sin límite por poligrafista)
        if ($sede->tieneTraslape($request->poligrafista_id, $inicio, $fin)) {
            $capacidad = max(1, (int) $sede->capacidad);
            $mensaje = "La sede \"{$sede->nombre}\" ya tiene {$capacidad} cita(s) en ese horario (capacidad máxima).";

            return back()
                ->withErrors(['traslape' => $mensaje])
                ->with('error', $mensaje)
                ->with('programar_evaluado_id', $evaluado->id)
                ->withInput();
        }

        $evaluado->programarEvaluacion(
            $inicio,
            $fin,
            $request->poligrafista_id ?: Auth::id(),
            $request->sede_id,
            $request->modalidad,
            $request->responsable_id
        );

        return redirect()->back()
            ->with('success', 'Cita programada correctamente para ' . $evaluado->nombre . ' ' . $evaluado->apellidos);
    }

    /**
     * Reprogramar una cita existente (PATCH).
     */
    public function reprogramar(ProgramarCitaRequest $request, EvaluadoOrden $evaluado)
    {
        $sede   = Sede::findOrFail($request->sede_id);
        $inicio = $request->getInicio();
        $fin    = $request->getFin();

        // Validar anti-traslape (excluyendo este evaluado)
        if ($sede->tieneTraslape($request->poligrafista_id, $inicio, $fin, $evaluado->id)) {
            $capacidad = max(1, (int) $sede->capacidad);
            $mensaje = "La sede \"{$sede->nombre}\" ya tiene {$capacidad} cita(s) en ese horario (capacidad máxima).";

            return back()
                ->withErrors(['traslape' => $mensaje])
                ->with('error', $mensaje)
                ->with('programar_evaluado_id', $evaluado->id)
                ->withInput();
        }

        $evaluado->reprogramarEvaluacion(
            $inicio,
            $fin,
            $request->poligrafista_id,
            $request->sede_id,
            $request->modalidad,
            $request->responsable_id,
            $request->motivo_reprogramacion
        );

        return redirect()->back()
            ->with('success', 'Cita reprogramada correctamente.');
    }

    /**
     * Cancelar una cita (DELETE).
     */
    public function cancelar(EvaluadoOrden $evaluado)
    {
        $fecha = $evaluado->fecha_programada
            ? Carbon::parse($evaluado->fecha_programada)->format('Y-m-d')
            : now()->format('Y-m-d');

        $evaluado->cancelarCita();

        return redirect()->route('calendario.dia', ['fecha' => $fecha])
            ->with('success', 'Cita cancelada correctamente.');
    }

    /**
     * Generar slots de 30 minutos para la vista diaria.
     *
     * @return array<int, array{hora: string, label: string}>
     */
    private function generarSlots(string $fecha): array
    {
        $slots = [];
        $hora = Carbon::parse($fecha)->setTime(self::HORA_INICIO, 0);
        $fin  = Carbon::parse($fecha)->setTime(self::HORA_FIN, 0);

        while ($hora < $fin) {
            $slots[] = [
                'hora'  => $hora->format('H:i'),
                'label' => $hora->format('h:i A'),
            ];
            $hora->addMinutes(self::INTERVALO_MINUTOS);
        }

        return $slots;
    }

    private function queryCitasPeriodo(Request $request, Carbon $inicio, Carbon $fin)
    {
        $query = EvaluadoOrden::query()
            ->deOrdenesActivas()
            ->whereNotNull('fecha_programada')
            ->whereBetween('fecha_programada', [$inicio, $fin->copy()->endOfDay()])
            ->with(['poligrafo', 'responsable', 'sede', 'orden.empresa']);

        $this->aplicarFiltrosCitas($query, $this->filtrosDesdeRequest($request));

        return $query;
    }

    /**
     * @return array{sedeId: ?string, poligrafistaId: ?string, encargadoId: ?string, tipoServicio: ?string, empresaId: ?string, fechaDesde: ?string, fechaHasta: ?string}
     */
    private function filtrosDesdeRequest(Request $request): array
    {
        $legacy = $request->input('fecha') ?: null;
        $fechaDesde = $request->input('fecha_desde') ?: $legacy;
        $fechaHasta = $request->input('fecha_hasta') ?: null;
        if ($fechaHasta === null && $legacy && ! $request->filled('fecha_desde')) {
            $fechaHasta = $legacy;
        }

        return [
            'sedeId' => $request->input('sede_id') ?: null,
            'poligrafistaId' => $request->input('poligrafista_id') ?: null,
            'encargadoId' => $request->input('encargado_id') ?: null,
            'tipoServicio' => $request->input('tipo_servicio') ?: null,
            'empresaId' => $request->input('empresa_id') ?: null,
            'fechaDesde' => $fechaDesde,
            'fechaHasta' => $fechaHasta,
        ];
    }

    /** @return array{0: Carbon, 1: Carbon} */
    private function periodoDesdeFiltros(array $filtros, Carbon $inicioDefault, Carbon $finDefault): array
    {
        $desde = $filtros['fechaDesde'] ?? null;
        $hasta = $filtros['fechaHasta'] ?? null;
        if ($desde === null && $hasta === null) {
            return [$inicioDefault, $finDefault];
        }

        $inicio = $desde
            ? Carbon::parse($desde)->startOfDay()
            : $inicioDefault->copy()->startOfDay();
        $fin = $hasta
            ? Carbon::parse($hasta)->endOfDay()
            : $finDefault->copy()->endOfDay();

        if ($inicio->gt($fin)) {
            [$inicio, $fin] = [$fin->copy()->startOfDay(), $inicio->copy()->endOfDay()];
        }

        return [$inicio, $fin];
    }

    private function aplicarFiltrosCitas($query, array $filtros): void
    {
        if (!empty($filtros['sedeId'])) {
            $query->where('sede_id', $filtros['sedeId']);
        }
        if (!empty($filtros['poligrafistaId'])) {
            $query->where('poligrafista_id', $filtros['poligrafistaId']);
        }
        if (!empty($filtros['encargadoId'])) {
            $query->where('responsable_id', $filtros['encargadoId']);
        }
        if (!empty($filtros['tipoServicio'])) {
            $query->where('tipo_servicio', $filtros['tipoServicio']);
        }
        if (!empty($filtros['empresaId'])) {
            $query->whereHas('orden', fn ($q) => $q->where('empresa_id', $filtros['empresaId']));
        }
    }
}
