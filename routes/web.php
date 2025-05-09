<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

//admin
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\UsersController;
use App\Http\Controllers\Admin\ConfigController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Página de inicio pública
Route::get('/', function () {
    return redirect('/login');
});

// Rutas de autenticación se manejan automáticamente por Laravel

// Rutas protegidas
Route::middleware(['auth', 'redirect.role'])->group(function () {
    Route::get('/dashboard', [AdminController::class, 'index'])->name('dashboard');

    //Admin Users
    Route::get('users', [UsersController::class, 'users']);
    Route::get('show-user/{id}', [UsersController::class, 'showuser']);
    Route::get('add-user', [UsersController::class, 'adduser']);
    Route::post('insert-user', [UsersController::class, 'insertuser']);
    Route::get('edit-user/{id}',[UsersController::class,'edituser']);
    Route::put('update-user/{id}', [UsersController::class, 'updateuser']);
    Route::get('delete-user/{id}', [UsersController::class, 'destroyuser']);
    Route::get('pdf-users', [UsersController::class, 'pdf']);
    Route::get('pdf-user/{id}', [UsersController::class, 'pdfuser']);

    //config
    Route::get('config', [ConfigController::class, 'index']);
    Route::put('update-config', [ConfigController::class, 'update']);

    // Rutas para el módulo de Empresas
    Route::get('empresas', [App\Http\Controllers\Admin\EmpresasController::class, 'index']);
    Route::get('add-empresa', [App\Http\Controllers\Admin\EmpresasController::class, 'create']);
    Route::post('insert-empresa', [App\Http\Controllers\Admin\EmpresasController::class, 'store']);
    Route::get('edit-empresa/{id}', [App\Http\Controllers\Admin\EmpresasController::class, 'edit']);
    Route::put('update-empresa/{id}', [App\Http\Controllers\Admin\EmpresasController::class, 'update']);
    Route::get('show-empresa/{id}', [App\Http\Controllers\Admin\EmpresasController::class, 'show']);
    Route::get('cambiar-estado-empresa/{id}/{estado}', [App\Http\Controllers\Admin\EmpresasController::class, 'cambiarEstado']);
    Route::get('pdf-empresas', [App\Http\Controllers\Admin\EmpresasController::class, 'pdf']);
    Route::get('pdf-empresa/{id}', [App\Http\Controllers\Admin\EmpresasController::class, 'pdfEmpresa']);
});

// Ruta de cambio de contraseña disponible para todos los usuarios autenticados
Route::post('change-password', [UsersController::class, 'changePassword'])
    ->middleware(['auth']);
