<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cuestionario;
use App\Models\EvaluadorNota;
use App\Support\AsistenteRedaccion;
use App\Support\AsistenteRedaccionGenerador;
use App\Support\AsistenteRedaccionSecciones;
use App\Support\EvaluadorNotasSupport;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use RuntimeException;

class AsistenteRedaccionController extends Controller
{
    public function generar(Request $request, int $cuestionario, AsistenteRedaccionGenerador $generador): JsonResponse
    {
        $registro = $this->autorizar($cuestionario);
        $datos = $request->validate([
            'seccion' => 'required|string|max:40',
            'modo' => 'required|string|in:confirmada,observaciones,instruccion,reescribir,otra_version,cambiar_estilo',
            'observacion' => 'nullable|string|max:5000',
            'resto_sin_cambios' => 'nullable|boolean',
            'texto_actual' => 'nullable|string|max:10000',
            'estilo' => 'nullable|string|in:formal,tecnica,breve,detallada',
            'proveedor' => 'nullable|string|in:claude,gpt',
        ]);

        if (! AsistenteRedaccionSecciones::permite($datos['seccion'])) {
            return response()->json(['message' => 'Esta sección no usa el asistente.'], 422);
        }

        $textoActual = trim((string) ($datos['texto_actual'] ?? ''));
        if (in_array($datos['modo'], ['reescribir', 'otra_version', 'cambiar_estilo', 'instruccion'], true) && $textoActual === '') {
            return response()->json(['message' => 'No hay un texto para modificar.'], 422);
        }
        if ($datos['modo'] === 'observaciones' && trim((string) ($datos['observacion'] ?? '')) === '') {
            return response()->json(['message' => 'Escriba la observación de la entrevista.'], 422);
        }
        if ($datos['modo'] === 'instruccion' && trim((string) ($datos['observacion'] ?? '')) === '') {
            return response()->json(['message' => 'Escriba la instrucción.'], 422);
        }

        try {
            $propuesta = $generador->proponer(
                $registro->evaluadoOrden,
                $datos['seccion'],
                $datos['modo'],
                trim((string) ($datos['observacion'] ?? '')),
                $request->boolean('resto_sin_cambios'),
                $textoActual,
                (string) ($datos['estilo'] ?? 'formal'),
                (string) ($datos['proveedor'] ?? 'claude')
            );
        } catch (ConnectionException) {
            $nombre = ($datos['proveedor'] ?? 'claude') === 'gpt' ? 'GPT' : 'Claude';

            return response()->json([
                'message' => $nombre.' tardó demasiado. Intente de nuevo.',
            ], 503);
        } catch (RuntimeException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 503);
        } catch (\Throwable) {
            return response()->json([
                'message' => 'No se pudo redactar. Intente de nuevo.',
            ], 503);
        }

        return response()->json($propuesta);
    }

    public function aceptar(Request $request, int $cuestionario): JsonResponse
    {
        $registro = $this->autorizar($cuestionario);
        $datos = $request->validate([
            'seccion' => 'required|string|max:40',
            'texto' => 'required|string|max:10000',
        ]);

        if (! AsistenteRedaccionSecciones::permite($datos['seccion'])) {
            return response()->json(['message' => 'Esta sección no usa el asistente.'], 422);
        }

        $texto = trim($datos['texto']);
        if ($texto === '') {
            return response()->json(['message' => 'La redacción está vacía.'], 422);
        }

        EvaluadorNota::guardarNota(
            $registro->evaluado_orden_id,
            $datos['seccion'],
            '',
            $texto,
            Auth::id()
        );

        return response()->json(['texto' => $texto]);
    }

    private function autorizar(int $cuestionario): Cuestionario
    {
        if (! AsistenteRedaccion::activo()) {
            abort(404);
        }
        if (! EvaluadorNotasSupport::puedeGestionar(Auth::user())) {
            abort(403);
        }

        return Cuestionario::query()->with('evaluadoOrden.cuestionario')->findOrFail($cuestionario);
    }
}
