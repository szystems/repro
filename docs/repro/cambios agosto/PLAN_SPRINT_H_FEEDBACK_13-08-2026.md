# Sprint H — Feedback cliente 13-ago-2026 (pruebas reales)

**Cliente:** Stephany Castro / REPRO  
**Fecha inicio:** 13 de agosto de 2026  
**Estado:** ✅ **CERRADO EN PROD + UAT** — H1–H14 + batch Stephany WA · pendiente **H16** (épica PDF final) · **H0** negocio  
**Prod:** https://reproappv2.szystems.com  
**Timeline cliente:** revisar hoy → pruebas mañana → reunión **sábado** → **go-live lunes**

**Fuentes:**
- WhatsApp 13-ago (tarde): pruebas reales con candidatos + 2 PDFs
- `docs/repro/cambios agosto/ultimos cambios 13-08-2026/ULTIMOS CAMBIOS FORMULARIO.pdf` (4 págs)
- `docs/repro/cambios agosto/ultimos cambios 13-08-2026/ULTIMOS CAMBIOS3.pdf` (4 págs)
- Mensaje cliente: también adjuntó reporte con info del candidato + Word generado (comparación) — **ubicar en WA/carpeta si no están en repo**

**Sprint anterior:** Sprint G ✅ cerrado prod 13-ago (`PLAN_SPRINT_G_FEEDBACK_12-08-2026.md`)

---

## Objetivo

Cerrar gaps detectados en **UAT real** (candidatos de verdad) que Sprint G no cubrió o que la cliente sigue viendo distinto en prod. Prioridad: **informe final + flujo REPRO + formulario candidato**.

**Acuerdo vigente:** informes primero; ayuda/tutorial después. Duda #2 migración/servidor → respuesta de negocio (fuera de código inmediato).

---

## Resumen WhatsApp 13-ago

| Tema | Mensaje cliente |
|------|-----------------|
| Alcance | Ajustes de formulario **y** informe generado; compara Word actual vs reporte del candidato |
| Pendientes previos | Algunos de CAMBIOS2/G ya hechos; otros faltan (CAMBIOS3) |
| Urgencia | Revisar hoy; probar mañana; reunión sábado; live lunes |
| Cierre PDF | «QUEDA PENDIENTE LA GENERACIÓN DEL INFORME FINAL» |
| Duda | Migración: ¿servidor principal REPRO o página nueva? |

---

## Matriz de ítems (H1–H16)

### Formulario + Word (`ULTIMOS CAMBIOS FORMULARIO.pdf`)

| ID | Pedido | Código / notas actuales | Prioridad |
|----|--------|-------------------------|-----------|
| **H1** | Sede/Región empresa vacía → no autocompletar sede REPRO (ej. Quetzaltenango) en formulario ni informe | ✅ **Deploy 13-ago** — `CuestionarioPrecarga` + `InformeDatos` | **P0** ✅ |
| **H2** | Padres: edad, teléfono, dirección no obligatorios | ✅ **Deploy 13-ago** — `InformacionFamiliarPadres` | **P1** ✅ |
| **H3** | Hijos &lt; 1 año: «Menor de 1 año» o meses | ✅ **Deploy 13-ago** — `TablaDinamica::opcionesEdadHijos()` | **P1** ✅ |
| **H4** | Hermanos: solo nombre obligatorio | ✅ **Deploy 13-ago** — `TablaDinamica::columnasHermanos()` | **P1** ✅ |
| **H5** | Fechas laborales: mes/año o solo año; no inventar día/mes | ✅ **Deploy 13-ago noche** — `type="month"` + formato mm/yyyy | **P1** ✅ |
| **H6** | Peso/estatura: etiquetas separadas sin repetir pregunta | ✅ **Deploy 13-ago** — `LABEL_PESO` / `LABEL_ESTATURA` | **P1** ✅ |
| **H7** | Nombre archivo Word: `NombreCompleto_Empresa.docx` | ✅ **Deploy 13-ago** — `InformeWordNombresArchivo` | **P2** ✅ |
| **H8** | Foto Word: ubicación/tamaño/proporción del modelo | ✅ **Deploy 13-ago noche** — quitar `wp:anchor`, ratio ~3:4 (240×300 px) | **P2** ✅ |
| **H9** | Correcciones formato informe vs ejemplo enviado | ✅ **Deploy 13-ago** (+ fechas « al » en informe H8 batch) | **P2** ✅ |

### Informe + flujo REPRO (`ULTIMOS CAMBIOS3.pdf`)

| ID | Pedido | Código / notas actuales | Prioridad |
|----|--------|-------------------------|-----------|
| **H10** | Académico: solo últimos 2 niveles en **informe** (tabla manual) | ✅ **Deploy 13-ago** — `InformeWordRelleno::filasAcademicasVisibles()` | **P1** ✅ |
| **H11** | REPRO: habilitar/deshabilitar formulario siempre visible | ✅ **Deploy 13-ago noche A–E** | **P0** ✅ |
| **H12** | Rehabilitar vencido | ✅ **Deploy 13-ago noche A–E** — Habilitar enlace | **P0** ✅ |
| **H13** | Vista previa Word inline tras editar | ✅ **Deploy 13-ago** — UX preview-first A–E | **P0** ✅ |
| **H14** | En INFORME (empresa) debe verse final/preliminar, no formulario candidato | ✅ **Deploy 13-ago noche A–E** — reportes + portal empresa | **P0** ✅ |
| **H15** | Papelería | No tocar | — |
| **H16** | Generación del informe final (cierre del PDF) | ⏸️ Acordado: Word manual → subir PDF; esperar Stephany | **P0 épica** |

### Batch Stephany WA (13-ago noche) — ✅ prod + UAT

| Ítem | Archivo | UAT |
|------|---------|-----|
| PDF papelería en anexos | `InformeWordPdfPaginas.php`, `InformeWordAnexos.php` | ✅ sin crash · fallback `[PDF]` (iPage sin conversor) |
| Espacio/tabla económica | `InformeWordXml.php`, `InformeWordRelleno.php` | ✅ sin `xxxx` |
| Pareja + expareja | `InformeWordRelleno.php` | ✅ Edgar #131 verificado |
| Fix namespace shell | `InformeWordPdfPaginas.php` | ✅ `\shell_exec` — evitaba 500 con PDF en papelería |

**Manifiesto:** `docs/deployment/SprintH_Stephany_WA_2026-08-13_deploy_manifest.txt`

### Negocio (no código)

| ID | Tema |
|----|------|
| **H0** | Migración / dominio / servidor principal vs sitio nuevo |

---

## Ítems Sprint G que la cliente **reabre** en UAT 13-ago

| Sprint G | Lo que implementamos | Lo que reporta ahora |
|----------|-------------------|----------------------|
| G1.3 | Modal vista previa + borrador Word | «No genera vista previa» tras editar; falta «Generar informe final» |
| G2.1 | Empresa ve informes subidos | «En INFORME aparece formulario del candidato» |
| G3.2 | Rehabilitar vencido | «Si vencido no tenemos opción de rehabilitarlo» |
| G3.x | Botones habilitar/deshabilitar | «Ya no aparece la opción… solo hasta que lo llena» |
| F2.1 / G5 | Académico últimos 2 niveles | Sigue pidiendo límite en informe (Word/PDF) |
| — | Sede región empresa | **Nuevo** en FORMULARIO.pdf — fallback automático |

**Hipótesis:** diferencia de expectativa (vista previa = informe editado vs PDF subido), pantalla/ruta distinta, o condiciones UI que ocultan botones. **Requiere códigos de orden/evaluado** de sus pruebas de hoy.

---

## Archivos clave (cuando se implemente)

| Área | Archivos probables |
|------|-------------------|
| Sede/agencia | `CuestionarioPrecarga.php`, `InformeDatos.php`, `InformePreempleo.php` |
| Validación familiar | `InformacionFamiliarPadres.php`, `TablaDinamica.php` |
| Fechas laborales | `FechasLaboradasCampo.php`, `TablaDinamica.php`, vistas historial laboral |
| Salud peso/estatura | `SaludHabitosCampos.php`, `antecedentes.blade.php` |
| Académico informe | `HistorialAcademico.php`, `InformeWordRelleno.php` |
| Flujo informe | `CuestionariosController.php`, `edit.blade.php`, `OrdenesController.php` |
| Empresa informe | `_informes_evaluado_empresa.blade.php`, `empresa/cuestionarios/show.blade.php` |
| Botones REPRO | `admin/ordenes/show.blade.php` |
| Nombre Word | `CuestionariosController.php`, `OrdenesController.php` |

---

## Preguntas abiertas (WhatsApp — ver borrador en conversación 13-ago)

1. ¿Qué significa «Generar informe final»? ¿PDF automático desde datos editados o seguir editando Word y subir PDF?
2. ¿Vista previa de qué? ¿Word borrador, PDF generado, o PDF ya subido?
3. Códigos orden/evaluado de casos de hoy + adjuntos comparación Word/PDF
4. Pantalla exacta donde ve formulario en lugar de informe (empresa vs REPRO)
5. En qué estados del evaluado deben verse habilitar/deshabilitar/rehabilitar
6. Confirmación prioridad para go-live lunes

---

## Deploy / verificación

- Manifiesto H parcial: `docs/deployment/SprintH_parcial_2026-08-13_deploy_manifest.txt`
- Manifiesto Stephany WA: `docs/deployment/SprintH_Stephany_WA_2026-08-13_deploy_manifest.txt`
- **UAT navegador 13-ago noche:** login OK · órdenes 155/158/159 · Word #128/#131/#132 · H13 preview Edgar (#47)
- **UAT probe servidor:** 27 OK / 5 FAIL (solo infra PDF iPage)
- **Usuario UAT temp:** `uat.g1.browser@repro.local` / `UAT.G1Word2026!` (eliminar post-validación Stephany)
- Casos referencia: evaluado **#128** Aldin · **#131** Edgar · **#132** Franklin

## Deploy / verificación (referencia Sprint G)

- Manifiesto G completo: `docs/deployment/SprintG_completo_2026-08-13_deploy_manifest.txt`
- Script deploy: `scripts/deploy_sprint_g_full.sh`
- Smoke prod 13-ago: 27/27 OK (servidor); UAT cliente 13-ago reabre ítems arriba

---

## Historial

| Fecha | Trabajo |
|-------|---------|
| 2026-08-13 noche | **Sprint H cerrado prod:** batch Stephany WA (PDF papelería fallback, económico, pareja/expareja). UAT navegador + probe 27/27 funcional. Mensaje WA a Stephany. Fix `\shell_exec` namespace. |
| 2026-08-13 | **Deploy H parcial prod** (10 archivos FTP): H1, H2, H3, H4, H6, H7, H10. Smoke 13/13 OK. |
| 2026-08-13 | Plan H creado tras WA + 2 PDFs. Sprint G en prod; UAT real reabre flujo informe + formulario. |
| 2026-08-13 | Deploy Sprint G completo (27 archivos FTP) + smoke prod OK. Cliente aún no validó formalmente — envía feedback mismo día. |

---

## Siguiente paso agente

1. **Sábado:** reunión migración H0 · checklist con Stephany post-UAT.  
2. **H16:** solo si Stephany confirma que quiere PDF final automático (hoy flujo manual Word → subir PDF).  
3. **Opcional:** pedir a iPage Poppler/Imagick para PDF papelería embebido; o aceptar fallback.  
4. Eliminar usuario UAT temp cuando Stephany confirme.  
5. **Go-live lunes** — Sprint H listo salvo H16/H0.
