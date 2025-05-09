<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedirectBasedOnRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $user = Auth::user();

            // Asignar el layout según el rol del usuario
            if ($user->role_as == 0) {
                // Usuario evaluado
                session(['layout' => 'layouts.evaluado']);
            } elseif ($user->role_as == 1) {
                // Usuario empresa
                session(['layout' => 'layouts.empresa']);
            } else {
                // Usuarios admin y repro
                session(['layout' => 'layouts.admin']);
            }
        }

        return $next($request);
    }
}
