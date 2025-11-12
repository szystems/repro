# Changelog - REPRO

Todos los cambios notables en este proyecto serán documentados en este archivo.

El formato está basado en [Keep a Changelog](https://keepachangelog.com/es/1.0.0/),
y este proyecto adhiere a [Semantic Versioning](https://semver.org/lang/es/).

---

## [No Liberado] - 2025-01-XX

### � En Progreso

#### Rediseño del Modelo de Evaluados
- **CAMBIO CRÍTICO:** Evaluados NO son usuarios del sistema
- Acceso mediante token único temporal sin autenticación
- Identificación única por DPI (Documento Personal de Identificación)
- Empresas NO tienen acceso a historial previo de evaluados
- Solo Repro y Admin pueden consultar historial completo por DPI

#### Tipos de Servicio y Formularios
- **Polígrafo Presencial:** 3 tipos de formulario (Pre-empleo, Periódica, Específica)
- **VSA Virtual:** Mismos 3 formularios que Polígrafo
- **Estudio Socioeconómico:** Basado en Pre-empleo con campos adicionales

#### Creación de Órdenes
- Usuario Empresa: Crea órdenes auto-asignadas a su empresa
- Usuario Repro: Crea órdenes seleccionando empresa manualmente
- Ambos tipos de usuario pueden crear órdenes

### ✅ Completado Hoy (8 Nov 2025)
- ✅ Rol "evaluado" eliminado del sistema RBAC
- ✅ Permiso "cuestionarios.completar" eliminado (obsoleto)
- ✅ Descripciones de roles actualizadas:
  - Repro: Incluye mención de historial completo por DPI
  - Empresa: Incluye restricción de acceso a historial
- ✅ Sistema de permisos actualizado: 24 permisos totales (en lugar de 25)
- ✅ Tabla `evaluados_orden` creada (migración ejecutada)
- ✅ Modelo `EvaluadoOrden` con relaciones, scopes y métodos de negocio
- ✅ Factory `EvaluadoOrdenFactory` con 4 states
- ✅ Seeder `EvaluadosOrdenSeeder` con 25 evaluados de ejemplo
- ✅ 20 usuarios con `role_as = 0` eliminados de tabla `users`
- ✅ Controlador `UsersController` actualizado (sin referencias a evaluados)
- ✅ Request `UserFormRequest` actualizado (validación de evaluados eliminada)

### ✅ Completado Adicional (11 Nov 2025)
- ✅ **TODAS las vistas del módulo de usuarios actualizadas** (7 archivos):
  - `add.blade.php` - Removida opción evaluado del formulario de creación
  - `index.blade.php` - Corregida lógica de badges (sin role_as=0)
  - `edit.blade.php` - Removida opción evaluado + badge header corregido  
  - `show.blade.php` - Removidos badges, íconos y pestaña "Datos de Evaluación"
  - `search.blade.php` - Removido filtro por evaluados + badges de resultado
  - `pdfuser.blade.php` - Badges corregidos en PDF individual
  - `pdf.blade.php` - Removidos filtro, estadísticas y badges de evaluados
- ✅ **Documentación técnica completa:**
  - `ESPECIFICACIONES_TECNICAS.md` - Requerimientos funcionales completos
  - `ARQUITECTURA_FORMULARIOS.md` - Diseño detallado del sistema de formularios
- ✅ **Sistema de formularios especificado:**
  - 3 tipos de servicio: Polígrafo, IVSAC, Estudio Socioeconómico
  - 3 formularios de entrada: Preempleo, Periódica/Específica, Socioeconómico  
  - 2 reportes de salida: Preliminar General, Reporte Final
  - 9 estados de proceso: Solicitud → Autorización → Requisito → Programación → En Proceso → Análisis → Preliminar → Final → Entregado
- ✅ **Sistema de autorización de informes:** Solo Repro puede autorizar que empresas vean informes finales

### 📋 Próximos Pasos
- [ ] Crear módulo de órdenes con estados y tipos de servicio  
- [ ] Implementar formularios dinámicos según tipo de servicio
- [ ] Desarrollar sistema de cuestionarios con acceso por token
- [ ] Dashboard de seguimiento de órdenes en tiempo real
- [ ] Sistema de notificaciones por email con tokens
- [ ] Módulo de reportes y análisis
- [ ] Sistema de carga de documentos adjuntos
- [ ] Implementar sistema de tokens únicos temporales
- [ ] Implementar módulo de evaluaciones
- [ ] Implementar módulo de resultados con control de privacidad
- [ ] Crear tests de integración para role system
- [ ] Implementar API REST endpoints
- [ ] Configurar CI/CD pipeline

---

## [2.0.0] - 2025-11-08

### 🚀 Agregado

#### Sistema de Roles y Permisos
- Sistema completo de roles y permisos RBAC (Role-Based Access Control)
- 4 roles principales: `admin`, `repro`, `empresa`, `evaluado`
- 25 permisos granulares distribuidos en 8 módulos
- Trait `HasRolesAndPermissions` para reutilización
- Middleware `CheckRole` para protección de rutas por rol
- Middleware `CheckPermission` para protección de rutas por permiso
- Métodos en User model:
  - `hasRole()`, `hasAnyRole()`, `hasAllRoles()`
  - `hasPermission()`, `hasAnyPermission()`, `hasAllPermissions()`
  - `assignRole()`, `syncRoles()`, `removeRole()`
  - `getAllPermissions()`

#### Migraciones de Base de Datos
- `2025_11_08_000000_improve_users_table_structure.php`: Mejoras en tabla users
  - Campos `documento_identidad`, `tipo_documento`
  - Campo `cuestionario_completado` con timestamp
  - Índices para optimizar búsquedas
- `2025_11_08_000001_create_roles_and_permissions_tables.php`: Tablas de RBAC
  - Tabla `roles` (id, name, display_name, description)
  - Tabla `permissions` (id, name, display_name, module, description)
  - Tabla `role_permission` (relación muchos a muchos)
  - Tabla `user_role` (relación muchos a muchos)

#### Seeders
- `RolesAndPermissionsSeeder`: Población inicial de roles y permisos
  - 4 roles con descripciones
  - 25 permisos organizados por módulo
  - Asignación de permisos por rol
    - Admin: 25 permisos (todos)
    - Repro: 13 permisos
    - Empresa: 7 permisos
    - Evaluado: 1 permiso

#### Documentación
- **PRD.md**: Product Requirements Document completo
  - Visión del proyecto REPRO
  - Definición de 4 tipos de usuarios
  - Desglose de 8 módulos
  - Roadmap en 5 fases
  - Decisiones técnicas documentadas
- **ARCHITECTURE.md**: Documentación técnica de arquitectura
  - Arquitectura MVC en 5 capas
  - Diagramas ERD de relaciones
  - Flujos de autenticación y autorización
  - Patrones de diseño implementados
  - Estrategia de deployment
- **API.md**: Documentación de API REST
  - Endpoints de autenticación
  - Endpoints de usuarios
  - Endpoints de empresas
  - Endpoints futuros (órdenes, cuestionarios, reportes)
  - Manejo de errores y paginación
- **ROLES_Y_PERMISOS.md**: Guía de uso del sistema de roles
- **ACTUALIZACION_USUARIOS.md**: Guía para actualizar vistas de usuarios

#### Controladores y Requests
- Actualización de `UsersController`:
  - Métodos `adduser()`, `insertuser()` actualizados
  - Métodos `edituser()`, `updateuser()` actualizados
  - Soporte para asignación de roles
  - Validación de campos nuevos (documento_identidad, tipo_documento)
- Actualización de `UserFormRequest`:
  - Validación para `documento_identidad` (requerido para evaluados)
  - Validación para `tipo_documento` (enum: DPI, Pasaporte, Licencia)
  - Validación para array de roles

#### Factories
- `EmpresaFactory`: Factory para testing de empresas
- `UserFactory`: Actualizado con soporte para nuevos campos

### ⬆️ Actualizado

#### Framework y Dependencias
- Laravel actualizado de `8.75` a `12.37.0`
- PHP actualizado de `7.4.33` a `8.3.16`
- Composer dependencias:
  - 88 paquetes actualizados
  - 16 paquetes removidos (obsoletos)
  - 18 paquetes nuevos instalados
- Paquetes clave:
  - `laravel/sanctum`: ^4.2.0
  - `laravel/ui`: ^4.6.1
  - `barryvdh/laravel-dompdf`: ^3.1.1
  - `maatwebsite/excel`: ^3.1.67
  - `phpunit/phpunit`: ^11.5.43

#### Modelos
- **User Model**:
  - Implementación de trait `HasRolesAndPermissions`
  - Relación `roles()` (many-to-many)
  - Sistema dual: mantiene compatibilidad con `role_as` (legacy)
  - Métodos helper: `isAdmin()`, `isRepro()`, `isEmpresa()`, `isEvaluado()`
  - Nuevos campos fillable: `documento_identidad`, `tipo_documento`, `cuestionario_completado`

#### Middleware
- Middleware CORS removido (`Fruitcake\Cors`) - Laravel 12 lo maneja nativamente
- Registro de middleware `role` y `permission` en `Kernel.php`

### 🔧 Configuración

#### Laravel Boost
- Instalado Laravel Boost MCP Server `1.7.1`
- Configuración en `.vscode/mcp.json`
- Configuración en `boost.json` (herd_mcp deshabilitado)
- 15+ herramientas especializadas disponibles:
  - `application-info`: Info de aplicación y paquetes
  - `database-schema`: Lectura de esquema DB
  - `database-query`: Queries read-only
  - `tinker`: Ejecución de PHP en contexto Laravel
  - `list-routes`: Inspección de rutas
  - `list-artisan-commands`: Comandos artisan disponibles
  - `read-log-entries`: Lectura de logs
  - `browser-logs`: Logs del navegador
  - `search-docs`: Búsqueda de docs con versión específica

#### PHP Multi-Version Setup
- Sistema de alias para cambio de versiones PHP:
  - `php74`: PHP 7.4.33 (proyectos Laravel 6/8)
  - `php83`: PHP 8.3.16 (proyectos Laravel 12)
  - `artisan74`, `artisan83`: Artisan con versión específica
  - `composer74`, `composer83`: Composer con versión específica
- ZIP extension habilitada en `php.ini` (línea 832)

### 🐛 Corregido
- Error de clase `Fruitcake\Cors\HandleCors` no encontrada (removido del Kernel)
- Error de base de datos `dbrepro` no encontrada (creada con utf8mb4_unicode_ci)
- Terminal usando PHP 7.4 en lugar de 8.3 (alias system implementado)
- ZIP extension faltante en PHP 8.3 impidiendo composer updates

### 🗑️ Removido
- Paquete `fruitcake/laravel-cors` (incompatible con Laravel 12)
- 16 paquetes obsoletos durante actualización de composer
- Herd MCP (no compatible con Laragon)

### 🔒 Seguridad
- Sistema RBAC implementado con protección granular
- Middleware de roles y permisos para rutas sensibles
- Laravel Sanctum configurado para autenticación API
- Validación mejorada en formularios de usuario

### 📊 Base de Datos
- Database: MySQL `dbrepro`
- Collation: `utf8mb4_unicode_ci`
- 150+ tablas detectadas (incluyendo nuevas tablas RBAC)
- Todas las migraciones ejecutadas exitosamente

### ✅ Testing
- PHPUnit actualizado a versión 11
- Configuración `phpunit.xml` actualizada para Laravel 12
- Factories actualizados para nuevos modelos
- Task de VS Code: "Run Feature Tests"

---

## [1.0.0] - 2024-XX-XX (Laravel 8)

### 🚀 Release Inicial

#### Módulos Implementados
- **Módulo de Usuarios**: CRUD completo con roles legacy (role_as: 0-3)
- **Módulo de Empresas**: Gestión de empresas clientes

#### Características
- Sistema de autenticación Laravel UI
- Dashboard por tipo de usuario
- Gestión de fotografías de usuarios
- Sistema de emails (UserMail, UserResetPasswordMail)
- Generación de contraseñas temporales

#### Base de Datos
- Tabla `users` con role_as (0=evaluado, 1=empresa, 2=repro, 3=admin)
- Tabla `empresas` con información de clientes
- Tabla `password_resets` para recuperación de contraseña

---

## Tipos de Cambios

- **Agregado**: Nuevas características
- **Actualizado**: Cambios en funcionalidad existente
- **Deprecated**: Características que serán removidas
- **Removido**: Características removidas
- **Corregido**: Bug fixes
- **Seguridad**: Vulnerabilidades corregidas

---

## Versionado

Este proyecto usa [Semantic Versioning](https://semver.org/):

- **MAJOR**: Cambios incompatibles con versiones anteriores
- **MINOR**: Nuevas características compatibles con versiones anteriores
- **PATCH**: Bug fixes compatibles con versiones anteriores

**Ejemplo:** `2.0.0`
- `2`: Major version (Laravel 12 upgrade)
- `0`: Minor version (sin nuevas features post-upgrade)
- `0`: Patch version (sin bug fixes)

---

**Última actualización:** 8 de noviembre de 2025
