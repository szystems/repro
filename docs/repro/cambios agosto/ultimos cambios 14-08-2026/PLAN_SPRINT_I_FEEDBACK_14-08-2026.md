# Sprint I — Feedback Stephany 14-ago-2026 (UAT real post-Sprint H)

**Cliente:** Stephany Castro / REPRO  
**Fecha recepción:** 14 de agosto de 2026 (WA mañana + tarde)  
**Estado:** 🟢 **UAT prod 15-ago** — I13b ✅ · I6/I7/I11/I1/I2 verificados · pendiente I4/I8/I9/I10 visual
**Prod:** https://reproappv2.szystems.com  
**Carpeta evidencia:** `docs/repro/cambios agosto/ultimos cambios 14-08-2026/`  
**Sprint anterior:** Sprint H ✅ cerrado prod 13-ago — **este feedback reabre 6 ítems críticos**

**Documentos en carpeta:**
| Archivo | Origen |
|---------|--------|
| `WhatsApp Image 2026-08-14 at 8.03.20 AM.jpeg` | Captura Word — papelería en tabla tatuajes (Franklin) |
| `Darwin_Iván_Macario_López_NOVOCOLOR_SA (2).docx` | Word generado prod — mismo bug papelería |
| `WhatsApp Image 2026-08-14 at 5.50.09 PM.jpeg` | Formulario móvil — campos duplicados sección 2 |
| `WhatsApp Image 2026-08-14 at 5.58.52 PM.jpeg` | Resumen requisitos Word (foto, formato, preview/final) |
| WA 14-ago ~6:26 PM (captura Stephany → Otto) | Candidato no pudo llenar — enlace expirado; pide revisar vencimiento (no pasa con todos) |

**Referencia cruzada:** `ultimos cambios 13-08-2026/ULTIMOS CAMBIOS3.pdf` (Stephany reenvió — ítems H11–H16 aún vigentes)

---

## Resumen ejecutivo

Stephany probó con **coordinador + candidatos reales** (Darwin, Franklin). Confirma mejoras parciales pero reporta:

1. **Regresión grave Word:** papelería/anexos se insertan en la **tabla de tatuajes** (no en sección ANEXOS).
2. **Cuadro económico Word:** sigue sin cuadrar con el modelo (espacio/layout).
3. **Vista previa:** al pulsar «Vista previa» **traba el sistema** (no genera).
4. **Formulario:** campos hogar/dependientes **duplicados** (familia + económico).
5. **Móvil:** fecha nacimiento inconsistente; usuario empresa no entra a orden.
6. **Reapertura H11–H16** de CAMBIOS3 (botones REPRO, informe final, empresa).

**Principio de implementación:** un ítem = un commit lógico · un archivo o función por fix · test antes de deploy · no tocar código adyacente.

---

## Matriz de ítems (I1–I12)

### Leyenda prioridad

| Nivel | Significado |
|-------|-------------|
| **P0** | Bloquea UAT / genera informe incorrecto — corregir primero |
| **P1** | UX/formulario — antes de go-live si es posible |
| **P2** | Épica o negocio — acordar alcance antes de codear |

| ID | Observación Stephany | Causa raíz (análisis código) | Fix quirúrgico propuesto | Archivos | Prioridad | Estado |
|----|---------------------|------------------------------|--------------------------|----------|-----------|--------|
| **I1** | «La papelería sí se adjunta pero en el cuadro de tatuajes» | `InformeWordAnexos::aplicarPapeleria()` usaba marcador **`TATUAJES`**. Plantilla v2 **no** tiene marcador `ANEXOS:` usable (conflicto con tabla poligráfica). | Insertar bloque **«DOCUMENTOS ADJUNTOS:»** (tabla 2 cols Documento/Descripción) **después** de tabla TATUAJES. Tatuajes reales siguen en `TATUAJES`. | `InformeWordAnexos.php`, `InformeWordXml.php` | **P0** | ✅ prod smoke PASS (Franklin #159) |
| **I1b** | «¿Agregamos un cuadro así para papelería?» (captura 8:03) | Stephany sugiere tabla dedicada tipo la de tatuajes pero para documentos (imagen + descripción). | **Implementado en I1** — tabla 2 columnas dedicada. Re-UAT visual con Stephany. | — | **P1** | ✅ (mismo fix I1) |
| **I2** | «Lo del cuadro económico sigue igual» | Sin deudas: filas Deudas/Entidad/TOTALES/xxxxx vacías permanecían. | `podarSeccionDeudasVacia()` en `InformeWordXml` + llamadas desde `InformeWordRelleno`. | `InformeWordRelleno.php`, `InformeWordXml.php` | **P0** | ✅ prod smoke (sin headers Deudas vacíos) |
| **I3** | «Vista previa no se genera y al apacharlo traba el sistema» | mammoth en hilo principal + fetch sin timeout; Swal undefined bloqueaba apertura modal tras guardar borrador. | AbortController 90s, mammoth en setTimeout(0), iframe PDF lazy, fallback notificación sin Swal, modal abre aunque falle borrador. | `edit.blade.php` | **P0** | ✅ prod (#47 Edgar — preview ~231 KB HTML) |
| **I4** | «Generar informe final» + «Descargar informe final» (CAMBIOS3 + captura 5:58) | H16 épica — hoy: Word manual → subir PDF en orden. Botones no existen con ese nombre. | **No implementar PDF auto sin acuerdo.** Acción inmediata: renombrar/clarificar botones existentes («Descargar borrador Word» / «Ir a orden subir final») O agregar enlaces al PDF final **si ya está subido** (`informe-final-preview`). Confirmar con Stephany en reunión. | `edit.blade.php`, `CuestionariosController.php` | **P2** | ⬜ |
| **I5** | «Cosas que ya funcionaban ahora fallan» (regresión general) | Deploy Sprint H batch WA tocó `InformeWordAnexos`, `InformeWordPdfPaginas`, relleno económico. | Checklist regresión antes de cada deploy I: Word #128 Aldin, #131 Edgar, #132 Franklin, Darwin/Novocolor caso nuevo. Documentar diff por commit. | Tests + probe | **P0** | ✅ smoke Franklin PASS |
| **I6** | Duplicado hogar/dependientes | Campos solo en sec. 4 | varios | **P1** | ✅ UAT browser Walter #134 |
| **I7** | Fecha nacimiento móvil manual | `type="text"` dd/mm/aaaa | `datos-personales.blade.php` | **P1** | ✅ UAT browser Walter sec.1 |
| **I8** | Usuario **cliente** en celular no entraba a una orden (¿lentitud?) | Sin reproducción aún. Posibles: timeout, permisos empresa, orden confidencial 403, JS pesado en `ordenes/show`. | Reproducir con usuario empresa real en móvil. Revisar logs + Network. No codear hasta tener orden ID y usuario. | `EmpresaController`, `empresa/ordenes/show` | **P1** | ⬜ |
| **I9** | Foto Word — ubicación/tamaño/proporción (reiterado 5:58) | H8 desplegado; Stephany aún ve diferencias vs modelo. | Comparar Darwin.docx foto vs plantilla. Ajustar solo `InformeWordFoto.php` constantes (240×300, inline). **No** tocar relleno. | `InformeWordFoto.php` | **P1** | ⬜ |
| **I10** | Formato informe vs ejemplo (reiterado) | H9 parcial; caso Darwin/Novocolor puede exponer gaps nuevos. | Diff Word Darwin vs ejemplo cliente; lista delta → fixes uno a uno en `InformeWordRelleno` (no batch). | `InformeWordRelleno.php` | **P1** | ⬜ |
| **I11** | Habilitar/deshabilitar formulario REPRO no visible (CAMBIOS3) | Botones ocultos cuando `cuestionario_completado`. | Habilitar/Invalidar enlace visibles para `role_as >= 2` sin condición de completado; Invalidar también si enlace vigente post-completado. | `ordenes/show.blade.php` | **P1** | ✅ UAT orden #161 |
| **I12** | Rehabilitar vencido (CAMBIOS3) | H12 / G3.2 — condiciones UI. | Rehabilitar ya visible cuando completado; Habilitar enlace cuando vencido. | `ordenes/show.blade.php` | **P1** | ✅ UAT orden #161 |
| **I13/I13b** | Enlace expirado (+120 min en 3 candidatos) | I13b deploy 15-ago: piso 30 días, hook saving, reparados #134/#135/#138 | `Config.php`, `EvaluadoOrden.php`, comando reparación | **P0** | ✅ prod |

---

## Orden de implementación recomendado

```
Fase 1 — P0 Word (sin esto no re-UAT informes)
  I1  → papelería en ANEXOS (1 línea marcador + verificación)
  I2  → económico (diagnóstico Darwin → fix puntual)
  I5  → regresión checklist post-fix

Fase 2 — P0 Preview
  I3  → anti-freeze + errores visibles

Fase 3 — P1 Formulario
  I6  → quitar duplicados familia/económico
  I7  → fecha nacimiento móvil

Fase 4 — P1 Word fino + UI REPRO
  I9, I10, I11, I12

Fase 5 — P2 Negocio / épica
  I4 (H16), I8 (investigar), I1b (tabla papelería si hace falta)

Fase 6 — P1 Vencimiento enlace (WA 14-ago noche)
  I13 → investigar casos «solo algunos» + vigencia visible para REPRO
```

**Regla:** no iniciar Fase 3 hasta Fase 1 verificada en prod con Darwin o equivalente.

---

## WA Stephany 14-ago ~6:26 PM — vencimiento de enlace (I13)

**Mensajes (transcripción):**

1. «También le quería comentar que uno de los de hoy dijo que no había podido llenar porque le había expirado el enlace pero ya no intenté no me dió tiempo»
2. «No sé cuánto tiempo tengo vigencia el enlace»
3. «También me ha dado ese error, por eso le digo lo del botón de habilitar y deshabilitar»
4. «Puede porfavor ver eso del vencimiento, no lo hace con todos, solo algunos»

**Relación con ítems existentes:**

| Ítem | Relación |
|------|----------|
| **I11/I12** | Stephany pide botones habilitar/deshabilitar **precisamente por este dolor** — UI ya desplegada 14-ago, pero **no sustituye** investigar por qué algunos candidatos ven «expirado» sin intervención manual |
| **I8** | Distinto — acceso usuario **empresa** en móvil a la orden admin |

**Comportamiento actual en código (referencia):**

| Concepto | Dónde | Valor / regla |
|----------|-------|----------------|
| Días de vigencia | `Config::diasVigenciaTokenEnlace()` · Admin → Configuración | Default **30 días** (mín. 1) |
| ¿Enlace activo? | `EvaluadoOrden::enlaceCuestionarioVigente()` | `token_unico` + `token_expira_at` futuro |
| ¿Puede llenar formulario? | `EvaluadoOrden::tokenValido()` | Además: `!cuestionario_completado` |
| Bloqueo manual | `estado_formulario === 'vencido'` | Vista `enlace-invalido` motivo «vencido» |
| Bloqueo por fecha | `token_expira_at <= now()` | Vista `enlace-invalido` motivo «expirado» |
| Renovar enlace | `OrdenesController::habilitarEnlaceFormulario()` | Nuevo `token_expira_at` = hoy + N días; mensaje «Vigencia: 30 días» |

**Acciones pendientes (sin implementar aún):**

1. ~~Obtener de Stephany: nombre/DPI u orden del candidato~~ → **Resuelto por probe prod** (ver tabla abajo)
2. **Mañana:** «Habilitar enlace» en ORD-0040, 0041, 0044 (Walter, Carla, Gerson)
3. **I13b (código):** corregir bug token +120 min vs +31 días al crear evaluado
4. Mostrar fecha de vencimiento clara en pantalla orden (UX)
5. UAT I11: Stephany prueba «Habilitar enlace» sobre caso real vencido

**Candidatos identificados (órdenes 13–14 ago, probe 14-ago 19:50 CST):**

| Orden | Evaluado | ID | Estado form | Completado | token_expira_at | Problema |
|-------|----------|-----|-------------|------------|-----------------|----------|
| ORD-2026-0040 | Walter Aníbal Morataya Soto | 134 | link_enviado | no | +120 min desde creación | **NO_PUEDE_LLENAR** |
| ORD-2026-0041 | Carla Lucia Castillo | 135 | link_enviado | no | +120 min desde creación | **NO_PUEDE_LLENAR** |
| ORD-2026-0044 | Gerson Bosbeli Vail Vásquez | 138 | link_enviado | no | +120 min desde creación | **NO_PUEDE_LLENAR** |
| ORD-2026-0039 | Darwin Iván Macario López | 133 | completado | sí | OK (completó a tiempo) | — |
| ORD-2026-0037/38 | Edgar / Franklin | 131/132 | completado | sí | expirado post-uso | normal |

**Nota técnica:** `SESSION_LIFETIME=120` (minutos) en prod; vigencia config = 31 días. Los 3 bloqueados tienen delta created→expira = **120.0 min** exactos.

---

## Casos de prueba UAT (obligatorios post-fix)

| # | Caso | Cómo validar | Ítem |
|---|------|--------------|------|
| 1 | Darwin / Novocolor — Word | Papelería en sección **ANEXOS**, no en filas Tamaño/Visible uniforme | I1 |
| 2 | Franklin #132 — Word con PDF papelería | Sin 500; PDF fallback o imagen según hosting | I1, I5 |
| 3 | Edgar #131 — expareja + económico | Tabla económica legible, sin filas basura | I2 |
| 4 | Aldin #128 — regresión foto | Foto inline, nombre presente | I5, I9 |
| 5 | Cuestionario completado — Vista previa | No congela >5s UI; error claro si falla | I3 |
| 6 | Formulario preempleo sec. 2→4 | Sin personas_hogar en familia; sí en económico | I6 |
| 7 | Formulario móvil Android | Fecha nacimiento editable manual | I7 |
| 8 | Orden REPRO — habilitar/rehabilitar | Botones visibles en estados acordados | I11, I12 |
| 9 | Candidato real 14-ago — enlace «expirado» | Identificar evaluado; auditar `token_expira_at` + config; habilitar enlace si procede | I13 |

---

## Análisis detallado por observación WA

### Mañana 14-ago (8:03)

> «La papelería si se adjunta pero en el cuadro de tatuajes»

**Evidencia:** captura Franklin — imágenes «Antecedentes Penales/Policíacos» dentro de tabla con columnas *Tamaño, Descripción, Tiempo, ¿Visible con uniforme?, Significado*.

**Causa confirmada en código:**

```php
// InformeWordAnexos.php:148 — INCORRECTO para papelería
InformeWordXml::reemplazarTablaPorMarcador($documentXml, 'TATUAJES', function ...
```

**Fix:** `'ANEXOS'` (marcador existe en plantilla v2).

---

> «Lo del cuadro económico sigue igual»

**Evidencia:** Darwin.docx — bloque ASPECTO ECONÓMICO con encabezado de deudas pero sin datos visibles en extracto XML.

**Hipótesis orden de investigación:**
1. ¿Darwin tiene deudas en cuestionario? Si no, ¿debe ocultarse tabla completa o mostrar «N/A»?
2. ¿`word_economico` bloque evaluador vacío rompe layout?
3. ¿Confundir «cuadro económico» del **formulario** (sec. 4) vs **Word**?

**Acción:** pedir a Stephany aclarar si habla del Word, del formulario, o ambos — mientras tanto auditar Darwin evaluado ID en prod.

---

> «¿Agreguemos un cuadro así para papelería?»

Propuesta UX: tabla dedicada anexos (como tatuajes pero columnas Documento/Archivo/Notas). Implementar **solo después** de I1 si layout ANEXOS plantilla es insuficiente.

---

### Tarde 14-ago (5:39–5:59)

> Coordinador probó; «menos Vista previa» — traba al pulsar

**Flujo actual** (`edit.blade.php`):
1. Click «Vista previa» → `guardarBorrador()` (AJAX POST todo el formulario admin)
2. Abre modal → `shown.bs.modal` → `fetch(informe-word-preview)` → mammoth en main thread

**Riesgos:** doble carga; mammoth bloquea; POST lento; PHP max_execution en generación Word.

---

> Word Darwin — papelería en tatuajes (reiteración)

Mismo I1. Archivo adjunto: `Darwin_Iván_Macario_López_NOVOCOLOR_SA (2).docx`.

---

> Fecha nacimiento móvil — calendario inconsistente; quiere manual

Campo actual: `input type="date"` en `datos-personales.blade.php:100`.

---

> Duplicado personas hogar / dependientes económicos

Captura sec. 2 móvil. Campos en `informacion-familiar.blade.php:212–244`. Mover a económico (I6).

---

> Cliente móvil no entraba a orden

Investigar con datos concretos (I8). Puede ser perf, no bug.

---

> Resumen 5:58 — foto, formato, preview, generar/descargar final

Items I9, I10, I3, I4 — alineados con CAMBIOS3.pdf (13-ago).

---

## Preguntas abiertas para Stephany (reunión)

1. **Cuadro económico:** ¿Word, formulario web, o ambos? ¿Puede enviar captura del «esperado» vs «actual»?
2. **Generar informe final:** ¿PDF automático desde sistema o seguir Word externo + subir PDF?
3. **Darwin:** ¿código orden / evaluado ID para reproducir en prod?
4. **Usuario empresa móvil:** ¿email, orden que no abrió, hora aproximada?
5. **Prioridad go-live lunes:** ¿I1+I2+I3 suficientes para re-UAT o exige I6+I7 también?

---

## Deploy y verificación

- **Manifiesto:** crear `docs/deployment/SprintI_*_deploy_manifest.txt` por fase (no mezclar I1 con I6).
- **Script:** `scripts/deploy_sprint_i_<fase>.sh`
- **Caché prod:** subir `public/clear_cache.php?key=REPRO_DEPLOY_2026_SECURE_KEY`
- **Probe:** script temporal `deploy_uat_sprint_i.php` — auto-eliminar tras corrida
- **No deploy batch grande:** máximo 3 archivos por deploy salvo plantilla Word

---

## Historial

| Fecha | Evento |
|-------|--------|
| 2026-08-14 | Plan I creado desde WA + carpeta evidencia + análisis código (causa I1 confirmada) |
| 2026-08-13 noche | Sprint H cerrado prod — UAT interno OK; Stephany aún no había probado casos Darwin/Novocolor |

---

## Siguiente paso agente

1. Implementar **I1** (cambio 1 línea + test unitario anexos papelería → marcador ANEXOS).
2. Diagnosticar **I2** con Darwin evaluado en prod (query evaluado Novocolor/Darwin).
3. **I3** — timeout + UX anti-freeze antes de tocar generación Word.
4. Actualizar `CONTEXTO_AGENTES.md` y `PROGRESS.md` al cerrar cada fase.
