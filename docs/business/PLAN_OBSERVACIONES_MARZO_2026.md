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
| 8A.1 | Fix: datos se borran al crear orden | Pasar `old('evaluados')` como JSON a JS, repoblar campos dinámicos al fallar validación | ☐ |
| 8A.2 | Fix: error al crear orden desde empresa | Validar `empresa_id` no null, corregir redirección post-store para role empresa | ☐ |
| 8A.3 | Fix: error en Configuración | Validar campo `currency` en ConfigFormRequest, manejar explode seguro | ☐ |
| 8A.4 | Fix: checkbox `principal` duplicado | Eliminar duplicado en `add.blade.php`, dejar solo uno dentro de `.empresa-fields` | ☐ |
| 8A.5 | Verificar copiar enlace en producción | Confirmar que funciona con HTTPS. Si no, agregar fallback con `document.execCommand` | ☐ |
| 8A.6 | Tests para todos los fixes | Tests de regresión para cada bug corregido | ☐ |

### Fase 8B — Ajustes Rápidos
**Prioridad: ALTA — Mejoras de texto, campos y accesibilidad**

| # | Tarea | Detalle Técnico | Estado |
|---|-------|-----------------|--------|
| 8B.1 | "Estado de Cuestionarios" → "Estado de Procesos" | Cambiar texto en sidebar empresa, vista cuestionarios, vista usuarios (3 archivos) | ☐ |
| 8B.2 | Nombre del PDF: `{nombre}_{apellido}_Orden{codigo}.pdf` | Cambiar en 4 métodos: `exportarPdf()`, `generarPDF()`, `generarPDFCuestionarioEmpresa()`, y descarga de resultado | ☐ |
| 8B.3 | Filtro por empresa en reporte de empresas | Agregar select de empresa en `ReportesController::empresas()` y vista | ☐ |
| 8B.4 | Quitar `fecha_limite`, mostrar fecha de creación | Ocultar campo `fecha_limite` de vistas, mostrar `created_at` formateado como "Fecha de creación" | ☐ |
| 8B.5 | Captura de foto en inputs de documentos | Agregar `capture="environment"` a inputs file en vistas de documentos y cuestionario | ☐ |
| 8B.6 | Hacer visible búsqueda historial por DPI | Agregar enlace en menú admin/repro a la vista `historial-dpi` existente | ☐ |
| 8B.7 | Dirección del evaluado | Migración: agregar `direccion` (string 300 nullable) a `evaluados_orden`. Agregar a `$fillable`, a JS `agregarEvaluado()` en create, a edit y show. Incluir en `procesarEvaluados()` | ☐ |
| 8B.8 | Observaciones por evaluado en formulario | El campo `observaciones` ya existe en BD y `$fillable` de `EvaluadoOrden` pero no tiene textarea en create/edit. Agregar textarea en `agregarEvaluado()` JS y en edit. Mostrar en show (admin + empresa). Obs. del cliente visibles para REPRO | ☐ |
| 8B.9 | Tests para ajustes | Validar cambios de texto, filtros, nombres de PDF, dirección, observaciones | ☐ |

### Fase 8C — Estados y UX del Cliente
**Prioridad: ALTA — La empresa ve información limitada y sin colores**

| # | Tarea | Detalle Técnico | Estado |
|---|-------|-----------------|--------|
| 8C.1 | Estados con colores para vista empresa | Reemplazar `bg-success` hardcodeado por colores específicos del cliente (9 estados con hex). Usar accessors existentes del modelo + colores custom | ☐ |
| 8C.2 | Mostrar todos los estados al cliente | Vista empresa muestra un solo badge. Mostrar estado de orden + estado de evaluación + estado de formulario con colores | ☐ |
| 8C.3 | Observaciones visibles donde corresponde | Mostrar observaciones del evaluado en vista empresa (las que la empresa ingresó). Mostrar observaciones de empresa en vista show de empresa | ☐ |
| 8C.4 | Mover firma a la página de autorización | Mover canvas de firma de `finalizar.blade.php` a `terminos.blade.php`. Guardar firma al aceptar términos | ☐ |
| 8C.5 | Botón reenviar enlace desde empresa | Agregar botón en vista empresa + ruta autorizada. Reutilizar lógica existente de reenvío (OrdenesController L692) | ☐ |
| 8C.6 | WhatsApp y enlace Maps en Sedes | Migración: agregar campos `whatsapp` y `enlace_maps` a tabla `sedes`. Actualizar CRUD y vistas | ☐ |
| 8C.7 | Sede responsable en cabecera de orden | Migración: agregar `sede_id` (FK nullable) a tabla `ordenes`. Select de sede en create/edit de orden. Mostrar sede en index de órdenes como badge/columna | ☐ |
| 8C.8 | Auto-sugerir sede al programar cita | Pre-seleccionar la sede de la orden al programar cita de evaluado (editable si el evaluado va a otra sede) | ☐ |
| 8C.9 | Filtro por sede en listado y reportes de órdenes | Agregar filtro de sede en: index de órdenes (admin), reportes de evaluaciones, reportes de empresas | ☐ |
| 8C.10 | Modalidad de cita (presencial/virtual) | Migración: agregar `modalidad` enum('presencial','virtual') nullable a `evaluados_orden`. Auto-asignar: polígrafo→presencial, vsa→virtual, socioeconómico→selector. Mostrar en show y calendario | ☐ |
| 8C.11 | Sede en usuario REPRO | Migración: agregar `sede_id` FK nullable a `users`. Relación `sede()` en User, `usuarios()` en Sede. Selector de sede en add/edit de usuario (solo role_as >= 2) | ☐ |
| 8C.12 | Notificación nueva orden a usuarios de sede | Crear `NuevaOrdenSedeMail`. En `OrdenesController@store()`: tras crear orden, enviar mail a `User::where('sede_id', $sedeId)->where('estado', 1)`. Usar cola para envío | ☐ |
| 8C.13 | Tests para UX | Validar colores, estados, firma, reenvío, sedes, modalidad, notificación sede | ☐ |

### Fase 8D — PDF y Documentos
**Prioridad: MEDIA — Mejoras al informe generado**

| # | Tarea | Detalle Técnico | Estado |
|---|-------|-----------------|--------|
| 8D.1 | Autorización/términos en el PDF | Agregar sección con texto de autorización + firma del evaluado en el PDF del cuestionario | ☐ |
| 8D.2 | Documentos verificados en el PDF | Agregar sección listando documentos subidos con estado de verificación (aprobado/rechazado) | ☐ |
| 8D.3 | Campo responsable del proceso | Nuevo campo `responsable_id` (FK users) en `evaluados_orden`. Select de poligrafista/responsable en vista admin | ☐ |
| 8D.4 | Firma/nombre del responsable en el PDF | Mostrar nombre y cargo del responsable en pie de firma del PDF generado | ☐ |
| 8D.5 | Tests para PDFs | Validar contenido de PDFs generados, responsable, documentos | ☐ |

### Fase 8E — Funcionalidades Medianas
**Prioridad: MEDIA — Nuevas capacidades del sistema**

| # | Tarea | Detalle Técnico | Estado |
|---|-------|-----------------|--------|
| 8E.1 | Múltiples servicios por evaluado | Relajar restricción unique de DPI: permitir misma persona en misma orden si es diferente servicio. Indicador visual "tiene otro servicio en esta orden" | ☐ |
| 8E.2 | Subir papelería desde vista empresa | Agregar sección de documentos en `empresa/ordenes/show.blade.php` por cada evaluado. Reutilizar modelo `DocumentoEvaluado` con `subido_por_tipo='empresa'`. Mostrar estado de verificación. Permitir descarga de docs subidos por REPRO | ☐ |
| 8E.3 | Papelería anticipada desde empresa | Permitir que la empresa suba papelería del evaluado desde su portal **antes** de que el evaluado complete el cuestionario. Endpoint nuevo en controller empresa. El evaluado verá docs ya subidos al llegar a la pantalla de documentos del cuestionario | ☐ |
| 8E.4 | Archivos adjuntos de seguimiento REPRO | En la sección de documentos del admin (ya existe `_documentos_evaluado.blade.php`), agregar tipo 'seguimiento' para archivos de seguimiento interno de REPRO. Estos son visibles para empresa pero no editables por ella | ☐ |
| 8E.5 | Reportes por mes | Agregar filtro rápido de mes/año en reportes. Dropdown de meses + año. Aplicar tanto en admin como empresa | ☐ |
| 8E.6 | Optimizar rendimiento cuestionario | Revisar `notificarCuestionarioCompletado()` — queries sin índice. Verificar que cola funcione para emails | ☐ |
| 8E.7 | Tests para funcionalidades | Tests integrales de multi-servicio, documentos, papelería anticipada, seguimiento, reportes | ☐ |

### Fase 8F — Funcionalidades Complejas
**Prioridad: BAJA — Módulos nuevos que requieren diseño**

| # | Tarea | Detalle Técnico | Estado |
|---|-------|-----------------|--------|
| 8F.1 | Sistema de notificaciones en sistema | Crear migración `notifications`, UI de campana en navbar, marcar como leído, reemplazar/complementar emails por notificaciones internas | ☐ |
| 8F.2 | Permisos granulares para personal REPRO | Activar tablas roles/permissions existentes (24 permisos). UI de gestión de permisos por usuario. Reemplazar `role_as >= 2` por `hasPermission()` en controllers | ☐ |
| 8F.3 | Control de permisos para admin empresa | Permitir que empresa principal configure qué puede hacer cada sub-usuario. UI de permisos para empresa | ☐ |
| 8F.4 | Tests para módulos complejos | Tests completos de notificaciones y permisos | ☐ |

---

## 📈 Progreso General

| Fase | Prioridad | Tareas | Estado |
|------|-----------|--------|--------|
| 8A: Bugs y Correcciones | INMEDIATA | 6 | ☐ Pendiente |
| 8B: Ajustes Rápidos | ALTA | 9 | ☐ Pendiente |
| 8C: Estados y UX | ALTA | 13 | ☐ Pendiente |
| 8D: PDF y Documentos | MEDIA | 5 | ☐ Pendiente |
| 8E: Funcionalidades Medianas | MEDIA | 7 | ☐ Pendiente |
| 8F: Funcionalidades Complejas | BAJA | 4 | ☐ Pendiente |
| **Total** | | **44** | |

---

## 📎 Documentos de Referencia

- [Observaciones del cliente 9-3-2026](../Observaciones%20cliente/observaciones%20cliente%209-3-2026)
- [Plan de Trabajo Original (Feb 2026)](PLAN_TRABAJO_FEBRERO_2026.md)
- [Informe de Avances Fases 1-7](INFORME_REPRO_MARZO_2026.md)

---

## ✅ Historial de Cambios

| Fecha | Acción | Detalle |
|-------|--------|---------|
| 2026-03-09 | 6 nuevas observaciones del cliente | Obs. 1: sede en users REPRO + notificación → 8C.11, 8C.12. Obs. 2: adjuntos seguimiento REPRO → 8E.4. Obs. 3: papelería anticipada empresa → 8E.2 actualizada, 8E.3. Obs. 4: dirección evaluado → 8B.7. Obs. 5: modalidad cita → 8C.10. Obs. 6: observaciones por evaluado → 8B.8. Total 37→44 tareas |
| 2026-03-09 | Sede responsable en orden | Agregadas tareas 8C.7-8C.9: sede_id en ordenes, auto-sugerir en programación, filtros por sede |
| 2026-03-09 | Documento creado | Análisis de 27 observaciones del cliente, plan de 37 tareas en 6 subfases |

---

*Documento creado: 9 de marzo de 2026*
*Última actualización: 9 de marzo de 2026*
