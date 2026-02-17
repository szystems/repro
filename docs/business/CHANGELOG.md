# Changelog - REPRO

Todos los cambios notables en este proyecto serán documentados en este archivo.

El formato está basado en [Keep a Changelog](https://keepachangelog.com/es/1.0.0/),
y este proyecto adhiere a [Semantic Versioning](https://semver.org/lang/es/).

---

## [2.3.0] - 2026-02-XX (En Desarrollo)

### 📋 Planificado

#### Reunión con Cliente - 4 de febrero 2026
- Sistema validado por el cliente ✅
- Identificados cambios necesarios para alinear con flujo de trabajo real
- Documentación creada: Plan de Trabajo y Cotización de Extras

#### Cambios Contemplados (Sin costo)
- [ ] Nuevos estados según diagrama de flujo
- [ ] Campo observaciones por evaluado (texto libre, editable solo por REPRO, visible para empresa)
- [ ] Quitar código postal (no aplica en Guatemala)
- [ ] Simplificar estado civil
- [ ] Ajustes de permisos (prioridad, fecha límite solo REPRO)
- [ ] Separar estado_formulario y estado_evaluacion
- [ ] Regla: socioeconómico → solo formulario preempleo
- [ ] Agregar requerimientos_generales en empresas
- [ ] Notificación email a empresa al activar switch de resultados visibles
- [ ] Rehabilitar cuestionario completado (REPRO puede desbloquear para que evaluado corrija datos)
- [ ] Dos campos de archivo por evaluado: resultado final + resultado preliminar (visibles si switch activo)
- [ ] Clasificación de colores de resultados reservados (NO usar en calendario ni otros estados):
  - Polígrafo/VSA: Verde (Aprobado/Sin Observaciones), Amarillo (Aprobado con Observación Leve / Aprobado con Excepción), Rojo (No Aprobado/Indicación de Mentira)
  - Socioeconómico: Verde (Tipo A), Amarillo (A-Condicionado), Naranja (Tipo B), Rojo (Tipo C)

#### Extras Cotizados (Pendiente Aprobación)
- [ ] E1: Sistema de Documentos del Evaluado (Q3,500)
- [ ] E2: Módulo Calendario de Programación (Q4,500)
- [ ] E3: Términos y Condiciones Digitales (Q1,500)
- [ ] E4: Sistema de Resultados con Archivo (Q1,200)
- [ ] E5: Módulo de Sedes REPRO + Integración Calendario/Evaluados (Q3,500)
  - CRUD de sedes (nombre, dirección, capacidad, estado activo/inactivo)
  - Campos sede_id y evaluador_id en evaluados/programaciones
  - Validación anti-traslape: misma sede + misma hora = conflicto
  - Permitir misma hora en diferente sede
  - Permitir misma sede + misma hora con diferente evaluador
  - Integración completa con módulo de Calendario de Programación

### 🔧 Corregido
- Vulnerabilidades de seguridad resueltas:
  - PHPUnit actualizado a 11.5.50 (CVE-2026-24765)
  - PsySH actualizado a 0.12.19 (CVE-2026-25129)
- Error PSR-4 en AdminControllerTest corregido
- Laravel Boost actualizado a 1.8.10 (fix protocolo MCP)

### 📦 Actualizaciones de Dependencias
- laravel/framework: 12.37.0 → 12.50.0
- laravel/boost: 1.8.0 → 1.8.10
- laravel/mcp: 0.3.2 → 0.5.4
- phpunit/phpunit: 11.5.43 → 11.5.50
- psy/psysh: 0.12.14 → 0.12.19

---

## [2.2.0] - 2026-01-23

### 🚀 Agregado

#### Portal Empresa ✅ (NUEVO)
- Dashboard específico para usuarios empresa
- Listado de órdenes de la empresa del usuario
- Vista detallada de orden con evaluados
- Acceso a cuestionarios completados (según `resultadosDisponiblesParaEmpresa()`)
- Botones "Copiar enlace" para compartir links de cuestionarios
- Redirección automática después de crear/editar/eliminar órdenes

**Archivos creados/modificados:**
- `app/Http/Controllers/EmpresaController.php` - Métodos `verOrden()` y `verCuestionario()`
- `resources/views/empresa/ordenes/show.blade.php`
- `resources/views/empresa/cuestionarios/show.blade.php`

#### Reporte de Evaluaciones Mejorado ✅
- Nueva columna "Tipo de Formulario" en vista web, PDF y Excel
- Reporte muestra TODOS los evaluados (sin filtro `resultados_visibles_empresa`)
- PDF con logo REPRO horizontal usando `public_path()`

**Archivos modificados:**
- `app/Http/Controllers/Admin/ReportesController.php`
- `resources/views/admin/reportes/evaluaciones.blade.php`
- `resources/views/admin/reportes/pdf/evaluaciones.blade.php`
- `app/Exports/EvaluacionesExport.php`

### 🔧 Corregido
- Navegación de usuarios empresa después de CRUD de órdenes
- Error de Carbon parsing en Excel cuando `cuestionario_completado_at` es null
- Visibilidad de cuestionarios para empresa controlada correctamente

### 🧹 Limpieza para Producción
- Eliminada ruta debug `/debug-orden` de `routes/web.php`
- Eliminado archivo vacío `public/test-busqueda.php`
- Eliminados todos los `console.log` de JavaScript en:
  - `admin/ordenes/create.blade.php`
  - `admin/user/edit.blade.php`
  - `admin/user/add.blade.php`

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
