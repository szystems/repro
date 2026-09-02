<?php

namespace App\Support;

use App\Models\Orden;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

/**
 * Sprint E §3.10 — Propuesta A+B: asignación de reclutador + flag confidencial.
 *
 * Modo compartido (default): trabajadores ven procesos no confidenciales de la empresa
 * más los propios/asignados (incluso si son confidenciales).
 *
 * Modo solo_propios: trabajadores solo ven órdenes creadas por ellos o asignadas a ellos.
 */
class EmpresaVisibilidadReclutadoresSupport
{
    public const MODO_COMPARTIDO = 'compartido';

    public const MODO_SOLO_PROPIOS = 'solo_propios';

    /** @return array<string, string> */
    public static function modosDisponibles(): array
    {
        return [
            self::MODO_COMPARTIDO => 'Compartido — todos ven procesos no confidenciales',
            self::MODO_SOLO_PROPIOS => 'Solo propios — cada reclutador ve únicamente sus procesos',
        ];
    }

    public static function esTrabajadorEmpresa(User $user): bool
    {
        return $user->isEmpresa() && (int) $user->principal !== 1;
    }

    public static function aplicaFiltroTrabajador(Builder $query, User $user): Builder
    {
        if (! self::esTrabajadorEmpresa($user)) {
            return $query;
        }

        $modo = $user->empresa?->modo_visibilidad_reclutadores ?? self::MODO_COMPARTIDO;

        return $query->where(function (Builder $q) use ($user, $modo) {
            $q->where('creado_por', $user->id)
                ->orWhere('reclutador_id', $user->id);

            if ($modo === self::MODO_COMPARTIDO) {
                $q->orWhere('confidencial', false);
            }
        });
    }

    public static function puedeVerOrden(User $user, Orden $orden): bool
    {
        if ($user->role_as >= 2) {
            return true;
        }

        if ((int) $user->empresa_id !== (int) $orden->empresa_id) {
            return false;
        }

        if ((int) $user->principal === 1) {
            return true;
        }

        if ((int) $orden->creado_por === (int) $user->id) {
            return true;
        }

        if ((int) $orden->reclutador_id === (int) $user->id) {
            return true;
        }

        if ($orden->confidencial) {
            return false;
        }

        $modo = $user->empresa?->modo_visibilidad_reclutadores ?? self::MODO_COMPARTIDO;

        return $modo === self::MODO_COMPARTIDO;
    }

    public static function reclutadorIdPorDefecto(User $user): ?int
    {
        if (! $user->isEmpresa() || (int) $user->principal === 1) {
            return null;
        }

        return (int) $user->id;
    }

    public static function filtrarQueryOrdenesEmpresa(Builder $query, User $user): Builder
    {
        if ((int) $user->role_as !== 1 || ! $user->empresa_id) {
            return $query;
        }

        $query->where('empresa_id', $user->empresa_id);

        return self::aplicaFiltroTrabajador($query, $user);
    }

    public static function filtrarQueryEvaluadosEmpresa(Builder $query, User $user): Builder
    {
        if ((int) $user->role_as !== 1 || ! $user->empresa_id) {
            return $query;
        }

        return $query->whereHas('orden', function (Builder $q) use ($user) {
            $q->activas()->where('empresa_id', $user->empresa_id);
            self::aplicaFiltroTrabajador($q, $user);
        });
    }
}
