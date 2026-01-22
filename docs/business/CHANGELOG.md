# Changelog - REPRO

Todos los cambios notables en este proyecto serán documentados en este archivo.

El formato está basado en [Keep a Changelog](https://keepachangelog.com/es/1.0.0/),
y este proyecto adhiere a [Semantic Versioning](https://semver.org/lang/es/).

---

## [2.1.0] - 2026-01-21

### 🚀 Agregado

#### Módulo Dashboard ✅ (NUEVO)
- Dashboard diferenciado por rol (Admin/REPRO vs Empresa)
- Tarjetas estadísticas: Órdenes, Evaluados, Completados, Pendientes
- Listas de evaluados y órdenes recientes con acciones rápidas
- Accesos directos a funcionalidades principales
- 6 tests automatizados pasando

**Archivos creados:**
- `app/Http/Controllers/Admin/DashboardController.php`
- `resources/views/admin/dashboard/index.blade.php`
- `tests/Feature/DashboardTest.php`

#### Módulo Reportes ✅ (NUEVO)
- Reporte de Evaluaciones con filtros (fecha, empresa, estado)
- Reporte de Empresas (solo Admin/REPRO)
- Exportación a PDF con branding REPRO
- Exportación a Excel
- Estadísticas de resumen en cada reporte
- 10 tests automatizados pasando

**Archivos creados:**
- `app/Http/Controllers/Admin/ReportesController.php`
- `resources/views/admin/reportes/evaluaciones.blade.php`
- `resources/views/admin/reportes/empresas.blade.php`
- `resources/views/admin/reportes/evaluaciones-pdf.blade.php`
- `app/Exports/EvaluacionesExport.php`
- `tests/Feature/ReportesTest.php`

#### Módulo Notificaciones Email ✅ (NUEVO)
- Email automático al asignar evaluado a orden
- Email de recordatorio para cuestionarios pendientes (diario 8:00 AM)
- Email de confirmación cuando se completa cuestionario
- Reenvío manual de correos desde UI (órdenes y cuestionarios)
- Comando artisan `cuestionarios:enviar-recordatorios`
- Templates con branding REPRO
- 8 tests automatizados pasando

**Archivos creados:**
- `app/Mail/EvaluadoAsignadoMail.php`
- `app/Mail/RecordatorioCuestionarioMail.php`
- `app/Mail/CuestionarioCompletadoMail.php`
- `resources/views/emails/evaluado-asignado.blade.php`
- `resources/views/emails/recordatorio-cuestionario.blade.php`
- `resources/views/emails/cuestionario-completado.blade.php`
- `app/Console/Commands/EnviarRecordatoriosCuestionario.php`
- `tests/Feature/NotificacionesEmailTest.php`

#### Mejoras en Listado de Cuestionarios (Admin) ✅
- 6 tarjetas estadísticas (Total, Pendientes, En Progreso, Completados, Hoy, Tasa Completado)
- Nueva columna "Orden" con link directo a la orden
- Nueva columna "Contacto" (email, teléfono, celular)
- Nueva columna "Servicio/Formulario"
- Botón reenviar correo al evaluado
- Botón de filtros más prominente

#### Mejoras en Módulo de Órdenes ✅
- Botón reenviar correo a evaluados desde detalle de orden
- Ruta `POST /evaluados/{evaluado}/reenviar-correo`
- Regenera token si está expirado
- Registra acción en logs
- 2 tests nuevos en OrdenesControllerTest

### 🔧 Corregido
- Nombre de ruta corregido de `cuestionario.acceso` a `cuestionario.mostrar`
- Test de mail usando `assertQueued` en lugar de `assertSent` para Mailables con ShouldQueue

### 📝 Documentación
- Actualizado `docs/status/ESTADO_ACTUAL.md` con todos los módulos nuevos
- Actualizado `docs/status/CONTEXTO_AGENTES.md` con contexto actualizado
- Actualizado `docs/README_DOCS.md` con índice y resumen de tests
- Actualizado `docs/business/CHANGELOG.md` con cambios de enero 2026

---

## [2.0.1] - 2026-01-20

### 🚀 Agregado

#### Módulo de Cuestionarios Público ✅
- Flujo completo para evaluados sin autenticación
- Verificación de identidad por DPI
- Navegación progresiva por secciones
- Guardado automático de respuestas
- Página de confirmación al completar
- Token único se bloquea después de completar
- Diseño responsivo con branding REPRO

**Vistas creadas:**
- `cuestionario/acceso.blade.php`
- `cuestionario/verificar-identidad.blade.php`
- `cuestionario/seccion.blade.php`
- `cuestionario/finalizar.blade.php`
- `cuestionario/completado.blade.php`
- `cuestionario/error.blade.php`
- `layouts/cuestionario.blade.php`

#### Módulo de Cuestionarios Admin ✅
- Listado de cuestionarios con filtros
- Ver detalle de cuestionario completado
- Editar respuestas de cuestionarios
- Marcar cuestionario como completo manualmente
- Generación de PDF con branding REPRO

**Vistas creadas:**
- `admin/cuestionarios/index.blade.php`
- `admin/cuestionarios/show.blade.php`
- `admin/cuestionarios/edit.blade.php`
- `admin/cuestionarios/pdf.blade.php`

#### Sistema de PDFs Unificado ✅
- Todos los PDFs con branding REPRO consistente
- Header: fondo azul (#000555), logo en recuadro blanco, texto amarillo
- Footer: información de contacto y confidencialidad
- Colores: #000555 (azul), #ffb000 (amarillo), #ffcc33 (amarillo claro)

**PDFs actualizados:**
- `admin/ordenes/pdf.blade.php` (NUEVO)
- `admin/cuestionarios/pdf.blade.php` (NUEVO)
- `admin/user/pdf.blade.php` (actualizado)
- `admin/user/pdfuser.blade.php` (actualizado)
- `admin/empresa/pdf.blade.php` (actualizado)
- `admin/empresa/pdfempresa.blade.php` (actualizado)

#### Mejoras en Módulo de Órdenes ✅
- Botón PDF en listado de órdenes
- Botón PDF en detalle de orden
- Botón PDF de cuestionario en lista de evaluados
- Visualización de observaciones en detalle
- Sistema de estados corregido y funcionando

### 🔧 Corregido
- Validación de estados en `OrdenesController::cambiarEstado()`
- Estados correctos: solicitud, autorizacion, requisito, programacion, en_proceso, analisis, preliminar, final, entregado, cancelado
- Método `puedeTransicionarA()` en modelo Orden más flexible
- Observaciones visibles en detalle de orden

### 📝 Documentación
- Actualizado `ESTADO_ACTUAL.md` con módulos completados
- Actualizado `CONTEXTO_AGENTES.md` con información actual
- Actualizado `README_DOCS.md` con índice actualizado
- Actualizado `CHANGELOG.md` con cambios de enero 2026

---

## [2.0.0] - 2025-11-15

### 🚀 Agregado

#### Sistema de Roles y Permisos ✅
- Sistema RBAC completo (Role-Based Access Control)
- 3 roles principales: admin, repro, empresa
- 26 permisos granulares en 8 módulos
- Trait `HasRolesAndPermissions`
- Middleware `CheckRole` y `CheckPermission`

#### Módulo de Órdenes ✅
- CRUD completo de órdenes
- Múltiples evaluados por orden
- 3 tipos de servicio: Polígrafo, VSA, Socioeconómico
- 3 tipos de formulario: Pre-empleo, Periódica, Específica
- Códigos únicos automáticos (ORD-YYYY-NNNN)
- Sistema de estados de workflow

#### Arquitectura de Evaluados ✅
- Evaluados NO son usuarios del sistema
- Tabla `evaluados_orden` con token único
- Acceso por token sin autenticación
- DPI como identificador único

#### Módulo de Empresas ✅
- CRUD completo
- Relación con usuarios y órdenes
- Generación de PDFs
- Control de estados

#### Módulo de Usuarios ✅
- CRUD completo
- Asignación de roles
- Generación de PDFs
- Sin soporte para evaluados (eliminado)

### 🔧 Corregido
- Eliminados usuarios con role_as = 0 (evaluados)
- Actualizado sistema de permisos
- Corregidas vistas de usuarios sin referencias a evaluados

---

## [1.0.0] - 2025-11-08

### 🚀 Inicial
- Migración de Laravel 8 a Laravel 12
- Estructura base del proyecto
- Autenticación básica
- Dashboard inicial

---

## Convenciones

- `🚀 Agregado` para nuevas funcionalidades
- `🔧 Corregido` para corrección de bugs
- `⚠️ Cambiado` para cambios en funcionalidades existentes
- `🗑️ Eliminado` para funcionalidades removidas
- `📝 Documentación` para cambios en documentación
- `🔒 Seguridad` para actualizaciones de seguridad
