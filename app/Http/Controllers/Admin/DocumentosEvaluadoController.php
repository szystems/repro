<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\DocumentoEvaluadoRequest;
use App\Models\DocumentoEvaluado;
use App\Models\EvaluadoOrden;
use App\Support\DocumentoEvaluadoPreview;
use App\Support\InformeWordAnexosPapeleria;
use App\Support\RedirectFichaOrden;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class DocumentosEvaluadoController extends Controller
{
    /**
     * Subir un documento para un evaluado.
     */
    public function store(DocumentoEvaluadoRequest $request)
    {
        $evaluado = EvaluadoOrden::findOrFail($request->evaluado_orden_id);

        // Verificar que el usuario puede ver esta orden
        if (!$this->puedeAcceder($evaluado)) {
            abort(403);
        }

        $archivo = $request->file('archivo');
        $ruta = $archivo->store(
            'documentos_evaluados/' . $evaluado->id,
            'local'
        );

        $documento = DocumentoEvaluado::create([
            'evaluado_orden_id'  => $evaluado->id,
            'tipo_documento'     => $request->tipo_documento,
            'nombre_original'    => $archivo->getClientOriginalName(),
            'ruta_archivo'       => $ruta,
            'mime_type'          => $archivo->getMimeType(),
            'tamano'             => $archivo->getSize(),
            'subido_por_tipo'    => Auth::user()->role_as >= 2 ? 'repro' : 'empresa',
            'subido_por_user_id' => Auth::id(),
            'estado_verificacion' => 'pendiente',
            'notas'              => $request->notas ?: null,
        ]);

        return RedirectFichaOrden::evaluado($evaluado, 'Documento "' . $documento->tipo_documento_texto . '" subido correctamente.');
    }

    /**
     * Descargar un documento.
     */
    public function download(DocumentoEvaluado $documento)
    {
        $evaluado = $documento->evaluadoOrden;

        if (!$this->puedeAcceder($evaluado)) {
            abort(403);
        }

        if (!Storage::disk('local')->exists($documento->ruta_archivo)) {
            return back()->with('error', 'El archivo no existe en el servidor.');
        }

        return Storage::disk('local')->download(
            $documento->ruta_archivo,
            $documento->nombre_original
        );
    }

    /**
     * Servir un documento de forma inline (vista previa en navegador).
     */
    public function preview(DocumentoEvaluado $documento)
    {
        $evaluado = $documento->evaluadoOrden;

        if (!$this->puedeAcceder($evaluado)) {
            abort(403);
        }

        if (!Storage::disk('local')->exists($documento->ruta_archivo)) {
            abort(404, 'El archivo no existe en el servidor.');
        }

        $ruta = DocumentoEvaluadoPreview::rutaParaPreview($documento);
        $mimeType = DocumentoEvaluadoPreview::mimeParaPreview($documento, $ruta);
        $nombre = $ruta === DocumentoEvaluadoPreview::rutaMiniatura($documento->ruta_archivo)
            ? pathinfo($documento->nombre_original, PATHINFO_FILENAME).(DocumentoEvaluadoPreview::usaJpeg() ? '.jpg' : '.png')
            : $documento->nombre_original;

        return Storage::disk('local')->response($ruta, $nombre, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline; filename="'.$nombre.'"',
            'Cache-Control' => 'private, max-age=86400',
        ]);
    }

    /**
     * Verificar (aprobar/rechazar) un documento — solo REPRO.
     */
    public function verificar(Request $request, DocumentoEvaluado $documento)
    {
        if (Auth::user()->role_as < 2) {
            abort(403);
        }

        $request->validate([
            'estado_verificacion' => 'required|in:aprobado,rechazado',
            'notas_verificacion'  => 'nullable|string|max:500',
        ]);

        $documento->verificar(
            $request->estado_verificacion,
            Auth::id(),
            $request->notas_verificacion
        );

        $etiqueta = $request->estado_verificacion === 'aprobado' ? 'aprobado' : 'rechazado';

        return RedirectFichaOrden::evaluado($documento->evaluadoOrden, "Documento {$etiqueta} correctamente.");
    }

    /**
     * Eliminar un documento.
     */
    public function destroy(DocumentoEvaluado $documento)
    {
        $evaluado = $documento->evaluadoOrden;

        if (!$this->puedeAcceder($evaluado)) {
            abort(403);
        }

        // Solo quien lo subió o REPRO puede eliminar
        $user = Auth::user();
        if ($user->role_as < 2 && $documento->subido_por_user_id !== $user->id) {
            abort(403, 'No tiene permiso para eliminar este documento.');
        }

        // Empresa solo puede eliminar documentos en estado pendiente
        if ($user->role_as < 2 && $documento->estado_verificacion !== 'pendiente') {
            abort(403, 'Solo se pueden eliminar documentos con estado pendiente.');
        }

        $nombre = $documento->tipo_documento_texto;
        $documento->eliminarConArchivo();

        return RedirectFichaOrden::evaluado($evaluado, "Documento \"{$nombre}\" eliminado.");
    }

    /**
     * Imágenes que REPRO marcó para pegar al final del informe Word.
     */
    public function guardarAnexosWord(Request $request, EvaluadoOrden $evaluado)
    {
        if (Auth::user()->role_as < 2 || ! $this->puedeAcceder($evaluado)) {
            abort(403);
        }

        $permitidos = InformeWordAnexosPapeleria::imagenesDisponibles($evaluado)
            ->pluck('id')
            ->map(static fn ($id): int => (int) $id)
            ->all();
        $pedidos = array_map('intval', (array) $request->input('ids', []));
        $ids = array_values(array_intersect($pedidos, $permitidos));

        InformeWordAnexosPapeleria::guardarSeleccion($evaluado->id, $ids, Auth::id());

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'incluidos' => count($ids),
            ]);
        }

        return RedirectFichaOrden::evaluado(
            $evaluado,
            count($ids) === 0
                ? 'El Word no llevará papelería.'
                : 'El Word llevará '.count($ids).' imagen'.(count($ids) === 1 ? '' : 'es').' al final.'
        );
    }

    /**
     * Verifica que el usuario logueado pueda acceder a documentos de este evaluado.
     */
    private function puedeAcceder(EvaluadoOrden $evaluado): bool
    {
        $user = Auth::user();

        // Admin/REPRO puede ver todo
        if ($user->role_as >= 2) {
            return true;
        }

        // Empresa solo ve evaluados de sus órdenes
        return $evaluado->orden
            && $evaluado->orden->empresa_id === $user->empresa_id;
    }
}
