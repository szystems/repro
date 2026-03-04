<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;

class AdminMiddleware
{
    /**
     * Verifica que el usuario sea administrador (role_as >= 2: repro o admin).
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            return redirect('/login')->with('status', 'Por favor inicie sesión para acceder.');
        }

        if (Auth::user()->role_as >= 2) {
            return $next($request);
        }

        return redirect('/dashboard')->with('status', 'Acceso denegado. No tiene permisos de administrador.');
    }
}
