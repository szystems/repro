# Sprint G — Feedback cliente 12-ago-2026

**Cliente:** Stephany Castro / REPRO  
**Fecha inicio:** 12 de agosto de 2026  
**Estado:** 🟢 CERRADO EN PROD — G1–G5 deploy 13-ago · **UAT 13-ago reabre ítems → Sprint H**  
**Prod:** https://reproappv2.szystems.com  

**Fuentes:**
- WhatsApp 12-ago (mañana): informe Word en blanco + confusión informe vs formulario
- `docs/repro/cambios agosto/ultimos cambios 12-08-2026/ULTIMOS CAMBIOS2.pdf` (8 págs)
- Captura: `ultimos cambios 12-08-2026/WhatsApp Image 2026-08-12 at 9.09.10 AM.jpeg`

**Sprint anterior:** Sprint F ✅ desplegado 11-ago (`PLAN_SPRINT_F_ULTIMOS_CAMBIOS_2026-08-10.md`)

---

## Objetivo

Cerrar el ciclo **formulario → tablas evaluador → Word → informe final subido → vista empresa**, que Stephany bloqueó en pruebas del 12-ago.

**Acuerdo con cliente (WA):** informes finales primero; módulo ayuda + capacitación virtual **después** de estabilizar informes.

---

## Resumen WhatsApp 12-ago

| Hora | Mensaje | Acción técnica |
|------|---------|----------------|
| 8:37 | Informe polígrafo preempleo sigue en blanco | G0 + G1.1 |
| 8:38 | Cuadro negro = falta foto; resto vacío; no enlaza formulario | G1.1 foto + relleno datos |
| 9:09 | En “Informe” aparece formulario candidato; debe verse final/preliminar subido | G2.1 |
| 9:43 | Prioridad: informes finales | Orden de sprints G1 → G2 antes de ayuda |
| 9:49 | Formularios validados — todos coinciden | ✅ Cerrado (no reprogramar formularios) |
| 10:38 | INFORNET: firma de arriba sin volver a firmar | G4.2 (imagen en PDF Infornet) |
| 10:38 | Adjuntó ULTIMOS CAMBIOS2.pdf | Checklist abajo |

---

## Tensión smoke vs cliente

| Hecho | Detalle |
|-------|---------|
| Smoke prod 10–11 ago | Word polígrafo/VSA/socio OK (~441/408/471 KB) con evaluados demo |
| Caso Stephany 12-ago | Nuevo preempleo polígrafo → Word vacío + silueta negra (plantilla v2 sin rellenar) |

**Hipótesis (orden):** deploy parcial · marcadores XML plantilla ≠ relleno · cuestionario sin datos vinculados · overrides tablas no persistidos · foto candidato ausente.

**Bloqueante G0:** pedir código de orden o ID evaluado del caso fallido y reproducir en prod.

---

## Matriz ULTIMOS CAMBIOS2.pdf

| # | Ítem | Estado post-F | Sprint G |
|---|------|---------------|----------|
| 1 | Infornet: reutilizar firma sin pedir firmar otra vez | Parcial (UI sí; PDF Infornet sin `<img>` firma) | G4.2 |
| 2 | Académico últimos 2 niveles | ✅ F2 | — |
| 3 | Dónde ver observación/motivo reprogramación | Campo existe; no visible post-guardado | G3.1 |
| 4 | Tablas informe: personal + tatuajes + estudia actualmente | Parcial (Word/PDF estudios; faltan tablas editables REPRO) | G1.2 |
| 5 | Select condicional preseleccionado “excelente” → “Seleccione” | Abierto (`salud_estado_general` sin placeholder) | G4.1 |
| 6 | REPRO: habilitar/deshabilitar formulario + rehabilitar vencido | Parcial (solo `role_as>=2`; vencido = estado final) | G3.2 |
| 7 | Vista previa tras editar informe + Generar/Descargar informe final | Falta (modal solo enlaza PDF cuestionario + Word) | G1.3 |
| 8 | Cliente: historial cambios + observaciones REPRO | Bug (historial solo REPRO en vista unificada) | G2.2 |
| 9 | En INFORME: final/preliminar subidos, no formulario; papelería igual | ✅ G2.1 | G2.1 |
| 10 | Estado evaluación no cambia (orden/calendario) | Investigar reglas S4/S5 | G3.3 |
| 11 | Unificación información formularios → alimenta informe final | Épica parcial | G5 |
| 12 | Duda migración servidor/dominio | ⏸️ después | — |
| 13 | “Queda pendiente generación del informe final” | Bloqueante producto | G1 |

---

## Sprints propuestos

### G0 — Diagnóstico (primera sesión)

**Respuesta Stephany (12-ago noche):**
- Descarga desde **la orden** → botón «Descargar informe Word (.docx)» (no usa editar cuestionario).
- **Vista previa nunca le ha funcionado** → confirma bug G1.3 + posible expectativa distinta.
- **Todos los evaluados creados hoy** (12-ago) tienen el mismo problema — no un caso aislado.
- Pide capacitación («todo lo he adivinado») → después de informes (acordado).

- [x] Confirmar flujo: orden → Descargar informe Word ✅
- [ ] Listar evaluados/órdenes creados 2026-08-12 en prod
- [ ] Reproducir Word en al menos uno del lote (tamaño .docx + DATOS GENERALES)
- [ ] Verificar archivos prod: plantillas v2 + clases InformeWord*

### G1 — Informe Word y flujo evaluador (P0 cliente)

| # | Tarea | Archivos clave |
|---|-------|----------------|
| G1.1 | Corregir Word en blanco (encabezado, tablas, foto) | `InformeWordRelleno`, `InformeWordDatos`, `InformeWordFoto`, plantillas v2 |
| G1.2 | Tablas editables: información personal, tatuajes, estudia actualmente | `InformePreempleo`, `tablas-informe-preempleo.blade.php` |
| G1.3 | Vista previa real post-edición + botones “Generar borrador Word” / “Descargar informe final” | `admin/cuestionarios/edit.blade.php`, `OrdenesController` |

### G2 — Qué ve empresa vs REPRO (P0 cliente)

| # | Tarea | Archivos clave |
|---|-------|----------------|
| G2.1 | Priorizar archivos final/preliminar; no confundir con PDF cuestionario | ✅ `shared/partials/_informes_evaluado_empresa`, `empresa/cuestionarios/show`, `empresa/ordenes/show`, `reportes/evaluaciones` |
| G2.2 | Historial visible empresa (`Config::historialVisibleEmpresa()`) | `OrdenesController::show`, partial historial |
| G2.3 | Observaciones REPRO visibles para cliente donde corresponda | `evaluado.observaciones`, vistas empresa |

### G3 — Operaciones día a día

| # | Tarea |
|---|-------|
| G3.1 | Mostrar `motivo_reprogramacion` en ficha evaluado y calendario (lectura) |
| G3.2 | Rehabilitar formulario `vencido` + visibilidad habilitar/deshabilitar para REPRO |
| G3.3 | Reproducir bug estado evaluación; mensajes claros si fallan S4/S5 |

### G4 — Pulidos formulario

| # | Tarea |
|---|-------|
| G4.1 | `Seleccione...` en `salud_estado_general` y selects similares |
| G4.2 | Imagen `firma_digital` en sección Infornet del PDF autorización |

### G5 — Épica (después de G1–G2)

Consolidación única de datos del formulario + correcciones del evaluado → alimenta Word e informe final sin duplicidad.

---

## Flujo objetivo

```
Candidato → formulario completado
    → REPRO edita tablas + 6 bloques narrativos
    → Generar borrador Word (datos + foto)
    → Evaluador revisa Word externo
    → Subir preliminar / final (PDF)
    → Empresa descarga informes subidos (no PDF cuestionario como “informe”)
    → Historial + observaciones visibles según config
```

---

## Cómo comenzar (recomendado)

1. **Mensaje a Stephany** pidiendo código de orden del caso en blanco (1 línea).
2. **G0 en prod** con ese evaluado (curl o navegador REPRO).
3. Según causa: **G1.1** (relleno) antes que G1.2/G1.3.
4. En paralelo si hay tiempo: **G2.1** (UX informe empresa) — victoria visible rápida.

---

## Tests existentes a extender

```bash
docker run --rm --network repro_default -v /home/szott/proyectos/repro:/var/www -w /var/www repro-laravel-app \
  php -d memory_limit=512M vendor/bin/phpunit --filter='InformeWord|SprintF'
```

Añadir en G1: test regresión “Word preempleo con cuestionario completo no vacío en DATOS GENERALES”.

---

## Fuera de scope inmediato

- Módulo ayuda / tutorial (acordado al cierre)
- Capacitación virtual
- Cutover dominio/servidor (duda #2)
- Cambios en papelería/documentos candidato (cliente: “no tocarlo”)

---

## Historial

| Fecha | Trabajo |
|-------|---------|
| 2026-08-12 | Plan G creado tras WA + ULTIMOS CAMBIOS2.pdf. Sprint F cerrado en prod; cliente reporta Word vacío en caso real. |
| 2026-08-12 | Análisis código: historial empresa no expuesto en vista unificada; Infornet PDF sin imagen firma; tablas informe incompletas. |
| 2026-08-12 | **G0 respuesta Stephany (WA noche):** descarga desde **orden** → «Descargar informe Word»; **vista previa nunca le funcionó**; afecta **todos los evaluados creados hoy** (12-ago). |
| 2026-08-13 | **G0 prod ejecutado** (`deploy_g0_sprint_g_word_v4.php`): 3 evaluados 12-ago (#128 ORD-2026-0034, #129 ORD-2026-0035, #130 ORD-2026-0036). **Bug confirmado:** PHP tiene datos (187 respuestas, foto OK) pero `document.xml` deja DATOS GENERALES vacíos; foto sí embebe. HTTP descarga 567 KB OK. **Causa raíz G1.1:** PhpWord no implementa `deleteName` con PCLZip — el XML rellenado en memoria nunca reemplaza `word/document.xml` en el .docx (prod sin ext-zip). |
| 2026-08-13 | **G1.1 deploy prod:** FTP 4 archivos + `leerEntrada` fix. Verify #128: nombre/apellidos/DPI OK (DPI formateado `1234 56789 1011`). Browser UAT: login → ORD-2026-0034 → descarga Word 452 KB con datos. Cliente confirmó «listo». |
| 2026-08-13 | **G5 deploy prod:** `InformeDatos` fuente única. Smoke evaluado #128: 9/9 OK. |
| 2026-08-13 | **Deploy Sprint G completo** (27 archivos FTP) + smoke 27/27 prod. |
| 2026-08-13 | **UAT real 13-ago (Stephany):** 2 PDFs en `ultimos cambios 13-08-2026/`. Reabre informe final, vista previa, botones REPRO, sede, formulario. → **Sprint H** |
