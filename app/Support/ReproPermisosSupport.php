<?php

namespace App\Support;

/**
 * Permisos operativos que un empleado REPRO (role_as >= 2) no puede perder
 * al guardar su ficha (rol personal user_{id}).
 *
 * No se tocan filas de usuarios: es un fallback en código.
 */
class ReproPermisosSupport
{
    /** @var list<string> */
    public const NUCLEO = [
        'ordenes.ver',
        'ordenes.crear',
        'ordenes.editar',
        'evaluaciones.ver',
        'evaluaciones.crear',
        'evaluaciones.editar',
        'evaluaciones.realizar',
        'resultados.ver',
        'resultados.descargar',
        'resultados.editar',
        'resultados.eliminar',
        'cuestionarios.ver',
        'empresas.ver',
        'calendario.ver',
        'calendario.editar',
        'notificaciones.ver',
        'documentos.ver',
        'documentos.subir',
        'documentos.verificar',
        'informe_preliminar.ver',
        'informe_preliminar.editar',
        'observacion.ver',
        'observacion.editar',
        'historial_dpi.ver',
    ];

    public static function esNucleo(string $permisoSistema): bool
    {
        return in_array($permisoSistema, self::NUCLEO, true);
    }

    /**
     * @param  list<string>  $permisos
     * @return list<string>
     */
    public static function conNucleo(array $permisos): array
    {
        return array_values(array_unique(array_merge($permisos, self::NUCLEO)));
    }
}
