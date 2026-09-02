# Sprint P — UAT Stephany 28-ago noche / 29-ago-2026

**Cliente:** Stephany Castro / REPRO  
**Estado:** 🟢 En prod · parche 29-ago **13:20** (P-S2 aspecto laboral socio + P-A1 badge Aprobado) · **esperando impresiones de Stephany**  
**Prod:** https://reproappv2.szystems.com · UAT en **PRUEBA 1**  
**Sprint O:** en prod 28-ago. Ella UAT-eó el listado y corrigió 4 + 5 + 6; luego sumó foto, separación, Excel y poligrafista.

**Evidencia:** `docs/repro/cambios agosto/Observaciones 28-08-2026 noche/`

**No tocar:** fechas del formulario · órdenes reales salvo diagnóstico lectura · peri/espe Información complementaria Q&A · acordeón/Q&A `labor_complementaria` (N-C1 **sigue fuera**) · no borrar Deudas / Hábitos / Judicial / Complementaria · no mostrar poligrafista al cliente.

**Sí volcar (29-ago 12:11–12:16, ella lo pidió otra vez):** el **párrafo** `word_laboral` en socio, recuadro título **INFORMACIÓN COMPLEMENTARIA LABORAL** bajo EMPLEOS. No es la Q&A ni los `xxxxx` de la foto 20:21.

---

## Cómo lo dijo ella (orden del chat)

| Hora | Qué mandó | Qué pide (lectura final) |
|------|-----------|--------------------------|
| 20:04 | Nuestra lista 1–8 | UAT: 1–3, 7–8 OK. **4 no.** 5 y 6 tienen ajuste. |
| 20:05 | Foto INFORMACIÓN LABORAL / EMPLEOS llena (Arium, Quick Box) + «la información laboral en socioeconómico aún no se pasa» | El Word socio **sigue vacío** en empleos. La foto es el **formato destino**, no un rediseño. |
| 20:08 | Foto Deudas + «el recuadro de observaciones económicas sí tiene 12; regresar a **letra 11 solo el cuadro de deudas**» | **No** borrar Deudas. Fuente 11 en la grilla; observaciones económicas se quedan en 12. |
| 20:08 | Foto conclusión Josué + X roja sobre **3 filas vacías** + «gracias al cambio ya se pasan las preguntas, pero **ya no va a ser necesario ese cuadro**» | «Ese cuadro» = la **tablita vacía** entre las R1/R2 y «se clasifica…». **No** es Deudas. **No** quitar la tabla de Preguntas poligráficas de la UI/Word. |
| 20:18 | Foto «Fotografía del candidato» (Tomar foto / Subir archivo) | Pegar imagen (Ctrl+V), **igual que papelería**. |
| 20:18 | «esos serían los únicos cambios» | Quedó **superado**: a las 20:20–20:51 agregó más. |
| 20:20 | Word de 2 páginas con X rojas al lado de los encabezados + «todas las tablas se pegaron, deben de quedar **separadas**» | Las X marcan **junturas**, no borrado. No quitar Hábitos / Drogas / Judicial / Complementaria / Económico. |
| 20:21 | Plantilla vieja con huecos + «así como estas» | **Referencia de separación** (espacio entre bloques). **No** volver a poner INFORMACIÓN LABORAL COMPLEMENTARIA ni los `xxxxx`. |
| 20:38–20:39 | Agenda del 19-ago | Excel del calendario, **como en las demás secciones**, respetando filtros (sede / poligrafista / tipo). |
| 20:48–20:51 | Texto + Historial de candidatos | Quieren asignarse solas; el nombre que se ve hoy es **quien programó**. Excel + totales. **Nunca** visible para la empresa. Lectura final: **10:37**. |
| **29-ago 10:28–10:36** | Ficha orden + historial + card + filtros | La orden no aparece al filtrar. Cambiar pestaña a **Encargado**. Botón **Autoasignarme**. Filtro **fecha + empresa**. |
| **29-ago 10:37** | «pero que no se pierda el registro de quien programó» | `poligrafista_id` se queda. Encargado = otro campo (`responsable_id`). |

---

## Lo que NO es (no repetir malentendidos)

| Se podría leer mal | Lo que realmente pidió |
|--------------------|------------------------|
| Borrar la tabla Deudas | Deudas se queda; solo baja a fuente 11 |
| Borrar Hábitos / Judicial / Complementaria (X en 20:20) | Solo pedir **espacio** entre tablas |
| Restaurar INFORMACIÓN LABORAL COMPLEMENTARIA (foto 20:21) | Es plantilla vieja de **referencia de huecos** |
| Quitar la tabla de Preguntas poligráficas | Solo el **cuadro vacío** de la hoja de Conclusión |
| Mostrar otra vez el poligrafista al cliente | Sigue oculto para empresa |
| Pisar `poligrafista_id` con quien hace la prueba | 10:37: **no se pierde** quien programó (eso es lo que se ve ahora) |

---

## Matriz Sprint P

| ID | Origen | Pedido | Causa / fix (verificar al implementar) |
|----|--------|--------|----------------------------------------|
| **P-S1** | 20:05 · O-S1 no le funcionó | Trasladar EMPLEOS en Word socioeconómico | El test O escribe `historial_laboral/empleos` y busca `EMPLEOS:`. Diagnosticar plantilla socio v2 (¿`INFORMACIÓN LABORAL` pisa la tabla mala?) y origen real de filas (override vacío de Tablas REPRO vs formulario). Prod: `tipo_servicio=socioeconomico` + cuestionario `socioeconomico`; `evaluados_orden.tipo_formulario` **no** es `socioeconomico`. |
| **P-R1** | 20:08 conclusión | Quitar las 3 filas vacías tras las preguntas DI | Al marcar NO APROBADO se reemplaza el marcador pero **queda** la tabla vacía siguiente. Reusar/extender `eliminarTablaSiguiente` **sin** borrar el párrafo de las R1/R2. Aprobado/excepción ya borran párrafo+tabla. |
| **P-E1** | 20:08 deudas | Deudas fuente 11; observaciones económicas 12 | `forzarTamanoFuenteTabla(..., 24)` pinta **toda** ASPECTO ECONÓMICO. Aplicar 22 half-points solo a la grilla Deudas/TOTALES. |
| **P-T1** | 20:20–20:21 | Separar **todas** las tablas contiguas | O-E2 solo insertó espacio tras 3 marcadores. Insertar párrafo entre `</w:tbl><w:tbl>` (idempotente; no duplicar si ya hay spacer). |
| **P-F1** | 20:18 | Pegar foto candidato | `foto-candidato` solo cámara/archivo. Reusar el patrón de `zona-pegar-papeleria` + paste en `foto-candidato.js`. |
| **P-X1** | 20:39 | Excel del calendario | No hay ruta. Misma query que filtros de `CalendarioController`. iPage sin XMLWriter → `.xls` HTML (`ExportacionesSupport`). REPRO only (`calendario.ver` / informes). |
| **P-P1** | 20:48 | Asignación interna + reporte mensual Excel | Corregido 29-ago 10:37: ver **P-P2**. |
| **P-P2** | 29-ago 10:28–10:37 | Dos personas: **Programó** (lo de ahora) y **Encargado** (quien hace la prueba). Autoasignarme en la ficha. Filtro calendario: **fecha + empresa**. Al filtrar debe aparecer la orden. | `poligrafista_id` = quien programó (**no pisar**). Encargado = `responsable_id`. Botón autoasignar solo escribe Encargado. Historial hoy solo lista informe final / cancelados → por eso no ve la orden. |

---

## Qué no reabrir

- N-C1: acordeón/Q&A `labor_complementaria` **sigue fuera**. El **párrafo** `word_laboral` en socio **sí** se vuelca (P-S2, 29-ago 13:20).  
- O-C1/O-C2 complementaria Q&A (ella la dio por OK).  
- O-U1 creador de empresa · O-W1 WhatsApp (OK).  
- N-R2 sin `[ X ]`. N-P1 PDF inline.  
- Socio: amistades / salud / estado civil (OK).  
- N-F0 / N-F1 peri.  
- Cutover iPage→portal: solo según `PLAN_MIGRACION_HETZNER_COOLIFY_2026-08-31.md` (autorizado 31-ago; no cortar prod hasta M6).

---

## Orden de trabajo

1. **P-S1** socio laboral (lo que ella marcó mal).  
2. **P-R1** cuadro vacío conclusión.  
3. **P-E1** fuente 11 deudas.  
4. **P-T1** separación de tablas.  
5. **P-F1** pegar foto.  
6. **P-X1** Excel calendario.  
7. **P-P2** (corrige P-P1): Programó + Encargado + Autoasignarme + filtros fecha/empresa.

## P-P2 — dos personas (29-ago 10:28–10:37)

| Campo | Qué es | Qué no hacer |
|-------|--------|--------------|
| `evaluados_orden.poligrafista_id` | **Programó** — quien agendó. Es lo que ya se veía. | No pisarlo al autoasignarse ni al reprogramar. |
| `evaluados_orden.responsable_id` | **Encargado** — quien está disponible y hace la prueba. | Empresa no lo ve. |

- Botón **Autoasignarme** en la ficha del evaluado (`POST evaluados/{id}/autoasignar-encargado`), solo `role_as >= 2`.
- Al programar: hidden `poligrafista_id` = el que ya estaba o `Auth::id()`. No se elige evaluador de antemano.
- Historial del calendario: todos los que tienen `fecha_programada` en el periodo (no solo informe final). Columnas Programó + Encargado.
- Filtros: Encargado (`responsable_id`), Empresa, Fecha. Excel: totales por Encargado.
- Evidencia chat: `Observaciones 28-08-2026 noche/chat_29-10-37_no_perder_quien_programo.png` y `WA_10-*.jpg`.

**UAT:** PRUEBA 1. Regenerar Word en ítems de informe. Tests **por archivo**. No regenerar NEVERIA / CORALSA / PERCO.

---

## 29-ago tarde — en prod, esperando a Stephany

Le mandamos mensaje: re-descargar Word; probar EMPLEOS + recuadro aspecto laboral socio; badge Aprobado en poli/VSA; avisar si algo falla.

| ID | Pedido de ella (12:11–12:51) | Qué hicimos | Verificado acá |
|----|------------------------------|-------------|----------------|
| **P-S1** | Empleos socio «pocos fallan» | Tabla tras INFORMACIÓN LABORAL; grilla anidada; override `-----` no pisa formulario | #255 Arium/Quick Box · #179 Adeph/Telus · #157 Repro |
| **P-S2** | X sobre recuadro vacío: ahí debe ir el párrafo | Socio vuelve a insertar INFORMACIÓN COMPLEMENTARIA LABORAL con `word_laboral`. UI ya no dice «no se vuelca» | #255: «Indicó haber cumplido…» en esa tabla |
| **P-A1** | Resultado aprobado poli/VSA | Label **Aprobado** (UI + fila Word sin «/ SIN OBSERVACIONES») | Badge Angeles #212 ORD-2026-0108 |
| **P-W1** | Word poli pide recuperar | PCLZip extrae a disco (no `EXTRACT_AS_STRING`); `filasTabla` no corta `tr` anidados | Otto no lo ve; ZIP #255 PK + 33 files |

**No codear más hasta su UAT.** Si un caso de empleos sigue vacío, pedir captura + nombre del evaluado.
