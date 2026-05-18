# PROGRESS — Requerimientos Cliente Mayo 2026

**Documento de seguimiento activo**
**Base de referencia:** docs/REQUERIMIENTOS_CLIENTE_2026-05.md
**Ultima actualizacion:** 2026-05-18 (nueva ronda observaciones cliente)
**Suite de tests:** 475/475
**Deploy a producción:** EJECUTADO el 2026-05-08 (iPage FTP)
**Ultimo archivo subido post-deploy:** resources/views/admin/cuestionarios/index.blade.php (2026-05-13, 36809 bytes verificados)

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
| Fase 9 | Hardening pre-deploy (auditoria) | COMPLETADA |
| Fase 10 | Correcciones rapidas 2a ronda | PENDIENTE |
| Fase 11 | Auto-estados por acciones | PENDIENTE |
| Fase 12 | Campo Sede/Region del evaluado | PENDIENTE |
| Fase 13 | Mejoras dashboard y WhatsApp | PENDIENTE |
| Fase 14 | Configuracion ampliada | PENDIENTE |
| Fase 15 | Auditoria de permisos por rol | PENDIENTE |

---

## NUEVA RONDA — Observaciones Cliente 2026-05-18

**12 requerimientos registrados. Analisis quirurgico realizado el 2026-05-18.**

### Mapa de requerimientos → Fases

| Ref | Descripcion resumida | Fase | Prioridad | Complejidad |
|-----|----------------------|------|-----------|-------------|
| R1 | Auto-cambio de estados por acciones | Fase 11 | ALTA | ALTA |
| R2 | Top empresas en dashboard admin | Fase 13 | MEDIA | BAJA |
| R3 | Configuracion ampliada in-app | Fase 14 | MEDIA | ALTA |
| R4 | Campo Sede/Region del evaluado | Fase 12 | ALTA | MEDIA |
| R5 | Auto-liberar informe al subirlo | Fase 10 | ALTA | MEDIA |
| R6 | WhatsApp dropdown de sedes | Fase 13 | MEDIA | MEDIA |
| R7 | Diferenciar preliminar vs final en cliente | Fase 10 | MEDIA | BAJA |
| R8 | Notificaciones con info y redireccion correcta | Fase 10 | ALTA | MEDIA |
| R9 | Quitar fecha tentativa en cliente | Fase 10 | ALTA | BAJA |
| R10 | Renombrar editor informe preliminar | Fase 10 | BAJA | MUY BAJA |
| R11 | Auditoria restricciones por rol | Fase 15 | ALTA | ALTA |
| R12 | Layout vistas cliente empresa (scroll/footer) | Fase 10 | ALTA | MEDIA |

---

## ANALISIS DETALLADO POR REQUERIMIENTO

### R1 — Auto-cambio de estados por acciones (Fase 11)

**Solicitud del cliente:** Los estados deben cambiar automáticamente al ejecutar ciertas acciones. Flujo deseado:
`Solicitud → Link Enviado → Llenando Formulario → Formulario Recibido → Programado → En Proceso → Resultado Preliminar (opcional) → Informe Completo Entregado`

**Estado actual del sistema:**
- Existen 14 estados en `estado_evaluacion`: pendiente, contactando, contactado, link_enviado, confirmado, programado, en_sede, docs_pendientes, en_proceso, completado, inasistencia, reprogramado, cancelado, desistio
- El estado_formulario tiene su propio ciclo: pendiente → link_enviado → en_progreso → completado

**Desfase identificado entre estados del cliente y del sistema:**

| Estado que describe el cliente | Estado actual del sistema | Accion que debe dispararlo |
|-------------------------------|--------------------------|---------------------------|
| Solicitud | pendiente (al crear evaluado) | Creación de la orden/evaluado |
| Link Enviado | link_enviado | Cuando admin envía el token/link |
| Llenando Formulario | (no existe → agregar) | Cuando candidato abre el form por primera vez |
| Formulario Recibido | (mapeado a: cuestionario_completado=true) | Auto: cuando candidato completa cuestionario |
| Programado | programado | Auto: cuando admin asigna fecha_programada |
| En Proceso | en_proceso | Manual: admin marca como en proceso |
| Resultado Preliminar | (no existe como estado separado claramente visible) | Auto: cuando se sube archivo_resultado_preliminar |
| Informe Completo Entregado | completado | Auto: cuando se sube archivo_resultado_final (ya parcial) |

**Acciones que YA disparan cambio de estado (verificadas en código):**
- Programar cita → `programado` (en programarEvaluacion())
- Subir informe final → `completado` + `resultados_visibles_empresa=true` (en OrdenesController ~línea 957)
- Completar cuestionario → `cuestionario_completado=true` pero NO cambia `estado_evaluacion` automáticamente

**Acciones que NO disparan cambio (gaps a corregir):**
- Enviar link → no cambia `estado_evaluacion` a `link_enviado`
- Candidato abre formulario → no hay estado "llenando_formulario"
- Candidato completa cuestionario → no avanza `estado_evaluacion` a "formulario_recibido"
- Subir informe preliminar → no cambia estado automáticamente a estado visible

**Plan de implementación Fase 11:**
1. Renombrar/simplificar estados para que coincidan con lo que ve el cliente (o agregar un accessor de mapeo)
2. En el controlador de envío de link: auto-set `estado_evaluacion = 'link_enviado'`
3. En CuestionariosController (cuando candidato accede): auto-set `estado_evaluacion = 'en_progreso'` si era `link_enviado`
4. En completarCuestionario(): auto-set `estado_evaluacion = 'docs_pendientes'` o similar "formulario_recibido"
5. Al subir preliminar: auto-set `estado_evaluacion = 'preliminar'` (agregar este estado o reusar uno)
6. Al subir final: ya funciona → `completado`
7. Lógica de skip: si se sube final sin preliminar → saltar directo a `completado` (ya existe parcialmente)
8. Colores por estado: auditar que todos los estados tengan colores apropiados en las vistas

**Archivos a modificar:**
- app/Models/EvaluadoOrden.php: estados, transiciones, colores
- app/Http/Controllers/Admin/OrdenesController.php: guardarInformePreliminar(), subirResultado()
- app/Http/Controllers/CuestionariosController.php: completarCuestionario() o show()
- Vistas: badges de color en admin y empresa ordenes

---

### R2 — Top empresas en dashboard admin (Fase 13)

**Solicitud:** Estadística "Top empresas" con número de procesos enviados en el dashboard de admin.

**Estado actual:** El dashboard admin (resources/views/admin/index.blade.php, 753 líneas) ya tiene stats generales, WhatsApp por sedes, últimas órdenes. No tiene ranking de empresas.

**Plan:**
1. En AdminController@index: agregar query `Orden::query()->groupBy('empresa_id')->withCount('evaluados')->orderByDesc('count')->take(5)->with('empresa')`
2. En admin/index.blade.php: agregar card "Top Empresas" con tabla de ranking
3. Test: verificar que aparece la empresa con más órdenes primero

**Archivos:** app/Http/Controllers/Admin/AdminController.php, resources/views/admin/index.blade.php

---

### R3 — Configuracion ampliada in-app (Fase 14)

**Solicitud:** Agregar más opciones al módulo de Configuración para que cambios se puedan hacer desde la UI sin tocar código.

**Estado actual del Config model:** logo, email, time_zone, currency, currency_simbol, currency_iso, fb_link, inst_link, yt_link, wapp_link, descuento_maximo, impuesto

**Configuraciones candidatas a agregar (análisis del código):**
- `dias_vigencia_token` — actualmente hardcoded en 30 días en EvaluadoOrden::generarToken()
- `max_intentos_acceso` — actualmente sin límite visible
- `texto_bienvenida_candidato` — texto de bienvenida en el formulario del candidato
- `mensaje_resultados_bloqueados` — mensaje cuando empresa trata de ver resultados no disponibles
- `habilitar_informe_preliminar` — toggle para habilitar/deshabilitar el paso de preliminar por defecto
- `notificaciones_activas` — toggle para habilitar notificaciones internas
- `nombre_empresa` — nombre comercial de REPRO para mostrar en PDFs y notificaciones
- `telefono_contacto` — teléfono general REPRO
- `direccion` — dirección de REPRO

**Plan:**
1. Migración para agregar columnas al configs
2. Actualizar Config model (fillable)
3. Actualizar ConfigFormRequest (reglas de validación)
4. Actualizar vistas de configuración (pestañas existentes + nuevos campos)
5. Usar las configs en el código donde están hardcodeadas
6. Tests de actualización de las nuevas configs

**Archivos:** database/migrations/nueva, app/Models/Config.php, app/Http/Requests/ConfigFormRequest.php, resources/views/admin/config/

---

### R4 — Campo Sede/Region del evaluado (Fase 12)

**Solicitud:** Agregar campo "Sede/Región de la empresa" en la info del evaluado. Diferente a la sede de REPRO donde se realiza la evaluación. Es la sede de la empresa cliente a la que pertenece el candidato (ej: "Regional Norte", "Sucursal Centro").

**Estado actual:** Existe `sede_id` en evaluados_orden → es la sede de REPRO donde se hace la evaluación. El campo que pide el cliente es la sede/región de la empresa del candidato, un campo de texto libre o referencia.

**Decisión de diseño:** Campo de texto libre `sede_region_empresa` (no FK) porque las regiones del cliente empresa son arbitrarias y cambian.

**Plan:**
1. Migración: `ALTER TABLE evaluados_orden ADD COLUMN sede_region_empresa VARCHAR(100) NULL`
2. EvaluadoOrden.php: agregar a $fillable
3. Formularios admin crear/editar orden: agregar campo input text
4. Formulario empresa crear orden: agregar campo input text
5. Vista show admin: mostrar en datos del evaluado
6. Vista show empresa: mostrar en datos del evaluado
7. PDF Orden de Servicio: incluir campo
8. PDF Informe Candidatos: incluir campo
9. Reportes: incluir en columnas donde aparece info del evaluado
10. Tests

**Archivos:** nueva migración, EvaluadoOrden.php, admin/ordenes/create.blade.php, admin/ordenes/edit.blade.php, admin/ordenes/show.blade.php, empresa/ordenes/show.blade.php, admin/ordenes/pdf.blade.php, admin/ordenes/pdf-informe.blade.php

---

### R5 — Auto-liberar informe al subirlo (Fase 10)

**Solicitud:** Al subir informe preliminar o final → auto liberar para el cliente (resultados_visibles_empresa=true). Solo admins REPRO pueden bloquearlo manualmente.

**Estado actual:**
- Subir informe FINAL: ya auto-libera (OrdenesController ~línea 957: `$orden->update(['resultados_visibles_empresa' => true])`)
- Subir informe PRELIMINAR (texto Quill): NO auto-libera. El admin debe hacer click en "Liberar resultados" separado.
- El botón toggle de resultados_visibles_empresa está disponible para todos los roles con acceso.

**Plan:**
1. En guardarInformePreliminar(): agregar `$orden->update(['resultados_visibles_empresa' => true])` después de guardar el texto
2. En subirResultadoPreliminar() (si existe como acción separada para archivo): igual
3. Asegurarse que el botón toggle de bloquear/liberar SOLO sea visible para admin (role_as === 1), no para repro/colaboradores
4. Tests: verificar auto-liberación al guardar preliminar

---

### R6 — WhatsApp dropdown de sedes (Fase 13)

**Solicitud:** Botón WhatsApp en barra lateral y panel de control como dropdown que liste todas las sedes activas con sus números. Primera opción: sede asignada a la orden si hay contexto.

**Estado actual:** En admin/index.blade.php ya existen botones WhatsApp por sede. En empresa/ordenes se muestra el WhatsApp de la sede de la orden. Pero NO hay dropdown en barra lateral.

**Plan:**
1. En layouts/empresa.blade.php (incempresa sidebar): agregar dropdown de sedes activas con WhatsApp
2. En layouts/admin.blade.php (incadmin sidebar): igual
3. En empresa/ordenes/show.blade.php: el primer item del dropdown debe ser la sede de la orden
4. Query de sedes: `Sede::where('activo', true)->whereNotNull('whatsapp')->get()`
5. Inyectar las sedes en todos los layouts via ViewComposer o AppServiceProvider

---

### R7 — Diferenciar preliminar vs final en vistas cliente (Fase 10)

**Solicitud:** Mostrar de manera diferente el informe preliminar y el final. Siempre mostrar ambos en el portal cliente. En reportes, mostrar todas las opciones no solo el final.

**Estado actual:**
- empresa/ordenes/show.blade.php: muestra texto_informe_preliminar como read-only cuando resultados_visibles_empresa. Muestra botón de archivo_resultado_final.
- No está claro si siempre muestra AMBOS (preliminar Y final) o solo uno según condición.
- Los reportes de empresa probablemente solo filtran el informe final.

**Plan:**
1. Auditar empresa/ordenes/show.blade.php: verificar que se muestren AMBAS secciones (Informe Preliminar / Observaciones + Informe Final)
2. Diferenciar visualmente: card de color diferente para cada tipo
3. Agregar etiquetas claras: "Informe Preliminar / Observaciones" vs "Informe Final"
4. Reportes empresa: revisar que filtre/muestre ambos tipos si existen

---

### R8 — Notificaciones con info y redireccion correcta (Fase 10)

**Solicitud:** Todas las notificaciones deben llevar info en el nombre (código de orden, nombre de candidato, etc.) y al presionarlas redirigir al lugar correcto.

**Estado actual (4 notificaciones):**
- OrdenCreadaNotification: mensaje `"Nueva orden #{codigo_orden} — {empresa}"`, URL generada por role_as ✓
- ResultadosDisponiblesNotification: tiene URL por role_as, falta verificar mensaje
- CuestionarioCompletadoNotification: verificar si lleva nombre del candidato y URL correcta
- EvaluadoAsignadoNotification: verificar si lleva datos y URL

**Plan:**
1. Auditar las 4 notificaciones: revisar toArray() de cada una
2. Verificar que el title/mensaje incluya: código de orden, nombre del candidato (si aplica)
3. Verificar URLs: que role_as === 1 → admin URL, role_as === 2 → empresa URL, etc.
4. En la vista de notificaciones (bell/centro): verificar que el link del item use la URL de la notificación correctamente
5. Test: notificación al presionar redirige a la orden correcta

**Archivos:** app/Notifications/*.php, resources/views/layouts/incadmin/_notificaciones_bell.blade.php, resources/views/layouts/incempresa/_notificaciones_bell.blade.php

---

### R9 — Quitar fecha tentativa en vistas cliente empresa (Fase 10)

**Solicitud:** Eliminar la fecha tentativa de las evaluaciones del lado del cliente empresa porque confunde (el cliente cree que él escoge la fecha de la cita con REPRO).

**Estado actual:**
- El campo `fecha_programada` existe en evaluados_orden y se usa internamente en REPRO
- En admin no se toca (el admin sí puede poner fecha interna)
- En empresa/ordenes/create.blade.php: NO hay referencias a fecha_programada (confirmado: grep no encontró nada en empresa/)
- Posibles apariciones en empresa/ordenes/show.blade.php

**Plan:**
1. Buscar y eliminar cualquier mención de "fecha tentativa" o `fecha_programada` en TODAS las vistas del portal empresa
2. Verificar que en empresa/ordenes/show.blade.php no se muestre la fecha_programada del evaluado como dato visible al cliente
3. Verificar empresa/ordenes/index.blade.php
4. El campo sigue existiendo en BD y admin, solo se oculta al cliente

**Nota:** El campo `fecha_programada` ya fue renombrado en etiquetas a "Fecha Tentativa (sujeta a agenda REPRO)" en Fase 1. Ahora se pide quitarlo completamente de la UI del cliente.

---

### R10 — Renombrar editor informe preliminar (Fase 10)

**Solicitud:** Cambiar el label del editor Quill de "Informe Preliminar" a "Informe Preliminar / Observaciones".

**Estado actual:** En resources/views/admin/ordenes/show.blade.php hay una card con el editor Quill para el informe preliminar. El título dice "Informe Preliminar".

**Plan:** Localizar el texto en admin/ordenes/show.blade.php y cambiarlo.

**Archivos:** resources/views/admin/ordenes/show.blade.php (1 cambio de texto)

---

### R11 — Auditoria restricciones por rol (Fase 15)

**Solicitud:** Revisar que todas las restricciones de permisos se cumplan en todos los módulos. Caso reportado: usuario repro no-admin puede acceder al módulo de usuarios.

**Estado actual:**
- Existe tabla permissions con 44 permisos en 16 módulos (desde Fase 9)
- Existe Middleware de roles: verificar app/Http/Middleware/
- El módulo de usuarios debería estar bloqueado para role_as !== 1

**Plan (análisis completo en Fase 15):**
1. Listar todos los módulos y sus rutas
2. Verificar middleware de protección en cada grupo de rutas (routes/web.php)
3. Para cada módulo: verificar que el middleware/gate/policy sea correcto
4. Módulo usuarios: agregar/verificar middleware que solo permita admins
5. Módulos de configuración, finanzas, reportes avanzados: revisar acceso
6. Test: intentar acceder a rutas protegidas con usuario repro no-admin

---

### R12 — Layout vistas cliente empresa (Fase 10)

**Solicitud:** En el formulario de creación de orden y vistas del portal empresa, el contenido inferior no se puede ver por problema con scroll/footer.

**Estado actual:**
- layouts/empresa.blade.php tiene CSS de Fase 8: `.content-wrapper-scroll { overflow:visible; height:auto }`
- El footer está en `app-footer` con `margin-top:auto`
- El problema puede ser que los formularios largos (create orden) no generan scroll porque el contenedor tiene `overflow:visible`

**Análisis:** El fix de Fase 8 para eliminar scrollbars duplicadas puede causar que en formularios muy largos el scroll natural del body no alcance a mostrar el final si el footer está posicionado de forma que tape el contenido.

**Plan:**
1. Abrir empresa/ordenes/create.blade.php en browser y reproducir el problema
2. Revisar el HTML/CSS del layout empresa completo
3. Verificar que `main-container` tenga la altura correcta para que el scroll del body funcione
4. Posible fix: asegurar que `.content-wrapper-scroll` tenga `min-height` apropiado y que el footer no sea `position:fixed` sino en flujo normal
5. Revisar todas las vistas largas de empresa (create, edit, show con muchos evaluados)

**Archivos:** resources/views/layouts/empresa.blade.php, resources/views/empresa/ordenes/create.blade.php

---

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
