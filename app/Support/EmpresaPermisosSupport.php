<?php

namespace App\Support;

/**
 * Puente permisos JSON sub-usuario empresa → permisos granulares del middleware.
 *
 * Fuente cliente: docs/repro/cambios agosto/USUARIO DE CLIENTE (2).pdf
 * Spec agentes: docs/repro/cambios agosto/PERMISOS_EMPRESA_CLIENTE.md
 */
class EmpresaPermisosSupport
{
    /**
     * Perfil por defecto al crear un trabajador (reclutador/asistente).
     * Sin crear órdenes ni reportes; incluye papelería, PDF orden y editar propias.
     *
     * @var list<string>
     */
    public const PERMISOS_DEFAULT_TRABAJADOR = [
        'ver_ordenes',
        'ver_resultados',
        'descargar_pdf',
        'subir_documentos',
        'editar_ordenes',
        'descargar_documentos',
    ];

    /** @var array<string, list<string>> permiso empresa (JSON) → permisos sistema */
    public const MAPA = [
        'ver_ordenes' => ['ordenes.ver'],
        'crear_ordenes' => ['ordenes.crear'],
        'editar_ordenes' => ['ordenes.editar', 'ordenes.eliminar'],
        'ver_resultados' => ['resultados.ver', 'cuestionarios.ver'],
        'descargar_pdf' => ['resultados.descargar', 'ordenes.ver'],
        'subir_documentos' => ['documentos.subir'],
        'descargar_documentos' => ['documentos.ver'],
        'ver_reportes' => ['reportes.ver'],
    ];

    public static function permisoSistemaPermitido(string $permisoSistema): bool
    {
        foreach (self::MAPA as $permisos) {
            if (in_array($permisoSistema, $permisos, true)) {
                return true;
            }
        }

        return false;
    }

    public static function empresaTienePermisoSistema(?string $permisoEmpresaJson, string $permisoSistema): bool
    {
        if (! self::permisoSistemaPermitido($permisoSistema)) {
            return false;
        }

        $permisos = self::decodificar($permisoEmpresaJson);

        foreach (self::MAPA as $claveEmpresa => $permisosSistema) {
            if (! in_array($permisoSistema, $permisosSistema, true)) {
                continue;
            }
            if (in_array($claveEmpresa, $permisos, true)) {
                return true;
            }
        }

        return false;
    }

    /** @return list<string> */
    public static function clavesDisponibles(): array
    {
        return array_keys(self::MAPA);
    }

    /** @return list<string> */
    public static function permisosDefaultTrabajador(): array
    {
        return self::PERMISOS_DEFAULT_TRABAJADOR;
    }

    public static function trabajadorTienePermiso(?string $permisoEmpresaJson, string $claveEmpresa): bool
    {
        return in_array($claveEmpresa, self::decodificar($permisoEmpresaJson), true);
    }

    /** @return list<string> */
    private static function decodificar(?string $permisoEmpresaJson): array
    {
        if ($permisoEmpresaJson === null || $permisoEmpresaJson === '') {
            return [];
        }

        $decoded = json_decode($permisoEmpresaJson, true);

        return is_array($decoded) ? $decoded : [];
    }
}
