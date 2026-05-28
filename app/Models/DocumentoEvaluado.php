<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

/**
 * Documento subido para un evaluado específico de una orden.
 *
 * Puede ser cargado por la empresa, REPRO o el propio evaluado
 * (durante el cuestionario). REPRO lo verifica (aprobado/rechazado).
 */
class DocumentoEvaluado extends Model
{
    use HasFactory;

    protected $table = 'documento_evaluados';

    protected $fillable = [
        'evaluado_orden_id',
        'tipo_documento',
        'nombre_original',
        'ruta_archivo',
        'mime_type',
        'tamano',
        'subido_por_tipo',
        'subido_por_user_id',
        'estado_verificacion',
        'verificado_por',
        'verificado_at',
        'notas_verificacion',
        'notas',
    ];

    protected function casts(): array
    {
        return [
            'tamano' => 'integer',
            'verificado_at' => 'datetime',
        ];
    }

    /**
     * Tipos de documento disponibles con sus etiquetas.
     *
     * @return array<string, string>
     */
    public static function tiposDocumento(): array
    {
        return [
            'antecedentes_penales'   => 'Antecedentes Penales',
            'antecedentes_policiacos' => 'Antecedentes Policíacos',
            'cv'                     => 'Currículum Vitae',
            'constancia_estudios'    => 'Constancia de Estudios',
            'licencia_auto'          => 'Licencia de Conducir (Auto)',
            'licencia_moto'          => 'Licencia de Conducir (Moto)',
            'dpi_archivo'            => 'DPI (Documento)',
            'pasaporte'              => 'Pasaporte',
            'carta_laboral'          => 'Carta Laboral',
            'foto_tatuaje'           => 'Fotografía de Tatuaje',
            'autorizacion_firmada'   => 'Autorización Firmada',
            'seguimiento'            => 'Seguimiento REPRO',
            'otro'                   => 'Otro',
        ];
    }

    /**
     * Estados de verificación con etiquetas.
     *
     * @return array<string, string>
     */
    public static function estadosVerificacion(): array
    {
        return [
            'pendiente' => 'Pendiente',
            'aprobado'  => 'Aprobado',
            'rechazado' => 'Rechazado',
        ];
    }

    // ─── Relaciones ───

    public function evaluadoOrden(): BelongsTo
    {
        return $this->belongsTo(EvaluadoOrden::class, 'evaluado_orden_id');
    }

    public function subidoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'subido_por_user_id');
    }

    public function verificador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verificado_por');
    }

    // ─── Accessors ───

    /**
     * Etiqueta legible del tipo de documento.
     */
    public function getTipoDocumentoTextoAttribute(): string
    {
        return static::tiposDocumento()[$this->tipo_documento] ?? $this->tipo_documento;
    }

    /**
     * Etiqueta legible del estado de verificación.
     */
    public function getEstadoVerificacionTextoAttribute(): string
    {
        return static::estadosVerificacion()[$this->estado_verificacion] ?? $this->estado_verificacion;
    }

    /**
     * Color de badge para el estado de verificación.
     */
    public function getEstadoVerificacionColorAttribute(): string
    {
        return match ($this->estado_verificacion) {
            'aprobado'  => 'success',
            'rechazado' => 'danger',
            default     => 'warning',
        };
    }

    /**
     * Tamaño legible (KB/MB).
     */
    public function getTamanoLegibleAttribute(): string
    {
        if ($this->tamano < 1024) {
            return $this->tamano . ' B';
        }
        if ($this->tamano < 1048576) {
            return round($this->tamano / 1024, 1) . ' KB';
        }
        return round($this->tamano / 1048576, 1) . ' MB';
    }

    /**
     * URL pública para descargar el archivo.
     */
    public function getUrlDescargaAttribute(): string
    {
        return route('documentos-evaluado.download', $this);
    }

    /**
     * Si el archivo es una imagen.
     */
    public function getEsImagenAttribute(): bool
    {
        return str_starts_with($this->mime_type ?? '', 'image/');
    }

    /**
     * Si el archivo es PDF.
     */
    public function getEsPdfAttribute(): bool
    {
        return $this->mime_type === 'application/pdf';
    }

    // ─── Acciones ───

    /**
     * Verifica (aprueba o rechaza) el documento.
     */
    public function verificar(string $estado, int $userId, ?string $notas = null): void
    {
        $this->update([
            'estado_verificacion' => $estado,
            'verificado_por'     => $userId,
            'verificado_at'      => now(),
            'notas_verificacion' => $notas,
        ]);
    }

    /**
     * Elimina el archivo de disco y el registro.
     */
    public function eliminarConArchivo(): bool
    {
        if (Storage::disk('local')->exists($this->ruta_archivo)) {
            Storage::disk('local')->delete($this->ruta_archivo);
        }
        return $this->delete();
    }

    // ─── Scopes ───

    public function scopePendientes($query)
    {
        return $query->where('estado_verificacion', 'pendiente');
    }

    public function scopeAprobados($query)
    {
        return $query->where('estado_verificacion', 'aprobado');
    }

    public function scopeRechazados($query)
    {
        return $query->where('estado_verificacion', 'rechazado');
    }

    public function scopePorTipo($query, string $tipo)
    {
        return $query->where('tipo_documento', $tipo);
    }
}
