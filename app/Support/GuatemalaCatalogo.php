<?php

namespace App\Support;

use App\Models\Departamento;
use Database\Seeders\DepartamentosMunicipiosSeeder;
use Illuminate\Support\Facades\Cache;

/**
 * Fase F (formularios) — E1.4: catálogo Deptos/Municipios GT para selects dependientes.
 */
class GuatemalaCatalogo
{
    public const OTRO_EXTRANJERO = '__otro_extranjero__';

    /**
     * Estructura anidada para JSON en cliente (sin latencia en selects).
     *
     * @return array<int, array{codigo: string, nombre: string, municipios: array<int, array{codigo: string, nombre: string}>}>
     */
    public static function paraSelectCliente(): array
    {
        if (! Departamento::exists()) {
            (new DepartamentosMunicipiosSeeder)->run();
            self::limpiarCache();
        }

        return Cache::rememberForever('guatemala_catalogo_select', function () {
            return Departamento::query()
                ->with(['municipios:id,departamento_id,codigo,nombre'])
                ->orderBy('orden')
                ->get(['id', 'codigo', 'nombre'])
                ->map(fn (Departamento $depto) => [
                    'codigo' => $depto->codigo,
                    'nombre' => $depto->nombre,
                    'municipios' => $depto->municipios->map(fn ($muni) => [
                        'codigo' => $muni->codigo,
                        'nombre' => $muni->nombre,
                    ])->values()->all(),
                ])
                ->values()
                ->all();
        });
    }

    public static function limpiarCache(): void
    {
        Cache::forget('guatemala_catalogo_select');
    }
}
