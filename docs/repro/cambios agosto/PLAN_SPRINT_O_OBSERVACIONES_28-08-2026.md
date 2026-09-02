# Sprint O — UAT Stephany 27-noche / 28-ago-2026

**Cliente:** Stephany Castro / REPRO  
**Estado:** 🟢 En prod 28-ago · smoke 23/23 OK  
**Prod:** https://reproappv2.szystems.com · UAT en **PRUEBA 1**  
**Sprint N:** lote A + UI + N-L1 + N-R2 + N-P1 + N-V1 + N-C4/N-L2/N-E1 en prod 27-ago.

**Evidencia:** `docs/repro/cambios agosto/Observaciones 28-08-2026/`  
- WA 27-ago 21:51 / 22:15 (tablas REPRO + laboral socio)  
- WA 28-ago 07:33–07:38 (confusión complementaria + corrección de alcance)  
- WA 28-ago tarde (DI rojo + conclusión; layout económico; quién creó empresa; WhatsApp cliente)

**No tocar:** fechas del formulario · Hetzner/dominio · órdenes reales salvo diagnóstico lectura · peri/espe Información complementaria Q&A (ella dijo que **no aplica**) · «Información complementaria laboral» (N-C1, eso sí se queda fuera) · socio económico/amistades/salud/estado civil que ya confirmó OK.

---

## Cómo lo dijo ella (orden del chat)

| Hora | Qué mandó | Qué pide (lectura final) |
|------|-----------|--------------------------|
| 21:51–21:56 (27) | UI Tablas preempleo, recuadro **Información complementaria** | Ayer pedimos quitarlo (N-C4). **Al día siguiente se arrepiente.** |
| 22:15 (27) | Word socio: tabla INFORMACIÓN LABORAL / EMPLEOS (2 filas) | «solo en socioeconómico si no se trasladó la información laboral» |
| 07:33 (28) | Texto + miniatura de tablas | Se **confundió**: pidió borrar «información complementaria» creyendo que era «información complementaria **laboral**» (esa ya la habíamos quitado). **Restaurar** la información en Word **y** en las tablas de REPRO. |
| 07:34 (28) | «ahora estoy en socioeconómico, favor quitar esa sección en las tablas de REPRO» | Quedó **superado** 2–4 min después. Era la misma confusión, no un tercer pedido. |
| 07:35–07:36 | Fotos tabla INFORMACIÓN COMPLEMENTARIA (vacía + llena: licencia, sindicato, metas, redes…) | «esta es la de socioeconómico» — la Q&A de licencia/metas |
| 07:37 | Alcance | «sería en polígrafo / VSA **preempleo** y en socioeconómico» |
| 07:38 | Corrección | «**no aplica en periódica ni específica**» |
| Tarde | UI Preguntas poligráficas + Word CONCLUSIÓN (R2/R5 vs preguntas 2 y 4) | Al marcar **DI**: número, pregunta, DI y puntuación en **rojo** en el Word. Esas preguntas **pasan solas** a Conclusión (hoy las copia a mano; por eso el rótulo no coincide). |
| Tarde | Word preempleo: laboral + complementaria narrativa + económico + salud pegados | 1) Económico un poco más ancho que el resto → mismo ancho. 2) Separación chica entre Aspecto laboral / Económico / Salud. 3) Económico está en 10 → **fuente 12**. |
| Tarde | Consulta | Ver **quién creó** una empresa (le duplicaron una y no pudo saber quién). |
| Tarde | Foto portal cliente | La casilla WhatsApp queda tapada por el logo Szystems + botón rojo. **Subirla**. |

---

## La confusión (no repetir N-C4)

Hay **tres** cosas distintas con nombre parecido:

| Nombre | Qué es | Qué quiere ahora |
|--------|--------|------------------|
| **Información complementaria laboral** | Acordeón Q&A de integridad / «labor complementaria» | **Sigue fuera** (N-C1 / M-P6). No restaurar. |
| **Información complementaria** | Acordeón + tabla Word Q&A: licencia, sindicato, familiar en empresa, metas, redes… | **Restaurar** en UI y Word. Solo **preempleo** (poli/VSA) y **socio**. **No** peri/espe. |
| **Aspecto laboral** (`word_laboral`) | Narrativa que escribe ella | Preempleo: recuadro **aparte** (no pisar la Q&A). Peri/espe: sigue en INFORMACIÓN COMPLEMENTARIA (N-L1). Socio: no se vuelca solo (ya tiene la Q&A). |

N-C4 quitó el acordeón en preempleo **y** socio. Eso se **revierte**. El 07:34 («quítala en socio») fue el mismo malentendido; 07:37–07:38 deja el alcance final.

N-L2 metió `word_laboral` **dentro** de INFORMACIÓN COMPLEMENTARIA y **borró** las filas de licencia. Eso choca con el restore. Queda: Q&A de vuelta + `word_laboral` en **ASPECTO LABORAL** debajo del historial.

---

## Matriz Sprint O

| ID | Origen | Pedido | Causa / fix (verificar al implementar) |
|----|--------|--------|----------------------------------------|
| **O-C1** | 07:33 revert N-C4 | Acordeón **Información complementaria** otra vez en Tablas REPRO (preempleo + socio). Peri/espe sin cambio. | `InformePreempleo::clavesTablas` hace `unset($claves['complementaria'])`. Quitar ese unset. `labor_complementaria` **no** vuelve. |
| **O-C2** | 07:35–07:38 | Word: tabla Q&A INFORMACIÓN COMPLEMENTARIA en preempleo + socio. Peri/espe no. | Preempleo: dejar de borrar la tabla Q&A en `rellenarAspectoLaboralComplementaria`. Volcar `rellenarInformacionComplementariaTabla` también en preempleo. `word_laboral` → recuadro **ASPECTO LABORAL** tras INFORMACIÓN LABORAL. |
| **O-S1** | 22:15 socio | Trasladar INFORMACIÓN LABORAL (empleos) en Word socioeconómico | `rellenarTablaLaboral` sí corre para socio. Hipótesis: plantilla socio / claves de fila / tabla vacía. Diagnosticar con Word real antes de parchear. |
| **O-R1** | Tarde DI | Filas DI en rojo (n.º, pregunta, DI, score). Conclusión se llena sola con esas preguntas (R2, R4…). | `rellenarPoligraficaTabla` no colorea. Conclusión usa nota libre `word_resultado_mentira` (por eso escribió R2/R5 a mano). Autollenar desde resultado=DI; si ella escribió algo, el auto manda. |
| **O-E2** | Tarde layout | Mismo ancho que las otras; espacio entre laboral / económico / salud; económico fuente 12 | `expandirTablaAnchoPagina(10915)` deja económico más ancho. Igualar al ancho de INFORMACIÓN LABORAL. Párrafo de espacio. `w:sz` 20 → 24 en esa tabla. |
| **O-U1** | Tarde empresa | Ver quién creó la empresa | `empresas` no tiene `created_by`. Columna + guardar en `store` + mostrar en ficha y listado. Históricas: «Sin registro». |
| **O-W1** | Tarde cliente | Subir WhatsApp para que no lo tape Szystems + power | Footer `position:absolute; bottom:0` se monta sobre el último ítem del menú. Padding inferior al menú / margen al botón. |

---

## Qué no reabrir

- N-C1 información complementaria **laboral** (sigue fuera).  
- N-L1 peri/espe (aspecto laboral en INFORMACIÓN COMPLEMENTARIA; recs en observaciones).  
- N-R2 sin `[ X ]`. N-P1 PDF inline. N-V1 nombre en verificación.  
- Socio: económico, amistades, salud, estado civil (OK).  
- N-F0 / N-F1 (peri vacío / salud peri) — no los mencionó hoy.

---

## Orden de trabajo

1. **O-C1 + O-C2** (revert + Word Q&A) — desbloquea UAT de la confusión.  
2. **O-S1** socio laboral.  
3. **O-R1** DI / conclusión.  
4. **O-E2** layout económico.  
5. **O-U1** creador de empresa.  
6. **O-W1** WhatsApp cliente.

**UAT:** PRUEBA 1. Regenerar Word en los ítems de informe. Tests **por archivo**.

---

## Fuera de este sprint

- Cutover Hetzner / dominio  
- N-F0 peri vacío / N-F1 salud peri  
- H16 PDF final automático  
- Foto HEIC / >5 MB
