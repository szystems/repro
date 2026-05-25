<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProgramarCitaRequest;
use App\Models\EvaluadoOrden;
use App\Models\Sede;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

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
        $mes  = $request->input('mes', now()->month);
        $anio = $request->input('anio', now()->year);

        $fecha = Carbon::createFromDate($anio, $mes, 1);
        $inicioMes = $fecha->copy()->startOfMonth();
        $finMes    = $fecha->copy()->endOfMonth();

        // Filtros opcionales
        $sedeId         = $request->input('sede_id');
        $poligrafistaId = $request->input('poligrafista_id');
        $tipoServicio   = $request->input('tipo_servicio');

        // Contar citas por día del mes
        $query = EvaluadoOrden::query()
            ->programados()
            ->whereBetween('fecha_programada', [$inicioMes, $finMes->copy()->endOfDay()]);

        if ($sedeId) {
            $query->where('sede_id', $sedeId);
        }
        if ($poligrafistaId) {
            $query->where('poligrafista_id', $poligrafistaId);
        }
        if ($tipoServicio) {
            $query->where('tipo_servicio', $tipoServicio);
        }

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

        // Datos para filtros
        $sedes         = Sede::activas()->orderBy('nombre')->get();
        $poligrafistas = User::poligrafistas()->get();

        // CO9-hist: historial de candidatos completados/inasistencias en este mes
        $historialQuery = EvaluadoOrden::query()
            ->whereIn('estado_evaluacion', ['completado', 'inasistencia', 'desistio', 'cancelado'])
            ->whereBetween('fecha_programada', [$inicioMes, $finMes->copy()->endOfDay()])
            ->with(['poligrafo', 'sede', 'orden.empresa']);

        if ($sedeId) {
            $historialQuery->where('sede_id', $sedeId);
        }
        if ($poligrafistaId) {
            $historialQuery->where('poligrafista_id', $poligrafistaId);
        }
        if ($tipoServicio) {
            $historialQuery->where('tipo_servicio', $tipoServicio);
        }

        $historial = $historialQuery->orderByDesc('fecha_programada')->get();

        return view('admin.calendario.index', compact(
            'fecha', 'inicioMes', 'finMes', 'citasPorDia',
            'sedes', 'poligrafistas',
            'sedeId', 'poligrafistaId', 'tipoServicio',
            'mes', 'anio',
            'historial'
        ));
    }

    /**
     * Vista diaria del calendario (slots por hora).
     */
    public function dia(Request $request, string $fecha)
    {
        $fechaCarbon = Carbon::parse($fecha);

        // Filtros opcionales
        $sedeId         = $request->input('sede_id');
        $poligrafistaId = $request->input('poligrafista_id');
        $tipoServicio   = $request->input('tipo_servicio');

        $query = EvaluadoOrden::query()
            ->programados()
            ->enDia($fecha)
            ->with(['poligrafo', 'sede', 'orden.empresa']);

        if ($sedeId) {
            $query->where('sede_id', $sedeId);
        }
        if ($poligrafistaId) {
            $query->where('poligrafista_id', $poligrafistaId);
        }
        if ($tipoServicio) {
            $query->where('tipo_servicio', $tipoServicio);
        }

        $citas = $query->orderBy('fecha_programada')->get();

        // Generar slots de 30 min
        $slots = $this->generarSlots($fecha);

        // Datos para filtros y modal de programación
        $sedes         = Sede::activas()->orderBy('nombre')->get();
        $poligrafistas = User::poligrafistas()->get();

        // Evaluados disponibles para programar: excluye solo estados terminales (no filtra por fecha_programada,
        // ya que un evaluado puede reprogramarse o aún no tener fecha asignada)
        $evaluadosPendientes = EvaluadoOrden::query()
            ->whereNotIn('estado_evaluacion', ['cancelado', 'completado', 'desistio', 'inasistencia'])
            ->with('orden.empresa')
            ->orderBy('created_at', 'desc')
            ->limit(200)
            ->get();

        return view('admin.calendario.dia', compact(
            'fechaCarbon', 'fecha', 'citas', 'slots',
            'sedes', 'poligrafistas', 'evaluadosPendientes',
            'sedeId', 'poligrafistaId', 'tipoServicio'
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

        // Validar anti-traslape
        if ($sede->tieneTraslape($request->poligrafista_id, $inicio, $fin)) {
            return back()->withErrors([
                'traslape' => 'El poligrafista ya tiene una cita en esta sede que se cruza con el horario seleccionado.',
            ])->withInput();
        }

        $evaluado->programarEvaluacion(
            $inicio,
            $fin,
            $request->poligrafista_id,
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
            return back()->withErrors([
                'traslape' => 'El poligrafista ya tiene una cita en esta sede que se cruza con el horario seleccionado.',
            ])->withInput();
        }

        $evaluado->reprogramarEvaluacion(
            $inicio,
            $fin,
            $request->poligrafista_id,
            $request->sede_id,
            $request->modalidad,
            $request->responsable_id
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
}
