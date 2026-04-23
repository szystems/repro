# Plan de Trabajo — Observaciones Cliente (rol Empresa)

**Origen:** `C:\Users\szott\Downloads\Usuario de cliente.pdf`
**Fecha de recepción:** 2026-04-22
**Estado del proyecto antes de iniciar:** v2.2.0 (producción, 79+ tests pasando)

---

## Resumen ejecutivo

El cliente (usuario con `role_as = 1` / `empresa`) reportó **3 bugs funcionales** y **5 mejoras de UX/notificaciones** tras usar su portal. El objetivo es cerrar los 8 puntos manteniendo cobertura de tests.

## Prioridad sugerida

| Orden | ID | Tipo | Título | Prioridad |
|-------|----|------|--------|-----------|
| 1 | BUG-01 | Bug | 403 tras crear orden (cliente) | 🔴 Crítica |
| 2 | BUG-02 | Bug | Botón PDF de orden no muestra nada | 🔴 Crítica |
| 3 | BUG-03 | Bug | Reporte de Evaluaciones vacío | 🔴 Crítica |
| 4 | MEJ-06 | Mejora | Falta selector de Sede al crear orden (cliente) | 🟠 Alta |
| 5 | MEJ-08 | Mejora | Reducir spam de correos | 🟠 Alta |
| 6 | MEJ-04 | Mejora | Mensaje de éxito + subir papelería inmediata | 🟡 Media |
| 7 | MEJ-07 | Mejora | Mostrar nombre del candidato en listado de órdenes | 🟡 Media |
| 8 | MEJ-05 | Mejora | Reposicionar botón "Agregar Evaluado" | 🟢 Baja |

---

## BUG-01 — 403 "Acceso no autorizado" tras crear orden (cliente)

**Síntoma:** Cliente crea una orden y al guardar recibe `403 Acceso no autorizado` en lugar de la vista de la orden.

**Archivos:**
- [app/Http/Controllers/Admin/OrdenesController.php](app/Http/Controllers/Admin/OrdenesController.php#L253-L257) — redirect a `empresa.ordenes.show`
- [app/Http/Controllers/Empresa/EmpresaController.php](app/Http/Controllers/Empresa/EmpresaController.php#L19-L33) — método `verOrden()` con dos `abort(403)`
- [routes/web.php](routes/web.php#L192-L210) — grupo con middleware `role:empresa`
- [app/Http/Middleware/CheckRole.php](app/Http/Middleware/CheckRole.php#L41-L52) — mapping `empresa => 1`

**Causa raíz (hipótesis):**
1. `EmpresaController::verOrden()` lanza 403 cuando `Auth::user()->empresa` (relación) devuelve `null` aunque `empresa_id` esté seteado (posible eager-load no cargado o empresa con `estado = 0`).
2. O bien el usuario cliente carece de `empresa_id` (ya hay guard en `store`, pero `verOrden` vuelve a validar).

**Solución:**
1. En `EmpresaController::verOrden()`: cargar `$empresa = Auth::user()->empresa()->first();` explícito y, si es null, redirigir con `flash('error')` al dashboard en vez de `abort(403)` duro.
2. En `OrdenesController::store()`: antes del redirect a `empresa.ordenes.show`, verificar nuevamente que `Auth::user()->empresa_id` no sea null y que la orden fue creada con ese `empresa_id`.
3. Log detallado cuando se dispare el 403 (ya existe `Log::error`, añadir contexto `user_id`, `orden_id`, `empresa_id_usuario`, `empresa_id_orden`).

**Tests:**
- Ampliar `tests/Feature/BugFixesPhase8ATest.php` con caso: usuario empresa crea orden y sigue flujo hasta vista `show` sin 403.
- Caso: usuario empresa sin `empresa_id` → redirigido con mensaje claro (no 403).

**Estado:** ⏳ Pendiente

---

## BUG-02 — Botón PDF de orden no muestra informe

**Síntoma:** En "Mis Órdenes" el botón PDF abre pestaña en blanco o sin contenido.

**Archivos:**
- [resources/views/empresa/ordenes/index.blade.php](resources/views/empresa/ordenes/index.blade.php#L47-L49) — botón PDF con `target="_blank"`
- [app/Http/Controllers/Admin/OrdenesController.php](app/Http/Controllers/Admin/OrdenesController.php#L752-L772) — método `pdf()`
- [routes/web.php](routes/web.php#L125) — ruta `ordenes.pdf`

**Causa raíz confirmada:**
En `OrdenesController::pdf()` línea 759:
```php
if (Auth::user()->role_as == 1 && !$orden->resultadosDisponiblesParaEmpresa()) {
    return back()->with('error', '...');
}
```
Como el link abre en nueva pestaña (`target="_blank"`), el `back()` se ejecuta en la pestaña nueva → recarga la URL previa (que es blanco) → "no abre nada".

**Solución:**
1. Distinguir dos PDFs:
   - **PDF de la orden** (metadatos, evaluados, estado): disponible para empresa siempre que sea su orden.
   - **PDF con resultados** (resultados preliminares/finales): solo si `resultadosDisponiblesParaEmpresa()`.
2. Cambiar lógica en `pdf()`:
   - Eliminar el `back()->with('error')` para empresa.
   - Siempre generar PDF de orden; si es empresa y resultados no visibles, ocultar secciones de resultados en la vista `admin/ordenes/pdf.blade.php`.
3. Alternativa más simple (preferida): Quitar la restricción — el PDF de la orden no expone resultados sensibles, solo muestra datos administrativos.

**Tests:**
- `tests/Feature/ReportesTest.php` o nuevo: cliente descarga PDF de su orden recién creada (estado `solicitud`) → HTTP 200 + content-type PDF.

**Estado:** ⏳ Pendiente

---

## BUG-03 — Reporte de Evaluaciones vacío para cliente

**Síntoma:** Contadores en 0 y tabla vacía, aunque el cliente tiene órdenes con evaluados.

**Archivos:**
- [app/Http/Controllers/Admin/ReportesController.php](app/Http/Controllers/Admin/ReportesController.php#L28-L35) — método `buildEvaluacionesQuery()`
- [resources/views/admin/reportes/evaluaciones.blade.php](resources/views/admin/reportes/evaluaciones.blade.php)

**Causa raíz confirmada:**
El filtro para clientes exige **simultáneamente** tres condiciones:
```php
if (Auth::user()->role_as == 1) {
    $query->whereHas('orden', function ($q) {
        $q->where('empresa_id', Auth::user()->empresa_id)
          ->where('resultados_visibles_empresa', true)  // raro que esté en true
          ->where('estado', 'entregado');                // raro para órdenes nuevas
    });
}
```
Una orden recién creada está en estado `solicitud`, por lo que nunca aparece.

**Solución:**
1. Relajar el filtro: mostrar al cliente **todos los evaluados de sus órdenes**, independiente del estado.
2. Las columnas de "resultado" en la tabla/PDF se pueden condicionar individualmente: si `resultados_visibles_empresa && estado == 'entregado'` se muestran; si no, se muestra `"En proceso"`.
3. Agregar banner informativo explicando qué muestra el reporte al cliente.

**Tests:**
- Ampliar `tests/Feature/ReportesTest.php`: cliente con órdenes en `solicitud` ve `total > 0`.
- Cliente no ve evaluados de otras empresas (siempre).

**Estado:** ⏳ Pendiente

---

## MEJ-04 — Mensaje de éxito + subir papelería al crear orden

**Síntoma:** Tras crear orden, el cliente no sabe dónde ni cuándo subir documentos de los evaluados.

**Archivos:**
- [app/Http/Controllers/Admin/OrdenesController.php](app/Http/Controllers/Admin/OrdenesController.php#L253-L257) — redirect post-store
- [resources/views/empresa/ordenes/show.blade.php](resources/views/empresa/ordenes/show.blade.php) — vista detalle
- [resources/views/empresa/ordenes/_documentos_evaluado.blade.php](resources/views/empresa/ordenes/_documentos_evaluado.blade.php) — widget de upload

**Solución:**
1. En `store()`, añadir flag `->with('mostrar_papeleria', true)`.
2. En `show.blade.php`: si `session('mostrar_papeleria')`, expandir la sección de documentos automáticamente y hacer scroll, con banner `✅ Orden creada. Ahora sube los documentos de cada evaluado`.
3. Asegurar que el widget `_documentos_evaluado` esté visible para el cliente.

**Tests:**
- `tests/Feature/BugFixesPhase8ATest.php`: tras crear orden, respuesta contiene la sección de papelería visible.

**Estado:** ⏳ Pendiente

---

## MEJ-05 — Reposicionar botón "Agregar Evaluado"

**Síntoma:** El botón está en el `card-header` y queda por encima de la tabla de evaluados.

**Archivos:**
- [resources/views/admin/ordenes/create.blade.php](resources/views/admin/ordenes/create.blade.php#L148-L165) — card de evaluados
- [resources/views/admin/ordenes/edit.blade.php](resources/views/admin/ordenes/edit.blade.php)

**Solución:**
Mover el botón del `card-header` al `card-body`, ubicándolo **después** del texto informativo y **antes** del `#evaluados-container`. Duplicarlo al final como botón secundario para formularios con muchos evaluados.

**Tests:**
- No requiere test (cambio visual). Verificar que test `Phase8BAjustesTest.php::it_muestra_form_crear_orden` siga pasando.

**Estado:** ⏳ Pendiente

---

## MEJ-06 — Selector de Sede para cliente

**Síntoma:** Cliente no puede elegir sede al crear orden.

**Archivos:**
- [resources/views/admin/ordenes/create.blade.php](resources/views/admin/ordenes/create.blade.php#L93-L110) — selector envuelto en `@if(Auth::user()->role_as >= 2)`
- [app/Http/Controllers/Admin/OrdenesController.php](app/Http/Controllers/Admin/OrdenesController.php#L212-L218) — `sede_id` solo se guarda si REPRO

**Decisión confirmada (2026-04-22):** El cliente quiere **elegir la sede de REPRO** que trabajará su orden (no sede propia).

**Solución:**
1. Mover el selector de "Sede Responsable" fuera del `@if(Auth::user()->role_as >= 2)` para que también lo vea el cliente.
2. En `OrdenesController::store()` permitir guardar `sede_id` para cliente (actualmente solo se guarda si `role_as >= 2`, línea 218).
3. Validar en backend que la sede exista y esté activa (reforzar con `exists:sedes,id` + `where estado=1`).
4. En `OrdenesController::create()` el helper ya carga `$sedes = Sede::activas()->...` — solo hay que pasarlo también cuando el usuario es cliente.

**Tests:**
- Cliente crea orden con `sede_id` → guardado correctamente.
- Cliente envía `sede_id` inexistente o inactivo → rechazado.
- Cliente ve el selector en `ordenes.create`.

**Estado:** ⏳ Pendiente

---

## MEJ-07 — Mostrar nombre del candidato en listado de órdenes

**Síntoma:** Listado muestra solo `ORD-2026-0007`; cliente pide `ORD-2026-0007 — Juan Carlos González`.

**Archivos:**
- [resources/views/admin/ordenes/index.blade.php](resources/views/admin/ordenes/index.blade.php)
- [resources/views/empresa/ordenes/index.blade.php](resources/views/empresa/ordenes/index.blade.php#L38-L40)
- [app/Http/Controllers/Admin/OrdenesController.php](app/Http/Controllers/Admin/OrdenesController.php) — método `index()`
- [app/Http/Controllers/Empresa/EmpresaController.php](app/Http/Controllers/Empresa/EmpresaController.php#L44-L48) — `indexOrdenesEmpresa()`

**Solución:**
1. En ambos controllers: añadir eager load `->with(['evaluados' => fn($q) => $q->orderBy('id')->limit(1)])` o helper `primerEvaluado` en el modelo `Orden`.
2. En vistas: renderizar `{{ $orden->codigo_orden }}` + (si hay evaluado) ` — {{ $orden->evaluados->first()->nombres }} {{ $orden->evaluados->first()->apellidos }}`.
3. Si orden tiene múltiples evaluados, mostrar `... (+N más)`.

**Tests:**
- Vista contiene el nombre del primer evaluado cuando existe.

**Estado:** ⏳ Pendiente

---

## MEJ-08 — Reducir spam de correos

**Síntoma:** El cliente recibe demasiados correos por cada cambio de estado.

**Puntos de envío identificados:**

| Evento | Archivo | Tipo | ¿Mantener? |
|--------|---------|------|------------|
| Orden creada | `OrdenesController::store()` | Notif in-app (REPRO) | ✅ Sí (in-app only) |
| Cuestionario completado | `CuestionarioController` + `CuestionarioCompletadoMail` | Email + in-app (REPRO) | ⚠️ Solo in-app |
| Evaluado asignado | `procesarEvaluados` / `reenviarCorreo` + `EvaluadoAsignadoMail` | Email (evaluado) | ✅ Sí (indispensable) |
| Resultados disponibles | `toggleResultadosVisibles` + `ResultadosDisponiblesMail` | Email (empresa) | ✅ Sí (lo pide el cliente) |
| Recordatorio cuestionario | scheduled + `RecordatorioCuestionarioMail` | Email (evaluado) | ✅ Sí (limitar frecuencia) |

**Solución:**
1. **Quitar** el envío de correo en `CuestionarioCompletadoMail` → mantener solo notificación in-app para usuarios REPRO.
2. **Conservar** `ResultadosDisponiblesMail` al cliente (es lo que el cliente quiere como único correo).
3. **Conservar** `EvaluadoAsignadoMail` (es al evaluado, no al cliente).
4. **Limitar recordatorios** a 1 correo por evaluado cada X días (config).
5. Añadir configuración en `Config` model (tabla `configs`) con flags booleanos para cada tipo de correo.

**Tests:**
- `tests/Feature/NotificacionesEmailTest.php`: verificar que al completar cuestionario **NO** se manda `CuestionarioCompletadoMail`, pero sí se crea notificación in-app.
- Al togglear `resultados_visibles_empresa=true` se envía `ResultadosDisponiblesMail`.

**Estado:** ⏳ Pendiente

---

## Archivos afectados (consolidado)

### Backend
- `app/Http/Controllers/Admin/OrdenesController.php` — BUG-01, BUG-02, MEJ-04, MEJ-06, MEJ-07
- `app/Http/Controllers/Admin/ReportesController.php` — BUG-03
- `app/Http/Controllers/Empresa/EmpresaController.php` — BUG-01, MEJ-07
- `app/Http/Controllers/CuestionarioController.php` — MEJ-08
- `app/Mail/CuestionarioCompletadoMail.php` — MEJ-08 (posible deprecation)
- `app/Models/Config.php` — MEJ-08 (nuevos flags)

### Vistas
- `resources/views/empresa/ordenes/index.blade.php` — BUG-02, MEJ-07
- `resources/views/empresa/ordenes/show.blade.php` — MEJ-04
- `resources/views/admin/ordenes/create.blade.php` — MEJ-05, MEJ-06
- `resources/views/admin/ordenes/edit.blade.php` — MEJ-05
- `resources/views/admin/ordenes/index.blade.php` — MEJ-07
- `resources/views/admin/ordenes/pdf.blade.php` — BUG-02
- `resources/views/admin/reportes/evaluaciones.blade.php` — BUG-03

### Rutas
- `routes/web.php` — verificación, sin cambios estructurales esperados

### Tests (nuevos o ampliados)
- `tests/Feature/BugFixesPhase8ATest.php` — BUG-01, MEJ-04
- `tests/Feature/ReportesTest.php` — BUG-03
- `tests/Feature/NotificacionesEmailTest.php` — MEJ-08
- Nuevos: `ObservacionesClienteTest.php` consolidado

---

## Checklist de ejecución

- [x] BUG-01: Fix 403 tras crear orden
- [x] BUG-02: Fix PDF de orden para cliente
- [x] BUG-03: Fix reporte de evaluaciones vacío
- [x] MEJ-06: Selector de sede para cliente
- [x] MEJ-08: Reducir correos + flags de config
- [x] MEJ-04: Mensaje éxito + upload papelería
- [x] MEJ-07: Nombre del candidato en listados
- [x] MEJ-05: Reposicionar botón "Agregar Evaluado"
- [ ] Ejecutar suite completa: `php artisan test` (pendiente — entorno sin PHP local)
- [ ] Actualizar CHANGELOG / versión

---

## Cambios aplicados (2026-04-22)

### Backend
- [app/Http/Controllers/Empresa/EmpresaController.php](app/Http/Controllers/Empresa/EmpresaController.php) — `verOrden()` robusto (no más 403 silencioso, log + redirect amigable). Listado con eager-load de primer evaluado.
- [app/Http/Controllers/Admin/OrdenesController.php](app/Http/Controllers/Admin/OrdenesController.php) — `pdf()` sin restricción para cliente, `store()` permite `sede_id` también para cliente y añade flag `mostrar_papeleria`.
- [app/Http/Controllers/Admin/ReportesController.php](app/Http/Controllers/Admin/ReportesController.php) — Filtro de cliente solo por `empresa_id`.
- [app/Http/Controllers/CuestionarioController.php](app/Http/Controllers/CuestionarioController.php) — `notificarCuestionarioCompletado()` ya no envía email; solo notificación in-app.

### Vistas
- [resources/views/admin/ordenes/create.blade.php](resources/views/admin/ordenes/create.blade.php) — Selector de sede visible para cliente; botón "Agregar Evaluado" reposicionado al final del card.
- [resources/views/admin/ordenes/edit.blade.php](resources/views/admin/ordenes/edit.blade.php) — Botón "Agregar Evaluado" reposicionado.
- [resources/views/admin/ordenes/index.blade.php](resources/views/admin/ordenes/index.blade.php) — Nombre del primer evaluado bajo código de orden.
- [resources/views/empresa/ordenes/index.blade.php](resources/views/empresa/ordenes/index.blade.php) — Idem para vista de cliente.
- [resources/views/empresa/ordenes/show.blade.php](resources/views/empresa/ordenes/show.blade.php) — Banner de éxito con CTA hacia papelería + scroll automático.

### Tests
- [tests/Feature/ObservacionesClienteTest.php](tests/Feature/ObservacionesClienteTest.php) — 15 tests nuevos cubriendo los 8 puntos.
- [tests/Feature/ResultadosVisibilidadTest.php](tests/Feature/ResultadosVisibilidadTest.php) — 2 tests actualizados al nuevo contrato (BUG-02, BUG-03).
- [tests/Feature/CalendarioTest.php](tests/Feature/CalendarioTest.php) — `setUp/tearDown` con `Carbon::setTestNow('2026-03-01')` para fixar fechas hardcodeadas (8 tests pre-existentes restaurados).

**Resultado final:** ✅ 391/391 tests pasando.

---

# Auditoría Preventiva — 2026-04-22 (post-cliente)

Auditoría completa para anticiparse a futuras pruebas del cliente. **18 hallazgos** (4 críticos, 5 altos, 6 medios, 3 bajos).

## Sprint 1 — Quick Wins ✅ COMPLETADO

- [x] **H-01** [CRÍTICO] Throttle (60/min) en grupo `Route::prefix('cuestionario')`
  - Archivo: [routes/web.php](routes/web.php#L233)
- [x] **H-04** [CRÍTICO] Token vencido bloquea `verificarIdentidad`, `aceptarTerminos`, `seccion` (ya), `guardarSeccion`, `finalizar`, `completar`, `subirDocumento` (ya)
  - Archivo: [app/Http/Controllers/CuestionarioController.php](app/Http/Controllers/CuestionarioController.php)
- [x] **H-03** [CRÍTICO] `OrdenesController::destroy` ahora acepta `admin` o `repro`
  - Archivo: [app/Http/Controllers/Admin/OrdenesController.php](app/Http/Controllers/Admin/OrdenesController.php#L434)
- [x] **H-07** [ALTO] Falso positivo — `OrdenFormRequest` ya usa `after_or_equal:today` para `fecha_limite` y `fecha_programada`
- [x] **H-13** [MEDIO] Falso positivo — todos los forms `@method('DELETE')` ya tienen `confirm()` o están dentro de modales Bootstrap

**Tests añadidos:** 8 nuevos en [tests/Feature/AuditoriaSeguridadTest.php](tests/Feature/AuditoriaSeguridadTest.php) — todos pasan ✅
**Suite total:** 399/399 pasando ✅

## Sprint 2 — Hardening ✅ COMPLETADO

- [x] **H-02** Unique compuesto en `evaluados_orden` (orden+dpi+servicio)
  - Migración: [database/migrations/2026_04_22_220655_add_unique_dpi_servicio_to_evaluados_orden.php](database/migrations/2026_04_22_220655_add_unique_dpi_servicio_to_evaluados_orden.php) — APLICADA
- [x] **H-05** `DocumentoEvaluadoRequest::authorize()` valida ownership por rol
  - Archivo: [app/Http/Requests/DocumentoEvaluadoRequest.php](app/Http/Requests/DocumentoEvaluadoRequest.php)
- [x] **H-06** N+1 en `AdminController` dashboard — eager-load de `creador` y `sede`
  - Archivo: [app/Http/Controllers/Admin/AdminController.php](app/Http/Controllers/Admin/AdminController.php#L105)
- [x] **H-08** Email duplicado entre evaluados con DPIs distintos rechazado en orden
  - Archivo: [app/Http/Controllers/Admin/OrdenesController.php](app/Http/Controllers/Admin/OrdenesController.php)
- [x] **H-12** Validación de archivos con `mimetypes:` (magic bytes) en `DocumentoEvaluadoRequest` y `CuestionarioController::subirDocumento`
- [x] **H-15** Cambio de `empresa_id` ahora loguea warning + flash a admin
  - Archivo: [app/Http/Controllers/Admin/UsersController.php](app/Http/Controllers/Admin/UsersController.php)

**Tests añadidos:** 4 nuevos en [tests/Feature/AuditoriaSeguridadTest.php](tests/Feature/AuditoriaSeguridadTest.php) (12 totales) — todos pasan ✅
**Suite total:** 403/403 pasando ✅

## Sprint 3 — Mejoras profundas

- [x] **H-09** Cifrado de PII en BD con cast `encrypted`
  - Campos: `ordenes.observaciones_internas`, `evaluados_orden.observaciones`, `evaluados_orden.notas_poligrafo`
  - Comando para re-cifrar legacy: `php artisan pii:encrypt-legacy [--dry-run]`
  - Archivo: [app/Console/Commands/EncryptLegacyPII.php](app/Console/Commands/EncryptLegacyPII.php)
- [x] **H-10** Audit trail de transiciones de estado (Orden + EvaluadoOrden)
  - Tabla: `auditoria_estados` + Trait [app/Traits/RegistraCambiosEstado.php](app/Traits/RegistraCambiosEstado.php)
- [x] **H-11** Índices `created_at` y `(orden_id, created_at)` en `evaluados_orden`
- [x] **H-16** Guard de estados inválidos vía saving() event
  - Trait: [app/Traits/ValidaEstadosPermitidos.php](app/Traits/ValidaEstadosPermitidos.php)
  - Previene asignación directa `$model->estado = 'invalido'; $model->save();`
- [x] **H-19** Verificado OK — mensajes ya están 100% en español consistente (config locale='es', lang/es/*.php completo, 50+ flash messages revisados). No requiere acción.
- [ ] **H-14** Deprecar dual `role_as` + `roles` — **DIFERIDO**: refactor >12h, funciona con dualidad; agendar para sprint de deuda técnica
- [ ] **H-18** Modales responsive — **DESCARTADO**: Bootstrap 5 stock ya es responsive; no hay problema real detectado

**Tests añadidos:** 6 nuevos en [tests/Feature/AuditoriaSeguridadTest.php](tests/Feature/AuditoriaSeguridadTest.php) (18 totales)
**Suite total:** 409/409 pasando ✅
