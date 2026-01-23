<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Mapeo de nombres de rol a role_as
     */
    protected array $roleMapping = [
        'evaluado' => 0,
        'empresa' => 1,
        'repro' => 2,
        'admin' => 3,
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'Debe iniciar sesión para acceder.');
        }

        $user = auth()->user();

        // Si no se especifican roles, solo verificar autenticación
        if (empty($roles)) {
            return $next($request);
        }

        // Verificar primero por el campo role_as (sistema heredado)
        foreach ($roles as $role) {
            if (isset($this->roleMapping[$role]) && $user->role_as == $this->roleMapping[$role]) {
                return $next($request);
            }
        }

        // Verificar si el usuario tiene alguno de los roles especificados (nuevo sistema)
        if ($user->hasAnyRole($roles)) {
            return $next($request);
        }

        // Usuario no autorizado
        abort(403, 'No tiene permisos para acceder a esta sección.');
    }
}

