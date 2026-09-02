<?php

namespace App\Support;

use App\Models\User;
use Maatwebsite\Excel\Facades\Excel;

class ExportacionesSupport
{
    public const PERMISO_INFORMES = 'reportes.generar';

    public const PERMISO_PADRON_EMPRESAS = 'empresas.exportar';

    public static function puedeExportarInformes(User $user): bool
    {
        if ((int) $user->role_as >= 2) {
            return true;
        }

        if ($user->isEmpresa()) {
            return $user->hasPermission('ordenes.ver') || $user->hasPermission('reportes.ver');
        }

        return $user->hasPermission(self::PERMISO_INFORMES);
    }

    public static function puedeExportarPadronEmpresas(User $user): bool
    {
        if ((int) $user->role_as >= 3) {
            return true;
        }

        return $user->hasPermission(self::PERMISO_PADRON_EMPRESAS);
    }

    public static function asegurarPuedeExportarInformes(?User $user): void
    {
        if (! $user || ! self::puedeExportarInformes($user)) {
            abort(403, 'No tiene permiso para descargar Excel de informes u órdenes.');
        }
    }

    public static function asegurarPuedeExportarPadronEmpresas(?User $user): void
    {
        if (! $user || ! self::puedeExportarPadronEmpresas($user)) {
            abort(403, 'No tiene permiso para descargar el padrón de empresas.');
        }
    }

    public static function descargarExcel(object $export, string $base)
    {
        if (! class_exists(\XMLWriter::class) && method_exists($export, 'toHtmlTable')) {
            return response($export->toHtmlTable(), 200, [
                'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="'.$base.'.xls"',
            ]);
        }

        return Excel::download($export, $base.'.xlsx');
    }
}
