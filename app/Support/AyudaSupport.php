<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Centro de ayuda — carga artículos desde config/ayuda/manifest.php
 * y filtra por audiencia (repro / empresa), permisos y rol principal.
 */
class AyudaSupport
{
    /** @var array<string, mixed>|null */
    private static ?array $manifest = null;

    /** @return array<string, mixed> */
    public static function manifest(): array
    {
        if (self::$manifest === null) {
            self::$manifest = config('ayuda', []);
        }

        return self::$manifest;
    }

    public static function audiencia(User $user): string
    {
        return $user->role_as >= 2 ? 'repro' : 'empresa';
    }

    public static function usuarioPuedeVer(User $user, array $articulo): bool
    {
        $audiencias = $articulo['audiencias'] ?? [];
        if (! in_array(self::audiencia($user), $audiencias, true)) {
            return false;
        }

        if (! empty($articulo['solo_principal']) && ! $user->principal) {
            return false;
        }

        if (! empty($articulo['solo_admin']) && $user->role_as < 3) {
            return false;
        }

        // Admin REPRO (role_as >= 3): acceso a todos los artículos de su audiencia.
        if ($user->role_as >= 3) {
            return true;
        }

        $permisos = $articulo['permisos'] ?? [];
        if ($permisos !== []) {
            foreach ($permisos as $permiso) {
                if (! $user->hasPermission($permiso)) {
                    return false;
                }
            }
        }

        return true;
    }

    /** @return Collection<int, array<string, mixed>> */
    public static function articulosParaUsuario(User $user): Collection
    {
        $articulos = collect(self::manifest()['articulos'] ?? [])
            ->filter(fn (array $a): bool => self::usuarioPuedeVer($user, $a))
            ->sortBy([
                ['orden', 'asc'],
                ['titulo', 'asc'],
            ])
            ->values();

        return $articulos;
    }

    /** @return array<string, mixed>|null */
    public static function articuloPorSlug(User $user, string $slug): ?array
    {
        $articulo = collect(self::manifest()['articulos'] ?? [])
            ->first(fn (array $a): bool => ($a['slug'] ?? '') === $slug);

        if ($articulo === null || ! self::usuarioPuedeVer($user, $articulo)) {
            return null;
        }

        return $articulo;
    }

    /** @return array<string, Collection<int, array<string, mixed>>> */
    public static function articulosPorCategoria(User $user): array
    {
        $categorias = self::manifest()['categorias'] ?? [];
        $agrupados = [];

        foreach ($categorias as $clave => $meta) {
            $agrupados[$clave] = self::articulosParaUsuario($user)
                ->filter(fn (array $a): bool => ($a['categoria'] ?? '') === $clave);
        }

        return $agrupados;
    }

    /** @return Collection<int, array<string, mixed>> */
    public static function buscar(User $user, string $termino): Collection
    {
        $termino = trim(Str::lower($termino));
        if ($termino === '') {
            return collect();
        }

        return self::articulosParaUsuario($user)->filter(function (array $articulo) use ($termino): bool {
            $texto = Str::lower(implode(' ', [
                $articulo['titulo'] ?? '',
                $articulo['resumen'] ?? '',
                $articulo['slug'] ?? '',
            ]));

            return Str::contains($texto, $termino);
        })->values();
    }

    /** @return array<int, array<string, mixed>> */
    public static function destacadosDashboard(User $user): array
    {
        return self::articulosParaUsuario($user)
            ->filter(fn (array $a): bool => ! empty($a['destacado']))
            ->take(3)
            ->values()
            ->all();
    }

    /** @return array<string, mixed>|null */
    public static function articuloContextual(User $user, string $path): ?array
    {
        $path = trim($path, '/');

        return self::articulosParaUsuario($user)
            ->filter(function (array $articulo) use ($path): bool {
                $patrones = $articulo['contexto'] ?? [];
                if ($patrones === []) {
                    return false;
                }

                foreach ($patrones as $patron) {
                    if (self::pathCoincideConPatron($path, trim($patron, '/'))) {
                        return true;
                    }
                }

                return false;
            })
            ->sortBy('orden')
            ->first();
    }

    private static function pathCoincideConPatron(string $path, string $patron): bool
    {
        if ($patron === $path) {
            return true;
        }

        $regex = '#^'.str_replace('\*', '[^/]+', preg_quote($patron, '#')).'$#';

        return (bool) preg_match($regex, $path);
    }

    /** @return array<int, array<string, string>> */
    public static function faqParaUsuario(User $user): array
    {
        $audiencia = self::audiencia($user);

        return collect(self::manifest()['faq'] ?? [])
            ->filter(function (array $item) use ($user, $audiencia): bool {
                $audiencias = $item['audiencias'] ?? ['repro', 'empresa'];
                if (! in_array($audiencia, $audiencias, true)) {
                    return false;
                }
                if (! empty($item['solo_principal']) && ! $user->principal) {
                    return false;
                }
                if (! empty($item['solo_admin']) && $user->role_as < 3) {
                    return false;
                }
                if ($user->role_as >= 3) {
                    return true;
                }
                if (! empty($item['permisos'])) {
                    foreach ($item['permisos'] as $permiso) {
                        if (! $user->hasPermission($permiso)) {
                            return false;
                        }
                    }
                }

                return true;
            })
            ->values()
            ->all();
    }

    /** @return array<int, array<string, string>> */
    public static function glosario(): array
    {
        return self::manifest()['glosario'] ?? [];
    }

    /** @return array<int, array<string, mixed>> */
    public static function relacionados(User $user, array $articulo): array
    {
        $slugs = $articulo['relacionados'] ?? [];

        return collect($slugs)
            ->map(fn (string $slug): ?array => self::articuloPorSlug($user, $slug))
            ->filter()
            ->values()
            ->all();
    }

    public static function categoriaLabel(string $clave): string
    {
        return self::manifest()['categorias'][$clave]['titulo'] ?? ucfirst($clave);
    }

    public static function categoriaIcono(string $clave): string
    {
        return self::manifest()['categorias'][$clave]['icono'] ?? 'bi-book';
    }

    /** @return array<string, Collection<int, array<string, mixed>>> */
    public static function articulosPorModulo(User $user): array
    {
        $modulos = self::manifest()['modulos'] ?? [];
        $agrupados = [];

        foreach ($modulos as $clave => $meta) {
            $agrupados[$clave] = self::articulosParaUsuario($user)
                ->filter(fn (array $a): bool => ($a['modulo'] ?? '') === $clave);
        }

        return $agrupados;
    }

    public static function moduloLabel(string $clave): string
    {
        return self::manifest()['modulos'][$clave]['titulo'] ?? ucfirst($clave);
    }

    public static function moduloIcono(string $clave): string
    {
        return self::manifest()['modulos'][$clave]['icono'] ?? 'bi-folder';
    }

    /** @return array<int, string> */
    public static function audienciaChips(array $articulo): array
    {
        $audiencias = $articulo['audiencias'] ?? [];
        if (count($audiencias) >= 2) {
            return ['ambos'];
        }

        return $audiencias;
    }

    public static function tiempoLectura(array $articulo): int
    {
        if (! empty($articulo['tiempo_lectura'])) {
            return (int) $articulo['tiempo_lectura'];
        }

        $secciones = count($articulo['secciones'] ?? []);
        $botones = count($articulo['botones'] ?? []);

        return max(3, min(12, 3 + $secciones + (int) ($botones > 0)));
    }

    /** @return array<int, array<string, mixed>> */
    public static function faqConEnlaces(User $user): array
    {
        return collect(self::faqParaUsuario($user))
            ->map(function (array $item) use ($user): array {
                if (! empty($item['articulo'])) {
                    $art = self::articuloPorSlug($user, $item['articulo']);
                    $item['articulo_data'] = $art;
                }

                return $item;
            })
            ->values()
            ->all();
    }

    /** @return array<int, array<string, string>> */
    public static function glosarioEnriquecido(): array
    {
        $iconos = [
            'Orden de evaluación' => 'bi-file-text',
            'Evaluado' => 'bi-person',
            'Estado de evaluación' => 'bi-flag',
            'Estado de formulario' => 'bi-ui-checks',
            'Estado de programación' => 'bi-calendar-event',
            'Enlace del candidato' => 'bi-link-45deg',
            'Usuario principal' => 'bi-person-badge',
            'Trabajador / Reclutador' => 'bi-person-lock',
            'Informe Word' => 'bi-file-earmark-word',
            'Resultado preliminar / final' => 'bi-file-earmark-pdf',
            'Papelería' => 'bi-cloud-upload',
            'Sede REPRO' => 'bi-geo-alt',
            'Archivar orden' => 'bi-archive',
            'Eliminar usuario' => 'bi-person-x',
            'Permisos individuales' => 'bi-toggles',
        ];

        return collect(self::glosario())
            ->map(function (array $term) use ($iconos): array {
                $term['icono'] = $iconos[$term['termino']] ?? 'bi-bookmark';

                return $term;
            })
            ->values()
            ->all();
    }
}
