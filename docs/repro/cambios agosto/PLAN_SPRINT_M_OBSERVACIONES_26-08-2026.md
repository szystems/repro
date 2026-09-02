# Sprint M — Observaciones Stephany 26-ago-2026

**Cliente:** Stephany Castro / REPRO  
**Evidencia:** `docs/repro/cambios agosto/Observaciones 26-08-2026/`  
- `CORRECCIONES DE POLIGRAFO PREEMPLEO2608.docx` (Ericka Xiomara / LA NEVERIA)  
- `CORRECCIONES SOCIOECONÓMICO 2.docx` (Carmen Fernanda / PRUEBA 1 — mismo caso UAT Lote B)  
- `OBSERVACIONES DEL PROCESO PERIODICO Y ESPECIFICO.docx` (Briyith Johana / CORALSA, VSA periódico)  
- WA 17:11–17:40: “ya se hicieron varios cambios; faltan pequeños”; pide viabilidad de **un solo campo de resultado**; foto no sale en algunos informes  
- Capturas: VSA preempleo **Jose Augusto Ligorria** (PERCO, 26/08/2026) y **Getzer Everardo Pérez Alejandro** (foto ausente; ella dijo “es en el único informe”)  

**Prod:** https://reproappv2.szystems.com · pruebas en **PRUEBA 1**  
**Sprint L:** cerrado prod 25-ago (A+B+C). Esto **no** reabre L0/L-S13 papelería ni Poou #111.

**No tocar:** fechas del formulario · cutover Hetzner/dominio · órdenes/usuarios reales de Stephany (foto: solo diagnóstico lectura) · comprimir papelería al subir · embeber PDF otra vez en Word.

---

## Cómo lo dijo ella (WA)

1. Ya probaron; **varios cambios del sábado sí se ven**.  
2. Siguen **cambios chicos** anotados en los tres Word.  
3. Pregunta si es viable **generar el resultado una sola vez** por evaluación (detalle en el .docx de polígrafo, resaltado verde).  
4. **Bug operativo hoy:** algunos Word **no traen la fotografía** (VSA Ligorria PERCO + Getzer Pérez).

H16 (PDF final automático) **no** es este pedido: aquí pide **un campo de resultado que se copie en la 1ª y última hoja del Word**. El flujo Word → ella sube PDF se mantiene.

---

## Qué ya está (no rehacer)

| Tema | Estado |
|------|--------|
| Cuadro 1ª hoja APROBADO / NO APROBADO / EXCEPCIÓN | `InformeWordResultado` marca `[ X ]` según `evaluado.resultado` + notas mentira/excepción |
| Pareja (nombre, edad, tel…) en tabla ESTADO CIVIL | L-E2 / L-P1 **sí** pasan los campos de la pareja |
| Complementaria socio = texto REPRO (`word_laboral`) | L-E5 |
| Patrimonio N filas + total socio | L-E6 |
| Tablas informe + anexos peri/espe | L-P4 / L-P6 |
| Miniaturas papelería | L-S13 |

El hueco de estado civil **no es la pareja**: el **título** de la tabla (`ESTADO CIVIL: ____`) no recibe `casada` / `unión libre` de datos personales. Ella lo anotó otra vez en **los tres** Word.

---

## Matriz Sprint M

### P0 — Foto (WA 17:40)

| ID | Pedido | Hipótesis (código) | Notas |
|----|--------|--------------------|--------|
| **M-F0** | Foto no aparece en algunos informes | `InformeWordFoto::prepararMedia` **omite** si >8 MP o >5 MB (anti-503). HEIC de iPhone **no** está en `cargarImagen`. Si no hay `foto_candidato` en disco, compacta el marco y el Word sale sin retrato. | Casos reales: Ligorria PERCO y Getzer Pérez. **Solo leer** disco/metadatos; no abrir papelería tipo Poou. No resetear nada. |

### Lote A — Word que ella marcó otra vez (barato, desbloquea)

| ID | Origen | Pedido | Causa probable |
|----|--------|--------|----------------|
| **M-E1** | Poli + socio + peri | Poner **estado civil** en el encabezado de la tabla ESTADO CIVIL | `rellenarTablaEstadoCivil` llena filas de pareja; **no escribe** `estado_civil` del candidato en la celda del título |
| **M-P1** | Poli + peri | Nombre del candidato en la conclusión (hoy dice NOMBRE DEL CANDIDATO) | `rellenarConclusiones` busca `XXXXXXXX`; la plantilla v2 dice `NOMBRE DEL CANDIDATO` |
| **M-P2** | Poli + peri | Firma / APA incorrectos; a veces sale **quien creó la orden** | `rellenarApa` busca `Stefanie9245 Rodrigo12871`. El XML real es el bloque concatenado Stefanie/Rodrigo/Narda. Nombre = `evaluado.poligrafista`; si va vacío, no sustituye o cae en otro dato |
| **M-P4** | Peri VSA | Columna **Respuesta** de preguntas (NO / SI) no se traslada | Filas en `word_preguntas_poligraficas`; plantilla arranca `respuesta` vacía. No jala del formulario candidato |
| **M-P5** | Peri | Totales de deudas en blanco (`Q.` vacíos) | `rellenarTablaDeudas` no está pegando TOTALES en plantilla peri (marcador/columnas distintas) o las claves monto/saldo/cuota no matchean |
| **M-S1** | Socio | No se trasladan las recomendaciones **de la tabla** | L-E8 inserta/llena un bloque `RECOMENDACIONES` aparte. Ella mira **GENERALIDADES → RECOMENDACIONES - OBSERVACIONES** |
| **M-S2** | Socio | No volcar nada a “información brindada por el candidato” de referencias laborales | El sistema copia empresa/tel/puesto del historial laboral a esos huecos; ella los llena a mano en verificación |
| **M-S5** | Socio | Primera línea de amistades ya no va en negrita | Cosmético XML/bold al reconstruir filas |
| **M-P7** | Poli + peri | “Pequeña diferencia de espacio” en económico | Layout/celdas (I9/I10 diferido). Solo si queda tiempo; no rediseñar plantilla |

### Lote B — Resultado único (lo que pregunta si es viable)

**Sí es viable.** Ya hay un resultado en `evaluados_orden.resultado` + dos notas (`word_resultado_mentira` / `word_resultado_excepcion`) que pintan la **primera hoja**. La **última hoja** (NDI / DI / excepción) **no** consume ese mismo valor.

| ID | Pedido | Alcance |
|----|--------|---------|
| **M-P3** | Un campo en el sistema → misma marca + textos en 1ª y última hoja. NO APROBADO habilita “preguntas con DI”; EXCEPCIÓN habilita “aspecto”; APROBADO sin extra. Colores de plantilla. | Polígrafo y VSA, **preempleo y periódico** (verde en el .docx) |
| **M-S3** | Socio: **propia** tabla de clasificación (Tipo A / A observaciones / A-Condicionado / B / C) con la **misma dinámica**, replicada en CONCLUSIONES DEL ESTUDIO | No reutilizar el combo APROBADO de poli/VSA |

UI: ocultar/mostrar los dos textareas según el resultado (el partial `resultado-word-detalle` hoy muestra ambos siempre).

### Lote C — Formularios + tablas que quitar

| ID | Pedido | Notas |
|----|--------|-------|
| **M-F2** | Candidato: ¿Padece alergias? + ¿Está embarazada? (mujeres) | **Todos** los formularios (verde en poli). Hoy no hay esos campos |
| **M-F3** | Peri/espe: “aún no tenemos la sección de salud” | §5 peri/espe solo trae tatuajes. Salud la redacta REPRO (`word_salud`) en el Word; el **formulario candidato** peri no tiene salud. Ella pide las dos preguntas ahí |
| **M-S4** | Socio salud Word: fila combinada como en económico | Narrativa `word_salud` en fila span, no solo celdas sueltas |
| **M-P6** | Quitar del Word la tabla **información complementaria laboral**; dejar solo historial laboral. **Todos los servicios** | Choca con L-P3/L-E5 (esa tabla era el destino del texto REPRO). Al quitarla, `word_laboral` hay que **no volcarla ahí**; confirmar si vive en observaciones/recomendaciones o solo en el Word a mano |

---

## Orden de trabajo (propuesta)

1. **M-F0** diagnóstico foto (lectura prod: ¿existe archivo, peso, MP, HEIC?) + fix si es omisión silenciosa / HEIC / plantilla VSA. UAT en PRUEBA 1, no en PERCO.  
2. **Lote A** en un deploy: M-E1, M-P1, M-P2, M-P4, M-P5, M-S1, M-S2.  
3. **Lote B** M-P3 + M-S3 (resultado único).  
4. **Lote C** M-F2/F3 (formulario) y M-P6 (quitar tabla) cuando Otto confirme dónde queda el texto laboral REPRO.  
5. M-P7 / M-S5 solo si sobra.

**UAT:** PRUEBA 1 · Carmen #148/#255 socio · no usar órdenes reales de foto salvo lectura. Poli/VSA: casos de prueba, no NEVERIA/CORALSA/PERCO para re-generar.

**Tests:** por **archivo**, no `--filter` amplio.

---

## Fuera de este sprint

- Cutover Hetzner / dominio.  
- Comprimir papelería al subir.  
- Embeber PDF en Word.  
- H16 PDF final automático.  
- Poou #111 preview.  
- I8 empresa móvil.

---

## Siguiente paso

Lote B **en prod** 26-ago (M-P3 resultado único + M-S3 clasificación socio). UAT PRUEBA 1: #114 VSA peri 2×`[ X ]` + mentira; #113 poli SÍ VERACIDAD; #148 socio Tipo C en 1ª y conclusiones. UI: un select; textareas solo si aplica.

**Lote C M-F2/F3 en prod.** Alergias + embarazo en todos los formularios; peri/espe §5 bloque corto.

**Lote D M-P6 en prod.** Tabla complementaria laboral fuera del Word. `word_laboral` UI borrador, no se vuelca.

**Lote E M-P4 en prod.** Columna Respuesta No/Sí se copia al Word (default No; vacío ya no es `—`).

**Lote F M-P7/M-S4/M-S5 en prod.** Espacio económico (col. fantasma), salud socio en fila combinada, encabezado amistades en negrita.

**Siguiente código:** nada del plan M. UAT 27-ago → **Sprint N** (`PLAN_SPRINT_N_OBSERVACIONES_27-08-2026.md`). No reabrir ítems que ella marcó OK.
