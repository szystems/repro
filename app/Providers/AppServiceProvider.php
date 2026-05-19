<?php

namespace App\Providers;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
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

        // Aplicar layout según el rol del usuario
        View::composer('*', function ($view) {
            if (Auth::check()) {
                $user = Auth::user();
                $layout = 'layouts.admin'; // Valor por defecto

                // Determinar layout según el rol
                if ($user->role_as == 0) {
                    $layout = 'layouts.evaluado';
                } elseif ($user->role_as == 1) {
                    $layout = 'layouts.empresa';
                }

                // Si la vista tiene un layout específico definido, úsalo
                if (!$view->offsetExists('layout')) {
                    $view->with('layout', $layout);
                }

                // Compartir algunas variables globales útiles para todos los layouts
                $view->with('currentUser', $user);
                $view->with('userRole', $user->role_as);
                $view->with('userRoleName', $user->getRoleName());

                // Sedes con WhatsApp para el dropdown de contacto en sidebars
                try {
                    $view->with('sedesWhatsApp', \App\Models\Sede::activas()->whereNotNull('whatsapp')->where('whatsapp', '!=', '')->orderBy('nombre')->get());
                } catch (\Exception $e) {
                    $view->with('sedesWhatsApp', collect());
                }
            }
        });

        // Crear una directiva para mostrar/ocultar elementos según el rol
        Blade::if('role', function($role) {
            return Auth::check() && Auth::user()->role_as == $role;
        });
    }
}
