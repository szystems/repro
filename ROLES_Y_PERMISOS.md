# Sistema de Roles y Permisos - Repro

## 📋 Estructura Implementada

### Roles Disponibles
| ID | Nombre | Display Name | Descripción |
|----|--------|--------------|-------------|
| 1 | admin | Administrador | Acceso total al sistema |
| 2 | repro | Personal Repro | Personal interno que realiza las pruebas |
| 3 | empresa | Usuario Empresa | Usuario de empresa cliente |
| 4 | evaluado | Persona Evaluada | Persona que completa cuestionario |

### Permisos por Módulo (25 permisos totales)

**Módulo: Órdenes** (4 permisos)
- `ordenes.ver` - Ver órdenes
- `ordenes.crear` - Crear órdenes
- `ordenes.editar` - Editar órdenes
- `ordenes.eliminar` - Eliminar órdenes

**Módulo: Evaluaciones** (4 permisos)
- `evaluaciones.ver` - Ver evaluaciones
- `evaluaciones.crear` - Crear evaluaciones
- `evaluaciones.editar` - Editar evaluaciones
- `evaluaciones.realizar` - Realizar pruebas

**Módulo: Resultados** (3 permisos)
- `resultados.ver` - Ver resultados
- `resultados.descargar` - Descargar resultados
- `resultados.editar` - Editar resultados

**Módulo: Cuestionarios** (2 permisos)
- `cuestionarios.ver` - Ver cuestionarios
- `cuestionarios.completar` - Completar cuestionario

**Módulo: Empresas** (4 permisos)
- `empresas.ver` - Ver empresas
- `empresas.crear` - Crear empresas
- `empresas.editar` - Editar empresas
- `empresas.eliminar` - Eliminar empresas

**Módulo: Usuarios** (4 permisos)
- `usuarios.ver` - Ver usuarios
- `usuarios.crear` - Crear usuarios
- `usuarios.editar` - Editar usuarios
- `usuarios.eliminar` - Eliminar usuarios

**Módulo: Reportes** (2 permisos)
- `reportes.ver` - Ver reportes
- `reportes.generar` - Generar reportes

**Módulo: Configuración** (2 permisos)
- `config.ver` - Ver configuración
- `config.editar` - Editar configuración

---

## 🎯 Permisos Asignados por Rol

### 👑 Administrador
✅ **TODOS** los permisos (25/25)

### 👨‍💼 Personal Repro
✅ `ordenes.ver`
✅ `evaluaciones.*` (todos los de evaluaciones)
✅ `resultados.*` (todos los de resultados)
✅ `cuestionarios.ver`
✅ `empresas.ver`
✅ `usuarios.ver`
✅ `reportes.*` (todos los de reportes)

### 🏢 Usuario Empresa
✅ `ordenes.ver`, `ordenes.crear`
✅ `evaluaciones.ver`
✅ `resultados.ver`, `resultados.descargar`
✅ `usuarios.ver`, `usuarios.crear`, `usuarios.editar`

### 👤 Persona Evaluada
✅ `cuestionarios.completar`

---

## 💻 Uso en Código

### 1. Verificar Roles en Controladores

```php
// Verificar un rol específico
if (auth()->user()->hasRole('admin')) {
    // Usuario es administrador
}

// Verificar múltiples roles (cualquiera)
if (auth()->user()->hasAnyRole(['admin', 'repro'])) {
    // Usuario es admin O repro
}

// Verificar múltiples roles (todos)
if (auth()->user()->hasAllRoles(['admin', 'repro'])) {
    // Usuario tiene AMBOS roles
}
```

### 2. Verificar Permisos en Controladores

```php
// Verificar un permiso específico
if (auth()->user()->hasPermission('ordenes.crear')) {
    // Usuario puede crear órdenes
}

// Verificar múltiples permisos (cualquiera)
if (auth()->user()->hasAnyPermission(['ordenes.ver', 'ordenes.crear'])) {
    // Usuario tiene al menos uno de estos permisos
}

// Verificar múltiples permisos (todos)
if (auth()->user()->hasAllPermissions(['ordenes.ver', 'ordenes.crear'])) {
    // Usuario tiene TODOS estos permisos
}

// Método simplificado
if (auth()->user()->can('crear', 'ordenes')) {
    // Verifica el permiso "ordenes.crear"
}
```

### 3. Usar Middlewares en Rutas

```php
// Proteger ruta por rol
Route::get('/admin/dashboard', [AdminController::class, 'index'])
    ->middleware('role:admin');

// Proteger por múltiples roles
Route::get('/evaluaciones', [EvaluacionController::class, 'index'])
    ->middleware('role:admin,repro');

// Proteger por permiso
Route::post('/ordenes', [OrdenController::class, 'store'])
    ->middleware('permission:ordenes.crear');

// Proteger por múltiples permisos
Route::get('/reportes', [ReporteController::class, 'index'])
    ->middleware('permission:reportes.ver,reportes.generar');
```

### 4. Verificar en Blade

```blade
{{-- Verificar rol --}}
@if(auth()->user()->hasRole('admin'))
    <button>Panel Admin</button>
@endif

{{-- Verificar permiso --}}
@if(auth()->user()->hasPermission('ordenes.crear'))
    <a href="{{ route('ordenes.create') }}">Nueva Orden</a>
@endif

{{-- Mostrar para múltiples roles --}}
@if(auth()->user()->hasAnyRole(['admin', 'repro']))
    <div>Área de trabajo</div>
@endif
```

### 5. Asignar Roles a Usuarios

```php
// Asignar un rol
$user->assignRole('empresa');

// Asignar múltiples roles
$user->assignRoles(['empresa', 'evaluado']);

// Remover rol
$user->removeRole('evaluado');

// Sincronizar roles (reemplaza todos)
$user->syncRoles(['admin']);
```

### 6. Gestionar Permisos de Roles

```php
// Dar permiso a un rol
$role = Role::where('name', 'empresa')->first();
$permission = Permission::where('name', 'reportes.ver')->first();
$role->givePermission($permission);

// Verificar si rol tiene permiso
if ($role->hasPermission('ordenes.crear')) {
    // El rol tiene este permiso
}

// Revocar permiso
$role->revokePermission($permission);
```

---

## 🔒 Ejemplo Completo: Controlador de Órdenes

```php
<?php

namespace App\Http\Controllers;

use App\Models\Orden;
use Illuminate\Http\Request;

class OrdenController extends Controller
{
    /**
     * Constructor con middleware
     */
    public function __construct()
    {
        // Solo usuarios con rol empresa o admin pueden acceder
        $this->middleware('role:empresa,admin');
        
        // Para crear órdenes, necesita el permiso específico
        $this->middleware('permission:ordenes.crear')->only(['create', 'store']);
        
        // Para editar, necesita el permiso específico
        $this->middleware('permission:ordenes.editar')->only(['edit', 'update']);
    }

    public function index()
    {
        $user = auth()->user();
        
        // Admin y Repro ven todas las órdenes
        if ($user->hasAnyRole(['admin', 'repro'])) {
            $ordenes = Orden::all();
        }
        // Empresa solo ve sus órdenes
        elseif ($user->hasRole('empresa')) {
            $ordenes = Orden::where('empresa_id', $user->empresa_id)->get();
        }
        
        return view('ordenes.index', compact('ordenes'));
    }

    public function store(Request $request)
    {
        // Verificar permiso adicional
        if (!auth()->user()->hasPermission('ordenes.crear')) {
            abort(403, 'No tiene permiso para crear órdenes');
        }
        
        // Lógica para crear orden
        $orden = Orden::create($request->all());
        
        return redirect()->route('ordenes.index')
            ->with('success', 'Orden creada exitosamente');
    }
}
```

---

## 🔄 Compatibilidad con Sistema Anterior

El sistema mantiene compatibilidad con el campo `role_as`:
- `role_as = 0` → Evaluado
- `role_as = 1` → Empresa
- `role_as = 2` → Repro
- `role_as = 3` → Administrador

Los métodos antiguos aún funcionan:
- `$user->isAdmin()` ✅
- `$user->isRepro()` ✅
- `$user->isEmpresa()` ✅
- `$user->isEvaluado()` ✅

Pero ahora también verifican el nuevo sistema de roles.

---

## 📝 Notas Importantes

1. **Migración gradual**: Puedes mantener ambos sistemas activos durante la transición
2. **Múltiples roles**: Un usuario puede tener varios roles simultáneamente
3. **Performance**: Las consultas usan eager loading para optimizar
4. **Seguridad**: Todos los middlewares redirigen a login si no está autenticado

---

## 🚀 Próximos Pasos Recomendados

1. Actualizar los seeders de usuarios para asignarles roles
2. Crear un panel de administración para gestionar roles y permisos
3. Agregar logs de auditoría para cambios de permisos
4. Implementar caché para permisos de usuario (mejor performance)
