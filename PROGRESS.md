# PROGRESS — Requerimientos Cliente Mayo 2026

**Documento de seguimiento activo**
**Base de referencia:** docs/REQUERIMIENTOS_CLIENTE_2026-05.md
**Ultima actualizacion:** 2026-07-18 — **Fase A + E7 ✅** · suite **796/796** · deploy Fase F pendiente

> **Regla (Otto):** al cerrar cualquier punto de trabajo, actualizar **este archivo**, `docs/business/PLAN_IMPLEMENTACION_FORMULARIOS_2026-06-22.md` y `docs/status/CONTEXTO_AGENTES.md` en la misma sesión (estado E1, siguiente paso, fecha).
**Deploy a producción:** ✅ Fase 20 2026-06-16 — commit `45c89dc5` · 5/5 archivos FTP · caché + OPcache limpiados · HTTP 200 login · vista enlace inválido verificada
**Informe cliente:** `docs/Informe_Cliente_2026-06-12_Fase19.md` · Fase 18: `docs/Informe_Cliente_2026-06-10.md`
**Alcance Fase 19:** `docs/Fase19_Alcance_Definitivo_2026-06-12.md` · Manifiesto deploy: `docs/deployment/Fase19_deploy_manifest.txt`
**Resumen pre-despliegue:** `docs/resumen_cambios_cliente.md`

---

## 🔴 FASE EN DESARROLLO AHORA

| Qué | Detalle |
|-----|---------|
| **Fase** | **F — Formularios (cierre del proyecto)** |
| **Etapa activa** | **Deploy Fase F + Fase A/E7 a producción** |
| **Etapa anterior** | **Fase A (legal) + E7 (Word) ✅ CERRADAS (18-jul)** |
| **Plan detallado (checklists)** | `docs/business/PLAN_IMPLEMENTACION_FORMULARIOS_2026-06-22.md` ← **documento principal para avanzar punto por punto** |
| **Análisis / spec / decisiones comerciales** | `docs/business/ANALISIS_FORMULARIOS_E_INFORME_2026-06-22.md` |
| **Contexto para agentes IA** | `docs/status/CONTEXTO_AGENTES.md` |
| **Cotización extras (Word, 1B, WhatsApp)** | `docs/business/COTIZACION_EXTRAS_JUNIO_2026_CLIENTE.md` |

### Progreso E1 (motor base)

| Punto | Estado |
|-------|--------|
| Decisiones técnicas (valor_json, evaluador_notas, catálogo GT, foto obligatoria) | ✅ |
| Migraciones `valor_json` + `evaluador_notas` (batch 11, Docker local) | ✅ |
| Modelos `CuestionarioRespuesta` + `EvaluadorNota` | ✅ |
| Tests regresión | ✅ 756/756 |
| **E1 Motor base** | ✅ **CERRADO** (1.1–1.9) |
| **1.9** Tests del motor + migraciones E1 | ✅ **completado** (`CuestionarioMotorE1Test`, `CuestionarioSeccionesTest`) |

### Progreso E2 (Pre-empleo)

| Punto | Estado |
|-------|--------|
| Tests regresión | ✅ 756/756 |
| **2.1** Sección 1 — datos generales | ✅ |
| **2.2** Padres (condicional ¿vive?) | ✅ |
| **2.3** Pareja actual (condicional) | ✅ |
| **2.4** Tabla dinámica Hijos | ✅ |
| **2.5** Tabla dinámica Hermanos | ✅ |
| **2.6** Exparejas (condicional) | ✅ |
| **2.7** Resumen familiar (informe) | ✅ |
| **2.8** Formación académica autogenerada | ✅ |
| **2.9** Tabla empleos + observaciones | ✅ |
| **2.10** 19 preguntas integridad (internas) | ✅ |
| **2.11** Tabla deudas | ✅ |
| **2.12** Situación económica ampliada (internas) | ✅ |
| **2.13–2.16** Salud, tatuajes, hábitos, sustancias | ✅ |
| **2.17** Aspecto judicial (interno) | ✅ |
| **2.18** Información complementaria (informe) | ✅ |
| **2.19** Documentos adjuntos (pantalla final) | ✅ |
| **2.20–2.21** Mensajes finales | ✅ |
| **Cierre E2** | ✅ **completado** — tests automatizados + **QA manual Pre-empleo OK (2-jul)** |

**Correcciones post-QA manual E2 (2-jul):**
- Formación académica (2.8): filas autogeneradas al elegir último nivel; tabla fija por nivel; validación y persistencia coherente (`HistorialAcademico`, `formacion-academica.js`).
- Situación económica (2.12): campos condicionales de detalle (vehículos, propiedades, SAT, bancarios, demandas, fiador).
- Salud y hábitos (2.13): campos condicionales de detalle (psicológica, ideación, tratamiento, hospitalizaciones, ausencias, deporte).
- Mensajes de validación legibles (`CuestionarioValidacionLabels`, `SaludHabitosCampos::mensajesValidacion`, `SituacionEconomicaCampos::mensajesValidacion`).
- Pantalla completado: botón «Cerrar ventana» con instrucciones visibles (SweetAlert + atajos teclado).
- Fixes previos sesión: spinner/foto/licencia, badge «Confidencial» en preguntas internas.

**Siguiente paso:** **E4.1** — Reutilizar las 5 secciones matriz en formulario Socioeconómico.

**Paralelo (aún no iniciado):** Fase A legal · Fase Word (.docx)

### Progreso E3 (evaluador + informe) — ✅ CERRADO (8-jul-2026)

| Punto | Estado |
|-------|--------|
| **3.1** UI espacios internos evaluador (solo REPRO) | ✅ `EvaluadorNotasSupport` + accordion admin show/edit |
| **3.2** Mapeo respuestas → tablas informe | ✅ `InformePreempleo` (familia, académico, laboral, deudas, complementaria) + overrides en `evaluador_notas` |
| **3.3** Reglas exclusión campos internos del informe | ✅ `CamposInternosPreempleo::filtrarRespuestasParaEmpresa()` — portal empresa + PDF |
| **3.4** Tests permisos (empresa no ve internas) | ✅ `InformePreempleoTest` + `InformePreempleoVisibilidadTest` |

**Mejoras post-E3 (portal empresa + PDF, 8-jul):**
- Vista empresa: pestañas reutilizan partials admin en solo lectura (`shared/cuestionario/seccion-lectura`) — cards, tablas dinámicas, badges (sin inputs).
- PDF empresa: agrupación por sección/subsección (`shared/cuestionario/pdf-secciones-empresa`) en lugar de listado plano.
- Sección 5 empresa: ocultos antecedentes/salud/referencias legacy; visibles tatuajes, perforaciones, complementaria, info adicional candidato.
- Helper `CuestionarioPresentacionEmpresa` centraliza filtrado + tablas por sección.

**Fixes QA formulario candidato (post-E2, 8-jul):**
- Scroll horizontal en tablas dinámicas (`tabla-dinamica.blade.php` + sync condicionales).
- Deudas: campos condicionales con `<x-campo-condicional>` (evita disabled por autosave).
- Tests: `CuestionarioTablaDinamicaTest` (8/8).

**Demo manual E4 (Socio):** `DemoPruebaManualE4Seeder` · token `e4demo2026pruebamanualtokenrepr0` · DPI `2405617300205` (13 dígitos)
- Empresa demo: `demo-empresa-e4@repro.local` / `empresa1234` (requiere orden `entregado` + `resultados_visibles_empresa=true`)

**Siguiente paso E4:** QA manual flujo socio completo (6 secciones) · admin partial sección 6 · PDF empresa sección 6.

### Progreso E4 (Socioeconómico) — ✅ CERRADO (14-jul-2026)

| Punto | Estado |
|-------|--------|
| **4.1** Reutilizar 5 secciones matriz + activar `tipo_formulario=socioeconomico` en cuestionario | ✅ |
| **4.2** Referencias fam/pers/vec/laborales (tablas dinámicas, import empleos) | ✅ |
| **4.3** Bienes y pertenencias + total autocalculado | ✅ |
| **4.4** Presupuesto personal + total autocalculado | ✅ |
| **4.5** Información de vivienda (campos condicionales) | ✅ |
| **4.6** Reglas informe (refs fam/pers sí; vecinales/vivienda ocultos empresa) | ✅ |
| **4.8** PDF empresa sección 6 + tablas informe refs editables + mensajes socio | ✅ |
| **4.7** Documentos socio (constancia laboral, recibo luz) | ✅ |
| **Cierre E4** | ✅ **completado** — commit `2b175bce` |

**Demo manual E4:** `DemoPruebaManualE4Seeder` · token `e4demo2026pruebamanualtokenrepr0` · DPI `2405617300205`

### Progreso E5 (Periódica + Específica) — ✅ CERRADO (18-jul-2026)

| Punto | Estado |
|-------|--------|
| **5.1** Base Pre-empleo; omitir IGSS, NIT, Hermanos, Aspectos Complementarios | ✅ |
| **5.2** Sección laboral propia (tabla simplificada + 26 preguntas internas) | ✅ |
| **5.3** Foto; documentos solo DPI; mensaje sin papelería | ✅ |
| **5.4** Específica: base Periódica; académica = solo último grado | ✅ |
| **5.5** Específica: pregunta 1 laboral = caso/hecho amplio | ✅ |
| **5.6** Específica: documentos solo DPI | ✅ |
| **Cierre E5** | ✅ |

**Demo Periódica:** `DemoPruebaManualE5PeriodicaSeeder` · token `e5demo2026periodicatokenrepr0` · DPI `2405617300305`  
**Demo Específica:** `DemoPruebaManualE5EspecificaSeeder` · token `e5demo2026especificatokenrepr0` · DPI `2405617300405`

### Progreso E6 (Integración) — ✅ CERRADO (18-jul-2026)

| Punto | Estado |
|-------|--------|
| **6.1** Selección automática formulario según servicio + tipo (matriz) | ✅ |
| **6.2** Mensajes "Información Importante" por tipo | ✅ |
| **6.3** Socio: marcar Formulario Completado manual (workaround Jotform) | ✅ |
| **6.4** Papelería post-envío 30 días | ✅ mismo enlace vigente (`token_expira_at`) · UI en completado + finalizar |
| **6.5** Regresión suite + móvil | ✅ suite **786/786** · smoke móvil completado (18-jul) |

**QA manual navegador (18-jul):** E6.1 matriz create/edit ✅ · E6.2 mensajes secc. 5/6 + completado ✅ · E6.3 Jotform solo socio ✅ · hermanos socio vs periódica ✅ · foto resaltado rojo ✅ · panel documentos post-envío ✅ · demos reseedeados.

**Fixes post-QA:** migración `constancia_laboral`/`recibo_luz` en `documento_evaluados`; tablas tatuajes/perforaciones también en socio secc. 5; tests estables (`tipo_servicio` + `tipo_formulario` fijos en pre-empleo/foto/E6.3 negativo).

**Siguiente paso:** deploy completo a iPage (Fase F + A + E7) · QA producción.

### Progreso Fase A (Legal) — ✅ CERRADO (18-jul-2026)

| Punto | Estado |
|-------|--------|
| **A.1** 7 autorizaciones por servicio + formulario | ✅ `AutorizacionesLegales` + `config/autorizaciones_legales.php` |
| **A.2** Infornet (pre-empleo, misma firma) | ✅ paso `/infornet` post-términos |
| **A.3** Campo motivo/hecho (Periódica/Específica) | ✅ `motivo_hecho_evaluacion` + UI admin |
| **A.4** Corrección Específica | ✅ cubierto por E5 |
| **A.5** Autorizaciones en PDF cuestionario | ✅ snapshot HTML + Infornet en `pdf.blade.php` |

**Nota:** textos legales en `config/autorizaciones_legales.php` — reemplazar con las 7 plantillas oficiales de REPRO cuando las entreguen (estructura lista).

### Progreso E7 (Word) — ✅ CERRADO (18-jul-2026)

| Punto | Estado |
|-------|--------|
| **7.2** Generación .docx por evaluado | ✅ `InformeWordExport` + botón en show orden |
| **7.3** Datos del informe (resultado, preliminar, notas) | ✅ versión base operativa |
| **7.4** Pruebas | ✅ `InformeWordExportTest` |
| **7.1** Plantilla oficial .docx REPRO | ⏳ pendiente cliente (Word usa layout REPRO en código) |

**Migraciones E1 (batch 11–14):** `valor_json` · `evaluador_notas` · `departamentos` · `municipios` · `instrucciones_*` · `datos_precarga_json`

---

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
| Fase 10 | Correcciones rapidas 2a ronda | COMPLETADA |
| Fase 11 | Auto-estados por acciones | COMPLETADA |
| Fase 12 | Campo Sede/Region del evaluado | COMPLETADA |
| Fase 13 | Mejoras dashboard y WhatsApp | COMPLETADA |
| Fase 14 | Configuracion ampliada | COMPLETADA |
| Fase 15 | Auditoria de permisos por rol | COMPLETADA |
| Fase 16 | Observaciones cliente 2026-05-22 | ✅ COMPLETADA Y DEPLOYADA |
| Fase 17 | Transiciones de estado ampliadas (cliente pide control total) | ❌ CANCELADA (reemplazada por Fase 18) |
| Fase 18 | Rediseño a 4 estados independientes (Formulario/Programación/Evaluación/Orden) | ✅ COMPLETADA Y DEPLOYADA 2026-06-10 — informe enviado al cliente |
| Fase 19 | Ajustes confirmados cliente 11/06 (duplicación, capacidad sede, historial empresa, archivar, búsqueda) | ✅ COMPLETADA Y DEPLOYADA 2026-06-13 — informe listo para cliente |
| Fase 20 | Hotfix enlace cuestionario — UX 404, logging, vigencia token | ✅ DEPLOYADA 2026-06-16 |
| Fase A (legal) | 7 autorizaciones + Infornet + motivo/hecho + PDF | ✅ **CERRADA** (18-jul) · textos oficiales pendientes swap |
| Fase Word | Informe empresa .docx editable (Q 1,600) | ✅ **CERRADA** (18-jul) · plantilla .docx cliente opcional |
| **Fase F (formularios)** | Motor + 4 formularios + integración | ✅ **E1–E6 CERRADOS** · **deploy pendiente** |
| Fase C (1B) | Agregar servicio con reutilización de datos (Q 5,200) | 🕐 DIFERIDA 2–3 meses (decisión cliente) |
| WhatsApp API | Notificaciones automáticas (Q 3,800) | 🕐 POSPUESTA (decisión cliente) |

---

## 🆕 FORMULARIOS = CIERRE DEL PROYECTO (decisión 22-jun-2026)

La cliente entregó la especificación funcional completa (`CREACIÓN FORMULARIOS DE SISTEMA.pdf`, 46 pág.) + informe de ejemplo para el Word. Al revisar los **formularios originales (ago-2025)** se confirmó que **el contenido ya estaba especificado desde el inicio del proyecto**; el sistema implementó solo una fracción. **Por tanto, completar los formularios NO se cobra aparte: es el cierre del proyecto y desbloquea el saldo Q 10,000.**

- **Análisis:** `docs/business/ANALISIS_FORMULARIOS_E_INFORME_2026-06-22.md`
- **Plan de trabajo ordenado (punto por punto):** `docs/business/PLAN_IMPLEMENTACION_FORMULARIOS_2026-06-22.md`
- **Cobrable aparte:** solo Word Q 1,600 (aprobado), 1B Q 5,200 (diferido), WhatsApp Q 3,800 (pospuesto).
- **Anulado:** referencias previas a "Fase F cobrable / Q 14,500-16,000".

---

## NUEVA RONDA — Observaciones Cliente 2026-05-18

**12 requerimientos registrados. Analisis quirurgico realizado el 2026-05-18.**

### Mapa de requerimientos → Fases

| Ref | Descripcion resumida | Fase | Prioridad | Complejidad |
|-----|----------------------|------|-----------|-------------|
| R1 | Auto-cambio de estados por acciones | Fase 11 | ALTA | ALTA | ✅ COMPLETADO |
| R2 | Top empresas en dashboard admin | Fase 13 | MEDIA | BAJA | ✅ YA IMPLEMENTADO |
| R3 | Configuracion ampliada in-app | Fase 14 | MEDIA | ALTA | ✅ COMPLETADO |
| R4 | Campo Sede/Region del evaluado | Fase 12 | ALTA | MEDIA | ✅ COMPLETADO |
| R5 | Auto-liberar informe al subirlo | Fase 10 | ALTA | MEDIA | ✅ COMPLETADO |
| R6 | WhatsApp dropdown de sedes | Fase 13 | MEDIA | MEDIA | ✅ COMPLETADO |
| R7 | Diferenciar preliminar vs final en cliente | Fase 10 | MEDIA | BAJA | ✅ COMPLETADO |
| R8 | Notificaciones con info y redireccion correcta | Fase 10 | ALTA | MEDIA | ✅ COMPLETADO |
| R9 | Quitar fecha tentativa en cliente y admin | Fase 10 | ALTA | BAJA | ✅ COMPLETADO |
| R10 | Renombrar editor informe preliminar | Fase 10 | BAJA | MUY BAJA | ✅ COMPLETADO |
| R11 | Auditoria restricciones por rol | Fase 15 | ALTA | ALTA | ✅ COMPLETADO |
| R12 | Layout vistas cliente empresa (scroll/footer) | Fase 10 | ALTA | MEDIA | ✅ COMPLETADO |

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

**Estado: ✅ COMPLETADO (2026-05-18)**
- Migración aplicada: `sede_region_empresa VARCHAR(100) NULL` en `evaluados_orden`
- Modelo, controller (store/update), validación actualizados
- Formularios create y edit (admin/empresa): campo después de Dirección
- Vista show admin (accordion evaluado) y show empresa
- PDF Orden: etiqueta "Sede/Región Empresa" y "Sede REPRO" claramente diferenciados
- PDF Informe: filas separadas con etiquetas explícitas
- 3 tests PHPUnit (8 assertions) — todos pasan

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

### R6 — WhatsApp dropdown de sedes (Fase 13) ✅ COMPLETADO

**Solicitud:** Botón WhatsApp en barra lateral y panel de control como dropdown que liste todas las sedes activas con sus números.

**Implementación:**
- `AppServiceProvider::boot()`: comparte `$sedesWhatsApp` globalmente via `View::composer('*')` con `Sede::activas()->whereNotNull('whatsapp')->where('whatsapp', '!=', '')->orderBy('nombre')->get()`
- `incadmin/sidebar.blade.php`: dropdown "WhatsApp REPRO" con ícono verde, lista todas las sedes, cada item abre `https://wa.me/{numero}` en nueva pestaña
- `incempresa/sidebar.blade.php`: misma estructura
- 4 tests (11 assertions): admin sidebar muestra, empresa sidebar muestra, no muestra si vacío, inactiva no aparece

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

## Fase 10 - Correcciones rapidas 2a ronda - COMPLETADA 2026-05-18

Implementada el 2026-05-18 como respuesta a la revisión del cliente de ese día.
Tests en desarrollo local (PHP no ejecutable via WSL con binario Windows).

| Ref | Descripcion | Estado | Commits |
|-----|-------------|--------|---------|
| R5 | Auto-liberar al guardar informe preliminar (texto y archivo) | ✓ | 5a21cba7 |
| R5 | Toggle resultados_visibles restringido a admin (role_as >= 3) | ✓ | 5a21cba7 |
| R7 | Portal empresa muestra Informe Final (verde) + Preliminar/Obs (azul) diferenciados | ✓ | 5a21cba7 |
| R8 | Notificaciones incluyen "— Orden #{codigo}" en el mensaje | ✓ | 5a21cba7 |
| R9 | Fecha Tentativa eliminada de portal empresa (show, index) | ✓ | 5a21cba7 |
| R9 ext | Fecha Tentativa eliminada de formularios admin (create, edit) y pdf-informe | ✓ | 0e6b3b0c |
| R10 | Label editor Quill: "Informe Preliminar / Observaciones" | ✓ | 5a21cba7 |
| R12 | Fix scroll portal empresa: main-container height:auto !important | ✓ | 5a21cba7 |
| CO3 | Admins (role_as>=3) pueden eliminar/reemplazar informe final en ordenes entregadas | ✓ | 5a21cba7 |

### Archivos clave Fase 10

- app/Http/Controllers/Admin/OrdenesController.php: guardarInformePreliminar(), subirResultadoArchivo(), toggleResultadosVisibles(), eliminarResultadoArchivo()
- app/Notifications/CuestionarioCompletadoNotification.php: mensaje con código de orden
- app/Notifications/EvaluadoAsignadoNotification.php: mensaje con código de orden
- app/Notifications/ResultadosDisponiblesNotification.php: mensaje con nombre y código de orden
- resources/views/admin/ordenes/show.blade.php: label "Informe Preliminar / Observaciones", toggle visible solo admin, botón delete informe solo admin
- resources/views/empresa/ordenes/show.blade.php: tarjeta verde (Informe Final) + tarjeta azul (Preliminar/Obs) cuando resultados_visibles_empresa
- resources/views/layouts/empresa.blade.php: .main-container { height: auto !important; min-height: calc(100vh - 65px) }
- resources/views/admin/ordenes/create.blade.php: eliminado input y JS de fechaProgramadaHtml
- resources/views/admin/ordenes/edit.blade.php: eliminado input Fecha Tentativa (2 instancias) y línea JS
- resources/views/admin/ordenes/pdf-informe.blade.php: eliminado @elseif con label "(tentativa)"

### Datos de prueba creados (locales)

- 5 órdenes en BD local (ORD-2026-0001 a 0005)
- Empresa de prueba: `arden67@example.net` / `cliente123`
- ORD-2026-0003: entregado + visible + 2 evaluados con texto_informe_preliminar → test tarjeta azul
- ORD-2026-0004: entregado + visible + 1 evaluado con texto + archivo final → test tarjeta verde + azul
- ORD-2026-0002: en_proceso + not visible + 3 evaluados sin informe → test R5 auto-release

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

## Fase 15 - Auditoría de permisos por rol - COMPLETADA 2026-05-18

Auditoría de módulos sensibles ejecutada el 2026-05-18.

| ID | Hallazgo | Severidad | Estado |
|----|----------|-----------|--------|
| R11-A | `GET /users` y `GET /show-user/{id}` sin middleware `role:admin` → repro y empresa podían acceder | ALTA | ✓ RESUELTO |
| R11-B | Sidebar mostraba "Usuarios", "Configuración" a repro (`role_as >= 2`) | MEDIA | ✓ RESUELTO |
| R11-C | Config protegida por ruta pero link visible en nav para repro | BAJA | ✓ RESUELTO |

### Cambios aplicados

- **routes/web.php**: `users.index` y `users.show` movidos dentro del grupo `role:admin`
- **sidebar.blade.php**: bloque "Administración" separado: admin ve todo; repro ve solo Finanzas
- **tests**: 5 tests R11 en `AuditoriaSeguridadTest` — repro/empresa bloqueados, admin autorizado

### Otros cambios (sesión 2026-05-18)

| Item | Descripcion | Archivos |
|------|-------------|----------|
| Paginas de error | 404/403/500 con logo REPRO, mensaje y botones Ir al inicio / Regresar | resources/views/errors/ |
| Navbar sticky | .page-header { position: sticky; top: 0; z-index: 1030 } en admin y empresa layouts | admin.blade.php, empresa.blade.php |

### Commits de esta sesión

| Hash | Descripcion |
|------|-------------|
| 5a21cba7 | feat: Fase 10 (R5/R7/R8/R9/R10/R12) + admin puede eliminar informe final |
| 0e6b3b0c | feat: eliminar campo Fecha Tentativa de formularios y PDFs admin |
| 34352956 | fix: R11 — modulo usuarios restringido exclusivamente a admin |
| 15cdb291 | feat: paginas de error personalizadas + navbar sticky |

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

---

## Fase 16 — Observaciones cliente 2026-05-22 (post-deploy)

**Origen:** docs/Observaciones cliente/cambios 2205.pdf
**Estado:** LOTES A + B COMPLETADOS + bugs post-testing corregidos — Lotes C/D pendientes

### Lote A — Quick wins UI + Auditoría de permisos ✅ COMPLETO

| ID | Descripción | Archivos | Estado |
|----|-------------|----------|--------|
| 7 | Nombre completo en "Últimas Órdenes" (dashboard admin) | resources/views/admin/index.blade.php | ✅ |
| 3 | Botón "papelería" en listado de evaluados del reporte | resources/views/admin/reportes/evaluaciones.blade.php | ✅ |
| 6a | Sidebar: "Sedes REPRO" condicionado por `sedes.ver` | sidebar.blade.php | ✅ |
| 6b | Sidebar: "Usuarios" condicionado por `usuarios.ver` | sidebar.blade.php | ✅ |
| SEC | Fix rutas sedes + usuarios con middleware `permission:` granular | routes/web.php, SedesController.php, vistas sedes | ✅ |
| SEC | Sidebar finanzas condicionado por `finanzas.ver` | sidebar.blade.php | ✅ |
| SEC | Sidebar calendario condicionado por `calendario.ver` | sidebar.blade.php | ✅ |
| SEC | Rutas finanzas → `permission:finanzas.ver` | routes/web.php | ✅ |
| SEC | Rutas calendario → `permission:calendario.ver` / `calendario.editar` | routes/web.php | ✅ |
| TEST | SeguridadTest actualizado y pasando (49/49) | tests/Feature/SeguridadTest.php | ✅ |

**Resumen técnico Lote A:**
- Agujero de seguridad detectado y corregido: cualquier `role_as >= 2` podía acceder a sedes/finanzas/calendario sin importar los permisos asignados.
- Todos los módulos con permisos definidos ahora usan middleware `permission:` granular en rutas.
- `CheckPermission` ya tenía bypass para `role_as >= 3` (admins), no se modificó.
- El módulo `empresas` se mantiene en `role:admin,repro` (sin permisos granulares definidos).

### Lote B — Notificaciones por rol

Matriz objetivo:

| Evento | Admin | Colaborador (poligrafo) | Cliente (empresa) |
|--------|-------|-------------------------|-------------------|
| Orden creada | ✓ ya | ✓ ya | ✗ FALTA |
| Candidato asignado | ✗ FALTA | ✗ FALTA | ✗ FALTA |
| Cuestionario completado | ✗ FALTA | ✓ ya | (no aplica) |
| Resultado preliminar subido | ✗ FALTA | (no aplica) | ✗ FALTA |
| Informe final / resultados disponibles | ✓ ya | (no aplica) | ✓ ya |

Acción: auditar destinatarios en cada `Notification` y ajustar. Todas in-app (database channel), no mail.

**Estado Lote B: ✅ COMPLETADO** — 8/8 tests NotificacionesInAppTest pasando. Desplegado a producción vía FTP.

Archivos modificados:
- `app/Notifications/ResultadoPreliminarNotification.php` — nueva clase
- `app/Http/Controllers/Admin/OrdenesController.php` — empresa notificada en orden creada; EvaluadoAsignadoNotification in-app; ResultadoPreliminarNotification en preliminar subido
- `tests/Feature/NotificacionesInAppTest.php` — 8 tests

### Lote C — Info Sedes para cliente ✅ COMPLETO

| ID | Descripción | Archivos | Estado |
|----|-------------|----------|--------|
| 4 | Nuevo item de menú "Sedes REPRO" para rol empresa con teléfonos, dirección y mapa | resources/views/layouts/incempresa/sidebar.blade.php + resources/views/empresa/sedes/index.blade.php + ruta + EmpresaController::sedesRepro() | ✅ |

**Detalles:**
- Ruta: `GET empresa/sedes-repro` → `empresa.sedes-repro` (protegida con `role:empresa`)
- Vista: cards por sede activa — nombre, dirección, teléfono, enlace WhatsApp (`wa.me/`), botón "Ver en mapa" (cuando tiene `enlace_maps`)
- Sidebar: ítem "Sedes REPRO" siempre visible en sección "Contacto" (independiente del bloque WhatsApp)
- Sedes inactivas (`estado=0`) no se muestran
- **Tests:** 8/8 en `LoteC_SedesReproTest.php` pasando
- **Suite completa:** 538/538 pasando

### Bugs detectados durante testing post-deploy (sesión 2026-05-22) — ✅ TODOS CORREGIDOS Y DEPLOYADOS

**Bug 1 — Empresa/repro no podía editar su propio perfil** (fix #1)
- Causa: ruta `edit-user/{id}` estaba dentro del grupo `role:admin`
- Fix: ruta movida fuera del grupo; controller verifica `Auth::id() == $id || role_as >= 3`
- Archivos: `routes/web.php`, `app/Http/Controllers/Admin/UsersController.php`
- Tests: 4 nuevos en `SeguridadTest` (empresa/repro pueden ver su edit, no el de otro → 403)

**Bug 2 — Permisos vuelven al guardar con todos desmarcados** (fix #2 parte A)
- Causa: HTML no envía arrays vacíos; `$request->has('permisos_sistema')` = false → sync nunca corría
- Fix: hidden `<input name="permisos_enviados" value="1">`; controller detecta ese campo en vez del array
- Archivos: `resources/views/admin/user/edit.blade.php`, `UsersController.php`

**Bug 3 — Repro user heredaba permisos del rol base `repro` aunque se desmarcaran todos** (fix #2 parte B)
- Causa: `hasPermission()` busca en TODOS los roles de `user_role`; el rol base `repro` tiene permisos propios
- Fix: al guardar permisos individuales de un repro, se desvincula el rol `repro` de `user_role`; `CheckRole` sigue usando campo `role_as` así que `role:repro` / `role:admin,repro` no se rompe
- Archivos: `UsersController.php`

**Bug 4 — Checkboxes de permisos mostraban permisos heredados como asignados** (fix #2 parte C)
- Causa: la vista usaba `getAllPermissions()` que incluye permisos heredados del rol base
- Fix: la vista carga solo permisos del rol personal `user_{id}`
- Archivos: `resources/views/admin/user/edit.blade.php`
- Tests: 2 nuevos en `AuditoriaSeguridadTest` (62/62 pasando)

### Lote D — Estados automáticos ✅ COMPLETO Y DEPLOYADO

Estados objetivo según cliente (nomenclatura exacta):
`Solicitud → (desistió/cancelado) | Link enviado → Llenando formulario → Formulario recibido → Programado → En proceso → Resultado Preliminar (saltable) → Informe completo entregado`

| ID | Descripción | Archivos | Estado |
|----|-------------|----------|--------|
| 1a | Renombrar label `docs_pendientes` → "Formulario Recibido" (mantener key) | EvaluadoOrden.php (getEstadoEvaluacionTextoAttribute + estadosEvaluacionDisponibles) | ✅ |
| 1b | Al subir informe **preliminar**, evaluado pasa a `en_proceso` si está en `programado`/`docs_pendientes`; auto-libera resultados_visibles_empresa=true | OrdenesController::subirResultadoArchivo() | ✅ |
| 1c | Al programar/reprogramar cita, redirect→back() en lugar de redirect→calendario.dia | CalendarioController::programar(), reprogramar() | ✅ |
| 2 | Bug "Reprogramar saca y no deja reprogramar" — redirect→back() corrige el problema | CalendarioController::reprogramar() | ✅ |

**Tests:** 8/8 en `LoteD_EstadosAutomaticosTest.php` pasando  
**Deploy:** FTP iPage — 5 archivos deployados (EvaluadoOrden.php, OrdenesController.php, CalendarioController.php, create.blade.php, edit.blade.php)  
**Suite completa:** 524/524 tests pasando (incluye corrección de 76 tests pre-existentes que fallaban por Lote A)

### Post-Lote D — Correcciones sesión 2026-05-24

#### Fecha Tentativa (D4) — eliminada correctamente
- Confirmado: campo `fecha_limite` / "Fecha Tentativa" YA NO aparece en formularios admin ni portal empresa (eliminado en Fase 10 R9 ext)
- El campo fue re-agregado por error durante el fix de tests en esta sesión → revertido
- `tests/Feature/Sprint1BugFixesTest.php`: tests N1 actualizados de `assertSee` → `assertDontSee('Fecha Tentativa')` y `assertDontSee('Fecha Programada')`
- 12/12 Sprint1BugFixesTest pasando ✅

#### PDFs actualizados (sesión 2026-05-24)

**PDF Orden de Servicio (`pdf.blade.php`):**
- Estado evaluado: antes `ucfirst(estado_evaluacion)` → mostraba `Docs_pendientes`, `En_proceso`
- Ahora: `$evaluado->estado_evaluacion_texto` (accessor del modelo) → muestra "Formulario Recibido", "En Proceso", etc.
- Color badge: antes solo cubría 3 estados → ahora usa `$evaluado->estado_evaluacion_color` (todos los estados)

**PDF Informe Candidatos (`pdf-informe.blade.php`):**
- Fecha evaluación: antes solo `fecha_realizada` → mostraba `—` si no se había realizado aún
- Ahora: `fecha_realizada` si ya ocurrió, o `fecha_programada` con label "(programada)" como fallback
- Estado de la orden: antes `ucfirst($orden->estado)` → ahora usa `$estados[$orden->estado]` (mapa de etiquetas legibles)
- Controller `pdfInforme()`: ahora pasa `$estados = Orden::estadosDisponibles()` a la vista

#### Datos de prueba creados en BD local (sesión 2026-05-24)

| Código | Estado | Empresa | Propósito |
|--------|--------|---------|-----------|
| ORD-2026-0013 (ID 13) | en_proceso | Corporación ABC | D1: evaluado Carlos Méndez en `docs_pendientes` → verificar badge "Formulario Recibido" |
| ORD-2026-0014 (ID 14) | en_proceso | Corporación ABC | D2: Roberto Fuentes (`programado`) + Valentina Torres (`docs_pendientes`) → probar subida de archivo preliminar → auto en_proceso |
| ORD-2026-0015 (ID 15) | programacion | Industrias XYZ | D3: Diego Hernández + Andrea López (`pendiente`) → probar programar/reprogramar sin redirigir |

**Nota D2:** el auto-avance a `en_proceso` ocurre al subir el **archivo** "Resultado Preliminar" (uploader azul), NO al guardar el texto Quill. El texto Quill libera `resultados_visibles_empresa` y notifica a empresa.

#### Deploy ejecutado 2026-05-24 — scripts/deploy_Fase16_LoteC_2026-05-24.sh

**10/10 archivos subidos, 9/9 MD5 verificados, caché limpiada (60 vistas + OPcache)**

| Archivo | Cambio |
|---------|--------|
| `app/Http/Controllers/Admin/OrdenesController.php` | pasa `$estados` a `pdfInforme()` |
| `app/Http/Controllers/Empresa/EmpresaController.php` | método `sedesRepro()` + import `Sede` |
| `resources/views/admin/ordenes/pdf.blade.php` | estado badge usa accessor (no raw keys) |
| `resources/views/admin/ordenes/pdf-informe.blade.php` | fecha fallback + estado orden legible |
| `resources/views/admin/ordenes/create.blade.php` | sin campo Fecha Tentativa |
| `resources/views/admin/ordenes/edit.blade.php` | sin campo Fecha Tentativa |
| `resources/views/empresa/sedes/index.blade.php` | nueva vista sedes (directorio nuevo) |
| `resources/views/layouts/incempresa/sidebar.blade.php` | ítem "Sedes REPRO" |
| `routes/web.php` | ruta `empresa.sedes-repro` |

**Migraciones:** Sin nuevas migraciones en este deploy. Las 2 migraciones del 2026-05-18 (`sede_region_empresa`, `dias_vigencia_token`/`nombre_empresa`) tenían las columnas en BD pero no estaban registradas en la tabla `migrations` — corregido en batch 102.

---

## Fase 17 — Transiciones de estado ampliadas (planificada 2026-06-01)

**Origen:** Q&A cliente 2026-06-01 — el cliente quiere más control sobre los estados de evaluación.

**Contexto de la decisión:**
El cliente notó que al agregar un evaluado con email, el estado salta automáticamente a `Link Enviado` sin pasar por `Pendiente`, y que el selector de estados solo muestra las transiciones permitidas desde el estado actual (no los 15 estados). Después de explicarle el sistema, el cliente propuso ver "todos los estados posibles que siguen en el proceso en orden".

**Solución acordada (compromiso):**
No eliminar restricciones del todo. En cambio, ampliar las transiciones para que desde cualquier estado se pueda avanzar a **cualquier estado posterior en el flujo principal**, además de las opciones de excepción (`cancelado`, `desistio`, `inasistencia`). No se puede retroceder (excepto `cancelado → pendiente`).

**Flujo principal (orden):**
`pendiente → contactando → contactado → link_enviado → confirmado → programado → en_sede → docs_pendientes → en_proceso → resultado_preliminar → completado`

**Regla nueva:**
Desde cualquier estado del flujo principal, habilitar como destino todos los estados que están **después** de él en ese flujo, más las excepciones del estado actual.

**Archivos a modificar:**
- `app/Models/EvaluadoOrden.php` — método `transicionesEvaluacion()`: ampliar arrays de transiciones permitidas

**Ejemplo del cambio:**
```php
// Antes:
'link_enviado' => ['confirmado', 'programado', 'cancelado', 'desistio'],

// Después:
'link_enviado' => ['confirmado', 'programado', 'en_sede', 'docs_pendientes', 'en_proceso', 'resultado_preliminar', 'completado', 'cancelado', 'desistio'],
```

**Tests a crear/actualizar:**
- `tests/Feature/CalendarioTest.php` o nuevo `Fase17TransicionesTest.php`
- Verificar que todos los saltos hacia adelante son posibles
- Verificar que retroceder al estado anterior sigue bloqueado
- Verificar que `completado` y `desistio` siguen siendo estados finales

**Notas adicionales:**
- Los cambios automáticos del sistema (programar cita → `programado`, enviar link → `link_enviado`, etc.) no se tocan
- El estado `cancelado → pendiente` se mantiene como única excepción de retroceso
- No se modifica nada de `transicionesFormulario()` (estado del cuestionario)
- **Estado:** ❌ CANCELADA — superada por el rediseño arquitectural de la **Fase 18** (4 estados independientes). El "control total" que pedía el cliente se resuelve mejor separando el estado único en 4 campos en lugar de ampliar transiciones de un único campo monolítico.

---

## Fase 18 — Rediseño a 4 estados independientes (planificada 2026-06-04)

**Origen:** PDFs definitivos del cliente `docs/Observaciones cliente/Listado de estados (1).pdf` + `ESTADOS.pdf` + `REPRO Informe de cambios y preguntas Junio 2026.pdf` + captura de flujo.
**Estado:** ✅ COMPLETADA Y DEPLOYADA — 2026-06-10. Semanas 1-4 + corrección `estado_evaluacion` + sinergia S2-S6 + frontend + notificaciones + deploy iPage + informe cliente enviado.

---

## ⭐ FUENTE DE VERDAD — Las 4 máquinas de estado (DEFINITIVO 2026-06-10)

> **Leer esto ANTES de tocar cualquier lógica de estados.** Esta tabla es la referencia oficial validada contra el PDF del cliente. Los 4 campos son **independientes** y cada candidato muestra 3 badges (Formulario / Programación / Evaluación); el estado de Orden es interno/oculto.
>
> **Error histórico corregido:** durante la Semana 1-2, `estado_evaluacion` se implementó por error con los valores del FORMULARIO (`link_pendiente`, `link_enviado`, `pendiente_de_llenar`). Esto era incorrecto: la evaluación física (polígrafo/VSA/socioeconómico) es presencial o por videollamada, **nunca tiene "link"**. Corregido el 2026-06-10.

### 1️⃣ `estado_formulario` — ¿El candidato llenó el formulario? (5 valores, automático)

| Valor (BD) | Etiqueta | Cuándo |
|------------|----------|--------|
| `link_pendiente` | Link Pendiente | Estado inicial si NO tiene email |
| `link_enviado` | Link Enviado | Automático al crear orden con email |
| `pendiente_de_llenar` | Pendiente de Llenar | Auto +24h sin abrir (job) |
| `formulario_completado_y_recibido` | Formulario Completado y Recibido | Al enviar el cuestionario (final) |
| `vencido` | Vencido | Auto +30 días sin completar (job) |

Transiciones: `link_pendiente → {link_enviado, vencido}` · `link_enviado → {pendiente_de_llenar, vencido}` · `pendiente_de_llenar → {formulario_completado_y_recibido, vencido}` · completado/vencido = finales.

### 2️⃣ `estado_programacion` — ¿Ya hay cita? (8 valores, mixto · respuesta cliente #3: SIN "Asistió")

| Valor (BD) | Etiqueta | Cómo cambia |
|------------|----------|-------------|
| `contactando` | Contactando | **Inicial**, automático al crear orden |
| `contactado` | Contactado | Manual |
| `programado` | Programado | Automático al agendar en calendario |
| `proceso_realizado` | Proceso Realizado | Automático cuando Evaluación → En revisión |
| `reprogramado` | Reprogramado | Manual (botón Reprogramar) |
| `inasistencia` | Inasistencia | Manual (solo tras hora programada) |
| `desistio` | Desistió | Manual — **reactivable** (cliente #8) |
| `cancelado` | Cancelado | Manual — final |

Transiciones: `contactando → {contactado, desistio, cancelado}` · `contactado → {programado, reprogramado, desistio, cancelado}` · `programado → {proceso_realizado, inasistencia, reprogramado, cancelado}` · `reprogramado → {contactando, programado, cancelado}` · `inasistencia → {reprogramado, cancelado}` · `desistio → {contactando}` (reactivable) · `proceso_realizado`/`cancelado` = finales.
> **Nota:** El calendario NO es un campo separado — refleja `estado_programacion` (respuesta cliente #4).

### 3️⃣ `estado_evaluacion` — ¿Etapa técnica de la prueba? (7 valores, manual salvo último paso)

| Valor (BD) | Etiqueta | Cómo cambia |
|------------|----------|-------------|
| `pendiente_de_evaluacion` | Pendiente de Evaluación | **Inicial**, automático al crear orden |
| `en_proceso` | En Proceso | **Manual** cuando se está realizando la prueba (cliente #2) |
| `en_revision` | En Revisión | Manual |
| `resultado_preliminar` | Resultado Preliminar | Manual |
| `informe_final_enviado` | Informe Final Enviado | Automático al subir el informe final |
| `cancelado` | Cancelado | Manual — **solo desde Pendiente de Evaluación** — reactivable |
| `desistio` | Desistió | Manual — **solo desde Pendiente de Evaluación** — reactivable |

Transiciones: `pendiente_de_evaluacion → {en_proceso, cancelado, desistio}` · `en_proceso → {en_revision}` · `en_revision → {resultado_preliminar}` · `resultado_preliminar → {informe_final_enviado}` · `informe_final_enviado` = final · `cancelado → {pendiente_de_evaluacion}` · `desistio → {pendiente_de_evaluacion}`.
> **Restricción dura (PDF p.2):** Cancelado/Desistió SOLO desde `pendiente_de_evaluacion`. Una vez en `en_proceso`, el flujo es irreversible (no retrocede ni se cancela, para proteger el historial).

### 4️⃣ `Orden.estado` — Estado interno general (4 valores, 100% automático, OCULTO)

| Valor (BD) | Etiqueta | Regla automática (`Orden::recalcularEstado()`) |
|------------|----------|--------------------------------------------------|
| `orden_recibida` | Orden Recibida | **Inicial**, al crear la orden |
| `en_proceso` | En Proceso | ≥1 candidato salió de su estado inicial de evaluación |
| `entregado` | Entregado | TODOS los candidatos en `informe_final_enviado`/`cancelado`/`desistio` |
| `cancelado` | Cancelado | TODOS los candidatos en `cancelado`/`desistio` |

> Solo visible en "Mis Órdenes" / "Mis Últimas Órdenes" y Listado de Empresas. **No se edita manualmente** (Opción A del cliente).

### 🔀 Reglas de sinergia (cruce entre campos) — PDF p.3

| # | Regla | Estado |
|---|-------|--------|
| S1 | Estados iniciales: Formulario=`link_enviado` (o `link_pendiente` sin email), Programación=`contactando`, Evaluación=`pendiente_de_evaluacion`, Orden=`orden_recibida` | ✅ Implementado |
| S2 | **Virtual:** Formulario debe estar `formulario_completado_y_recibido` antes de poder `programar` | ✅ Implementado |
| S3 | **Presencial:** se puede `programar` con formulario incompleto (se llena en oficina) | ✅ Implementado |
| S4 | Evaluación no entra a `en_proceso` si Formulario ≠ `formulario_completado_y_recibido` | ✅ Implementado |
| S5 | Evaluación no entra a `en_proceso` sin haber pasado por `programado` | ✅ Implementado |
| S6 | Evaluación → `en_revision` dispara Programación → `proceso_realizado` (auto) | ✅ Implementado |
| S7 | Todo cambio de estado guarda fecha/hora, estado anterior/nuevo, observación, usuario en `estado_historial` | ✅ Implementado (vía `cambiarEstado*()`) |
| S8 | Modalidad editable; al cambiar aplica regla a programaciones NUEVAS; citas ya agendadas se respetan | ✅ Implementado |

> ⚠️ **Deuda técnica clave:** `programarEvaluacion()`/`reprogramarEvaluacion()` asignan `estado_programacion` directamente sin pasar por `cambiarEstadoProgramacion()`, por lo que **NO validan transición ni registran historial** de programación. Refactorizar al implementar la sinergia.

---

### Decisión arquitectural

El campo único `estado_evaluacion` (15 valores) se **descompone en 4 estados independientes**. Cada uno responde a una sola pregunta y se muestra como badge separado en las vistas.

| Campo nuevo | Pregunta | Visibilidad |
|-------------|----------|-------------|
| `estado_formulario` (redefinido) | ¿Llenó el formulario? | REPRO, Cliente, Candidato |
| `estado_programacion` (**NUEVO**) | ¿Ya hay cita? | REPRO, Cliente |
| `estado_evaluacion` (redefinido, 7 valores) | ¿Etapa técnica? | REPRO, Cliente |
| `estado_orden` (= `Orden.estado`, simplificado a 4) | Estado interno | **Oculto** salvo "Mis Órdenes" / Listado empresas |

### Máquina de estados estricta (Estado Actual → Siguientes Permitidos)

**1. Formulario** (automático por tiempo/acción):
```
Link enviado → {Pendiente de llenar (auto 24h), Llenando (al abrir)}
Pendiente de llenar → {Llenando, Vencido (auto 30 días)}
Llenando → {Formulario completado y recibido, Vencido}
Formulario completado y recibido → (final)
Vencido → {Llenando, Formulario completado y recibido}   // si reutiliza el enlace
```

**2. Evaluación** (manual salvo el último paso):
```
Pendiente de evaluación → {En proceso, Cancelado, Desistió}
En proceso            → {En revisión}
En revisión           → {Resultado Preliminar}
Resultado Preliminar  → {Informe final enviado}        // auto al subir informe final
Informe final enviado → (final)
Cancelado / Desistió  → (final)
```
> Restricción dura: `Cancelado`/`Desistió` SOLO desde `Pendiente de evaluación`. A partir de `En proceso` el flujo es irreversible (no retrocede ni se cancela).

**3. Programación** (mixto):
```
Contactando      → {Contactado, Programado, Desistió, Cancelado}
Contactado       → {Programado, Reprogramado, Desistió, Cancelado}
Programado       → {Asistió, Inasistencia, Reprogramado, Desistió, Cancelado}
Asistió          → {Proceso realizado}
Reprogramado     → {Programado}
Proceso realizado→ (final del tramo)
```
> `Programado` (auto al calendarizar). `Proceso realizado` (auto cuando Evaluación → En revisión). `Inasistencia` solo después de la hora programada.

**4. Orden** (100% automático, invisible):
```
Orden recibida → En proceso → {Entregado | Cancelado}
```
- `En proceso`: ≥1 candidato sale de su estado inicial.
- `Entregado`: TODOS los candidatos en `Informe final enviado` / `Cancelado` / `Desistió`.
- `Cancelado`: TODOS en `Cancelado` / `Desistió`.

### Reglas de sinergia (cruce entre estados)

1. Estados iniciales auto: Formulario=`Link enviado`, Evaluación=`Pendiente de evaluación`, Programación=`Contactando`, Orden=`Orden recibida`.
2. **Virtual:** Formulario debe estar `Completado y recibido` antes de `Programado`.
3. **Presencial:** puede programarse con formulario en `Link enviado`/`Pendiente`/`Llenando`.
4. Evaluación no entra a `En proceso` si Formulario ≠ `Completado y recibido` (virtual y presencial).
5. Evaluación no entra a `En proceso` sin haber pasado por `Programado`.
6. Evaluación → `En revisión` dispara Programación → `Proceso realizado` (auto).
7. `Inasistencia`/`Desistió`/`Cancelado` en Programación bloquean el avance de Evaluación.
8. Todo cambio guarda: fecha/hora, estado anterior, estado nuevo, observación opcional.

### Cambios de base de datos

| Cambio | Detalle |
|--------|---------|
| Nueva columna | `estado_programacion VARCHAR` en `evaluados_orden` (default `contactando`) |
| Redefinir | `estado_evaluacion` → 7 valores nuevos (mapeo desde los 15 actuales) |
| Confirmar | `estado_formulario` con los 5 valores nuevos |
| Simplificar | `Orden.estado` a 4 valores automáticos (eliminar autorización/requisito/informe preliminar) |
| Nueva tabla | `estado_historial` (evaluado_id, campo, estado_anterior, estado_nuevo, observacion, user_id, created_at) |
| Nuevo campo | `modalidad` (virtual/presencial) en `evaluados_orden` o `ordenes` — **confirmar si ya existe** |
| Nuevo campo | `motivo_observacion` transversal (o se deriva de `estado_historial`) |

### Job programado (scheduler)

- `app/Console/Kernel.php`: comando que cada hora revisa formularios:
  - `Link enviado` con +24h sin abrir → `Pendiente de llenar`
  - cualquier estado no completado con +30 días → `Vencido`
- Requiere cron activo en producción (iPage) — **verificar disponibilidad de cron**; si no, fallback con check on-access.

### Migración de datos en producción

Mapear cada `estado_evaluacion` actual a los 3 campos nuevos:

| Estado actual | → Formulario | → Programación | → Evaluación |
|---------------|--------------|----------------|--------------|
| pendiente | Link enviado | Contactando | Pendiente de evaluación |
| contactando/contactado | (según cuestionario) | Contactado | Pendiente de evaluación |
| link_enviado | Link enviado | Contactando | Pendiente de evaluación |
| confirmado/programado | (según cuestionario) | Programado | Pendiente de evaluación |
| en_sede | Completado y recibido | Asistió | Pendiente de evaluación |
| docs_pendientes | Completado y recibido | Programado | Pendiente de evaluación |
| en_proceso | Completado y recibido | Proceso realizado | En proceso |
| completado | Completado y recibido | Proceso realizado | Informe final enviado |
| inasistencia | (sin cambio) | Inasistencia | Pendiente de evaluación |
| reprogramado | (sin cambio) | Reprogramado | Pendiente de evaluación |
| cancelado | (sin cambio) | Cancelado | Cancelado |
| desistio | (sin cambio) | Desistió | Desistió |

### Cronograma — 4 semanas

**Semana 1 — Base de datos y modelos**
- Migraciones: `estado_programacion`, redefinir `estado_evaluacion`, simplificar `Orden.estado`, tabla `estado_historial`, campo `modalidad`.
- Backend: actualizar `EvaluadoOrden` (casts, accessors de texto/color para los 3 campos), `Orden::estadosDisponibles()` a 4 valores.
- Script de migración de datos (mapeo de la tabla anterior) idempotente para producción.

**Semana 2 — Lógica de transiciones, sinergia y jobs**
- Backend: máquinas de estado por campo (`transicionesFormulario/Programacion/Evaluacion()`), validación de transiciones.
- Reglas de sinergia (virtual/presencial, En revisión → Proceso realizado, gating de En proceso).
- Recalculo automático de `estado_orden`.
- Registro en `estado_historial` en cada cambio.
- Job/comando programado (24h → Pendiente de llenar, 30 días → Vencido).
- Controladores: `OrdenesController::cambiarEstadoEvaluado()`, `CalendarioController::programar/reprogramar()`, `CuestionarioController::completar()`.

**Semana 3 — Frontend / vistas**
- Selector dinámico que muestra solo "siguientes estados permitidos" por campo.
- 3 badges (Formulario/Programación/Evaluación) en: listado órdenes, listado evaluados, cuestionarios (cliente), reportes.
- Vista simplificada del candidato (4 estados).
- Panel/modal de historial de cambios con fecha/hora.
- Campo Motivo/Observación en cambios de estado.
- Renombrar columnas Listado de empresas: "Estado" → "Estado de Orden", "Registro" → "Fecha de registro".

**Semana 4 — Tests, QA y deploy**
- Tests de máquina de estados (transiciones válidas/inválidas por campo).
- Tests de sinergia (gating virtual/presencial, auto Proceso realizado, recalculo de Orden).
- Tests del job programado (24h/30 días).
- Test de migración de datos.
- Deploy FTP iPage + verificación MD5 + limpieza de caché + verificación de cron.

### Archivos clave a modificar

- `app/Models/EvaluadoOrden.php` — 3 máquinas de estado, accessors, casts.
- `app/Models/Orden.php` — `estadosDisponibles()` a 4 automáticos + recalculo.
- `app/Http/Controllers/Admin/OrdenesController.php` — `cambiarEstadoEvaluado()`, gating.
- `app/Http/Controllers/Admin/CalendarioController.php` — `programar()`, `reprogramar()`.
- `app/Http/Controllers/CuestionarioController.php` — `completar()` (sincroniza estado_formulario).
- `app/Console/Kernel.php` + nuevo comando — auto-transiciones por tiempo.
- Vistas: `admin/ordenes/index`, `admin/ordenes/show`, `reportes/evaluaciones`, `cuestionarios/index`, `empresa/ordenes/*`, vista candidato.

### Contradicciones detectadas (requieren confirmación del cliente)

1. **Captura (1 estado lineal) vs PDFs (4 estados):** la captura muestra `Solicitud → Link Enviado → ... → Completado` en una sola línea; los PDF definen 4 estados separados. Se asume **PDFs definitivos** — confirmar.
2. **"En Proceso" manual vs automático:** `Listado de estados` dice que debe ser manual; `ESTADOS` lo lista manual también, pero el Lote D lo hizo automático al subir preliminar. Revertir a manual.
3. **"Desistió" reactivable:** `Listado de estados` lo quiere reactivable como Cancelado; `ESTADOS` lo trata como final. Confirmar.
4. **Nomenclatura Orden:** `Listado de estados` pide `Pendiente → Recibida`; `ESTADOS` usa `Orden recibida`. Confirmar.
5. **Campo `modalidad`** (virtual/presencial): confirmar si ya existe o se crea.
6. **Motivo/Observación:** obligatorio u opcional (el PDF dice ambas cosas en secciones distintas).

### ✅ Respuestas del cliente confirmadas (2026-06-04)

El cliente (Otto Szarata) devolvió el informe con las respuestas dentro de cada pregunta + comentarios finales. Decisiones cerradas:

| # | Pregunta | Respuesta del cliente | Impacto en el desarrollo |
|---|----------|------------------------|--------------------------|
| 1 | Modelo definitivo (4 estados) | **Sí**, los 4 estados independientes de los PDF son lo oficial. El último documento manda. | Se procede con el rediseño de 4 campos. |
| 2 | "En Proceso" ¿manual? | **Manual.** No al subir preliminar, sino **cuando se está realizando la prueba**. | Revertir auto-cambio del Lote D. `En proceso` solo manual. |
| 3 | Estado "Asistió" | **Se quita.** Programación queda: `Contactando → Contactado → Programado → Proceso realizado → Reprogramado → Inasistencia/Desistió/Cancelado`. Las notas aclaratorias cubren el caso. | **Eliminar `Asistió`** de Programación (8 valores, no 9). |
| 4 | Calendario vs Programación | **Son el mismo estado.** No hay estado de calendario separado; el calendario refleja el estado de Programación. | UN solo campo `estado_programacion`. El calendario lo lee. |
| 5 | Migración de datos | **Las órdenes actuales son de prueba, se pueden eliminar.** | No se requiere script de migración de datos complejo; se limpian las órdenes de prueba. |
| 6 | Motivo/Observación | **Sí**, mismo campo disponible para cualquier situación (salud, papelería, no quiso la prueba, etc.). | Campo de notas/observación transversal en cambios de estado. |
| 7 | Virtual vs Presencial | **RESUELTO (2026-06-04).** Se agrega campo `modalidad` (Presencial/Virtual) editable en la orden. Presencial: programa sin formulario completo. Virtual: exige formulario completado antes de programar. El cliente preguntó si la modalidad se puede editar → **Sí**, editable en cualquier momento con registro en historial. | Campo `modalidad` editable. Regla condicional al programar. Cambio de modalidad queda en `estado_historial`. Citas ya programadas se respetan; la regla aplica solo a programaciones nuevas. |
| 8 | "Desistió" reactivable | **Sí, que se pueda reactivar.** | `Desistió` reactivable (como `Cancelado`). |
| 9 | Notificación al creador | **Sí**, correcto; y otras notificaciones nuevas también. | Aplicar matriz de notificaciones del documento. |

**Comentarios adicionales del cliente:**
- **Estado de Orden:** solo debe quedar lo del último documento (un solo cuadro, no dos).
- **Notificaciones al candidato (NUEVO requerimiento):** el cliente pregunta si el candidato puede recibir notificaciones (formulario recibido, papelería validada y aceptada, fecha de programación). → **Evaluar canal**: el candidato no tiene login (accede por token), así que sería por **correo electrónico** o WhatsApp, no in-app.
- **WhatsApp API (NUEVO requerimiento):** el cliente pregunta si se puede integrar WhatsApp API. → **Recomendación: NO en esta fase.** Diferir a una fase posterior (costos de API de Meta, verificación de número, plantillas aprobadas). Cotizar aparte.
- **Aclaración importante:** los estados "ya existían, no se creó ninguno" (estado de orden, evaluación, link, calendario/programación). El trabajo es **reorganizarlos en campos separados**, no inventar estados nuevos.

**Ajustes al plan tras las respuestas:**
- Programación pasa de 9 → **8 valores** (sin `Asistió`).
- No hay campo `estado_calendario`; el calendario consume `estado_programacion`.
- Migración de datos simplificada (órdenes actuales son de prueba → se pueden borrar).
- `En proceso` (Evaluación) **100% manual** (revertir Lote D 1b).
- `Desistió` reactivable.
- **Modalidad (pregunta 7) RESUELTA:** campo `modalidad` (Presencial/Virtual) **editable** en cualquier momento desde la orden. Al cambiarla, el sistema aplica la regla correcta a las programaciones nuevas (Virtual exige formulario completo; Presencial no). Las citas ya programadas se respetan. Todo cambio de modalidad se registra en `estado_historial` (fecha/hora/usuario).
- Backlog nuevo (fase posterior): notificaciones al candidato + WhatsApp API.

**Estado:** ✅ Todas las preguntas de clarificación están RESUELTAS. La Fase 18 está lista para iniciar desarrollo (Semana 1).

### ✅ Semana 1 — Base de datos y modelos (COMPLETADA 2026-06-09)

**Estado:** Migraciones ejecutadas, todos los tests pasan (166 tests, 343 assertions)

#### Archivos creados/modificados:

**Migraciones (4):**
1. ✅ `database/migrations/2026_06_04_100001_add_estado_programacion_to_evaluados_orden.php`
   - Agrega campo `estado_programacion` ENUM(8 valores) DEFAULT 'contactando'
2. ✅ `database/migrations/2026_06_04_100002_create_estado_historial_table.php`
   - Tabla nueva con evaluado_orden_id, orden_id, campo, estado_anterior/nuevo, observacion, user_id, timestamps
3. ✅ `database/migrations/2026_06_04_100003_redefine_estado_evaluacion_values.php`
   - Mapea 15 valores antiguos → 7 nuevos + redefine ENUM
4. ✅ `database/migrations/2026_06_04_100004_simplify_orden_estado_values.php`
   - Mapea 9 valores antiguos → 4 nuevos + redefine ENUM

**Modelos (3):**
1. ✅ `app/Models/EstadoHistorial.php` (nuevo)
   - Relaciones: evaluadoOrden(), orden(), usuario()
   - Scopes: deEvaluado(), deOrden(), deCampo(), masRecientes()
   - Métodos estáticos: obtenerHistorialEvaluado(), obtenerHistorialCampo()

2. ✅ `app/Models/EvaluadoOrden.php` (actualizado)
   - estadosFormularioDisponibles() → 5 valores actualizados
   - estadosProgramacionDisponibles() → 8 valores (sin Asistió)
   - estadosEvaluacionDisponibles() → 7 valores redefinidos
   - transicionesFormulario(), transicionesProgramacion(), transicionesEvaluacion()
   - puedeTransicionarEstado*() + cambiarEstado*() para 3 campos
   - Accessors: getEstadoFormularioTexto/Color, getEstadoProgramacionTexto/Color, getEstadoEvaluacionTexto/Color (actualizados)
   - Relación: historialEstados()
   - camposEstadoValidos() actualizado con 3 campos

3. ✅ `app/Models/Orden.php` (actualizado)
   - estadosDisponibles() → 4 valores (orden_recibida, en_proceso, entregado, cancelado)
   - recalcularEstado() → lógica automática basada en evaluados + registro en historial
   - getEstadoColorAttribute() actualizado para 4 valores

**Tests (2):**
1. ✅ `tests/Unit/Fase18MaquinaEstadosTest.php`
   - 20+ tests para transiciones de 3 estados
   - Tests de independencia entre estados
   - Tests de Orden.recalcularEstado() (4 casos)

2. ✅ `tests/Unit/Fase18SinergiaTest.php`
   - Tests modalidad virtual/presencial + formulario
   - Tests modalidad editable + historial
   - Tests En revisión → Proceso realizado
   - Tests relaciones + accessors

#### Pasos para ejecutar:

```bash
# 1. Ejecutar migraciones
docker compose exec -T app php artisan migrate --no-interaction

# 2. Verificar esquema
docker compose exec -T app php artisan migrate:status

# 3. Ejecutar tests de Fase 18
docker compose exec -T app php artisan test --filter=Fase18

# 4. Si tests pasan, ejecutar suite completa
docker compose exec -T app php artisan test
```

#### Reglas implementadas:

✅ **Programación** = 8 valores (NO "Asistió" per respuesta cliente #3)  
✅ **Evaluación "En proceso"** = 100% manual (revertir Lote D 1b si existe, per respuesta cliente #2)  
✅ **Desistió** = reactivable a "Contactando" (per respuesta cliente #8)  
✅ **Modalidad** = editable anytime + historial + reglas hacia adelante (Virtual require formulario, Presencial libre)  
✅ **Historial** = registro automático en estado_historial para todos los cambios de estado + cambios de modalidad  
✅ **Orden** = recalcularEstado() automático basado en todos los evaluados

#### Verificaciones completadas (2026-06-09):

- [x] Migraciones ejecutadas exitosamente (5 migraciones Fase 18 + fix estado_formulario ENUM)
- [x] Tests Fase18MaquinaEstadosTest pasan (44 tests ✅)
- [x] Tests Fase18SinergiaTest pasan (12 tests ✅)
- [x] Tests Fase5FlujosYCierreTest pasan (55 tests ✅)
- [x] Tests CalendarioTest pasan ✅
- [x] Tests CuestionarioTest pasan ✅
- [x] Tests AuditoriaSeguridadTest pasan (29 tests ✅)
- [x] estado_programacion ENUM(8 valores) en DB
- [x] tabla estado_historial con estructura correcta
- [x] estado_evaluacion redefinido a 7 valores
- [x] Orden.estado redefinido a 4 valores
- [x] estado_formulario redefinido a ENUM(5 valores)

---

### ✅ Semana 2 — Lógica de transiciones y sinergia (COMPLETADA 2026-06-10)

**Estado:** 211 tests pasando (464 assertions) — Semana 1 (166) + Semana 2 (45 nuevos).

**Archivos modificados / creados:**

- `app/Http/Controllers/Admin/OrdenesController.php` — Todos los estados viejos actualizados:
  - `resumen()`: `pendiente` → `orden_recibida`, `completada` → `entregado`
  - `destroy()`: condición de bloqueo actualizada a estados Fase 18
  - `cambiarEstado()`: validación → `'orden_recibida,en_proceso,entregado,cancelado'`
  - `store()/update()`: estado inicial evaluado → Formulario `link_pendiente`/`link_enviado` + Programación `contactando` + Evaluación `pendiente_de_evaluacion` *(corregido 2026-06-10)*
  - Auto-estado al crear evaluado con email → `cambiarEstadoFormulario('link_enviado')` (SOLO formulario; evaluación NO cambia) *(corregido 2026-06-10)*
  - Reenviar link → actualiza SOLO `estado_formulario` a `link_enviado` *(corregido 2026-06-10)*
  - `subirResultadoArchivo()` final → `estado_evaluacion = 'informe_final_enviado'` + `recalcularEstado()` *(corregido 2026-06-10)*
  - `subirResultadoArchivo()` preliminar → sin auto-cambio de estado (respuesta cliente #2)
  - `rehabilitarCuestionario()` → reset SOLO `estado_formulario` a `link_pendiente` *(corregido 2026-06-10)*
  - `deshabilitarCuestionario()` → bloquea `estado_formulario = 'formulario_completado_y_recibido'`
- `app/Console/Commands/AutoTransicionarEstadosFormulario.php` (NUEVO) — Job:
  - `link_enviado` +24h (sin expirar) → `pendiente_de_llenar`
  - cualquier estado incompleto expirado → `vencido`
  - Opción `--dry-run` para previsualizar sin aplicar
- `app/Console/Kernel.php` — Programado cada hora (`formulario:auto-transiciones`)
- `app/Models/EvaluadoOrden.php` — Añadida transición `link_pendiente → vencido`
- `tests/Unit/Fase18AutoTransicionesTest.php` (NUEVO) — 8 tests del job programado
- `tests/Feature/Fase2DocumentacionTest.php` — Assertions actualizadas para Fase 18

**Nota sobre cron en producción:** El job `formulario:auto-transiciones` requiere cron activo en iPage. Si no hay cron, implementar fallback on-access (Semana 3/4).

---

### 🔴 Corrección crítica — `estado_evaluacion` rediseñado (2026-06-10)

**Problema detectado durante verificación manual:** al crear una orden, los 3 badges del candidato mostraban `Polígrafo / Link Enviado / Link Enviado`. El campo `estado_evaluacion` se había implementado (Semana 1) reutilizando por error los valores del FORMULARIO. La evaluación física es presencial o por videollamada y **nunca tiene "link"** — debía seguir el flujo técnico del PDF p.2.

**Valores ANTES (incorrectos)** → **DESPUÉS (correctos):**

| Antes (eran de formulario) | Después (PDF p.2) |
|----------------------------|-------------------|
| `link_pendiente` | `pendiente_de_evaluacion` *(inicial)* |
| `link_enviado` | *(eliminado)* |
| `pendiente_de_llenar` | *(eliminado)* |
| `en_proceso` | `en_proceso` *(se mantiene)* |
| `en_revision` | `en_revision` *(se mantiene)* |
| `completado` | `informe_final_enviado` *(renombrado)* |
| `cancelado` | `cancelado` *(se mantiene)* |
| *(faltaba)* | `resultado_preliminar` *(nuevo)* |
| *(faltaba)* | `desistio` *(nuevo, reactivable)* |

**Archivos modificados en la corrección:**
- ✅ `database/migrations/2026_06_10_100001_fix_estado_evaluacion_to_correct_values.php` (NUEVO) — VARCHAR temporal → mapeo → ENUM(7 correctos)
- ✅ `app/Models/EvaluadoOrden.php` — `estadosEvaluacionDisponibles()`, `transicionesEvaluacion()`, accessors texto/color, `evaluacionCompletada()`, scope `sinProgramar()`, `completarEvaluacion()`
- ✅ `app/Models/Orden.php` — `recalcularEstado()`: terminales = `informe_final_enviado`/`cancelado`/`desistio`; orden de chequeo `todosCancelados` ANTES de `todosCompletados`
- ✅ `app/Http/Controllers/Admin/OrdenesController.php` — estado inicial, upload final, reenviar link, resumen (`evaluadosCompletados`)
- ✅ `app/Http/Controllers/Admin/CalendarioController.php` — filtros de historial y disponibles
- ✅ `resources/views/empresa/ordenes/{index,show}.blade.php` + `admin/ordenes/{index,show}.blade.php` — botón Editar visible solo en `orden_recibida`
- ✅ Factory + ~15 archivos de tests actualizados a los estados correctos

**Resultado:** 528 tests pasando. 3 fallos restantes son PREEXISTENTES y sin relación (rutas de creación de usuario `insert-user`/`show-user` devuelven 404/403 — investigar aparte).

---

### ⏳ TRABAJO PENDIENTE (estructurado por prioridad)

#### ✅ Prioridad 1 — Reglas de sinergia (COMPLETADA — sesión 2026-06-10)

- [x] **S2 — Gating Virtual:** `CalendarioController::programar()` — si `modalidad === 'virtual'` y `estado_formulario !== 'formulario_completado_y_recibido'` → bloquea con error. `reprogramar()` NO bloquea (citas existentes se respetan).
- [x] **S4 — Gating En Proceso (formulario):** `EvaluadoOrden::cambiarEstadoEvaluacion('en_proceso')` lanza `ValidationException` si `estado_formulario !== 'formulario_completado_y_recibido'`.
- [x] **S5 — Gating En Proceso (programado):** `cambiarEstadoEvaluacion('en_proceso')` lanza `ValidationException` si `estado_programacion` no es `programado` ni `proceso_realizado`.
- [x] **S6 — Auto Proceso Realizado:** `cambiarEstadoEvaluacion('en_revision')` dispara automáticamente `cambiarEstadoProgramacion('proceso_realizado')` (solo si estaba en `programado`).
- [x] **Deuda técnica:** `programarEvaluacion()`/`reprogramarEvaluacion()` ahora registran historial de `estado_programacion` mediante `registrarCambioEstado()`.
- [x] **Manejo de excepciones en controller:** `OrdenesController::cambiarEstadoEvaluado()` captura `ValidationException` de S4/S5 y devuelve mensaje de error claro.
- [x] Tests de sinergia: `tests/Feature/Fase18SinergiaReglasSemana3Test.php` — 16 tests (S2×4, S4×2, S5×2, S6×3, modalidad×3, historial×2).

#### 📋 Especificación funcional — Selector de Modalidad (Presencial/Virtual) [CONFIRMADO por cliente 2026-06-04]

> **Acuerdo textual con el cliente (chat 04/06/2026, Otto Szarata ↔ Stephany Castro Repro).** Propuesta presentada y confirmada ("si todo esta bien comienza con tu recomendación"). Este es el alcance exacto a desarrollar.

**Qué pidió el cliente (literal):**
- Agregar al **crear/editar la orden** un selector simple de modalidad con dos opciones: **Presencial** o **Virtual**.
- **Presencial:** se puede programar la cita aunque el formulario NO esté completo (el candidato lo llena en oficina). Sin restricción.
- **Virtual:** el sistema EXIGE que el formulario esté `formulario_completado_y_recibido` antes de poder programar (no irá presencialmente a llenarlo).
- El sistema aplica la regla automáticamente según el tipo de proceso (sin que el personal lo recuerde manualmente).
- **Editable en cualquier momento** desde la orden. Al cambiar, la regla correcta aplica **desde ese momento hacia adelante**.
- **Consideración crítica confirmada:** si una orden ya tenía una **cita programada siendo Presencial** (sin formulario completo) y luego la cambian a **Virtual**, el sistema **respeta esa cita ya agendada (NO la elimina)**. La regla "formulario antes de programar" solo aplica a **programaciones NUEVAS** posteriores al cambio.
- Todo cambio de modalidad queda registrado en el historial (fecha/hora/usuario).

**Estado actual del código (COMPLETADO 2026-06-10):**
- ✅ Campo `modalidad` ENUM(`presencial`,`virtual`) NULLABLE en `evaluados_orden`.
- ✅ Selector Presencial/Virtual en `admin/ordenes/create.blade.php` (template JS por evaluado).
- ✅ Selector Presencial/Virtual en `admin/ordenes/edit.blade.php` (evaluados existentes y template nuevo evaluado).
- ✅ `OrdenesController` guarda `modalidad` al crear y actualizar. Registra cambio en `estado_historial` si cambia al editar.
- ✅ S2 gating implementado en `CalendarioController::programar()`. `reprogramar()` no bloquea (citas existentes respetadas).
- ✅ Default `presencial` — sin restricción al programar.

#### 🎨 Prioridad 2 — Semana 3 (Frontend / vistas)
- [x] **3 badges separados** (Formulario/Programación/Evaluación) en listado de órdenes admin (`admin/ordenes/index.blade.php`). Cada evaluado muestra sus 3 estados + fecha cita si aplica.
- [x] **Limpiar selector manual de estado de Orden** en `admin/ordenes/show.blade.php`: reemplazadas 9 opciones viejas por las 4 correctas (`orden_recibida`, `en_proceso`, `entregado`, `cancelado`) con nota informativa.
- [x] **Actualizar mapas de badges** en: `admin/index.blade.php` (2 lugares), `admin/ordenes/pdf.blade.php`, `admin/ordenes/edit.blade.php` → usan accessors `estado_color`/`estado_human` del modelo.
- [x] **Renombrar columnas** Listado empresa: "Estado" → "Estado de Orden", "Fecha Creación" → "Fecha de Registro". Limpiados estados viejos en lógica de permisos de las vistas empresa.
- [x] **Selector dinámico** los 3 campos en `admin/ordenes/show.blade.php`: Evaluación, Formulario, **y Programación** — cada uno muestra SOLO los estados permitidos desde el estado actual (usa `transicionesEvaluacion()`, `transicionesFormulario()`, `transicionesProgramacion()`). `OrdenesController::cambiarEstadoEvaluado()` extendido para soportar `tipo_estado=programacion`.
- [x] **Panel de historial** de cambios (`estado_historial`) en `admin/ordenes/show.blade.php` — expandible con `<details>`, muestra campo, estado anterior→nuevo, usuario y observación de cada entrada registrada.
- [x] **Campo Motivo/Observación** en los cambios de estado — textarea colapsable con `<details>` en los 3 formularios de cambio de estado. `cambiarEstadoEvaluacion/Formulario/Programacion()` aceptan `?string $observacion`. Controller valida `observacion` (nullable, max 1000) y la pasa al modelo. Se persiste en `estado_historial.observacion` y se muestra en el historial.
- [x] **Vista candidato** simplificada — `cuestionario/estado.blade.php` con timeline Bootstrap de 4 pasos (Formulario → Evaluación → Revisión → Informe final). Ruta `cuestionario.estado` (`GET /{token}/estado`). `mostrar()` redirige al estado cuando el formulario ya está completado. Cada paso muestra estado visual (✅ done / 🔵 active / gris pending), fecha de cita y modalidad si están disponibles. No expone estados internos al candidato.

#### 🔔 Prioridad 3 — Notificaciones (matriz del PDF p.4)
- [x] Nueva orden creada → **incluye al creador** como confirmación (eliminado `where('id', '!=', Auth::id())`).
- [x] Candidato completó cuestionario → **ahora también la empresa** recibe notificación in-app (`CuestionarioController::notificarCuestionarioCompletado`).
- [x] Resultado preliminar subido → **ahora también colaboradores** (role_as >= 2 en lugar de >= 3 en `notificarPreliminarSubido`).
- [x] Informe final disponible → **admin y colaborador** reciben notificación in-app además de la empresa (`notificarResultadosDisponibles` — nuevo bloque `$usuariosRepro`).

#### 🧪 Prioridad 4 — Semana 4 (QA y deploy)
- [x] **Migración de datos en producción** — 6 migraciones Fase 18 aplicadas vía `deploy_migrate_fase18.php` (2026-06-10). Datos de prueba reacomodados a 4 estados. ENUM `estado_evaluacion` correcto verificado.
- [x] **Deploy FTP iPage** — `/reproappv2` · commit `53c6487c` · 17/17 archivos críticos verificados · caché + OPcache limpiados · sitio HTTP 200.
- [x] **Informe cliente** — `docs/Informe_Cliente_2026-06-10.md` enviado al cliente (10/06/2026).
- [ ] Verificar cron activo en iPage para `formulario:auto-transiciones`; si no, implementar fallback on-access. *(único pendiente post-Fase 18)*
- [x] **Resolver tests preexistentes de creación de usuario** (`Phase8CEstadosUXTest`):
  - `Phase8CEstadosUXTest` migrado a usar `CreatesRolesAndPermissions` (eliminado setUp manual sin level ni permisos).
  - `test_admin_puede_crear_usuario_repro_con_sede`: ahora pasa `role_id` del rol 'repro' en lugar de `role_as => 2`.
  - `test_admin_show_usuario_repro_muestra_sede`: se resuelve con el trait (admin ahora tiene `usuarios.ver` permission).
  - `CreatesRolesAndPermissions` trait mejorado: roles creados con `level` correcto (admin=3, repro=2, empresa=1), admin recibe todos los permisos del sistema.

#### 📦 Backlog (fase posterior, fuera de Fase 18)
- [ ] Notificaciones al candidato por correo/WhatsApp (formulario recibido, papelería aceptada, fecha de programación).
- [ ] Integración WhatsApp API (diferida — costos Meta, plantillas aprobadas, verificación de número).
- [ ] Fallback on-access para `formulario:auto-transiciones` si cron no está activo en iPage.
- [ ] Confirmar con cliente: estado "Llenando", reactivación de "Vencido", nombre "Papelería validada" en vista candidato, saltos adelante en Evaluación.

---

## ✅ Cierre Fase 18 — 2026-06-10

| Hito | Detalle |
|------|---------|
| **Commit código** | `53c6487c` — 57 archivos, Fase 18 completa |
| **Plataforma** | https://reproappv2.szystems.com |
| **Migraciones** | 6/6 aplicadas en `dbreprov2` (batch 105+) |
| **Informe cliente** | `docs/Informe_Cliente_2026-06-10.md` — enviado |
| **Documentación previa** | `docs/resumen_cambios_cliente.md` |
| **Pendiente operativo** | Cron iPage para auto-transiciones 24h/30d |

**Próximo desarrollo:** según feedback del cliente tras pruebas Fase 18 (ya desplegada).

---

## ✅ Cierre Fase 19 — 2026-06-13

| Hito | Detalle |
|------|---------|
| **Commit código** | `8093ab0a` — 73 archivos (fix duplicación, capacidad sede, historial empresa, archivar, búsqueda DPI/nombre) |
| **Commit manifiesto** | `14a95f47` — `docs/deployment/Fase19_deploy_manifest.txt` (58 archivos app) |
| **Plataforma** | https://reproappv2.szystems.com |
| **Migraciones** | 2/2 aplicadas en `dbreprov2` (batch 111): `historial_visible_empresa`, `ordenes.archivada` |
| **Verificación deploy** | 58/58 tamaños OK en FTP · MD5 OK en 6 archivos críticos · HTTP 200 |
| **Informe cliente** | `docs/Informe_Cliente_2026-06-12_Fase19.md` |
| **Tests** | 653 pasando (`Fase19Sprint3Test`, sinergia S4/S5, `CalendarioTest` capacidad sede) |

### Entregables Fase 19 (Sprints 1–4)

| Sprint | Entregas |
|--------|----------|
| 1 P0 | Fix duplicación al editar orden · capacidad por sede · quitar S2 (Virtual sin formulario al programar) |
| 2 UI | Etiquetas "Estado de…" · cuestionarios 3 estados + progreso · reportes · calendario inasistencia · sidebar informes · poligrafista/responsable opcionales |
| 3 Features | Historial visible empresa (config default ON) · historial búsqueda por nombre · archivar órdenes (solo admin) · búsqueda dashboard cliente |
| 4 QA/Deploy | Regresión S4/S5 · 653 tests · informe cliente · deploy iPage verificado |

### Reglas de sinergia vigentes (post-Fase 19)

- **S4 ACTIVO:** En Proceso exige formulario completado
- **S5 ACTIVO:** En Proceso exige haber estado Programado
- **S2 ELIMINADO:** Virtual puede programarse sin formulario completado
- **Capacidad:** límite por `sedes.capacidad`, no por poligrafista
- **Órdenes:** archivar (admin), no borrar

**Pendiente operativo (heredado):** Cron iPage para `formulario:auto-transiciones` 24h/30d; fallback on-access si no hay cron.

**Próximo desarrollo:** Fase 20 hotfix enlace cuestionario (reporte cliente 404) → deploy cuando tests pasen.

---

## FASE 20 — Hotfix enlace cuestionario (2026-06-16)

**Origen:** Cliente reportó 404 al abrir enlace de formulario en pruebas recientes. Análisis: el 404 genérico ocultaba token inválido/expirado; posible `dias_vigencia_token = 0` en BD.

### Objetivo
Mejorar diagnóstico y UX del acceso público `/cuestionario/{token}` sin cambiar el flujo principal.

### Alcance (paquete mínimo)

| # | Item | Estado |
|---|------|--------|
| 1 | Vista dedicada `cuestionario/enlace-invalido` (token no encontrado vs expirado) | ✅ |
| 2 | Logging en acceso rechazado (`motivo`, prefijo token, evaluado_id) | ✅ |
| 3 | `Config::diasVigenciaTokenEnlace()` con mínimo 1 día (0 → 30) | ✅ |
| 4 | `EvaluadoOrden::calcularExpiracionToken()` centralizado en OrdenesController | ✅ |
| 5 | Fix `getUrlCuestionario()` → ruta `cuestionario.mostrar` | ✅ |
| 6 | Tests `CuestionarioEnlaceInvalidoTest` | ✅ (5 tests OK 2026-06-16) |

### Pendiente Fase 20 (post-hotfix)
- Validar expiración en `terminos()` / `estadoCandidato()` (consistencia)
- Diagnosticar caso concreto del cliente en producción (URL + BD + logs `Acceso a cuestionario rechazado`)
- Push GitHub commit `45c89dc5` (pendiente aprobación remota)

### Deploy 2026-06-16
- **FTP:** 5 archivos según `docs/deployment/Fase20_deploy_manifest.txt`
- **Migraciones:** ninguna
- **Caché:** `deploy_cache_fase20.php` ejecutado y auto-eliminado (56 vistas + OPcache)
- **Verificación:** `/login` HTTP 200 · `/cuestionario/token-invalido-*` muestra vista dedicada

### Archivos tocados
- `app/Http/Controllers/CuestionarioController.php`
- `app/Http/Controllers/Admin/OrdenesController.php`
- `app/Models/Config.php`, `app/Models/EvaluadoOrden.php`
- `resources/views/cuestionario/enlace-invalido.blade.php`
- `tests/Feature/CuestionarioEnlaceInvalidoTest.php`
- `tests/Feature/CuestionarioModuloCompletoTest.php`
