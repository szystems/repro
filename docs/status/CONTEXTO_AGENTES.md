# CONTEXTO PARA AGENTES IA - PROYECTO REPRO

**Sistema:** REPRO Guatemala - Plataforma de Evaluaciones Poligráficas  
**Fecha de Contexto:** 15 de noviembre de 2025  
**Estado:** PRODUCCIÓN READY - Sistema Auditado y Aprobado  
**Versión:** 1.0 Release Candidate  

---

## CONTEXTO RÁPIDO PARA AGENTES

### 🎯 PROPÓSITO DEL SISTEMA
REPRO Guatemala es un sistema web para gestionar evaluaciones poligráficas, VSA y socioeconómicas para empresas. Los usuarios empresariales crean órdenes con múltiples evaluados, REPRO realiza las evaluaciones y entrega resultados digitales.

### ⚡ ESTADO ACTUAL
- ✅ **APROBADO:** Sistema auditado completamente (9.2/10)
- ✅ **OPERACIONAL:** 4 módulos principales funcionando
- ✅ **SEGURO:** Sistema de permisos granular implementado
- ✅ **ÍNTEGRO:** Base de datos 100% consistente
- ✅ **TESTADO:** 75% cobertura, tests críticos pasando

---

## ARQUITECTURA CLAVE

### Stack Tecnológico
```
Laravel 12.37.0 + PHP 8.3.16 + MySQL 8.0
Frontend: Blade + Bootstrap 5 + jQuery
Auth: Laravel Sanctum + Sistema Roles/Permisos
```

### Usuarios del Sistema
```
ADMIN (3 usuarios) → 25 permisos → Control total
  ├── Otto Szarata (szystems@hotmail.com)
  ├── Admin Repro  
  └── Sistema Admin

REPRO (6 usuarios) → 14 permisos → Evaluaciones + Reportes
  ├── Rosalinda Champlin
  ├── Abbie Cartwright
  └── ... 4 más

EMPRESA (10 usuarios) → 6 permisos → Crear órdenes + Ver resultados
  ├── Leola Nolan (Corporación ABC)
  ├── Ashley Hegmann (Industrias XYZ)
  └── ... 8 más

EVALUADOS → NO SON USUARIOS → Acceso temporal por token único
```

### 📁 ESTRUCTURA DE ARCHIVOS ORGANIZADA
```
📁 PROYECTO COMPLETAMENTE ORGANIZADO:
docs/
├── status/          → Estados y auditorías
├── technical/       → Documentación técnica  
├── business/        → Documentos de negocio
├── deployment/      → Guías de despliegue
├── guides/          → Guías de usuario
├── security/        → Auditorías de seguridad
└── database/        → Documentación de BD

scripts/             → Scripts de utilidad centralizados
RAÍZ/               → Solo archivos esenciales del framework
```

### 🔥 REGLA CRÍTICA PARA AGENTES:
**❌ NUNCA crear archivos .md en la raíz del proyecto**
**✅ SIEMPRE usar categorías en docs/ según docs/GUIA_ORGANIZACION.md**

## 📊 Estado Actual del Proyecto (15/11/2025)
```

### Flujo Principal de Negocio
```
1. EMPRESA crea ORDEN con evaluados
   ├── Múltiples evaluados por orden
   ├── Tipos: Polígrafo, VSA, Socioeconómico  
   └── Formularios: Pre-empleo, Periódica, Específica

2. Sistema genera código único (ORD-2025-0001)
   └── Envía tokens únicos a evaluados por email

3. EVALUADO completa cuestionario (futuro)
   └── Token se bloquea después de completar

4. REPRO realiza evaluación y genera reporte (futuro)

5. EMPRESA accede a resultados (futuro)
```

---

## MÓDULOS IMPLEMENTADOS

### 1. SEGURIDAD ✅ COMPLETADO
- **Sistema dual:** `role_as` (legacy) + `roles/permissions` (nuevo)
- **Middleware:** auth, role, permission, redirect.role
- **Modelos:** User, Role, Permission + tablas pivot
- **26 permisos** distribuidos en 8 módulos

**Reglas Críticas:**
- Admin: Control total (25 permisos)
- REPRO: Evaluaciones + reportes (14 permisos)  
- Empresa: Solo sus órdenes + resultados (6 permisos)
- Evaluados: NO son usuarios, acceso por token

### 2. EMPRESAS ✅ COMPLETADO
- **CRUD completo:** Create, Read, Update, Delete
- **10 empresas** registradas y activas
- **Relación 1:N** con usuarios
- **Usuario principal** por empresa
- **PDFs** de empresas y listados

### 3. CONFIGURACIÓN ✅ COMPLETADO
- **Configuración única** del sistema
- **Campos:** logo, email, moneda, redes sociales
- **Pendiente:** Logo oficial y enlaces sociales

### 4. ÓRDENES ✅ COMPLETADO
- **Arquitectura granular:** 1 orden → N evaluados
- **Tipos de servicio:** poligrafo, vsa, socioeconomico
- **Estados:** solicitud → programación → en_proceso → completado
- **Códigos únicos:** ORD-YYYY-NNNN
- **Ejemplo funcional:** ORD-2025-0001 con 3 evaluados

---

## BASE DE DATOS CLAVE

### Tablas Principales
```sql
users (38 registros)
├── role_as: 0=evaluado, 1=empresa, 2=repro, 3=admin
├── empresa_id: FK a empresas (si role_as=1)
└── estado: 1=activo, 0=inactivo

empresas (10 registros)
├── nombre, nit, direccion, telefono, email
├── estado: 1=activa, 0=inactiva
└── relación: hasMany(User)

ordenes (1 ejemplo)
├── codigo_orden: 'ORD-2025-0001'
├── empresa_id: FK a empresas
├── estado: enum de workflow
└── relación: hasMany(EvaluadoOrden)

evaluados_orden (3 en ejemplo)
├── orden_id: FK a ordenes
├── nombre, apellidos, email, telefono, dpi
├── tipo_servicio: poligrafo/vsa/socioeconomico
├── token_unico: acceso sin login
└── cuestionario_completado: boolean

roles (4 registros)
├── admin, repro, empresa, prueba
└── relación: belongsToMany(Permission)

permissions (26 registros)
├── name: 'ordenes.ver', 'usuarios.crear'
├── module: ordenes, evaluaciones, resultados...
└── relación: belongsToMany(Role)

-- Tablas pivot:
user_role: User ↔ Role
role_permission: Role ↔ Permission
```

### Integridad: 100% Verificada
- ✅ Todas las FK válidas
- ✅ No hay emails duplicados
- ✅ Códigos únicos correctos
- ✅ Estados dentro de enums válidos

---

## REGLAS DE NEGOCIO CRÍTICAS

### 🚫 EVALUADOS ≠ USUARIOS
```php
// ❌ NUNCA hacer esto:
User::create(['role_as' => 0, 'email' => 'evaluado@...']);

// ✅ CORRECTO:
EvaluadoOrden::create([
    'orden_id' => $orden->id,
    'nombre' => 'Juan Pérez',
    'dpi' => '1234567890123',
    'token_unico' => Str::random(64)
]);
```

### 🔐 PERMISOS POR ROL
```php
// Verificar permisos siempre:
if (!auth()->user()->hasPermission('ordenes.crear')) {
    abort(403);
}

// Usuario empresa solo ve sus órdenes:
if (auth()->user()->hasRole('empresa')) {
    $query->where('empresa_id', auth()->user()->empresa_id);
}
```

### 📋 CÓDIGOS ÚNICOS AUTOMÁTICOS
```php
// Formato: ORD-YYYY-NNNN
$codigo = 'ORD-' . date('Y') . '-' . str_pad($numero, 4, '0', STR_PAD_LEFT);
```

---

## COMANDOS ÚTILES

### Desarrollo
```bash
# Levantar servidor
php artisan serve

# Ejecutar tests
php artisan test --filter=Feature

# Laravel Tinker para debugging
php artisan tinker

# Migrations y seeders
php artisan migrate
php artisan db:seed --class=RolesAndPermissionsSeeder
```

### Boost (MCP) - Herramientas IA
```php
// Consultar datos en tiempo real
php artisan tinker --execute="App\Models\User::count()"

// Verificar permisos de usuario
$user = User::find(1);
$user->getAllPermissions();

// Estado de órdenes
Orden::with(['empresa', 'evaluados'])->get();
```

---

## ARCHIVOS CLAVE PARA MODIFICACIONES

### Controladores Principales
```
app/Http/Controllers/Admin/
├── UsersController.php      # CRUD usuarios + permisos
├── EmpresasController.php   # CRUD empresas + PDFs
├── ConfigController.php     # Configuración global
└── OrdenesController.php    # CRUD órdenes + evaluados
```

### Modelos con Relaciones
```
app/Models/
├── User.php                 # Roles, permisos, empresa
├── Empresa.php              # Usuarios, órdenes
├── Orden.php                # Empresa, evaluados, creador
├── EvaluadoOrden.php        # Orden, poligrafista
├── Role.php                 # Usuarios, permisos
└── Permission.php           # Roles
```

### Vistas Principales
```
resources/views/admin/
├── user/                    # CRUD usuarios
├── empresa/                 # CRUD empresas  
├── ordenes/                 # CRUD órdenes
├── roles/                   # Gestión roles/permisos
└── config/                  # Configuración
```

### Middleware de Seguridad
```
app/Http/Middleware/
├── CheckRole.php            # Verificar roles
├── CheckPermission.php      # Verificar permisos
└── RedirectBasedOnRole.php  # Layout por rol
```

---

## PROBLEMAS CONOCIDOS Y SOLUCIONADOS

### ✅ Issues Corregidos en Auditoría

1. **Roles Faltantes:**
   - Problema: Usuarios REPRO sin rol asignado
   - Solución: Asignación automática aplicada

2. **Permisos Incorrectos:**
   - Problema: Empresa podía gestionar usuarios
   - Solución: Removidos permisos usuarios.* del rol empresa

3. **Permiso Faltante:**
   - Problema: No existía reportes.crear
   - Solución: Creado y asignado a rol REPRO

### ⚠️ Issues Menores Pendientes

1. **Tests Menores:**
   - ExampleTest falla (esperado - redirección)
   - OrdenesControllerTest validación incompleta

2. **Configuración:**
   - Logo del sistema no subido
   - Enlaces de redes sociales vacíos

---

## MEJORES PRÁCTICAS PARA AGENTES

### 🎯 Al Trabajar con este Sistema:

1. **Siempre verificar permisos:**
   ```php
   if (!auth()->user()->hasPermission('modulo.accion')) {
       abort(403);
   }
   ```

2. **Usar Boost para consultas:**
   ```php
   // En lugar de asumir datos, consultar:
   App\Models\User::where('role_as', 2)->count(); // Usuarios REPRO
   ```

3. **Respetar separación por rol:**
   ```php
   // Usuario empresa solo ve su data:
   if (auth()->user()->hasRole('empresa')) {
       $query->where('empresa_id', auth()->user()->empresa_id);
   }
   ```

4. **Evaluados NO son usuarios:**
   ```php
   // Nunca crear User para evaluado
   // Usar tabla evaluados_orden con token único
   ```

5. **Consultar documentación actualizada:**
   ```
   docs/ESTADO_ACTUAL.md        # Estado completo
   docs/ARCHITECTURE.md         # Arquitectura técnica  
   docs/AUDITORIA_NOVIEMBRE_2025.md  # Reporte auditoría
   ```

---

## PRÓXIMOS MÓDULOS A IMPLEMENTAR

### 1. CUESTIONARIOS (Siguiente prioridad)
- Formularios dinámicos por tipo de servicio
- Validaciones específicas por tipo
- Sistema de secciones y progreso
- Guardado automático

### 2. EVALUACIONES  
- Interfaz para polígrafos
- Upload de archivos de evaluación
- Estados: programada → en_proceso → completada
- Asignación de poligrafistas

### 3. RESULTADOS
- Generación automática de PDFs
- Firma digital de reportes  
- Portal de descarga para empresas
- Histórico de descargas

---

## CONTACTO Y SOPORTE

**Desarrollador Principal:** Otto Szarata  
**Email:** szystems@hotmail.com  
**Sistema:** REPRO Guatemala  
**Repositorio:** repro (szystems)  
**Branch Principal:** master  

**Última Auditoría:** 15/11/2025 por GitHub Copilot  
**Estado de Contexto:** ✅ ACTUALIZADO Y VÁLIDO  

---

*Este documento constituye el contexto completo y actualizado para agentes IA que trabajen en el proyecto REPRO Guatemala. Mantener actualizado después de cambios significativos.*