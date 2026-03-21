# Plan de Trabajo — Observaciones Marzo 2026

## 📋 Contexto

**Fecha de Observaciones:** 9 de marzo de 2026
**Estado del Sistema:** Desplegado en producción (https://reproappv2.szystems.com)
**Versión:** Fases 1-7 completadas — 285 tests, 730 assertions
**Origen:** Observaciones del cliente tras pruebas del sistema en producción

---

## 🎯 Resumen

El cliente realizó pruebas del sistema en producción e identificó 27 observaciones clasificadas en: bugs, ajustes de texto/campo, mejoras de UX, y funcionalidades nuevas. Este plan organiza la resolución en 6 subfases priorizadas por impacto y urgencia.

---

## 📊 Clasificación de Observaciones

### 🐛 Bugs Confirmados (5)

| # | Bug | Causa Raíz | Archivo Afectado |
|---|-----|-----------|-----------------|
| B1 | Datos se borran al fallar validación al crear orden | Evaluados dinámicos (JS) no se repoblan con `old()` al error | `create.blade.php` (ordenes) |
| B2 | Error al crear orden desde usuario empresa | Posible `empresa_id` null + ruta de redirección incorrecta | `OrdenesController.php` L167-181 |
| B3 | Error en Configuración | `explode(' ', $currency)` falla sin validación previa | `ConfigController.php` L20 |
| B4 | Campos duplicados al crear usuario | Checkbox `principal` aparece 2 veces con mismo `name` e `id` | `add.blade.php` (user) L200 y L253 |
| B5 | Copiar enlace no funciona (empresa) | `navigator.clipboard` requiere HTTPS — verificar en producción | JS en vistas empresa |

### ✅ Ya Funciona Correctamente (Explicar al Cliente)

| # | Observación | Estado Real |
|---|------------|-------------|
| Sede al crear orden | La sede se asigna **por evaluado** al programar cita, no por orden. Un evaluado puede ir a sede diferente que otro de la misma orden. Adicionalmente, se agregará sede responsable a nivel de orden (8C.7) y campo `sede_id` en usuarios REPRO para saber qué personal pertenece a cada sede (8C.11). |
| Resultado preliminar | Ya existe dentro de cada evaluado en la vista de la orden. Se puede subir archivo preliminar y final. |
| Observaciones internas | Están correctamente ocultas para empresas (solo visibles para REPRO/admin). Son observaciones internas. |
| Búsqueda de candidato | Ya existe `historialPorDpi()` — solo falta hacerlo más visible/accesible en el menú. |
| Correo @szystems | Es temporal de pruebas. Se cambia en configuración cuando tengan dominio de correo propio. |

### 📝 Cambios Solicitados (22)

| Tipo | Cantidad |
|------|----------|
| Cambios de texto/campo | 4 |
| Mejoras de UX/vista | 7 |
| PDF y documentos | 4 |
| Funcionalidades medianas | 4 |
| Funcionalidades complejas | 3 |

---

## 📅 Plan de Trabajo

### Fase 8A — Bugs y Correcciones Críticas
**Prioridad: INMEDIATA — Afectan el uso diario del sistema**

| # | Tarea | Detalle Técnico | Estado |
|---|-------|-----------------|--------|
| 8A.1 | Fix: datos se borran al crear orden | Pasar `old('evaluados')` como JSON a JS, repoblar campos dinámicos al fallar validación | ✅ |
| 8A.2 | Fix: error al crear orden desde empresa | Validar `empresa_id` no null, corregir protección en EmpresaController | ✅ |
| 8A.3 | Fix: error en Configuración | Validar campo `currency` en ConfigFormRequest, manejar explode seguro | ✅ |
| 8A.4 | Fix: checkbox `principal` duplicado | Eliminar duplicado en `add.blade.php`, dejar solo uno dentro de `.principal-check-container` | ✅ |
| 8A.5 | Verificar copiar enlace en producción | Agregar fallback con textarea + `execCommand('copy')` + prompt manual | ✅ |
| 8A.6 | Tests para todos los fixes | 8 tests, 22 assertions — todos pasan | ✅ |

### Fase 8B — Ajustes Rápidos
**Prioridad: ALTA — Mejoras de texto, campos y accesibilidad**

| # | Tarea | Detalle Técnico | Estado |
|---|-------|-----------------|--------|
| 8B.1 | "Estado de Cuestionarios" → "Estado de Procesos" | Cambiar texto en sidebar empresa, vista cuestionarios, vista usuarios (3 archivos) | ✅ |
| 8B.2 | Nombre del PDF: `{nombre}_{apellido}_Orden{codigo}.pdf` | Cambiar en 4 métodos: `exportarPdf()`, `generarPDF()`, `generarPDFCuestionarioEmpresa()`, y descarga de resultado | ✅ |
| 8B.3 | Filtro por empresa en reporte de empresas | Agregar select de empresa en `ReportesController::empresas()` y vista | ✅ |
| 8B.4 | Quitar `fecha_limite`, mostrar fecha de creación | Ocultar campo `fecha_limite` de vistas, mostrar `created_at` formateado como "Fecha de creación" | ✅ |
| 8B.5 | Captura de foto en inputs de documentos | Agregar `capture="environment"` a inputs file en vistas de documentos y cuestionario | ✅ |
| 8B.6 | Hacer visible búsqueda historial por DPI | Agregar enlace en menú admin/repro a la vista `historial-dpi` existente | ✅ |
| 8B.7 | Dirección del evaluado | Migración: agregar `direccion` (string 300 nullable) a `evaluados_orden`. Agregar a `$fillable`, a JS `agregarEvaluado()` en create, a edit y show. Incluir en `procesarEvaluados()` | ✅ |
| 8B.8 | Observaciones por evaluado en formulario | El campo `observaciones` ya existe en BD y `$fillable` de `EvaluadoOrden` pero no tiene textarea en create/edit. Agregar textarea en `agregarEvaluado()` JS y en edit. Mostrar en show (admin + empresa). Obs. del cliente visibles para REPRO | ✅ |
| 8B.9 | Tests para ajustes | Validar cambios de texto, filtros, nombres de PDF, dirección, observaciones | ✅ |

### Fase 8C — Estados y UX del Cliente
**Prioridad: ALTA — La empresa ve información limitada y sin colores**

| # | Tarea | Detalle Técnico | Estado |
|---|-------|-----------------|--------|
| 8C.1 | Estados con colores para vista empresa | Reemplazar `bg-success` hardcodeado por accessors `estado_color`/`estado_human` en empresa index y show | ✅ |
| 8C.2 | Mostrar todos los estados al cliente | Dos badges por evaluado: `estado_evaluacion_color/texto` + `estado_formulario_color` en empresa show | ✅ |
| 8C.3 | Observaciones visibles donde corresponde | Observaciones del evaluado visibles en empresa/ordenes/show con icono chat | ✅ |
| 8C.4 | Mover firma a la página de autorización | Canvas de firma movido a `terminos.blade.php`, guardado al aceptar términos. `finalizar.blade.php` simplificado | ✅ |
| 8C.5 | Botón reenviar enlace desde empresa | Formulario POST a `evaluados.reenviar-correo` con confirmación, solo si email existe y cuestionario no completado | ✅ |
| 8C.6 | WhatsApp y enlace Maps en Sedes | Migración `whatsapp` (varchar 30) + `enlace_maps` (varchar 500). CRUD y vistas actualizados con wa.me link | ✅ |
| 8C.7 | Sede responsable en cabecera de orden | Migración `sede_id` FK nullable en ordenes. Select en create/edit, badge en index, fila en show | ✅ |
| 8C.8 | Auto-sugerir sede al programar cita | Fallback `$evaluado->sede_id ?? $orden->sede_id` en modal de programar cita | ✅ |
| 8C.9 | Filtro por sede en listado de órdenes | Select de sede en filtros admin, query `where('sede_id', ...)` en controller | ✅ |
| 8C.10 | Modalidad de cita (presencial/virtual) | Migración `modalidad` enum en evaluados_orden. Auto-sugerencia: polígrafo→presencial, vsa→virtual. Select en modal + calendario | ✅ |
| 8C.11 | Sede en usuario REPRO | Migración `sede_id` FK en users. Relación `sede()` en User. Selector en add/edit, display en show | ✅ |
| 8C.12 | Notificación nueva orden a usuarios de sede | `NuevaOrdenSedeMail` (queued). Envío automático en `store()` a usuarios REPRO con `sede_id` matching | ✅ |
| 8C.13 | Tests para UX | 16 tests, 32 assertions — colores, estados, firma, reenvío, sedes, modalidad, notificación | ✅ |

### Fase 8D — PDF y Documentos
**Prioridad: MEDIA — Mejoras al informe generado**

| # | Tarea | Detalle Técnico | Estado |
|---|-------|-----------------|--------|
| 8D.1 | Autorización/términos en el PDF | Sección completa con texto autorización, tipo evaluación, declaraciones, consentimiento polígrafo/VSA, firma digital evaluado | ✅ |
| 8D.2 | Documentos verificados en el PDF | Tabla con #, Tipo, Archivo, Estado (badge color), Subido por. Condicional si hay documentos | ✅ |
| 8D.3 | Campo responsable del proceso | Migración `responsable_id` FK users nullable. Select en modal programar/reprogramar. Display en show | ✅ |
| 8D.4 | Firma/nombre del responsable en el PDF | Bloque firma con nombre, cargo, "Responsable del Proceso — REPRO Guatemala". Condicional si hay responsable | ✅ |
| 8D.5 | Tests para PDFs | 15 tests, 36 assertions — responsable CRUD, PDF autorización, documentos, firma, validación | ✅ |

### Fase 8E — Funcionalidades Medianas
**Prioridad: MEDIA — Nuevas capacidades del sistema**

| # | Tarea | Detalle Técnico | Estado |
|---|-------|-----------------|--------|
| 8E.1 | Múltiples servicios por evaluado | Validación DPI+tipo_servicio en OrdenesController. Indicador DPI duplicado en create. Badge 'Multi-servicio' en show | ✅ |
| 8E.2 | Subir papelería desde vista empresa | Partial `_documentos_evaluado.blade.php` con upload/download/delete. Rediseño show empresa con cards por evaluado. Eager loading en EmpresaController | ✅ |
| 8E.3 | Papelería anticipada desde empresa | Usa mismo endpoint de 8E.2 — empresa puede subir docs en cualquier momento antes/después del cuestionario | ✅ |
| 8E.4 | Archivos adjuntos de seguimiento REPRO | Migración enum 'seguimiento' en tipo_documento. Actualización modelo DocumentoEvaluado. Visible para empresa, solo subible por REPRO | ✅ |
| 8E.5 | Reportes por mes | Dropdown 'Mes Rápido' (últimos 12 meses) en evaluaciones y empresas. JS auto-fill de fechas | ✅ |
| 8E.6 | Optimizar rendimiento cuestionario | `where('role_as', '>=', 2)` reemplaza `whereHas('roles')`. `loadMissing()` en lugar de `load()` | ✅ |
| 8E.7 | Tests para funcionalidades | 17 tests, 33 assertions — multi-servicio, documentos empresa, seguimiento, reportes, optimización | ✅ |

### Fase 8F — Funcionalidades Complejas
**Prioridad: BAJA — Módulos nuevos que requieren diseño**

| # | Tarea | Detalle Técnico | Estado |
|---|-------|-----------------|--------|
| 8F.1 | Sistema de notificaciones en sistema | Migración `notifications` (UUID). 4 notificaciones: OrdenCreada, CuestionarioCompletado, ResultadosDisponibles, EvaluadoAsignado. Controller API JSON. Bell dropdown en nav con polling 30s | ✅ |
| 8F.2 | Permisos granulares para personal REPRO | Migración asigna roles existentes. CheckPermission admin bypass. UI permisos por módulo con íconos. Rol personal `user_{id}` con permisos sincronizados | ✅ |
| 8F.3 | Control de permisos para admin empresa | 6 permisos empresa en create/edit sub-usuarios. JSON en campo `permisos`. `tienePermisoEmpresa()` en User model | ✅ |
| 8F.4 | Tests para módulos complejos | 19 tests, 52 assertions — notificaciones, permisos REPRO, permisos empresa | ✅ |

---

## 📈 Progreso General

| Fase | Prioridad | Tareas | Estado |
|------|-----------|--------|--------|
| 8A: Bugs y Correcciones | INMEDIATA | 6 | ✅ Completada |
| 8B: Ajustes Rápidos | ALTA | 9 | ✅ Completada |
| 8C: Estados y UX | ALTA | 13 | ✅ Completada |
| 8D: PDF y Documentos | MEDIA | 5 | ✅ Completada |
| 8E: Funcionalidades Medianas | MEDIA | 7 | ✅ Completada |
| 8F: Funcionalidades Complejas | BAJA | 4 | ✅ Completada |
| **Total** | | **44** | |

---

## 📎 Documentos de Referencia

- [Observaciones del cliente 9-3-2026](../Observaciones%20cliente/observaciones%20cliente%209-3-2026)
- [Plan de Trabajo Original (Feb 2026)](PLAN_TRABAJO_FEBRERO_2026.md)
- [Informe de Avances Fases 1-7](INFORME_REPRO_MARZO_2026.md)

---

## ✅ Historial de Cambios

| Fecha | Acción | Detalle |
|-------|--------|---------||
| 2026-03-20 | Fase 8F completada | 4 tareas de Funcionalidades Complejas: notificaciones in-app (4 tipos, bell dropdown, polling 30s), permisos granulares REPRO (rol personal, UI por módulo), permisos admin empresa (6 permisos JSON). 2 migraciones, 23 archivos. 19 tests, 52 assertions. Commit: b00dcce3 |
| 2026-03-20 | Fase 8E completada | 7 tareas de Funcionalidades Medianas: multi-servicio DPI, papelería empresa con anticipada, adjuntos seguimiento REPRO, filtro mes en reportes, optimización cuestionario. 1 migración, 14 archivos. 17 tests, 33 assertions. Commit: 99b05c30 |
| 2026-03-20 | Fase 8D completada | 5 tareas de PDF y Documentos: autorización/términos en PDF, documentos verificados en PDF, campo responsable_id, firma responsable en PDF. 1 migración, 13 archivos modificados. 15 tests, 36 assertions. Commit: 81b4a67e |
| 2026-03-09 | Fase 8C completada | 13 tareas de Estados y UX: colores dinámicos, firma en autorización, reenviar enlace, WhatsApp/Maps en sedes, sede en orden/usuarios, modalidad cita, filtro sede, notificación sede. 4 migraciones, 1 Mailable. 16 tests, 32 assertions |
| 2026-03-09 | Fase 8B completada | 9 ajustes rápidos: texto, PDFs, filtros, fecha_limite, captura, historial DPI, dirección, observaciones. 16 tests, 43 assertions |
| 2026-03-09 | Fase 8A completada | 5 bugs corregidos, 8 tests, 22 assertions. Commit: d5f347a4 |
| 2026-03-09 | 6 nuevas observaciones del cliente | Obs. 1: sede en users REPRO + notificación → 8C.11, 8C.12. Obs. 2: adjuntos seguimiento REPRO → 8E.4. Obs. 3: papelería anticipada empresa → 8E.2 actualizada, 8E.3. Obs. 4: dirección evaluado → 8B.7. Obs. 5: modalidad cita → 8C.10. Obs. 6: observaciones por evaluado → 8B.8. Total 37→44 tareas |
| 2026-03-09 | Sede responsable en orden | Agregadas tareas 8C.7-8C.9: sede_id en ordenes, auto-sugerir en programación, filtros por sede |
| 2026-03-09 | Documento creado | Análisis de 27 observaciones del cliente, plan de 37 tareas en 6 subfases |

---

*Documento creado: 9 de marzo de 2026*
*Última actualización: 20 de marzo de 2026 — TODAS LAS FASES COMPLETADAS (44/44 tareas)*
