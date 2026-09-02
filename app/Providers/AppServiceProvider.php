<?php

namespace App\Providers;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\View;
use Illuminate\Pagination\Paginator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191);
        Paginator::useBootstrap();

        // Register Blade Components (classes removed — commented to avoid build errors)
        // Blade::component('page-header', PageHeader::class);
        // Blade::component('stats-card', StatsCard::class);

        // Layout y datos de sidebar. No consultar BD en login/errores:
        // si MySQL ya está en max_user_connections, Auth::check() vuelve a fallar y LiteSpeed tira 503.
        View::composer('*', function ($view) {
            $nombreVista = (string) $view->name();
            if ($nombreVista === 'auth.login' || str_starts_with($nombreVista, 'errors')) {
                return;
            }

            try {
                if (! Auth::check()) {
                    return;
                }
            } catch (\Throwable $e) {
                return;
            }

            $user = Auth::user();
            $layout = 'layouts.admin';

            if ($user->role_as == 0) {
                $layout = 'layouts.evaluado';
            } elseif ($user->role_as == 1) {
                $layout = 'layouts.empresa';
            }

            if (! $view->offsetExists('layout')) {
                $view->with('layout', $layout);
            }

            $view->with('currentUser', $user);
            $view->with('userRole', $user->role_as);
            $view->with('userRoleName', $user->getRoleName());

            try {
                $view->with('sedesWhatsApp', Cache::remember(\App\Models\Sede::CACHE_WHATSAPP, 300, static function () {
                    return \App\Models\Sede::activas()
                        ->whereNotNull('whatsapp')
                        ->where('whatsapp', '!=', '')
                        ->orderBy('nombre')
                        ->get();
                }));
            } catch (\Throwable $e) {
                $view->with('sedesWhatsApp', collect());
            }
        });

        // Crear una directiva para mostrar/ocultar elementos según el rol
        Blade::if('role', function($role) {
            return Auth::check() && Auth::user()->role_as == $role;
        });
    }
}
