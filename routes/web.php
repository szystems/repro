<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

//admin
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\UsersController;
use App\Http\Controllers\Admin\ConfigController;
use App\Http\Controllers\Admin\OrdenesController;
use App\Http\Controllers\Admin\RolesController;

//cuestionarios
use App\Http\Controllers\CuestionarioController;

//empresa
use App\Http\Controllers\Empresa\EmpresaController;

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

// Registrar rutas de autenticación predeterminadas
Auth::routes();

// Ruta para la página de inicio de sesión
Route::get('/login', function () {
    return view('auth.login');
})->name('login');

// Ruta para procesar el inicio de sesión
Route::post('/login', [\App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'store'])->name('login.store');

// Rutas de autenticación se manejan automáticamente por Laravel

// Ruta para la solicitud de restablecimiento de contraseña
Route::get('/password/reset', function () {
    return view('auth.passwords.email');
})->name('password.request');

// Ruta para enviar el correo de restablecimiento de contraseña
Route::post('/password/email', [\App\Http\Controllers\Auth\ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');

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

    //Roles y Permisos (solo para administradores)
    Route::prefix('admin')->group(function () {
        Route::get('roles/permissions', [RolesController::class, 'permissions'])->name('roles.permissions');
        Route::resource('roles', RolesController::class);
    });

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

    // Módulo de Sedes - Solo REPRO (role_as >= 3)
    Route::get('sedes', [App\Http\Controllers\Admin\SedesController::class, 'index'])->name('sedes.index');
    Route::get('add-sede', [App\Http\Controllers\Admin\SedesController::class, 'create'])->name('sedes.create');
    Route::post('insert-sede', [App\Http\Controllers\Admin\SedesController::class, 'store'])->name('sedes.store');
    Route::get('show-sede/{id}', [App\Http\Controllers\Admin\SedesController::class, 'show'])->name('sedes.show');
    Route::get('edit-sede/{id}', [App\Http\Controllers\Admin\SedesController::class, 'edit'])->name('sedes.edit');
    Route::put('update-sede/{id}', [App\Http\Controllers\Admin\SedesController::class, 'update'])->name('sedes.update');
    Route::get('cambiar-estado-sede/{id}/{estado}', [App\Http\Controllers\Admin\SedesController::class, 'cambiarEstado'])->name('sedes.cambiar-estado');
    Route::delete('delete-sede/{id}', [App\Http\Controllers\Admin\SedesController::class, 'destroy'])->name('sedes.destroy');

    // Módulo Calendario de Programación - REPRO (role_as >= 2)
    Route::get('calendario', [App\Http\Controllers\Admin\CalendarioController::class, 'index'])->name('calendario.index');
    Route::get('calendario/dia/{fecha}', [App\Http\Controllers\Admin\CalendarioController::class, 'dia'])->name('calendario.dia');
    Route::post('calendario/programar', [App\Http\Controllers\Admin\CalendarioController::class, 'programar'])->name('calendario.programar');
    Route::patch('calendario/evaluados/{evaluado}/reprogramar', [App\Http\Controllers\Admin\CalendarioController::class, 'reprogramar'])->name('calendario.reprogramar');
    Route::delete('calendario/evaluados/{evaluado}/cancelar', [App\Http\Controllers\Admin\CalendarioController::class, 'cancelar'])->name('calendario.cancelar');

    // Rutas para el módulo de Órdenes - Disponible para admin, repro y empresas
    Route::resource('ordenes', OrdenesController::class)->parameters(['ordenes' => 'orden']);

    // Rutas adicionales para órdenes
    Route::patch('ordenes/{orden}/cambiar-estado', [OrdenesController::class, 'cambiarEstado'])->name('ordenes.cambiar-estado');
    Route::patch('ordenes/{orden}/toggle-resultados-visibles', [OrdenesController::class, 'toggleResultadosVisibles'])->name('ordenes.toggle-resultados-visibles');
    Route::get('ordenes/{orden}/pdf', [OrdenesController::class, 'pdf'])->name('ordenes.pdf');

    // Reenviar correo a evaluado
    Route::post('evaluados/{evaluado}/reenviar-correo', [OrdenesController::class, 'reenviarCorreo'])->name('evaluados.reenviar-correo');

    // Documentos de evaluados
    Route::post('documentos-evaluado', [\App\Http\Controllers\Admin\DocumentosEvaluadoController::class, 'store'])->name('documentos-evaluado.store');
    Route::get('documentos-evaluado/{documento}/download', [\App\Http\Controllers\Admin\DocumentosEvaluadoController::class, 'download'])->name('documentos-evaluado.download');
    Route::patch('documentos-evaluado/{documento}/verificar', [\App\Http\Controllers\Admin\DocumentosEvaluadoController::class, 'verificar'])->name('documentos-evaluado.verificar');
    Route::delete('documentos-evaluado/{documento}', [\App\Http\Controllers\Admin\DocumentosEvaluadoController::class, 'destroy'])->name('documentos-evaluado.destroy');

    // Archivos de resultado (preliminar / final) — solo REPRO/admin
    Route::post('evaluados/{evaluado}/resultado-archivo', [OrdenesController::class, 'subirResultadoArchivo'])->name('evaluados.subir-resultado-archivo');
    Route::get('evaluados/{evaluado}/resultado-archivo/{tipo}', [OrdenesController::class, 'descargarResultadoArchivo'])->name('evaluados.descargar-resultado-archivo');
    Route::delete('evaluados/{evaluado}/resultado-archivo/{tipo}', [OrdenesController::class, 'eliminarResultadoArchivo'])->name('evaluados.eliminar-resultado-archivo');

    // Rehabilitación de cuestionario — solo REPRO/admin
    Route::post('evaluados/{evaluado}/rehabilitar-cuestionario', [OrdenesController::class, 'rehabilitarCuestionario'])->name('evaluados.rehabilitar-cuestionario');
    Route::post('evaluados/{evaluado}/deshabilitar-cuestionario', [OrdenesController::class, 'deshabilitarCuestionario'])->name('evaluados.deshabilitar-cuestionario');

    // Rutas para diferentes tipos de usuario con middleware específico
    Route::middleware(['role:admin,repro'])->group(function () {
        // Solo admin y repro pueden acceder a todas las órdenes y estadísticas
        Route::get('ordenes-resumen', [OrdenesController::class, 'resumen'])->name('ordenes.resumen');
    });

    // ========================================
    // MÓDULO DE REPORTES
    // ========================================
    Route::prefix('reportes')->name('reportes.')->group(function () {
        // Reporte de Evaluaciones - Disponible para todos los usuarios autenticados
        Route::get('evaluaciones', [App\Http\Controllers\Admin\ReportesController::class, 'evaluaciones'])->name('evaluaciones');
        Route::get('evaluaciones/pdf', [App\Http\Controllers\Admin\ReportesController::class, 'evaluacionesPdf'])->name('evaluaciones.pdf');
        Route::get('evaluaciones/excel', [App\Http\Controllers\Admin\ReportesController::class, 'evaluacionesExcel'])->name('evaluaciones.excel');

        // Reporte de Empresas - Solo para admin y repro
        Route::middleware(['role:admin,repro'])->group(function () {
            Route::get('empresas', [App\Http\Controllers\Admin\ReportesController::class, 'empresas'])->name('empresas');
            Route::get('empresas/pdf', [App\Http\Controllers\Admin\ReportesController::class, 'empresasPdf'])->name('empresas.pdf');
            Route::get('empresas/excel', [App\Http\Controllers\Admin\ReportesController::class, 'empresasExcel'])->name('empresas.excel');
        });
    });

    // ========================================
    // ADMINISTRACIÓN DE CUESTIONARIOS (ADMIN Y REPRO)
    // ========================================
    Route::middleware(['role:admin,repro'])->prefix('cuestionarios')->name('admin.cuestionarios.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\CuestionariosController::class, 'index'])->name('index');
        Route::get('/{cuestionario}', [App\Http\Controllers\Admin\CuestionariosController::class, 'show'])->name('show');
        Route::get('/{cuestionario}/editar', [App\Http\Controllers\Admin\CuestionariosController::class, 'edit'])->name('edit');
        Route::put('/{cuestionario}', [App\Http\Controllers\Admin\CuestionariosController::class, 'update'])->name('update');
        Route::get('/{cuestionario}/pdf', [App\Http\Controllers\Admin\CuestionariosController::class, 'generarPDF'])->name('pdf');
        Route::post('/{cuestionario}/completar', [App\Http\Controllers\Admin\CuestionariosController::class, 'marcarCompleto'])->name('completar');
    });

    // ========================================
    // MÓDULO EMPRESA (USUARIOS TIPO EMPRESA)
    // ========================================
    Route::middleware(['role:empresa'])->prefix('empresa')->name('empresa.')->group(function () {
            // Listado de órdenes para empresa
            Route::get('ordenes', [EmpresaController::class, 'indexOrdenesEmpresa'])->name('ordenes.index');
        // Mi Empresa
        Route::get('mi-empresa', [EmpresaController::class, 'miEmpresa'])->name('mi-empresa');
        Route::get('mi-empresa/editar', [EmpresaController::class, 'editarEmpresa'])->name('mi-empresa.edit');
        Route::put('mi-empresa', [EmpresaController::class, 'actualizarEmpresa'])->name('mi-empresa.update');

        // Usuarios de empresa (solo usuario principal)
        Route::get('usuarios', [EmpresaController::class, 'usuarios'])->name('usuarios');
        Route::get('usuarios/crear', [EmpresaController::class, 'crearUsuario'])->name('usuarios.create');
        Route::post('usuarios', [EmpresaController::class, 'guardarUsuario'])->name('usuarios.store');
        Route::get('usuarios/{usuario}/editar', [EmpresaController::class, 'editarUsuario'])->name('usuarios.edit');
        Route::put('usuarios/{usuario}', [EmpresaController::class, 'actualizarUsuario'])->name('usuarios.update');
        Route::delete('usuarios/{usuario}', [EmpresaController::class, 'eliminarUsuario'])->name('usuarios.destroy');

        // Órdenes (solo lectura para empresa)
        Route::get('ordenes/{orden}', [EmpresaController::class, 'verOrden'])->name('ordenes.show');
        // Cuestionarios (solo lectura)
            Route::get('cuestionarios', [EmpresaController::class, 'cuestionarios'])->name('cuestionarios');
            Route::get('cuestionarios/{evaluado}', [EmpresaController::class, 'verCuestionario'])->name('cuestionarios.show');
            Route::get('cuestionarios/{evaluado}/pdf', [EmpresaController::class, 'generarPDFCuestionarioEmpresa'])->name('cuestionarios.pdf');
    });
});

// Ruta de cambio de contraseña disponible para todos los usuarios autenticados
Route::post('change-password', [UsersController::class, 'changePassword'])
    ->middleware(['auth']);

// Ruta para el cierre de sesión
Route::post('/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect('/');
})->name('logout');

// ========================================
// RUTAS PÚBLICAS DE CUESTIONARIOS (SIN AUTENTICACIÓN)
// ========================================

// Ruta de prueba para verificar cuestionarios
Route::get('/test-cuestionario/{token}', function($token) {
    $evaluado = \App\Models\EvaluadoOrden::where('token_unico', $token)->first();
    if ($evaluado) {
        return response()->json([
            'status' => 'success',
            'token' => $token,
            'evaluado' => $evaluado->nombre . ' ' . $evaluado->apellidos,
            'dpi' => $evaluado->dpi,
            'expira' => $evaluado->token_expira_at,
            'completado' => $evaluado->cuestionario_completado
        ]);
    } else {
        return response()->json(['status' => 'error', 'message' => 'Token no encontrado']);
    }
});

Route::prefix('cuestionario')->name('cuestionario.')->group(function () {
    // Acceso inicial con token
    Route::get('/{token}', [CuestionarioController::class, 'mostrar'])->name('mostrar');

    // Verificación de identidad
    Route::post('/{token}/verificar', [CuestionarioController::class, 'verificarIdentidad'])->name('verificar');

    // Términos y condiciones (autorización)
    Route::get('/{token}/terminos', [CuestionarioController::class, 'terminos'])->name('terminos');
    Route::post('/{token}/aceptar-terminos', [CuestionarioController::class, 'aceptarTerminos'])->name('aceptar-terminos');

    // Navegación por secciones
    Route::get('/{token}/seccion/{numero}', [CuestionarioController::class, 'seccion'])
        ->name('seccion')
        ->where('numero', '[0-9]+');

    Route::post('/{token}/seccion/{numero}', [CuestionarioController::class, 'guardarSeccion'])
        ->name('guardar-seccion')
        ->where('numero', '[0-9]+');

    // Finalización y firma
    Route::get('/{token}/finalizar', [CuestionarioController::class, 'finalizar'])->name('finalizar');
    Route::post('/{token}/completar', [CuestionarioController::class, 'completar'])->name('completar');

    // Subida de documentos por evaluado
    Route::post('/{token}/subir-documento', [CuestionarioController::class, 'subirDocumento'])->name('subir-documento');

    // Página de completado
    Route::get('/{token}/completado', [CuestionarioController::class, 'completado'])->name('completado');
});
