# Sprint L — Observaciones Stephany 24-ago-2026

**Cliente:** Stephany Castro / REPRO  
**Evidencia:** `docs/repro/cambios agosto/Observaciones 24-08-2026/`  
- `OBSERVACIONES DEL SISTEMA.docx` (sáb 12:41)  
- `CORRECCIONES DE SOCIOECONÓMICOS.docx` (sáb 15:22)  
- `CORRECCIONES POLIGRAFO ESPECIFICO.docx` (sáb 15:32) — periódica = **las mismas** notas  
**Prod:** https://reproappv2.szystems.com · empresa de pruebas **PRUEBA 1**  
**Prioridad de ella (WA):** sistema + socioeconómico primero; específicas/periódicas después (pocas se hacen).

**No tocar:** fechas del formulario · cutover BD/dominio · órdenes reales · usuarios reales de Stephany · comprimir papelería al subir.

---

## Cómo trabajamos

1. Un lote a la vez → tests Docker → deploy FTP → UAT navegador en **PRUEBA 1** → mensaje WA.
2. Word: cambios quirúrgicos en relleno/XML/plantilla. No rediseñar anexos salvo lo ya hecho en L0.
3. Periódica no tiene .docx aparte: aplicar L-P también a la plantilla periódica.

**UAT socio:** Carmen Fernanda Castillo · cuestionario **#148** · evaluado **#255** · ORD-2026-0141 · PRUEBA 1.  
**UAT polígrafo:** Angeles Villagrán (el del .docx específico, PRUEBA 1 Huehuetenango) si sigue en prod.

---

## Ya cerrado

| ID | Pedido | Estado |
|----|--------|--------|
| **L0** | Word 503 con papelería + “no deja editar” | ✅ prod 24-ago noche. PDF en anexos = nombre; imágenes si no pesan. UAT #148/#255: editar OK, Word 200 en ~4.6 s. |
| **L-S10** | “Editar contenido de cuestionario” en rojo | ✅ **prod** 24-ago. Cabecera `bg-danger`; UAT #148. |
| **L-S11** | Título **INICIO DE REDACCIÓN DE INFORME EN WORD** | ✅ **prod** 24-ago. |
| **L-S12** | Tablas para informe **antes** de Redacción Word | ✅ **prod** 24-ago. Edit + show. |
| **L-S13** | Miniaturas preview papelería (P0 noche 24-ago) | ✅ **prod** 24-ago noche. Preview JPEG ≤480 px; original intacto. Smoke 9/9. UAT #148 doc 261 preview **13 KB**. |
| **L-S1** | A las 24 h sin llenar → *Pendiente de llenar* | ✅ **prod** 24-ago noche. On-access al listar/ver órdenes (cron iPage no hace falta). Smoke 5/5 PRUEBA 1. |
| **L-S14** | Banner Word **antes** de Resultado (primera hoja) | ✅ **prod** 24-ago noche. Pedido WA 20:39. UAT #148: banner → resultado → tablas → redacción. |
| **L-S2** | Excel de estadísticas (antes tiraba error) | ✅ **prod** 24-ago noche. Fallback `.xls` HTML (iPage sin XMLWriter). UAT: 200, 45 KB, PRUEBA 1 / Carmen / ORD-2026-0141. |
| **L-S3** | Título **INFORMES DE EMPRESAS** | ✅ **prod** 24-ago noche. Menú + heading + breadcrumb + PDF. |
| **L-S8** | Pegar imagen en papelería (Ctrl+V) | ✅ **prod** 24-ago. Zona de subida REPRO/cliente/candidato. UAT ORD-2026-0141. |
| **L-S4/S5** | Excel informes/órdenes y Empresas y evaluaciones por rol | ✅ **prod** 24-ago. `reportes.generar`. Cliente filtrado (confidencial). |
| **L-S6** | Padrón EMPRESAS PDF/Excel solo admin o permiso | ✅ **prod** 24-ago. `empresas.exportar`. Excel padrón 6.7 KB con PRUEBA 1. |
| **L-S7** | Papelería: cámara/tablet REPRO y clientes | ✅ **prod** 24-ago noche. `image/*` + **Tomar foto**. UAT ORD-2026-0141: botón visible. |
| **L-S9** | Listado papelería: **tatuajes** | ✅ **prod** 24-ago noche. Tipo **Tatuajes** (`foto_tatuaje`); también en catálogo socio. UAT: select = Tatuajes. |
| **L-E1…E8** | Word socioeconómico Carmen | ✅ **prod** 25-ago. Smoke #255: 14/14. Pareja Mario. 5 bienes + total Q990,000. |
| **L-P1…P6** | Word polígrafo específica/periódica Angeles | ✅ **prod** 25-ago. Smoke #212 / #114: 16/16. Pareja MARIO LÓPEZ. Diversificado. REPRO/RRHH. Anexos + tablas informe en editar. |

“No deja editar” del chat de hoy = el mismo 503, no un permiso de formulario. Lo de “no hay tablas para editar el Word” en polígrafo es **L-P6**, otro tema.

---

## Lote A — Sistema (`OBSERVACIONES DEL SISTEMA.docx`)

Prioridad alta. Varios ítems son permisos/reportes (más riesgo); la UI de redacción Word es barata y desbloquea al equipo.

| ID | Pedido | Notas de implementación |
|----|--------|-------------------------|
| **L-S1** | A las 24 h sin llenar → estado *Pendiente de llenar* | ✅ prod 24-ago. `FormularioAutoTransiciones` + on-access 5 min. |
| **L-S2** | Excel de “Estadísticas de evaluación” tira error | ✅ **prod** 24-ago noche. Fallback `.xls` HTML si no hay XMLWriter. UAT: HTTP 200, `reporte-evaluaciones-2026-08-24.xls`, 45 KB, incluye PRUEBA 1 / Carmen / ORD-2026-0141. |
| **L-S3** | Renombrar título a **INFORMES DE EMPRESAS** | ✅ **prod** 24-ago noche. Menú + heading + breadcrumb + PDF de `/reportes/evaluaciones`. |
| **L-S4** | Excel de informes/órdenes: permiso por rol REPRO; cliente solo lo suyo y según proceso/confidencial | ✅ **prod** 24-ago. Permiso `reportes.generar`. Cliente filtrado (confidencial). |
| **L-S5** | Empresas y evaluaciones: igual, permiso REPRO quién descarga Excel | ✅ **prod** 24-ago. Mismo `reportes.generar`. |
| **L-S6** | En EMPRESAS: PDF (y Excel si se agrega) solo admin o permiso de rol | ✅ **prod** 24-ago. `empresas.exportar` (no va al rol REPRO por defecto) + Excel del padrón. |
| **L-S7** | Papelería: cámara/tablet para REPRO y clientes (hoy solo candidato) | ✅ prod 24-ago. `image/*` + botón Tomar foto en REPRO y cliente. |
| **L-S8** | ¿Pegar imagen en papelería? | ✅ **prod** 24-ago. Ctrl+V en la zona de subida (REPRO, cliente y candidato). |
| **L-S9** | Listado de papelería: agregar **tatuajes** | ✅ prod 24-ago. Label **Tatuajes**; incluido en catálogo socio. |
| **L-S10** | “Editar contenido de cuestionario” en **rojo** (hoy amarillo) | ✅ prod 24-ago |
| **L-S11** | Título visible: **INICIO DE REDACCIÓN DE INFORME EN WORD** | ✅ prod 24-ago |
| **L-S12** | Mover **Tablas para informe** *antes* de **Redacción de informes Word** | ✅ prod 24-ago. Edit + show. |

**Orden interno A:** S10–S14 ✅ · L-S13 ✅ · L-S1 ✅ · L-S2 + L-S3 ✅ · L-S7 + L-S9 ✅ · **L-S8 + L-S4/S5/S6 ✅ prod**. Lote A sistema cerrado.

---

## Lote B — Socioeconómico (`CORRECCIONES DE SOCIOECONÓMICOS.docx`)

Prioridad alta junto con sistema. Caso: Carmen Fernanda / PRUEBA 1.

| ID | Pedido | Qué hacer |
|----|--------|-----------|
| **L-E1** | No pasa **estudia actualmente** al Word | ✅ código 24-ago. Socio dice `Estudia actualmente:`; el relleno solo buscaba mayúscula. |
| **L-E2** | No pasa **estado civil** (bloque pareja) | ✅ código 24-ago. Llena si hay nombre aunque `tiene` venga vacío. |
| **L-E3** | Quitar **con quién vive** en hijos (Word) | ✅ código 24-ago. Se quita la columna al generar. |
| **L-E4** | Amistades: en sistema **no hay** dirección ni ocupación | ✅ código 24-ago. Columnas: nombre, teléfono, motivo, años de conocerlo. |
| **L-E5** | Complementaria laboral: salen preguntas del candidato, no las observaciones REPRO | ✅ código 24-ago. Socio escribe `word_laboral`; ya no vuelca las Q&A. |
| **L-E6** | Patrimonio: el sistema deja N bienes; Word solo **3** y no suma total | ✅ código 24-ago. Filas dinámicas + total. |
| **L-E7** | Quitar del Word **bienes inmuebles** y **vehículos propios** | ✅ código 24-ago. Se eliminan de OTROS ASPECTOS. |
| **L-E8** | No se trasladan **recomendaciones** de la plataforma | ✅ código 24-ago. Se inserta el bloque y se llena con `word_recomendaciones`. |

No reabrir P0/P1 ya en prod (expareja, presupuesto Q, deudas, salud extra) salvo que el .docx de hoy los vuelva a marcar.

---

## Lote C — Polígrafo específico + periódica

Mismas observaciones. Prioridad más baja (pocas se hacen).

| ID | Pedido | Qué hacer |
|----|--------|-----------|
| **L-P1** | No pasa **estado civil** | ✅ **prod** 25-ago. Angeles #212: MARIO LÓPEZ, 36 años. |
| **L-P2** | Nivel académico: en sistema solo “nivel de estudio” y no pasa al Word; **combinar celdas** | ✅ **prod** 25-ago. Word: Diversificado + celdas combinadas. |
| **L-P3** | No se traslada información laboral / complementaria | ✅ **prod** 25-ago. REPRO/RRHH + Q&A (ya no Xxxx). |
| **L-P4** | Anexos: “no está el apartado en el sistema” | ✅ código 25-ago. Bloque visible aunque no haya papelería subida. |
| **L-P5** | Tatuajes en plantilla | ✅ **prod** 25-ago. Tabla TATUAJES en Word #212. |
| **L-P6** | No hay **tablas para editar el Word** (salud la llena el candidato) | ✅ **prod** 25-ago. `aplicaATipo` incluye periodica/especifica. Smoke 16/16. |

---

## Fuera de este sprint (salvo que Otto lo pida)

- Migración a servidor propio / dominio. Destino revisado 24-ago: Hetzner proyecto **szystems**, VPS `ubuntu-8gb-hil-1` **CPX31** (4 vCPU / 8 GB / 160 GB). Capacidad OK para 600–700/mes. Backups VPS apagados.  
- Comprimir papelería al subir.  
- Embeber PDF otra vez dentro del Word (L0 lo quitó a propósito).  
- Subir `memory_limit` en iPage: **no** arregla el 503 de login (workers LiteSpeed).

### Poou #111 (24-ago noche)

Juan Bernardo Poou Caal · #111 / evaluado #203 / ORD-2026-0104. Word **OK** 1.47 s sin anexos marcados. 6 JPEG ~15 MB. Preview carga el archivo entero → satura iPage. **No re-probar descarga/preview/delete de #111** en navegador.

---

## Siguiente paso

Sprint L cerrado (A+B+C) **en prod**. Re-smoke 25-ago noche: login UAT 200; Word ZIP #114/#148/#113.  
**Pendiente humano:** WA a Stephany (Word socio + específico/periódica de los .docx del sábado) para que revise en **PRUEBA 1**. Hetzner/dominio solo si Otto lo pide.

### Borrador WA (25-ago, aún no enviado)

```
Stephany buenas noches.

Ya quedaron en el sistema de pruebas los cambios de los Word que nos pasaste el sábado.

Socioeconómico: pareja/estado civil, si estudia actualmente, hijos sin “con quién vive”, amistades, patrimonio con todos los bienes y el total, recomendaciones, y la complementaria laboral con lo que escribe REPRO.

Polígrafo específico y periódica: estado civil, último grado académico, laboral y complementaria (ya no sale Xxxx), tatuajes, tablas para editar el informe y el apartado de anexos.

¿Puedes revisar en PRUEBA 1 un socioeconómico y un polígrafo (específico o periódica) y nos dices si el Word ya te cuadra?
```

