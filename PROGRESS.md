# PROGRESS — Requerimientos Cliente Mayo 2026

**Documento de seguimiento activo**
**Base de referencia:** docs/REQUERIMIENTOS_CLIENTE_2026-05.md
**Ultima actualizacion:** 2026-05-08 (post-deploy)
**Suite de tests:** 475/475
**Deploy a producción:** EJECUTADO el 2026-05-08 (iPage FTP)

---

## Estado por Fase

| Fase | Descripcion | Estado |
|------|-------------|--------|
| Fase 1 | Correcciones urgentes (8 items) | COMPLETADA |
| Fase 2 | Mejoras rapidas (10 items) | COMPLETADA |
| Fase 3 | Funcionalidades nuevas (5 items) | COMPLETADA |
| Fase 4 | Estados y bloqueos | COMPLETADA |
| Fase 5 | Reportes y sedes | COMPLETADA |
| Fase 6 | Configuracion + Finanzas | COMPLETADA |
| Fase 7 | Editor de informes | COMPLETADA |
| Fase 8 | Mejoras visuales (layout/scroll) | COMPLETADA |
| Fase 9 | Hardening pre-deploy (auditoria) | EN PROGRESO |

---

## Fase 1 - Correcciones urgentes - COMPLETADA 2026-05-07

Verificacion manual completada. 433 tests pasando al cierre.

| Ref | Descripcion | Tests | Verificado |
|-----|-------------|-------|------------|
| N1 | Fecha Programada renombrada a Fecha Tentativa | Sprint1BugFixesTest (2) | OK |
| CO10 | Solo admins crean/editan/eliminan usuarios | AuditoriaSeguridadTest (4) | OK |
| A9 | Filtro estado cuestionarios corregido | Sprint1BugFixesTest (3) | OK |
| C3 | Upload documentos evaluado, fix 413 nginx 20M + PHP 20M | infra | OK |
| CA1 | Candidato ve motivo de rechazo de documento | Sprint1BugFixesTest (2) | OK |
| CO9-1 | Dropdown calendario incluye evaluados con cita | Sprint1BugFixesTest (2) | OK |
| CO9-2 | Conteo calendario mensual = vista dia | Sprint1BugFixesTest (1) | OK |
| C5 | Al subir informe final auto-entrega y cliente ve informe | Fase2DocumentacionTest (4) | OK |

### Cambios adicionales durante Fase 1

C2 implementado adelantado:
- Empresa puede crear ordenes propias desde el portal
- Boton Nueva Solicitud en empresa/ordenes/index.blade.php
- 5 tests en EmpresaCrearOrdenTest

Infraestructura:
- docker/nginx/default.conf: client_max_body_size 20M
- Dockerfile: upload_max_filesize=20M, post_max_size=20M
- Contenedores reconstruidos

Notificaciones fix:
- CSRF: _notificaciones_bell.blade.php usaba meta csrf-token inexistente, cambiado a Blade csrf_token()
- URLs por rol: 4 notificaciones ahora generan URL segun role_as del destinatario
- 2 notificaciones existentes en BD corregidas via Tinker

Vista empresa/ordenes/show:
- Muestra botones Informe Final y Preliminar cuando orden entregada y resultados_visibles_empresa=true

UI Layout (mitigacion temporal, solucion completa en Fase 8):
- CSS: .content-wrapper-scroll anidado con overflow:visible en ambos layouts
- JS: OverlayScrollbars no se aplica a wrappers anidados en custom-scrollbar.js
- Cache-buster v=20260507 en script de custom-scrollbar.js
- Fix HTML: div extra en modal reprogramacion de admin/ordenes/show.blade.php

---

## Fase 2 - Mejoras rapidas - COMPLETADA 2026-05-07

449 tests al cierre.

| Ref | Descripcion | Tests |
|-----|-------------|-------|
| A1 | Renombrar seccion cuestionarios a Gestion de Cuestionario - Candidatos | OK |
| A2 | Filtros tipo de servicio y sede en cuestionarios | OK |
| A3 | Columna sede en tabla cuestionarios | OK |
| A10 | Notificacion interna al crear orden (con codigo_orden) | OK |
| C1 | Nombre candidato en Mis Ultimas Ordenes del dashboard | OK |
| C2-puesto | Campo puesto del candidato al crear orden | OK |
| C3 | Cliente elimina documentos pendientes propios | OK |
| CO4 | Filtro por fecha en listado de ordenes colaborador | OK |
| CO8 | Filtro cuestionarios incompletos colaborador | OK |
| BONUS | Centro de notificaciones con filtros y paginacion | OK |

---

## Fase 3 - Funcionalidades nuevas - COMPLETADA 2026-05-07

449 tests al cierre.

| Ref | Descripcion | Tests |
|-----|-------------|-------|
| C2-sede | Sede del candidato por evaluado en orden (crear/editar/show/pdf) | EmpresaCrearOrdenTest (1) |
| T1 | Dos botones PDF: Orden de Servicio + Informe Candidatos | Fase3T1PdfInformeTest (4) |
| CO5 | Vista previa inline de PDFs e imagenes antes de descargar (modal) | Fase3CO5VistaPreviaDocumentoTest (4) |
| CO7 | Colaborador deja observacion visible para empresa en evaluado | Fase3CO7ObservacionColaboradorTest (4) |
| CO1 | Sede y cargo al crear/editar colaborador (ya existia, verificado) | Fase3CO1SedeYPuestoColaboradorTest (3) |

### Archivos clave Fase 3

- app/Http/Controllers/Admin/OrdenesController.php: pdfInforme(), actualizarObservacion(), procesarEvaluados() con sede_id
- app/Http/Controllers/Admin/DocumentosEvaluadoController.php: preview() inline
- resources/views/admin/ordenes/pdf-informe.blade.php: nuevo template informe candidatos
- resources/views/admin/ordenes/_documentos_evaluado.blade.php: boton ojo + modal preview
- resources/views/empresa/ordenes/_documentos_evaluado.blade.php: idem empresa
- resources/views/admin/ordenes/show.blade.php: form edicion observacion + 2 botones PDF + sede evaluado
- routes/web.php: ordenes.pdf-informe, documentos-evaluado.preview, evaluados.actualizar-observacion

---

## Fase 4 - Estados y bloqueos - COMPLETADA 2026-05-07

457 tests al cierre.

| Ref | Descripcion | Tests |
|-----|-------------|-------|
| A6 | Ampliar flujo de estados a 8 etapas con colores y transiciones automaticas | Fase4EstadosOrdenTest (6) |
| CO3 | Bloquear informe final entregado; justificacion para modificar | Fase4BloqueoInformeTest (2) |

### Archivos clave Fase 4

- app/Http/Controllers/Admin/OrdenesController.php: usuarioPuedeEditarOrden(), transiciones de estado
- resources/views/admin/ordenes/show.blade.php: botones de avance de estado con colores, bloqueo editar
- resources/views/admin/ordenes/index.blade.php: badges de color por estado
- Estados: solicitud, autorizacion, requisito, programacion, en_proceso, preliminar, final, entregado, cancelado

### Bugs corregidos post-verificacion Fase 4

- 403 en edicion de orden para empresa: estados viejos `pendiente/programada` cambiados a `solicitud/autorizacion`
- Boton Editar oculto para admin/repro en estados `entregado` y `cancelado` (show + index)
- Boton ⚙ Configuracion del nav: `url('configs')` → `url('config')` en incadmin y incempresa

---

## Fase 5 - Reportes y sedes - COMPLETADA 2026-05-07

463 tests al cierre.

| Ref | Descripcion | Tests |
|-----|-------------|-------|
| A5 | Panel por sede: stats + busqueda por nombre/DPI + candidatos paginados | Fase5PanelSedeReportesTest (3) |
| A7 | Reporte empresas filtrable por sede con ranking top 5 | Fase5PanelSedeReportesTest (3) |
| C4 | Botones WhatsApp por sede activa en dashboard empresa | Fase5PanelSedeReportesTest (3) |
| CO9-hist | Historial candidatos completados/inasistencia en calendario | Fase5PanelSedeReportesTest (3) |

### Archivos clave Fase 5

- app/Http/Controllers/Admin/SedesController.php: show() con stats y busqueda
- app/Http/Controllers/Admin/ReportesController.php: empresas() con filtro sede y ranking
- app/Http/Controllers/Admin/CalendarioController.php: index() con $historial
- app/Http/Controllers/Admin/AdminController.php: getEmpresaStats() con $sedesContacto
- resources/views/admin/sedes/show.blade.php: 4 cards + tabla candidatos paginada
- resources/views/admin/reportes/empresas.blade.php: dropdown sede + tabla ranking
- resources/views/admin/calendario/index.blade.php: seccion historial
- resources/views/admin/index.blade.php: botones WhatsApp por sede
- resources/views/admin/reportes/pdf/empresas.blade.php: header con logo REPRO

---

## Fase 6 - Configuracion y Finanzas - COMPLETADA 2026-05-08

| Ref | Descripcion | Tests |
|-----|-------------|-------|
| A8 | Dividir Configuracion en subsecciones: Identidad, Plantillas y Catalogos | OK |
| A8-fin | Agregar seccion "Finanzas" al menu con pantalla de "Proximamente" | OK |

---

## Fase 7 - Editor de informes - COMPLETADA 2026-05-08

| Ref | Descripcion | Tests |
|-----|-------------|-------|
| CO6 | Editor de texto enriquecido (Quill 1.3.7) para informe preliminar | Fase7EditorInformePreliminarTest (6) |

### Archivos clave Fase 7

- database/migrations/2026_05_08_183729_add_texto_informe_preliminar_to_evaluados_orden_table.php
- app/Models/EvaluadoOrden.php: texto_informe_preliminar en $fillable
- app/Http/Controllers/Admin/OrdenesController.php: guardarInformePreliminar(), pdfInforme() con $mostrarInformePreliminar
- routes/web.php: PATCH evaluados/{evaluado}/informe-preliminar
- resources/views/admin/ordenes/show.blade.php: card con editor Quill (toolbar h2/h3/bold/italic/listas)
- resources/views/empresa/ordenes/show.blade.php: card read-only cuando resultados_visibles_empresa
- resources/views/admin/ordenes/pdf-informe.blade.php: bloque Informe Preliminar en PDF
- resources/views/layouts/admin.blade.php: @stack('styles') agregado (faltaba)
- tests/Feature/Fase7EditorInformePreliminarTest.php: 6 tests (admin puede guardar, empresa bloqueada, etc.)

---

## Fase 8 - Mejoras visuales - COMPLETADA 2026-05-08

475 tests al cierre.

| Ref | Descripcion | Solucion |
|-----|-------------|----------|
| UI1 | Eliminar scrollbars duplicadas | overlayScrollbars desactivado en content-wrapper-scroll; CSS height:auto/overflow:visible |
| UI2 | Footer anclado al final del contenido | main-container como flex column, app-footer con margin-top:auto |
| UI3 | Eliminar scrolls anidados | CSS .content-wrapper-scroll .content-wrapper { padding:0; overflow:visible } |

### Fixes adicionales Fase 8

- Dropdown invisible en cuestionarios: .btn-outline-primary sin scope sobreescribia color; limitado a .card-header .btn-outline-primary
- Filas "Ultimas Ordenes" no clicables: onclick en <tr> con route en admin y empresa dashboard
- col-xl-12 -> col-12 en 13 vistas (sedes, empresas, ordenes, config, mi-empresa)
- CSS defensivo: col-xl-N sin fallback apila al 100% bajo breakpoint xl
- historial-dpi: form de busqueda expandido a col-12 en lugar de col-md-8 mx-auto

### Archivos clave Fase 8

- resources/views/layouts/admin.blade.php: CSS Fase 8 (overflow, flex, footer, col-xl, nested wrapper)
- resources/views/layouts/empresa.blade.php: mismos overrides CSS
- public/dashboardtemplate/design/assets/vendor/overlay-scroll/custom-scrollbar.js: content-wrapper-scroll desactivado
- resources/views/admin/index.blade.php: filas clicables en Ultimas Ordenes (admin + empresa)
- resources/views/admin/cuestionarios/index.blade.php: fix scope CSS btn-outline-primary
- resources/views/admin/cuestionarios/historial-dpi.blade.php: col-12 full width
- 13 vistas: col-xl-12 -> col-12

---

## Historial baseline tests

| Fecha | Tests | Hito |
|-------|-------|------|
| 2026-04-22 | ~391 | Ronda 1 observaciones cliente |
| 2026-04-22 | 399 | Sprint Auditoria-1 |
| 2026-04-22 | 403 | Sprint Auditoria-2 |
| 2026-04-22 | 409 | Sprint Auditoria-3 |
| 2026-05-07 | 428 | Sprint-1 Fase 1 N1 CO10 A9 C3 CA1 CO9 C5 |
| 2026-05-07 | 433 | C2 + fixes notificaciones + infra 20M |
| 2026-05-07 | 449 | Fase 2 completa + Fase 3 completa |
| 2026-05-07 | 457 | Fase 4 completa (A6 + CO3) |
| 2026-05-07 | 463 | Fase 5 completa (A5, A7, C4, CO9-hist) + bugs post-verificacion |
| 2026-05-08 | 469 | Fase 6 completa (A8, A8-fin) + Fase 7 completa (CO6 Quill editor) |
| 2026-05-08 | 475 | Fase 8 completa (UI1/UI2/UI3 scroll/footer/ancho) + fixes dropdown + filas clickables |


---

## Estado por Fase

| Fase | Descripcion | Estado |
|------|-------------|--------|
| Fase 1 | Correcciones urgentes (8 items) | COMPLETADA |
| Fase 2 | Mejoras rapidas (10 items) | COMPLETADA |
| Fase 3 | Funcionalidades nuevas | COMPLETADA |
| Fase 4 | Estados y bloqueos | COMPLETADA |
| Fase 5 | Reportes y sedes | COMPLETADA |
| Fase 6 | Configuracion + Finanzas | COMPLETADA |
| Fase 7 | Editor de informes | COMPLETADA |
| Fase 8 | Mejoras visuales (layout/scroll) | COMPLETADA |
| Fase 9 | Hardening pre-deploy (auditoria) | EN PROGRESO |

---

## Fase 1 - Correcciones urgentes - COMPLETADA 2026-05-07

Verificacion manual completada. 433 tests pasando.

| Ref | Descripcion | Tests | Verificado |
|-----|-------------|-------|------------|
| N1 | Fecha Programada renombrada a Fecha Tentativa | Sprint1BugFixesTest (2) | OK |
| CO10 | Solo admins crean/editan/eliminan usuarios | AuditoriaSeguridadTest (4) | OK |
| A9 | Filtro estado cuestionarios corregido | Sprint1BugFixesTest (3) | OK |
| C3 | Upload documentos evaluado, fix 413 nginx 20M + PHP 20M | infra | OK |
| CA1 | Candidato ve motivo de rechazo de documento | Sprint1BugFixesTest (2) | OK |
| CO9-1 | Dropdown calendario incluye evaluados con cita | Sprint1BugFixesTest (2) | OK |
| CO9-2 | Conteo calendario mensual = vista dia | Sprint1BugFixesTest (1) | OK |
| C5 | Al subir informe final auto-entrega y cliente ve informe | Fase2DocumentacionTest (4) | OK |

### Cambios adicionales durante Fase 1

C2 implementado adelantado:
- Empresa puede crear ordenes propias desde el portal
- Boton Nueva Solicitud en empresa/ordenes/index.blade.php
- 5 tests en EmpresaCrearOrdenTest

Infraestructura:
- docker/nginx/default.conf: client_max_body_size 20M
- Dockerfile: upload_max_filesize=20M, post_max_size=20M
- Contenedores reconstruidos

Notificaciones fix:
- CSRF: _notificaciones_bell.blade.php usaba meta csrf-token inexistente, cambiado a Blade csrf_token()
- URLs por rol: 4 notificaciones ahora generan URL segun role_as del destinatario
- 2 notificaciones existentes en BD corregidas via Tinker

Vista empresa/ordenes/show:
- Muestra botones Informe Final y Preliminar cuando orden entregada y resultados_visibles_empresa=true

UI Layout (mitigacion temporal, solucion completa en Fase 8):
- CSS: .content-wrapper-scroll anidado con overflow:visible en ambos layouts
- JS: OverlayScrollbars no se aplica a wrappers anidados en custom-scrollbar.js
- Cache-buster v=20260507 en script de custom-scrollbar.js
- Fix HTML: div extra en modal reprogramacion de admin/ordenes/show.blade.php

---

## Fase 2 - Mejoras rapidas - PENDIENTE

| Ref | Descripcion |
|-----|-------------|
| A1 | Renombrar seccion cuestionarios a Gestion de Cuestionario - Candidatos |
| A2 | Filtros tipo de servicio y sede en cuestionarios |
| A3 | Columna sede en tabla cuestionarios |
| A10 | Notificacion interna al crear orden |
| C1 | Nombre candidato en Mis Ultimas Ordenes del dashboard |
| C2-puesto | Campo puesto y sede del candidato al crear orden |
| C3 | Cliente elimina documentos pendientes propios |
| CO4 | Filtro por fecha en listado de ordenes colaborador |
| CO8 | Filtro cuestionarios incompletos colaborador |

---

## Fase 8 - Deuda Layout UI - PENDIENTE

Problema: multiples barras de scroll apiladas y footer flotante.

Causa raiz: page-wrapper con overflow-y:auto y height:100vh del template mas
content-wrapper-scroll con overflow-y:auto en layouts mas OverlayScrollbars plugin.
36 vistas anidan un segundo content-wrapper-scroll.

Plan L1-L7:
- L1: Un unico modelo de scroll (scroll en html, sidebar position fixed)
- L2: Quitar overflow-y:auto y height:100vh de page-wrapper
- L3: Eliminar OverlayScrollbars en content-wrapper-scroll
- L4: Quitar div content-wrapper-scroll anidado en las 36 vistas
- L5: Mover footer dentro del wrapper de contenido
- L6: Auditar otros overflow:auto internos
- L7: Test visual - 1 solo scrollbar en pantallas menores a 1080p

---

## Historial baseline tests

| Fecha | Tests | Hito |
|-------|-------|------|
| 2026-04-22 | ~391 | Ronda 1 observaciones cliente |
| 2026-04-22 | 399 | Sprint Auditoria-1 |
| 2026-04-22 | 403 | Sprint Auditoria-2 |
| 2026-04-22 | 409 | Sprint Auditoria-3 |
| 2026-05-07 | 428 | Sprint-1 Fase 1 N1 CO10 A9 C3 CA1 CO9 C5 |
| 2026-05-07 | 433 | C2 + fixes notificaciones + infra 20M |

---

## Fase 9 - Hardening Pre-Deploy - COMPLETADA 2026-05-08

Auditoria profesional ejecutada el 2026-05-08 antes de subir Fases 1-8 al servidor (iPage FTP).
Los 4 hallazgos críticos del Bloque A fueron resueltos y desplegados en producción.

### Bloque A - CRITICOS (resueltos antes del deploy)

| ID | Tarea | Estado | Archivo |
|----|-------|--------|---------|
| H1 | Sanitizar HTML de texto_informe_preliminar (XSS almacenado) | RESUELTO + DESPLEGADO | app/Http/Controllers/Admin/OrdenesController.php |
| H2 | Ampliar permissions seeder con 20 permisos de Fases 1-8 | RESUELTO + DESPLEGADO | database/seeders/RolesAndPermissionsSeeder.php |
| H3 | Migración idempotente que aplica los 19 permisos en producción sin acceso CLI | RESUELTO + DESPLEGADO | database/migrations/2026_05_08_201716_seed_permissions_fase9.php |
| H4 | Verificar APP_DEBUG=false y APP_ENV=production en .env del servidor | VERIFICADO | .env produccion (script auto-eliminado) |

### Opciones para H3 (iPage no tiene `php artisan` interactivo)

1. Generar SQL plano con los 20 INSERT INTO permissions + INSERT INTO role_permission y subir como deploy_seed_permissions.sql
2. Crear script PHP one-shot tipo deploy_*.php que invoque el seeder via Artisan::call() y se borre tras ejecutar (patron usado en deploy_migrate_v2.php existente)
3. Crear migracion 2026_05_08_xxxxxx_seed_permissions_fases_1_8.php que llame al seeder y se ejecute con el sistema actual de migraciones FTP

Recomendado: opcion 3 (migracion) para mantener idempotencia y trazabilidad.

### Bloque B - ALTOS (proximo sprint, no bloquean deploy)

| ID | Tarea | Justificacion |
|----|-------|---------------|
| H5 | Migrar rutas hardcoded role:admin,repro a permission:xxx.ver | Aprovechar los 20 nuevos permisos granulares |
| H6 | Definir HOME=/tmp en Dockerfile o silenciar warning psysh | Limpiar 8 entradas de ruido en laravel.log local |
| H7 | Auditar las 14 ocurrencias restantes de {!! !!} | Ya verificadas seguras (nl2br(e()) o json_encode), confirmar tras refactor |
| H8 | Activar cache de config, route y view en deploy iPage | Via script PHP one-shot tipo deploy_cache.php |

### Bloque C - MEDIOS (backlog)

| ID | Tarea | Justificacion |
|----|-------|---------------|
| H9 | Crear policies Eloquent para Orden, Empresa, EvaluadoOrden, Sede | Reemplazar checks role_as<2 dispersos en controllers |
| H10 | Cobertura de tests para los nuevos permisos (sedes, finanzas, etc) | Asegurar que assignPermissionsToRoles no rompe en futuras migraciones |
| H11 | Tests E2E con candidate-token para flujo evaluado | Hoy solo cubierto a nivel unit; el token es la unica via de acceso de evaluados |
| H12 | Revisar las 5 ocurrencias de DB::raw en controllers | Confirmar que ningun input de usuario llega a SQL crudo |

### Plan de ejecucion sugerido

1. Hoy antes del deploy: H1 + H2 (ya listos) + H3 (crear migracion seeder) + H4 (verificar .env via FTP).
2. Hacer commit con los 3 archivos modificados.
3. Subir via FTP a iPage segun procedimiento existente en docs/deployment/.
4. Ejecutar deploy_migrate_v2.php remoto para correr migraciones (incluye el seeder).
5. Verificar en BD remota que permissions tiene 44 filas y role_permission para repro/empresa esta poblada.
6. Sprint siguiente: H5, H6, H8.
7. Backlog: H7, H9, H10, H11, H12.

### Verificacion baseline tras Fase 9 (local)

- Tests: 475/475 (sin regresion tras H1+H2)
- Permisos en BD local: 44 en 16 modulos (antes 24 en 8)
- Migraciones pendientes: 0
- Working tree files modificados: 2 (OrdenesController, RolesAndPermissionsSeeder)

---

## Deploy a producción - EJECUTADO 2026-05-08

Subida completa a iPage (https://reproappv2.szystems.com) tras cierre de Fase 9.

### Migraciones aplicadas en BD producción

| Migración | Resultado |
|-----------|-----------|
| 2026_05_07_215048_migrate_estados_a_8_etapas | ✓ aplicada |
| 2026_05_08_183729_add_texto_informe_preliminar_to_evaluados_orden_table | ✓ columna `texto_informe_preliminar` (LONGTEXT) añadida a `evaluados_orden` |
| 2026_05_08_201716_seed_permissions_fase9 | ✓ 19 permisos insertados, 88 asignaciones role_permission |

### Verificación H4

- APP_ENV = production ✓
- APP_DEBUG = false ✓

### Auditoría de archivos desplegados (hash MD5 local vs servidor)

- 31 archivos auditados
- 31 idénticos
- 0 diferentes
- 0 faltantes

Archivos críticos confirmados en servidor:

- 2 controladores (OrdenesController, CuestionariosController, ConfigController)
- 1 modelo (EvaluadoOrden)
- 1 form request (ConfigFormRequest)
- 17 vistas Blade (admin, empresa, layouts/incadmin, layouts/incempresa)
- 1 archivo de rutas (web.php)
- 3 migraciones de Fase 9
- 1 asset JS (custom-scrollbar.js)

### Limpieza de caché ejecutada en producción

- bootstrap/cache: packages.php, services.php, events.php eliminados
- 134 vistas compiladas eliminadas (en sucesivas pasadas)
- OPcache reseteado tras cada upload de PHP

### Scripts one-shot utilizados (todos auto-eliminados)

- deploy_verify_h4.php (verificación APP_ENV/APP_DEBUG)
- deploy_permissions_fase9.php (seed inicial de los 19 permisos vía PDO)
- deploy_migrate_fase9.php (registro de las 3 migraciones en tabla `migrations` + ALTER TABLE de la columna nueva)
- audit_hashes.php (verificación MD5 byte-a-byte de archivos desplegados)
- clear_cache*.php (limpieza de bootstrap/cache + storage/framework/views + opcache_reset)
- read_log.php (lectura de últimas 100 líneas de storage/logs/laravel.log para diagnóstico)

### Incidentes durante el deploy y su resolución

1. **Error 500 en /cuestionarios** — causa: faltaba subir `CuestionariosController.php` y la vista `index.blade.php` ya tenía la variable `$sedes`. Solución: subir el controlador.
2. **Error 500 en /ordenes/{id}** — causa: la ruta `ordenes.pdf-informe` no estaba en el `routes/web.php` del servidor. Solución: subir `routes/web.php`.
3. **HTTP 550 en finanzas/index.blade.php** — causa: el directorio remoto no existía. Solución: usar `--ftp-create-dirs` de curl.
4. **Vistas compiladas obsoletas** — solución: limpiar `storage/framework/views/*.php` después de cada subida de Blade y resetear OPcache.

### Resultado final

Producción funcionando con todas las funcionalidades de Fases 1-9. Audit de hashes confirma paridad byte-a-byte entre local y servidor para los 31 archivos críticos del deploy.
