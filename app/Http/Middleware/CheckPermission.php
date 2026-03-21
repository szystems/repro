<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$permissions
     */
    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'Debe iniciar sesión para acceder.');
        }

        $user = auth()->user();

        // Admin siempre tiene todos los permisos
        if ($user->role_as >= 3) {
            return $next($request);
        }

        // Si no se especifican permisos, solo verificar autenticación
        if (empty($permissions)) {
            return $next($request);
        }

        // Verificar si el usuario tiene alguno de los permisos especificados
        if ($user->hasAnyPermission($permissions)) {
            return $next($request);
        }

        // Usuario no autorizado
        abort(403, 'No tiene los permisos necesarios para realizar esta acción.');
    }
}

