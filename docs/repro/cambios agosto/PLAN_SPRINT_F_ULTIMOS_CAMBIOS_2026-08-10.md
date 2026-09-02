# Sprint F — Últimos cambios + informes Word por servicio

**Cliente:** Stephany Castro / REPRO  
**Fecha inicio:** 10 de agosto de 2026  
**Estado:** 🟢 DESPLEGADO EN PROD (11 ago 2026)  

 
**Fuentes:** `ULTIMOS CAMBIOS.pdf` · `FORMATOS.pdf` · 7× `.docx` finales · audios WA 7-ago / 10-ago · capturas WhatsApp  
**Carpeta:** `docs/repro/cambios agosto/`

---

## Objetivo

1. Pulir detalles post-UAT (`ULTIMOS CAMBIOS.pdf`).
2. Corregir Word “en blanco” (datos editados no salen en el `.docx`).
3. Generar **un informe Word por servicio/tipo**, como las autorizaciones (7 plantillas cliente).

**No rehacer:** formularios E1–E6 ni Sprints A–E ya en prod. Conservar lógica de relleno; adaptar diseño.

---

## Sprints

| Sprint | Alcance | Estado |
|--------|---------|--------|
| **F0 — Fixes seguros** | 1 fecha auth · quitar esclarecimiento · tel padres opcional · ASPECTOS VARIOS · ocultar eliminar orden cliente | ✅ |
| **F1 — Word relleno** | Celdas multi-run + encabezado/datos editados en Word | ✅ |
| **F2 — Formulario/ops** | Académico 2 niveles · motivo reprogramación · ocultar poligrafista al agendar · PDF estudia actualmente · selects residuales · REPRO reclutador | ✅ |
| **F3 — 7 plantillas Word** | Matriz v2 + FORMATOS (VSA/socio/foto/preguntas) | ✅ |
| **Deploy** | iPage + migrate `motivo_reprogramacion` | ✅ |
| **UAT** | Smoke + navegador prod (Word×3, UI reprogramar, VSA sin score, PDFs, órdenes) | ✅ 10-ago noche |

---

## Checklist F0

| # | Cambio | Archivos | Estado |
|---|--------|----------|--------|
| F0.1 | Una sola Fecha/Lugar en autorización (quitar duplicado config + partial) | `autorizaciones_legales.php`, `autorizacion-legal.blade.php`, `infornet.blade.php` | ✅ |
| F0.2 | Específica: borrar línea “esclarecimiento de: ___” (polígrafo + VSA) | `autorizaciones_legales.php` | ✅ |
| F0.3 | Teléfono padres no obligatorio | `InformacionFamiliarPadres.php`, `datos-progenitor.blade.php` | ✅ |
| F0.4 | Título sustancias → **ASPECTOS VARIOS** | `SaludHabitosCampos.php` | ✅ |
| F0.5 | Admin cliente NO elimina órdenes (solo REPRO archiva) | `admin/ordenes/index`, `empresa/ordenes/index` | ✅ |

## Checklist F1

| # | Cambio | Notas | Estado |
|---|--------|-------|--------|
| F1.1 | Reproducir Word en blanco con evaluado preempleo con datos | Audio 1 + tests F1 | ✅ |
| F1.2 | Fix relleno celdas multi-run + encabezado/tablas | `InformeWordXml::establecerTextoCelda`, tests F1 | ✅ |

## Checklist F2 (resumen)

| # | Cambio | Estado |
|---|--------|--------|
| F2.1 | Regla académico últimos 2 niveles (tabla ULTIMOS CAMBIOS) | ✅ |
| F2.2 | Motivo al reprogramar | ✅ |
| F2.3 | No mostrar poligrafista/responsable al agendar | ✅ |
| F2.4 | Tablas informe: académica + estudios actuales | ✅ |
| F2.5 | PDF candidato/empresa: tabla estudia actualmente | ✅ |
| F2.6 | Verificar REPRO puede asignar reclutador confidencial | ✅ (Sprint E; UI create/edit) |
| F2.7 | Select condicional residual → «Seleccione…» | ✅ (sin residual «excelente») |

## Checklist F3 — plantillas Word

| Plantilla cliente | Runtime (`resources/templates/`) | Estado |
|-------------------|----------------------------------|--------|
| Polígrafo preempleo | `informe-poligrafo-preempleo-v2.docx` | ✅ matriz + encabezado |
| Polígrafo periódico | `informe-poligrafo-periodica-v2.docx` | ✅ matriz + encabezado |
| Polígrafo específico | `informe-poligrafo-especifica-v2.docx` | ✅ matriz + encabezado |
| VSA preempleo | `informe-vsa-preempleo-v2.docx` | ✅ matriz + encabezado |
| VSA periódico | `informe-vsa-periodica-v2.docx` | ✅ matriz + encabezado |
| VSA específico | `informe-vsa-especifica-v2.docx` | ✅ matriz + encabezado |
| Socioeconómico | `informe-socioeconomico-v2.docx` | ✅ matriz + encabezado |

**Hecho 10-ago:** matriz 7 plantillas v2 · encabezado · FORMATOS periódica · VSA sin puntuación (UI + Word) · socio refs familiares/amistades/laborales + presupuesto/patrimonio · foto ancla `Agencia/Sede`/`DATOS GENERALES` · preguntas v2.  
**Conclusiones socio / verificación estatus:** quedan editables en plantilla (trabajo de evaluador); no auto-relleno desde formulario.  
**Pendiente ops:** deploy + migrate + 3 respuestas a dudas cliente.

**Diferencias (FORMATOS.pdf):** tipografía Helvetica; periódico/específico omiten pareja/hermanos + procedimiento con motivo desde orden + académico último grado + laboral solo empleo actual + preguntas editables en blanco; VSA sin puntuación / 1 firma / equipo CVSA III; socio refs + económica form + conclusiones socio.

**Enfoque cliente (audio 1):** base diseño plantillas en blanco + mezcla con tablas del sistema para autoalimentar desde formulario.

---

## Dudas cliente (respuesta ops, no código)

| # | Pregunta cliente | Estado / decisión (Otto, 10-ago) | Bloquea |
|---|------------------|----------------------------------|---------|
| **1** | ¿Las tablas del evaluador alimentan el Word o también hay que editar candidato? | ✅ **Cerrada 10-ago (Stephany):** «Son las mismas» — las tablas que edita el evaluador son las que van al Word; no hace falta re-editar candidato a mano para el informe. | — |
| **2** | ¿Migración a servidor principal o nueva página? | ⏸️ Pendiente cutover. Se elegirá **nuevo dominio** con el cliente y luego migración al **nuevo servidor** (no cutover inmediato desde `reproappv2`). | Go-live / DNS / BD definitiva |
| **3** | ¿Capacitación o manual de usuario? | ✅ Alcance de cierre: módulo de **ayuda / tutorial rápido** en el sistema + posiblemente **capacitación virtual**. Se hace al final de todo. | Handover / adopción |

**Prod actual mientras tanto:** `https://reproappv2.szystems.com` (staging/prod de trabajo).

---

## Historial

| Fecha | Trabajo |
|-------|---------|
| 2026-08-10 | Plan creado. Inventario WA + PDFs + 7 docx + transcripciones audio. |
| 2026-08-10 | **F0 cerrado** (fechas auth, esclarecimiento, padres tel, ASPECTOS VARIOS, ocultar eliminar). |
| 2026-08-10 | **F1** relleno celdas multi-run + test datos familiares. **F3 base:** 7 plantillas v2 en `resources/templates/`, resolver matriz, encabezado DATOS GENERALES. |
| 2026-08-10 | **F3 FORMATOS parcial:** motivo procedimiento, omitir pareja periódica, último grado académico, test VSA específica. |
| 2026-08-10 | **F2+F3 cerrados en código:** motivo reprogramación, ocultar poligrafista, PDF estudios actuales, socio refs laborales, foto tests v2, suite 38 OK. |
| 2026-08-11 | **Deploy prod:** 95 archivos FTP · migrate `motivo_reprogramacion` DONE · OPcache/vistas limpiados · login 200. Manifiesto `docs/deployment/SprintF_2026-08-10_deploy_manifest.txt`. |
| 2026-08-11 | **Smoke prod OK:** 7 plantillas v2 · Word polígrafo/VSA/socio (441/398/460 KB) · VSA sin puntuación · reprogramar motivo · UI sin poligrafista · controller download `.docx`. |
| 2026-08-10 | **Dudas ops:** #1 enviada al cliente (espera) · #2 cutover diferido a nuevo dominio/servidor · #3 ayuda/tutorial + capacitación virtual al cierre. |
| 2026-08-10 | **Duda #1 cerrada (Stephany WA 21:35):** «Son las mismas» — tablas del evaluador = datos del Word. |
