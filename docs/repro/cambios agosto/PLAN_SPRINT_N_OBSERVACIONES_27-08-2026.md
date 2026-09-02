# Sprint N — UAT Stephany 26-noche / 27-ago-2026

**Cliente:** Stephany Castro / REPRO  
**Estado:** 🟡 Lote A + UI + N-L1 + **N-R2** + **N-P1** + **N-V1** + **N-C4/N-L2/N-E1 preempleo** · N-F0 Jaquelin · N-F1 plan B  
**Prod:** https://reproappv2.szystems.com · UAT en **PRUEBA 1**  
**Sprint M:** A–F en prod 26-ago. Ella confirmó lo que sí quedó; el resto fue malentendido o quedó a medias.

**Evidencia:** `docs/repro/cambios agosto/Observaciones 27-08-2026/`  
- WA 26-ago 22:25–23:14 + 27-ago 10:00 / 11:30 / 17:58  
- `FIRMAS PARA POLIGRAFO.docx` (Stefanie 9245 · Rodrigo 12897 · Narda 10296)  
- Capturas chat Otto (resultado, complementaria UI, firma Elizabeth, económico, spec peri/espe, VSA, peri vacío)

**No tocar:** fechas del formulario · Hetzner/dominio · órdenes reales salvo diagnóstico lectura · embeber PDF · resetear passwords de Stephany · socio económico/amistades/salud (ella dijo que **ya están bien**) · estado civil (OK en los tres)

---

## Cómo lo dijo ella (orden del chat)

| Hora | Qué mandó | Qué pide |
|------|-----------|----------|
| 22:25 | Foto CLASIFICACIÓN socio (5 tipos, `[ X ] Tipo A`) | ¿El informe va **así entero** o **solo la opción seleccionada**? |
| 22:27 | Foto RESULTADO poli (APROBADO / NO APROBADO / EXCEPCIÓN) | «lo mismo en polígrafo» |
| 22:28 | UI «Resultado de evaluación (primera y última hoja)» | «está excelente la tablita, solo faltaría que se traslade a los informes» |
| 22:29–22:35 | UI **Tablas para informe** (poli, peri, socio) | «la tabla de información complementaria laboral **sigue apareciendo**» — marca el acordeón, no el Word |
| 22:36 | Word poli: 3 firmas + texto **Elizabeth Silvestre** | «la firma no está bien (polígrafo)» |
| 22:41 | ASPECTO ECONÓMICO + X roja a la derecha del recuadro | «espacio económico está igual (polígrafo y VSA; en socioeconómico ya está bien)» |
| 23:04–23:05 | Tablas spec peri/espe, §4 Salud resaltada | Salud peri/espe = **la misma de preempleo**. Si es difícil: meterla en antecedentes recientes y **renombrar la pestaña** a «Salud y antecedentes recientes» |
| 23:10–23:14 | Foto 3 firmas + `FIRMAS PARA POLIGRAFO.docx` | Firma poli «que quede así»; **que la tabla no marque los cuadros**; aplica preempleo + periódico + específico |
| 10:00 (27) | Firma VSA Stefanie Castro Certified Examiner VSA | «la firma de VSA se arruinó y estaba bien» |
| 11:30 | Empleos + ASPECTO ECONÓMICO con X roja | Siente que **retrocedimos**; el hueco/bloque económico poli/VSA sigue mal |
| 17:58 | Texto largo | **P0:** periódica al editar sale **casi vacía** (solo última sección) aunque el estado dice lleno; al rellenar a mano el Word solo trae la 1ª parte. Ayer sí andaba. Pedir validar **específica** también |
| 20:55–20:59 | Word peri: «RECOMENDACIONES PERIODICA» bajo INFORMACIÓN LABORAL; OBSERVACIONES ADICIONALES vacío | **N-L1:** debajo del historial laboral va **aspecto laboral**; las recomendaciones van en **Observaciones adicionales** |
| 21:50–21:52 | Word preempleo laboral+económico + UI Tablas | **N-L2:** volcar aspecto laboral. **N-E1:** franja derecha económico. **N-C4:** quitar Información complementaria de tablas preempleo |

Checklist que ella misma armó sobre nuestro WA:

**Socio — OK:** estado civil, espacio económico, encabezado amistades, salud fila. **Mal:** complementaria laboral sigue. **A medias:** resultado = solo el seleccionado.

**Poli preempleo (Nevería) — OK:** estado civil, nombre en conclusión, alergias/embarazo en formulario. **Mal:** firma; espacio económico; complementaria laboral. **A medias:** resultado 1ª/última hoja solo el marcado.

**Peri/espe — OK:** estado civil, totales deudas, columna No/Sí. **Mal:** nombre+firma en polígrafo aún no; espacio económico; sección de salud (no el mini bloque); complementaria. **A medias:** resultado solo el marcado.

---

## Qué malentendimos (no rehacer lo que ya está bien)

| Sprint M | Qué hicimos | Qué quería ella |
|----------|-------------|-----------------|
| **M-P3 / M-S3** | Un campo en UI + `[ X ]` sobre la fila, **dejando las 5 / 3 filas** en el Word (comentario en código: «antes se reducía a título + una fila») | UI (el select) está bien. En el **Word** debe verse **solo la opción marcada**, con su color. No Aprobado abre mentira; excepción abre aspecto — en **esa** fila, no el menú completo |
| **M-P2** | Quitamos Stefanie/Rodrigo/Narda y pusimos el **poligrafista de la orden** (por eso Elizabeth Silvestre bajo las 3 firmas de plantilla) | El bloque institucional de **3 firmas** (documento `FIRMAS PARA POLIGRAFO.docx`). **No** sustituir por quien hizo esa evaluación. Tabla **sin bordes**. VSA **no tocar** el texto/firma que ya estaba bien |
| **M-P6** | Quitamos la tabla del **Word**; el acordeón **Tablas para informe** sigue mostrando `labor_complementaria` | Lo que ella circundó es la **UI**. También dice que en el Word «aún aparece» → verificar marcadores al implementar |
| **M-F3** | Peri/espe: 2 preguntas (alergias + embarazo) en §5 | Spec: **Información de Salud y Hábitos Personales = la de preempleo**. Ella ofrece plan B: no crear sección nueva, ampliar §5 y renombrar pestaña |
| **M-P7** | Compactar col. fantasma; socio quedó bien | Poli y VSA **siguen** con el hueco. No retocar socio |

**No reabrir (ella confirmó OK):** M-E1 estado civil · M-P1 nombre conclusión preempleo · M-P4 No/Sí · M-P5 totales · M-F2 alergias preempleo · M-S4 salud socio · M-S5 amistades · espacio económico **socio**.

---

## Dudas para preguntarle a Stephany (antes de N-F1 y para el P0)

1. **Periódica vacía (17:58):** ¿nombre del candidato / empresa / código de orden de **hoy**? Sin eso el diagnóstico es a ciegas (PRUEBA 1 #114 puede no ser el mismo caso). ¿Al “abrir para editar” es el cuestionario del candidato, la pantalla REPRO del evaluado, o el Word?
2. **Salud peri/espe:** ¿vamos con **su plan B** (ampliar la última pestaña y llamarla «Salud y antecedentes recientes», sin sección extra) o quiere **sección nueva** tipo preempleo?

**Casi seguro (confirmar si quiere, no bloquea el resto):** las 3 firmas de polígrafo van **siempre** (Stefanie / Rodrigo / Narda) aunque la evaluación la haya hecho otra persona. VSA vuelve a **Stefanie Castro Certified Examiner VSA**, sin el nombre del poligrafista de la orden.

---

## Matriz Sprint N

### P0 — Regresión periódica (WA 17:58) — primero, sin meter más campos al formulario

| ID | Pedido | Hipótesis a verificar **antes** de parchear | Notas |
|----|--------|---------------------------------------------|--------|
| **N-F0** | Peri: al editar, secciones 1–4 vacías; estado “lleno”; Word solo 1ª parte. Chequear específica | 1) Vista admin/candidato no carga `respuestasExistentes` por slug (`actualizacion_datos` / `antecedentes_recientes`). 2) Al guardar §5 con `salud_alergias` obligatorio (M-F3) se pisa JSON de otras secciones. 3) Word peri solo lee encabezado si el resto no compiló. | **No** ampliar salud (N-F1) hasta ver que el guardado no borra. UAT: PRUEBA 1 peri #114 + el caso que ella nombre. No resetear enlaces vencidos sin pedirlo. |

### Lote A — Word: lo que ella ve mal hoy (barato, desbloquea UAT)

| ID | Origen | Pedido | Causa en código (verificar al implementar) |
|----|--------|--------|--------------------------------------------|
| **N-R1** | 22:25–22:28 + checklist | Word: **solo la fila** del resultado/clasificación elegida (1ª y última hoja). UI select no se toca | `rellenarCuadroResultado` y `marcarOpcionesTabla` **conservan** todas las filas y prefijan `[ X ]`. Volver a dejar título + fila marcada (color de plantilla). Mentira / excepción en esa fila |
| **N-R2** | 21:02–21:22 + socio 21:40 | Poli/VSA/socio: **sin `[ X ]`**. Aprobado y excepción: «SÍ RESPONDIÓ…» negro/negrita, sin preguntas ni tabla. No aprobado: «NO RESPONDIÓ… SIGUIENTES…» negro/negrita + preguntas + tabla en blanco. Excepción: el motivo va en ASPECTO QUE ORIGINA. Socio: solo la fila de clasificación (Tipo A–C) con color. Firmas OK. | `marcarOpcionesTabla(..., false)` también en socio. Tests: `InformeWordSprintNLoteRTest` + lote A socio |
| **N-P1** | 21:43 PDF + Auth | Abrir en el navegador (no descargar solos). Si quieren guardar, lo bajan desde el visor | `$pdf->download` → `$pdf->stream` en admin y portal empresa. Los botones ya tenían `target=_blank` |
| **N-V1** | 21:47 verificación DPI | Nombre del candidato debajo de «Bienvenido/a» | `verificar-identidad.blade.php` usa `$evaluado->nombre_completo` |
| **N-C1** | 22:29–22:35 UI | Quitar **Información complementaria laboral** de «Tablas para informe» (poli, peri, socio) | `InformePreempleo::clavesTablas` sigue incluyendo `labor_complementaria`. No quitar «Información complementaria» (licencia/metas) ni historial laboral |
| **N-C2** | Checklist «aún aparece en el Word» | Q&A complementaria laboral fuera. Peri/espe **sí** conservan el recuadro INFORMACIÓN COMPLEMENTARIA para aspecto laboral (N-L1) | `quitarTablaLaborComplementaria` / N-L1 |
| **N-C3** | 20:28 UI | Quitar **Hermanos** de «Tablas para informe» en peri/espe. Preempleo/socio siguen | Candidato y Word ya omitían hermanos. Quedaba `informe-familiar` con «Agregar hermano» |
| **N-A1** | 22:36 + 23:10 + `FIRMAS PARA POLIGRAFO.docx` | Restaurar bloque 3 firmas poli; **sin bordes** de tabla; preempleo + periódico + específico | `rellenarApa` sustituye el bloque Stefanie/Rodrigo/Narda por `$poligrafista` → Elizabeth bajo las 3 imágenes. **Dejar de sustituir** ese texto. Quitar `tblBorders` visibles en plantillas poli v2 (o `sz=0`) |
| **N-A2** | 10:00 VSA | Restaurar firma VSA (estaba bien) | `rellenarApa` también reemplaza `Aldin Tobar Certified Examiner VSA`. No aplicar lógica de poligrafista a VSA. Comparar plantilla `informe-vsa-*-v2.docx` vs Word generado |

### Lote B — Layout económico poli/VSA (no socio)

| ID | Pedido | Hipótesis | Notas |
|----|--------|-----------|--------|
| **N-L1** | WA 20:55: recs bajo INFORMACIÓN LABORAL; OBSERVACIONES ADICIONALES vacío | M-P6 borraba INFORMACIÓN COMPLEMENTARIA en peri y compactaba LABORAL→RECOMENDACIONES. Ahora peri/espe vuelcan `word_laboral` ahí; recs van a OBSERVACIONES ADICIONALES; se elimina el recuadro RECOMENDACIONES del medio. Socio/preempleo no cambian | Tests: `InformeWordSprintNLoteLTest` |

| **N-C4** | 21:51–21:56 UI | Quitar **Información complementaria** de Tablas para informe en **preempleo y socio** | `clavesTablas` ya no incluye `complementaria` |
| **N-L2** | 21:50 Word preempleo | Aspecto laboral debajo de Información laboral (el recuadro INFORMACIÓN COMPLEMENTARIA) | Mismo volcado que peri; se quitan las filas Q&A de licencia |
| **N-E1** | 21:50 X verde a la derecha de económico | Narrativa con gridSpan 8 de 9 columnas | `extenderFilasDeUnaCeldaAlGrid` en `finalizarTablaAspectoEconomico` |

### Lote D — Formación académica (WA 20:13–20:18) — independiente de N-F0

| ID | Pedido | Causa | Notas |
|----|--------|-------|-------|
| **N-F2** | Tabla académica: Word solo último grado → candidato llena **una fila** | Ella 20:48: no pedir todos los grados. Antes la REGLA era 2 filas (univ+div). Quedó 1 = el seleccionado. |

### Lote C — Formulario peri/espe salud (después de N-F0)

| ID | Pedido | Alcance (plan B, ella 20:05: lo más fácil) |
|----|--------|---------------------------------------------|
| **N-F1** | Salud y hábitos = preempleo; hoy solo alergias/embarazo | Ampliar última pestaña y llamarla **«Salud y antecedentes recientes»**. No sección 6. Específica igual. **No implementar hasta N-F0 estable.** |

---

## Orden de trabajo

1. **Preguntas** (dudas 1–2) si Otto puede mandarlas ya.  
2. **N-F0** diagnóstico + fix si hay pérdida de datos — **antes** de N-F1.  
3. **Lote A** un deploy: N-R1, N-C1, N-C2, N-A1, N-A2. **N-C3** (hermanos UI peri/espe) y **N-F2** (académico JS) en código, mismo lote de formulario/UI.  
4. **Lote B** N-E1 (solo poli/VSA).  
5. **Lote C** N-F1 cuando N-F0 esté estable (plan B confirmado 27-ago 20:05).  
6. **Lote D** N-F2 formación académica (JS ya alineado con la REGLA; deploy con el JS).

**UAT:** PRUEBA 1. No regenerar NEVERIA / CORALSA / PERCO. Tests **por archivo**.

**Tests a revertir/ajustar:** `InformeWordSprintMTest` hoy exige que **no** salga Stefanie/Rodrigo — N-A1 es lo contrario. Añadir: Word resultado = 1 fila de opción; UI sin acordeón `labor_complementaria`; VSA conserva «Certified Examiner VSA» de plantilla.

---

## Fuera de este sprint

- Cutover Hetzner / dominio  
- H16 PDF final automático  
- Foto HEIC / >5 MB (M-F0 se mantiene)  
- Poou #111 · I8 empresa móvil  

---

## Siguiente paso

**N-C4 / N-L2 / N-E1** en prod: preempleo sin acordeón Información complementaria; aspecto laboral en el Word; recuadro económico a todo el ancho. Regenerar el Word.

**N-V1** en prod: en verificación de identidad aparece el nombre debajo de Bienvenido/a. Abrir el enlace del candidato (no hay que regenerar Word).

**N-P1** en prod: PDF y Auth se abren en el navegador. Regenerar no aplica; clic de nuevo en los botones.

**N-R2** en prod: sin X en polígrafo, VSA y socioeconómico. En socio sale solo la fila de clasificación (verde de recomendable, etc.). Regenerar el Word.

N-A1 bordes: las firmas de plantilla van en WordArt/text box, no en `w:tbl`. Este lote **deja de sustituir** Stefanie/Rodrigo/Narda (y no toca Aldin en VSA). Si ella aún ve cuadros, el siguiente paso es copiar el bloque de `FIRMAS PARA POLIGRAFO.docx` (3 filas, bordes nil) a las 3 plantillas poli.

Otto: N-F0 Jaquelin = CORALSA `ORD-2026-0179` VSA peri c#191. N-F1 = plan B. N-F2 académico JS. N-C3 hermanos fuera de tablas REPRO peri/espe. N-E1 económico poli/VSA sigue pendiente.
