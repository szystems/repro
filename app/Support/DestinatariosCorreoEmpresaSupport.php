<?php

namespace App\Support;

use App\Models\Orden;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Quién recibe correo/campana de resultados hacia la empresa.
 *
 * Reclutador asignado y proceso confidencial son independientes:
 * el reclutador define el responsable del seguimiento y del correo;
 * confidencial solo recorta quién puede ver la orden en SIGOR.
 *
 * Matriz (Stephany, sep-2026):
 * 1. Hay reclutador activo de esa empresa → solo él (aunque no sea confidencial).
 * 2. Sin reclutador y la creó la empresa → solo el usuario empresa que la creó.
 * 3. La creó REPRO (o no hay responsable empresa) → titulares (principal=1).
 * 4. Nunca se avisa a quien no puede ver la orden.
 * Si no queda usuario con email, el correo usa empresas.email.
 */
class DestinatariosCorreoEmpresaSupport
{
    /**
     * Usuarios empresa para correo y campana de resultados / informe preliminar.
     *
     * @return Collection<int, User>
     */
    public static function usuariosResultados(Orden $orden): Collection
    {
        $orden->loadMissing(['empresa', 'reclutador', 'creador']);

        $candidatos = collect();

        $reclutador = self::usuarioEmpresaActivo($orden, $orden->reclutador_id);
        if ($reclutador) {
            $candidatos = collect([$reclutador]);
        } else {
            $creador = self::creadaPorEmpresa($orden)
                ? self::usuarioEmpresaActivo($orden, $orden->creado_por)
                : null;

            $candidatos = $creador
                ? collect([$creador])
                : self::titularesEmpresa($orden);
        }

        return $candidatos
            ->filter(fn (User $usuario) => self::puedeRecibir($usuario, $orden))
            ->unique('id')
            ->values();
    }

    /**
     * Destinos SMTP de ResultadosDisponiblesMail.
     *
     * @return Collection<int, string>
     */
    public static function emailsResultados(Orden $orden): Collection
    {
        $emails = self::usuariosResultados($orden)
            ->pluck('email')
            ->map(fn ($email) => strtolower(trim((string) $email)))
            ->filter()
            ->unique()
            ->values();

        if ($emails->isNotEmpty()) {
            return $emails;
        }

        $empresaEmail = strtolower(trim((string) ($orden->empresa?->email ?? '')));

        return $empresaEmail !== '' ? collect([$empresaEmail]) : collect();
    }

    /**
     * Campana de eventos que no son resultados (orden creada, evaluado, cuestionario).
     * Todos los usuarios empresa que sí pueden ver la orden.
     *
     * @return Collection<int, User>
     */
    public static function usuariosVisiblesEmpresa(Orden $orden): Collection
    {
        if (! $orden->empresa_id) {
            return collect();
        }

        return User::query()
            ->where('empresa_id', $orden->empresa_id)
            ->where('role_as', 1)
            ->where('estado', 1)
            ->orderBy('id')
            ->get()
            ->filter(fn (User $usuario) => EmpresaVisibilidadReclutadoresSupport::puedeVerOrden($usuario, $orden))
            ->values();
    }

    public static function creadaPorEmpresa(Orden $orden): bool
    {
        return ($orden->tipo_creador ?? '') === 'empresa';
    }

    private static function puedeRecibir(User $usuario, Orden $orden): bool
    {
        if (trim((string) $usuario->email) === '') {
            return false;
        }

        return EmpresaVisibilidadReclutadoresSupport::puedeVerOrden($usuario, $orden);
    }

    private static function usuarioEmpresaActivo(Orden $orden, mixed $userId): ?User
    {
        $id = (int) $userId;
        if ($id <= 0 || ! $orden->empresa_id) {
            return null;
        }

        return User::query()
            ->where('id', $id)
            ->where('empresa_id', $orden->empresa_id)
            ->where('role_as', 1)
            ->where('estado', 1)
            ->first();
    }

    /**
     * @return Collection<int, User>
     */
    private static function titularesEmpresa(Orden $orden): Collection
    {
        if (! $orden->empresa_id) {
            return collect();
        }

        return User::query()
            ->where('empresa_id', $orden->empresa_id)
            ->where('role_as', 1)
            ->where('principal', 1)
            ->where('estado', 1)
            ->orderBy('id')
            ->get();
    }
}
