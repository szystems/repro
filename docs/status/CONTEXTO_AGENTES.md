# CONTEXTO PARA AGENTES IA - PROYECTO REPRO

**Sistema:** REPRO Guatemala - Plataforma de Evaluaciones Poligráficas  
**Fecha de Contexto:** 31 de agosto de 2026  
**Estado:** 🟢 Sprint P en prod · 📋 **MIGRACIÓN autorizada** (plan, aún no cutover) · iPage sigue vivo  

**Plan migración (leer antes de tocar infra):** `docs/repro/cambios agosto/PLAN_MIGRACION_HETZNER_COOLIFY_2026-08-31.md`  
**Destino:** web `reprogt.com` (hoy reproxela.com) · app `portal.reprogt.com` · Coolify en Hetzner CPX31 `ubuntu-8gb-hil-1`  
**No cortar** https://reproappv2.szystems.com hasta M6. **No** `migrate:fresh`. **Copiar el mismo `APP_KEY`.**

**Evidencia 28-ago noche:** `docs/repro/cambios agosto/Observaciones 28-08-2026 noche/`  
**Plan Sprint P (UAT Word, no bloquea migración):** `docs/repro/cambios agosto/PLAN_SPRINT_P_OBSERVACIONES_28-08-2026-NOCHE.md`  
**Evidencia 28-ago mañana:** `docs/repro/cambios agosto/Observaciones 28-08-2026/`  
**Plan Sprint O (cerrado UAT con correcciones):** `docs/repro/cambios agosto/PLAN_SPRINT_O_OBSERVACIONES_28-08-2026.md`  
**Evidencia 27-ago:** `docs/repro/cambios agosto/Observaciones 27-08-2026/`  
**Plan Sprint N (cerrado código; N-C4 revertido en O):** `docs/repro/cambios agosto/PLAN_SPRINT_N_OBSERVACIONES_27-08-2026.md`  
**Evidencia 26-ago:** `docs/repro/cambios agosto/Observaciones 26-08-2026/`  
**Plan Sprint M (cerrado código; UAT con correcciones):** `docs/repro/cambios agosto/PLAN_SPRINT_M_OBSERVACIONES_26-08-2026.md`  
**Evidencia 24-ago:** `docs/repro/cambios agosto/Observaciones 24-08-2026/`  
**Plan Sprint L (cerrado prod 25-ago):** `docs/repro/cambios agosto/PLAN_SPRINT_L_OBSERVACIONES_24-08-2026.md`  
**Plan Sprint K (cerrado prod 21-ago):** `docs/repro/cambios agosto/PLAN_SPRINT_K_FEEDBACK_20-08-2026.md`  
**Plan Sprint J (cerrado prod):** `docs/repro/cambios agosto/PLAN_SPRINT_J_OBSERVACIONES_19-08-2026.md`  
**Plan Sprint I (residual):** `docs/repro/cambios agosto/ultimos cambios 14-08-2026/PLAN_SPRINT_I_FEEDBACK_14-08-2026.md`  
**Evidencia 14-ago:** `docs/repro/cambios agosto/ultimos cambios 14-08-2026/`  
**Plan Sprint H (cerrado):** `docs/repro/cambios agosto/PLAN_SPRINT_H_FEEDBACK_13-08-2026.md`  
**Sprint G (cerrado prod):** `docs/repro/cambios agosto/PLAN_SPRINT_G_FEEDBACK_12-08-2026.md`  
**Feedback 13-ago:** `docs/repro/cambios agosto/ultimos cambios 13-08-2026/`  
**Sprint F (cerrado):** `docs/repro/cambios agosto/PLAN_SPRINT_F_ULTIMOS_CAMBIOS_2026-08-10.md`  
**Plan A–E (cerrado):** `docs/repro/cambios agosto/PLAN_REVISION_AGOSTO_2026.md`  
**Permisos empresa (OBLIGATORIO leer antes de tocar portal cliente):** `docs/repro/cambios agosto/PERMISOS_EMPRESA_CLIENTE.md`  
**Feedback 12-ago:** `docs/repro/cambios agosto/ultimos cambios 12-08-2026/`  
**Producción:** https://reproappv2.szystems.com  
**Repo:** https://github.com/szystems/repro · branch `master`

---

## 📋 MIGRACIÓN — LEER PRIMERO (autorizada 31-ago-2026)

**Plan:** `docs/repro/cambios agosto/PLAN_MIGRACION_HETZNER_COOLIFY_2026-08-31.md`  
**Siguiente paso:** commitear código prod a GitHub (master está atrás del FTP). Luego MySQL vacío + app en Coolify proyecto **REPRO**. **M5 dump = copia exacta: avisar a Otto antes.** No `portal` DNS aún. No dump/cutover ahora.

| ID | Qué falta para EMPEZAR |
|----|------------------------|
| **C1** | Comprar **reprogt.com** | ✅ 1-sep-2026 |
| **C2** | Zona Cloudflare `reprogt.com` **Active**. NS `casey` + `jewel`. Correo **iPage**: MX `mx.ipage.com` + SPF `ip4:66.96.128.0/18` + DMARC `p=none`. IMAP/SMTP iPage no van en CF. No Email Routing. No `portal` aún. |
| **C3** | SSH desde este WSL: permission denied. Coolify Terminal sí. Clave a agregar: `szystems@gmail.com` ed25519. |
| **C4** | ✅ Coolify **v4.1.2** en `http://5.78.235.235:8000/` (mismo CPX31). Proyecto **REPRO** creado (vacío). Otras apps: Asonata, ControClinic, Portal Szystems, Clínicas del Valle. |
| **C5** | GitHub App **szystems** ya está en Coolify Sources. Falta conectar el repo `szystems/repro` al proyecto REPRO (después de pushear código actual). |
| **C6** | Password MySQL iPage + `.env` prod (mismo `APP_KEY`) |
| **C7** | `MAIL_*` actuales |
| **C8** | Acceso sitio **reproxela.com** (no está en este repo) |
| **C9** | ✅ Backups VPS ya estaban ON (Hetzner, 7 slots). |

**Ya tenemos:** FTP iPage, host/user/BD `dbreprov2`, repo GitHub, identidad del VPS, UAT PRUEBA 1.  
**No hacer aún:** dump prod (hasta freeze confirmado por Otto), cambiar DNS de la app, cutover.

---

## 🟡 SPRINT P — Word (28-ago noche / 29 ago 2026) · no bloquea migración

**Origen:** WA 28-ago 20:04–20:51 + **29-ago 10:28–10:37** + **29-ago 11:46–12:51**.  
**Plan:** `docs/repro/cambios agosto/PLAN_SPRINT_P_OBSERVACIONES_28-08-2026-NOCHE.md`  
**Siguiente paso:** no codear más. Esperar impresiones de Stephany (le pedimos re-descargar Word).

**No restaurar** acordeón/Q&A `labor_complementaria` (N-C1). **Sí** volcar el párrafo `word_laboral` en socio (ella 12:11–12:16). **No** borrar Deudas ni Hábitos/Judicial. **No** mostrar Encargado ni quién programó a la empresa. **No** regenerar NEVERIA / CORALSA / PERCO.

| ID | Qué | Estado |
|----|-----|--------|
| **P-S1** | Socio Word: EMPLEOS (Arium / Quick Box) | ✅ prod 29-ago · verificado #255 / #179 / #157. Ella: «pocos fallan» → override `-----` ya no pisa el formulario |
| **P-S2** | Socio: párrafo Aspecto laboral bajo EMPLEOS (título INFORMACIÓN COMPLEMENTARIA LABORAL) | ✅ prod 13:20. M-P6 quitó el recuadro; ella lo quiere de vuelta **solo como párrafo**, no Q&A. UAT #255: texto «Indicó haber cumplido…» |
| **P-A1** | Badge polígrafo/VSA: solo **Aprobado** (no «/ Sin Observaciones») | ✅ prod. Vista: Angeles ORD-2026-0108. Socio sigue Tipo A / observaciones |
| **P-R1** | Quitar cuadro vacío tras preguntas DI en Conclusión | ✅ prod |
| **P-E1** | Deudas fuente 11; observaciones económicas 12 | ✅ prod |
| **P-T1** | Separar todas las tablas del Word | ✅ prod |
| **P-F1** | Pegar foto candidato (como papelería) | ✅ prod |
| **P-X1** | Excel del calendario (filtros; rango `fecha_desde`/`fecha_hasta`) | ✅ prod |
| **P-P1** | Asignación interna (mal leído 20:48) | ↩ corregido por **P-P2** |
| **P-P2** | **Programó** (`poligrafista_id`, no pisar) + **Encargado** (`responsable_id`, Autoasignarme) | ✅ prod 29-ago 11:05 |
| **P-W1** | Word polígrafo «contenido no legible» | Endurecido (filas anidadas + PCLZip a disco, no EXTRACT_AS_STRING). Otto no lo reproduce. Socio abre bien. Ella UAT polígrafo pre-empleo |

**UAT PRUEBA 1 (no tocar órdenes reales):** socio **#255** Carmen Fernanda Castillo · orden **264** / ORD-2026-0141 · cuestionario **148**. Poli badge: **#212** Angeles Villagrán · orden **231** / ORD-2026-0108.

**Por qué fallaba (para el próximo agente):**  
1. EMPLEOS: `filasTabla` depth-aware + override vacío/`-----` tapaba el formulario; a veces se llenaba la tabla mala.  
2. Aspecto laboral socio: `quitarTablaLaborComplementaria` + socio **no** llamaba `rellenarAspectoLaboral*`. El texto sí estaba en `word_laboral`.  
3. Aprobado: `getResultadoTextoAttribute` / opciones decían «Aprobado / Sin Observaciones».

---

## 🟢 SPRINT O — EN PROD (28 ago 2026) · UAT con correcciones en P

**Origen:** WA 27-noche → 28-ago. Se confundió «información complementaria» con «información complementaria laboral». El 07:34 («quítala en socio») quedó anulado a las 07:37–07:38.

**Plan:** `docs/repro/cambios agosto/PLAN_SPRINT_O_OBSERVACIONES_28-08-2026.md`  
**No restaurar** `labor_complementaria` (N-C1). Peri/espe **sin** Q&A complementaria.

| ID | Qué | Estado |
|----|-----|--------|
| **O-C1** | Restaurar acordeón Información complementaria (preempleo + socio) | ✅ prod · ella OK |
| **O-C2** | Word Q&A complementaria en preempleo + socio; `word_laboral` preempleo en ASPECTO LABORAL | ✅ prod · ella OK |
| **O-S1** | Socio: trasladar INFORMACIÓN LABORAL (empleos) | → **P-S1** ✅ prod 29-ago · espera UAT ella |
| **O-R1** | DI en rojo + auto a Conclusión | ✅ prod · ella: quitar el cuadro vacío → **P-R1** |
| **O-E2** | Económico: mismo ancho, espacio, fuente 12 | ✅ prod · ella: deudas otra vez 11 → **P-E1** |
| **O-U1** | Quién creó la empresa | ✅ prod · ella OK |
| **O-W1** | Subir WhatsApp en portal cliente | ✅ prod · ella OK |

---

## 🟢 SPRINT N — EN PROD (27 ago 2026)

**Origen:** UAT WhatsApp 26-noche → 27-ago + `Observaciones 27-08-2026/` + `FIRMAS PARA POLIGRAFO.docx`. Ella: malentendimos sitios; cambiamos cosas que ya estaban bien.

**Plan:** `docs/repro/cambios agosto/PLAN_SPRINT_N_OBSERVACIONES_27-08-2026.md`  
**No implementar** un ítem sin re-leer código/plantilla de ese ítem. Socio económico / amistades / salud / estado civil: **no retocar**.

### Lote A en prod 27-ago noche

| ID | Qué | Causa / fix |
|----|-----|-------------|
| **N-R1** | Word: solo la fila del resultado/clasificación elegida (1ª y última hoja) | `rellenarCuadroResultado` / `marcarOpcionesTabla` ahora filtran las otras opciones. UI select no se toca. Pendiente = cuadro completo |
| **N-R2** | Poli/VSA/socio sin `[ X ]`; poli/VSA conclusión según aprobado / no aprobado / excepción | Excepción = misma frase que aprobado (negro, negrita, sin tabla). Motivo en ASPECTO QUE ORIGINA. Socio: solo la fila de clasificación con color. Firmas OK (ella 21:21). |
| **N-P1** | PDF y Auth se abren en el visor del navegador (no descarga automática) | `$pdf->stream` (inline). Descargar queda en el visor del browser. |
| **N-V1** | Nombre del candidato debajo de «Bienvenido/a» en verificación de identidad | `verificar-identidad.blade.php` · `$evaluado->nombre_completo` |
| **N-C4** | Preempleo y socio: sin acordeón Información complementaria en Tablas para informe | `clavesTablas` sin `complementaria` |
| **N-L2** | Preempleo: `word_laboral` debajo de Información laboral | Recuadro INFORMACIÓN COMPLEMENTARIA; se quitan filas de licencia/metas |
| **N-E1** | Poli/VSA: narrativa económica a todo el ancho (sin franja a la derecha) | `extenderFilasDeUnaCeldaAlGrid`. Socio no se toca |
| **N-C1** | Quitar acordeón «Información complementaria laboral» | `InformePreempleo::clavesTablas` ya no incluye `labor_complementaria`. Se conserva «Información complementaria» (licencia/metas) e historial |
| **N-C3** | Quitar **Hermanos** de tablas REPRO para Word en peri/espe (WA 20:28) | Candidato y Word ya lo omitían. Quedaba `informe-familiar` con «Agregar hermano». Preempleo/socio no se tocan |
| **N-C2** | Word sin Q&A complementaria laboral | Preempleo/socio: M-P6. Peri/espe: N-L1 reusa el recuadro INFORMACIÓN COMPLEMENTARIA para `word_laboral` |
| **N-A1** | Firmas poli = Stefanie / Rodrigo / Narda de plantilla | `rellenarApa` **ya no sustituye** por el poligrafista de la orden (Elizabeth). Las firmas están en WordArt, no en `w:tbl` |
| **N-A2** | VSA no reescribir Aldin | Mismo: no se reemplaza `Aldin Tobar Certified Examiner VSA` |

PHPUnit 59 OK (Export jpeg GD excluido: `imagejpeg` ausente en contenedor). FTP 3 PHP + caché. Manifiesto: `docs/deployment/SprintN_LoteA_2026-08-27_deploy_manifest.txt`. Login prod HTTP 200.

### Aún no en prod / no implementar aún

- **N-R3** ✅ **prod** 27-ago noche: poli/VSA «se clasifica al evaluado(a):» fijo (ya no «a la … como»). Socio no usa esa frase.
- **N-F0** peri vacío: Jaquelin Xiomara Castillo Díaz · CORALSA `ORD-2026-0179` · VSA periódica · c#191 · 5/5 100%. Las 5 secciones peri tienen filas. Además hay slugs de preempleo (`datos_personales`, `informacion_familiar`, `historial_laboral`, `antecedentes` con salud completa). No es pérdida de datos. **No** ampliar salud (N-F1) hasta ver por qué la UI/Word no arma 1–4. Ella va a llenar otro; es segunda prueba, no bloquea.
- **N-F1** salud peri/espe: **plan B** (20:05, lo más fácil) — ampliar última pestaña y llamarla **«Salud y antecedentes recientes»**. No sección nueva.
- **N-F2** ✅ prod 27-ago noche (ajustado): candidato llena **solo el último grado** (el Word peri ya mostraba uno). Recargar F5.
- **N-C3** ✅ prod 27-ago noche. Tablas REPRO peri/espe sin Hermanos.
- **N-L1** ✅ **prod** 27-ago noche: peri/espe Word — aspecto laboral debajo de Información laboral; recomendaciones en Observaciones adicionales. Preempleo: **N-L2** sí vuelca `word_laboral`. Socio sigue sin volcarse.
- **N-E1** ✅ **prod** 27-ago noche: poli/VSA narrativa económica con gridSpan = todas las columnas. Socio no se tocó.

**Siguiente paso:** ella regenera el Word peri de la prueba (20:55). Luego N-F0 UI/Word. **No migrar** Hetzner. Tests por archivo.

### Malentendidos Sprint M (no repetir)

| M | Qué hizo M | Qué quiere ella ahora |
|---|------------|------------------------|
| M-P3/S3 | `[ X ]` dejando **todas** las filas Tipo A–C / Aprobado–Excepción | Word: **solo la opción elegida**. El select de la UI está bien |
| M-P2 | Texto APA = poligrafista de la orden (Elizabeth bajo 3 firmas) | Bloque fijo Stefanie / Rodrigo / Narda, **sin bordes**. VSA no se tocaba y se rompió |
| M-P6 | Tabla fuera del Word; acordeón UI sigue | Quitar de **Tablas para informe** (`labor_complementaria`) |
| M-F3 | 2 preguntas en peri §5 | Salud+hábitos **como preempleo**; plan B: renombrar pestaña §5 |
| M-P7 | Hueco socio OK | Poli/VSA siguen mal |

---

## 🟢 SPRINT M — CERRADO CÓDIGO / UAT CON CORRECCIONES (26 ago 2026 noche)

**Origen:** WA 26-ago 17:11–17:40 + 3 .docx en `Observaciones 26-08-2026/`. Ella confirma que **varios cambios del sábado sí quedaron**; faltan ajustes chicos + foto en algunos Word + pregunta si un **solo campo de resultado** puede ir a 1ª y última hoja.

**Plan:** `docs/repro/cambios agosto/PLAN_SPRINT_M_OBSERVACIONES_26-08-2026.md`

### P0 foto (WA, informes reales)

**M-F0 ✅ prod 26-ago.** Ya no se tira la foto solo por ser >8 MP: si pesa ≤5 MB se embebe (JPEG de celular). >5 MB sigue omitida (anti-503). HEIC sigue sin soporte (iPage sin Imagick). UAT PRUEBA 1 #113/#114/#148: `foto_evaluado.png` en el Word. Ligorria/Getzer: que ella regenere; no tocamos esas órdenes.

### Lote A en prod 26-ago

| ID | Qué | Causa real / fix |
|----|-----|------------------|
| **M-E1** | Estado civil en el **título** ESTADO CIVIL | ✅ prod. UAT: Casado(a) / Viudo(a) en r0 |
| **M-P1** | Nombre en conclusión | ✅ prod. Plantilla = `NOMBRE DEL CANDIDATO` no `XXXXXXXX` |
| **M-P2** | Firma/APA de plantilla | ✅ prod. Se quita Stefanie/Rodrigo/Narda y Aldin; si hay poligrafista se pone su nombre. UAT sin poligrafista = WordArt vacío (mejor que nombres ajenos) |
| **M-P5** | Totales deudas en blanco | ✅ prod. `Q.40,000.00` no sumaba (la coma). UAT #113 `Q. 5,500.00` |
| **M-S1** | Recomendaciones socio | ✅ prod. Van a GENERALIDADES última fila, no a Referencia Laboral 1 |
| **M-S2** | No volcar refs laborales | ✅ prod. Ya no copia empresa/tel del candidato a esos huecos |

### Lote B en prod 26-ago (noche)

| ID | Qué | Causa real / fix |
|----|-----|------------------|
| **M-P3** | Un resultado → 1ª y última hoja (poli/VSA) | ✅ prod. Campo único en redacción Word. Marca `[ X ]` en 1ª hoja y NDI/DI/excepción de última. NO APROBADO pega mentira; EXCEPCIÓN pega aspecto; APROBADO cambia a «SÍ RESPONDIÓ CON VERACIDAD». UI oculta textareas según opción. |
| **M-S3** | Socio Tipo A/B/C en 1ª y CONCLUSIONES | ✅ prod. Combo propio (no APROBADO). «Tipo A observaciones» usa `aprobado_con_obs` (ENUM ya existía). |

UAT prod PRUEBA 1 (resultado restaurado a null): #114 VSA peri 2 marcas + mentira; #113 poli espe SÍ VERACIDAD; #148 socio Tipo C en CLASIFICACIÓN y conclusiones. UI: select 3 opciones poli / 5 socio.

### Lote C — M-F2/F3 (formulario, 26-ago noche)

| ID | Qué | Causa real / fix |
|----|-----|------------------|
| **M-F2** | ¿Padece alergias? + ¿Está embarazada? en **todos** los formularios | ✅ prod 26-ago. No hay campo sexo (se quitó a propósito). Embarazo Sí/No para todos; ayuda «Si no aplica, seleccione No.» Detalle de alergias solo si Sí. |
| **M-F3** | Peri/espe §5 pedía «sección de salud» | ✅ prod 26-ago. Bloque corto **Aspectos de salud** (solo esas dos) antes de tatuajes. No se volcó el cuestionario de salud completo de preempleo. |

Campos: `salud_alergias`, `salud_detalle_alergias`, `salud_embarazada`. Internos (no van al PDF empresa). Word: entran a la narrativa `word_salud` si REPRO no la reescribe.

UAT prod: smoke 9/9; admin socio #60 (cuestionario 60) ve las 3 preguntas en Aspectos de salud; admin peri #114 (cuestionario 34) ve bloque corto (alergias/embarazo, sin preocupaciones). Enlace candidato #114 vencido — no se reactivó.

### Lote D — M-P6 (Word, 26-ago noche)

| ID | Qué | Causa real / fix |
|----|-----|------------------|
| **M-P6** | Quitar tabla **información complementaria laboral**; solo historial | ✅ prod 26-ago. Preempleo: se elimina `INFORMACIÓN LABORAL COMPLEMENTARIA`. Socio: `INFORMACIÓN COMPLEMENTARIA LABORAL`. Peri/espe: `INFORMACIÓN COMPLEMENTARIA` (era el Q&A laboral). Se conserva la complementaria de licencia/metas en preempleo. `word_laboral` sigue en la UI como borrador y **ya no se vuelca** al Word. |

UAT prod smoke 12/12: #113 espe y #114 peri sin complementaria y con historial; #148 socio sin complementaria laboral y con EMPLEOS.

### Lote E — M-P4 (Word, 26-ago noche)

| ID | Qué | Causa real / fix |
|----|-----|------------------|
| **M-P4** | Columna **Respuesta** (No / Sí) no se trasladaba | ✅ prod 26-ago. La plantilla Word ya trae `No`; el relleno la vaciaba o ponía `—` porque la UI arrancaba vacía. Ahora default `No`, select No/Sí, `SI`/`NO` se normalizan, vacío → `No` (nunca guión). No hay otra fuente (no jala del formulario candidato). |

UAT prod smoke 11/11: #114 VSA peri y #113 poli espe `["No","Sí","No"]`. UI cuestionario 34: 5 selects No/Sí. Notas de prueba restauradas.

### Lote F — cosmético (26-ago noche)

| ID | Qué | Causa real / fix |
|----|-----|------------------|
| **M-P7** | Pequeña diferencia de espacio en económico | ✅ prod. La col. grid de 22 dxa absorbía el resto al expandir (`expandirTablaAnchoPagina`). Ahora las cols. &lt;50 dxa se quedan; se compacta el hueco ECONÓMICO→SALUD. `numeroMoneda` acepta `Q.40,000.00`. |
| **M-S4** | Salud socio: fila combinada como en económico | ✅ prod. Narrativa `word_salud` en fila `gridSpan` (Observaciones:), no en 4 celdas sueltas. |
| **M-S5** | Primera línea de amistades ya no iba en negrita | ✅ prod. `establecerTextoCelda` quitaba `w:b`; el encabezado se re-aplica con `aplicarNegritaFila`. |

UAT prod smoke 9/9: #114/#113 col. fantasma=22 dxa; #148 salud combinada + amistades negrita. Notas de prueba restauradas.

### Pendiente (pasó a Sprint N)

UAT 27-ago: resultado solo fila marcada · firmas poli/VSA · acordeón complementaria laboral · espacio económico poli/VSA · salud peri completa · **P0 peri vacío**.

**Esto NO es H16** (PDF final automático). Sigue: Word → ella sube PDF.

**Siguiente paso:** Sprint N (plan; sin código hasta verificar ítem). **No migrar** Hetzner. **No re-descargar #111.** Tests por archivo.

**UAT:** PRUEBA 1. No regenerar Word de PERCO / NEVERIA / CORALSA. Admin `uat.g1.browser@repro.local`. **No resetear passwords de Stephany.**

**No tocar:** fechas formulario; cutover; órdenes reales; comprimir papelería; embeber PDF.

---

## 🟢 SPRINT L — CERRADO PROD (25 ago 2026 noche)

**Origen:** WA sáb–lun + `OBSERVACIONES DEL SISTEMA.docx` + `CORRECCIONES DE SOCIOECONÓMICOS.docx` + `CORRECCIONES POLIGRAFO ESPECIFICO.docx` (periódica = mismas notas que específico).  
**Plan:** `docs/repro/cambios agosto/PLAN_SPRINT_L_OBSERVACIONES_24-08-2026.md`  
**Prioridad ella:** sistema + socioeconómico primero; polígrafo específico/periódica después. **Los tres lotes ya están en iPage.**

### Cerrado en prod

| Lote | IDs | UAT (PRUEBA 1, no órdenes reales) |
|------|-----|-----------------------------------|
| **A sistema** 24-ago | L0, L-S1…S14 | Login OK · #148 rojo + banner + tablas antes de redacción · Excel `.xls` (iPage sin XMLWriter) · título **INFORMES DE EMPRESAS** · cámara/Ctrl+V/tipo Tatuajes en ORD-2026-0141 · preview doc **261** = 13 KB |
| **B socio** 25-ago | L-E1…E8 | Carmen **#148** / evaluado **#255** / orden **264** ORD-2026-0141. Pareja Mario. Patrimonio N bienes + total. Word ~418 KB |
| **C poli** 25-ago | L-P1…P6 | Específica Angeles **#114** / **#212**. Periódica Alesandra Gramajo **#113** / **#211**. Estado civil, último grado combinado, laboral/complementaria (sin `Xxxx`), tatuajes, tablas informe + anexos en UI |

**Re-smoke 25-ago noche:** login UAT HTTP 200 → dashboard. Edit 114/148/113 = 200, `bg-danger`, tablas, anexos. Word ZIP: #114 492 KB / 3.2 s · #148 418 KB / 4.3 s · #113 474 KB / 2.9 s. Reportes título INFORMES DE EMPRESAS. Archivos lote C **byte-a-byte iguales** local vs FTP (nada pendiente de subir).

**L-P1…P6 causas (no reabrir):** estado civil no corría en peri/espe · específica solo `ultimo_nivel_academico` (fila sintética + `combinarCeldasFila`) · complementaria buscaba `… LABORAL` · anexos UI solo si había papelería · tatuajes fuera de §5 peri/espe · `InformePreempleo::aplicaATipo` no incluía periodica/especifica. `InformeWordXml.php` **hay que subirlo** con el relleno (el primer smoke C falló en prod porque faltaba).

**Si no marcan checkboxes de anexos, `InformeWordAnexosPapeleria::documentosParaWord` no procesa papelería.** Fotos `foto_tatuaje` sí se intentan embeber (tope 8 MP / 5 MB). No volver a embeber PDF en el Word.

### WA 24-ago noche — Poou / papelería / servidor (diagnóstico, sin fix nuevo)

Stephany: Word 503 en Poou, “no adjunté nada”, no deja eliminar papelería; borró caché y **ya no entra**.  
**Juan Bernardo Poou Caal** · cuestionario **#111** · evaluado **#203** · ORD-2026-0104 · CORPORACIÓN ARIUM. 6 JPEG ~15 MB. Selección anexos Word vacía. Word **OK** sin anexos. Causa: preview cargaba el JPEG entero → satura LiteSpeed (también login). `memory_limit` iPage **no** arregla workers. **L-S13 miniaturas ✅ prod.** **No re-descargar ni abrir papelería de #111.**

### Hetzner (migración **autorizada** 31-ago — ejecutar según plan)

Consola Otto · proyecto **szystems** · `ubuntu-8gb-hil-1` **CPX31** · **4 vCPU / 8 GB / 160 GB SSD** · Hillsboro. Backups VPS **siguen apagados** (C9). SSH root desde este WSL: permission denied (C3). Capacidad 600–700/mes OK. Plan: `PLAN_MIGRACION_HETZNER_COOLIFY_2026-08-31.md`.

### Fuera de Sprint L (Stephany/Otto)

- Cutover Hetzner / dominio.  
- Comprimir papelería al subir.  
- Embeber PDF otra vez en Word.  
- **H16** PDF final automático (hoy: Word → ella sube PDF).  
- I8 empresa móvil; I9/I10 foto/formato fino Word solo si lo vuelve a marcar.  
- Validación de **Stephany con órdenes reales** (nosotros solo PRUEBA 1).

**UAT:** admin `uat.g1.browser@repro.local` / `UAT.G1Word2026!` (user 223) · cliente PRUEBA 1 `uat.cliente.prueba1@repro.local` / `UAT.Cliente2026!` (user 224, empresa_id 99). **No resetear passwords de Stephany.**

**Cerrado.** El lote nuevo es Sprint M (26-ago). No reabrir L0/L-S13 ni Poou #111. PHPUnit: tests por **archivo**, no `--filter` amplio.

**No tocar:** fechas del formulario; cutover BD/dominio; órdenes reales; usuarios reales de Stephany; comprimir papelería al subir. Empresa de pruebas: **PRUEBA 1**.

---

## Sprint K — cerrado prod (20–21 ago 2026)

**Origen:** WA + `Cambios de socioeconomico.docx` + capturas en `Ultimos cambios 20-08-2026/`. UAT ella: archivo OK, pudo borrar algunos usuarios.

**Cerrado prod 21-ago.** Excel del listado + quitar evaluado en prod y UAT (PRUEBA 1). El lote nuevo es Sprint L (24-ago).

**WA 21-ago noche (listado — ✅ prod + UAT navegador):**
- **Excel:** botón en Lista de Órdenes (cliente y REPRO). Misma query que los filtros; todas las filas, no la página de 15. iPage **sin XMLWriter** → baja `.xls` (Excel lo abre). No usar Maatwebsite XLSX en prod.
- **Quitar evaluado:** el tachito ahora manda `evaluados_eliminar[]` y el update sí borra. Sin ese campo, se sigue **preservando** (no borrar por accidente). Mínimo 1. Bloquea si hay cuestionario/papelería/informe. Cliente solo edita en **Orden Recibida**. Dos órdenes del mismo DPI ≠ dos filas: la extra la archiva solo admin.
- UAT: filtro PRUEBA 1 → 18 filas Excel. Orden de prueba **ORD-2026-0140** (#263): se quitó “UAT Quitar Duplicado”, quedó “UAT Quitar Uno”. Se puede archivar (no es orden real de Stephany).

**Explicar (no es código de portal cliente):** desactivar titular en **REPRO → Usuarios**, no en Usuarios de la empresa.

**No tocar:** fechas del formulario; cutover BD/dominio; órdenes reales de Stephany; usuarios reales. Empresa de pruebas: **PRUEBA 1**.

---

## Sprint J — cerrado prod (19–20 ago 2026)

**Origen:** WA Stephany 18–19 ago · Word socio Rebeca + polígrafo/VSA Reyna · `Observaciones 19-08-2026/`  
**Plan:** `docs/repro/cambios agosto/PLAN_SPRINT_J_OBSERVACIONES_19-08-2026.md`  
**No tocar:** fechas del formulario (cerradas por ella). Reset BD + dominio al final.

### WA 19-ago noche — usuarios / roles / archivo (✅ prod + UAT navegador)

Stephany: no puede editar roles, al editar usuario “se genera un rol”, no elimina algunos usuarios, órdenes archivadas siguen en evaluados, empleados con permiso de editar órdenes no podían.

**Causas:** al guardar un empleado REPRO se crea `user_{id}` (aparece en Roles) y se le quita el rol `repro`; `usuarioPuedeEditarOrden` exigía `hasAnyRole(['repro'])`. Archivo sí ocultaba órdenes, no evaluados/historial/calendario/reportes. Eliminar usuarios: no se borra el historial (se desactiva); antes tampoco se podía borrar un titular de empresa.

**Fix:** editar orden por `role_as >= 2` · ocultar roles `user_*` · filtrar `deOrdenesActivas()` en listados · admin puede desactivar titular (no el último admin ni a sí mismo). Tests: `UsuariosRolesArchivoTest`. **Deploy FTP 19-ago noche** · UAT: Roles = 5 del sistema (sin `Permisos de…`) · checkboxes de empleado con `ordenes.editar` marcados · titular tiene botón Eliminar (no se confirmó borrar usuarios reales) · candidato UAT desapareció de cuestionarios e historial DPI al archivar · empleado solo-rol-personal abrió `/ordenes/222/edit` (antes 403). Durante UAT se archivó por error **ORD-2026-0099** (#221 DECORABAÑOS) y se **desarchivó de inmediato**. Datos UAT (users 215/216, orden 222) eliminados.

**Centro de Ayuda (✅ actualizado 19-ago):** `ayuda/seguridad-usuarios` — crear/editar, permisos individuales, editar roles, eliminar (desactivar, titular, último admin, no a sí mismo). Artículo nuevo `ayuda/archivar-ordenes`. FAQ (4) + glosario. Notas en historial DPI, cuestionarios, calendario, reportes y detalle de orden. Botón **Ayuda** en Usuarios, editar usuario, Roles y historial DPI.

### Borradores WA 19-ago (enviar en dos mensajes)

**1 — Word + servidor/dominio** (ya no dice que roles queda para después; eso va en el 2)

```
Hola Stephany buenas noches

Ya quedaron subidos los cambios de las pruebas que nos mandaste del Word (el socioeconómico y el de polígrafo/VSA).

Si podés, revisá en el de pruebas que ya esté así:
- el encabezado del Word con Empresa, Agencia/Sede, Puesto y Fecha, como en la foto que nos enviaste
- el botón para descargar el borrador Word directo en la pantalla de edición, sin salir
- empleos, referencias, agregar hijos/hermanos, presupuesto y lo de recomendaciones

Ojalá puedas volver a generar el Word de Rebeca y el de Reyna (o el último que hayas llenado) y nos digás si ya quedó como lo necesitan.

Y lo del servidor nuevo y el dominio nuevo: eso lo hacemos hasta que ya veamos que no hay más cambios por hacer. Cuando estemos seguros de eso, se sube al servidor real para que hagan las pruebas allá. Por ahora seguimos en este de pruebas para no perder lo que ya está.

Cualquier cosa me avisás
```

**2 — Usuarios / roles / archivo**

```
Stephany, sobre lo de usuarios, roles y las órdenes archivadas: ya quedó en el mismo de pruebas.

- Ya se pueden editar los roles del sistema (Administrador, Personal Repro, etc.). Lo que salía como un rol nuevo al editar un empleado ya no aparece ahí; esos permisos se marcan en Usuarios.
- Eliminar un usuario no borra el historial: se desactiva y deja de poder entrar. Ahora también se puede desactivar al titular de una empresa; si era el principal, hay que asignar otro. No se puede borrar el último administrador ni a uno mismo.
- Si un empleado tiene permiso de editar órdenes, ya lo deja entrar a editar (antes a veces no, aunque el permiso estuviera marcado).
- Al archivar una orden, el evaluado ya no debe salir en candidatos ni en historial por DPI.

Si algo no te cuadra al probarlo, me avisás.
```

### UAT producción 20-ago (navegador + Word generado)

| Prueba | Resultado |
|--------|-----------|
| Edición socio #90 (Yengxi Licema) | ✅ presupuesto/bienes editables · dirección padre/madre · Agregar hijo/hermano · casilla Recomendaciones (J8) · **Descargar borrador Word** (J12) |
| Edición polígrafo #101 (Juan Francisco Cañiz) | ✅ mismo botón Word · labor_complementaria / complementaria por separado · recomendaciones opcionales |
| Word socio #90 (tras hotfix J5) | ✅ EMPLEOS: Súper Efectivo + Comercial Pérez · refs personales #1/#2 **sin** esos empleos |
| Word polígrafo #101 | ✅ Pinkys / Universo en INFORMACIÓN LABORAL · RECOMENDACIONES = — (no copia notas internas) |
| J13 Puesto en encabezado | ✅ 7 plantillas v2 con `Puesto:` + etiquetas celeste · desplegado prod |
| J16 Estudios extra | ✅ Override académico no se filtra (H10 intacto sin override) |
| J11 Vista previa | ✅ prod 21-ago — modal no esperaba el PUT (socio 677 campos se colgaba). Ahora abre al clic; Word + PDF OK en #86 Rebeca |
| Ops reset BD / dominio | ⏸️ Al final, sin borrar código |

**Hotfix J5 (20-ago):** `InformeWordXml::limitesTablaPorMarcador` — si el título está *entre* tablas (p. ej. «INFORMACIÓN LABORAL» antes de EMPLEOS), rellena la tabla **siguiente**, no la de arriba.

---

## 🔴 SPRINT I — (14 ago 2026, residual)

**Origen:** WA Stephany — pruebas coordinador + candidatos reales (Darwin/Novocolor, Franklin)  
**Plan completo:** `ultimos cambios 14-08-2026/PLAN_SPRINT_I_FEEDBACK_14-08-2026.md`  
**Estado deploy:** Fases 1–4 en prod (I1–I3, I5–I7, I11–I12 UI) · smoke Word PASS

### WA 14-ago ~6:26 PM — vencimiento enlace (nuevo **I13**)

Stephany reporta: un candidato de **hoy** no pudo llenar el formulario porque el enlace apareció **expirado**; ella misma ha visto el mismo error; **no ocurre con todos, solo algunos**; no sabe cuánto dura la vigencia del enlace. Relaciona esto con su pedido de botones **habilitar/deshabilitar** (I11/I12 — UI ya desplegada, investigación pendiente).

**Vigencia en sistema:** `configs.dias_vigencia_token` (default **30 días**, Admin → Configuración). Bloqueo por `token_expira_at` pasado o `estado_formulario = vencido`. Renovar: botón «Habilitar enlace» en orden.

**Pendiente I13:** ~~identificar evaluado~~ → **3 candidatos:** Walter ORD-0040 (#134), Carla ORD-0041 (#135), Gerson ORD-0044 (#138). Enlace expiró a las **2 horas** (no 31 días). Bug código I13b pendiente. Acción: Habilitar enlace mañana sin molestar a Stephany.

### Hecho en prod (14-ago noche)

I1 papelería → DOCUMENTOS ADJUNTOS · I2 económico podado · I3 preview · I6/I7 formulario · I11/I12 botones enlace

### Pendiente Sprint I

I4 (H16 PDF) · I8 (empresa móvil) · I9/I10 (Word fino) · **I13/I13b (vencimiento — Walter/Carla/Gerson identificados)** · UAT recorrido completo

---

## ✅ SPRINT H — CERRADO EN PROD (13 ago 2026)

**Cliente:** Stephany Castro / REPRO · **pruebas reales con candidatos** (13-ago)  
**Timeline:** Stephany revisa → reunión **sábado** → **go-live lunes**  
**Mensaje WA enviado:** 13-ago noche (foto, parejas, económico, preview-first; limitación PDF papelería iPage)  
**Fuentes:** WA 13-ago · `ultimos cambios 13-08-2026/ULTIMOS CAMBIOS FORMULARIO.pdf` · `ULTIMOS CAMBIOS3.pdf`

### UAT producción confirmado (13-ago noche)

| Prueba | Resultado |
|--------|-----------|
| Login navegador + dashboard | ✅ usuario temp `uat.g1.browser@repro.local` |
| Word #128 Aldin · #131 Edgar · #132 Franklin | ✅ HTTP 200 · 413–594 KB · nombre, expareja, sin `xxxx` |
| H13 preview-first (cuestionario #47 Edgar) | ✅ mammoth.js · botón descarga tras preview |
| Órdenes 155 / 158 / 159 | ✅ botón «Descargar informe Word» visible |
| Franklin ORD-2026-0038 | ✅ PDF final en disco · `resultadosDisponiblesParaEmpresa()` OK |
| Probe servidor | ✅ 27 OK / 5 FAIL (solo infra PDF: Imagick, pdftoppm, gs ausentes en iPage) |
| Probe HTTP autenticado | ✅ 24/24 OK (script auto-eliminado) |

**Casos referencia prod:**

| Evaluado | Orden | Código |
|----------|-------|--------|
| Aldin #128 | 155 | ORD-2026-0034 |
| Edgar #131 | 158 | ORD-2026-0037 |
| Franklin #132 | 159 | ORD-2026-0038 |

### ✅ Sprint H completo en prod

| ID | Estado | Archivos clave |
|----|--------|----------------|
| **H1–H4, H6, H7, H10** | ✅ prod | ver manifiesto parcial |
| **H5** | ✅ prod | `FechasLaboradasCampo` — `type="month"` · « al » en informe |
| **H8** | ✅ prod | `InformeWordFoto.php` — ratio ~3:4, quita `wp:anchor` |
| **H9** | ✅ prod | `InformeWordRelleno.php` — expareja/pareja, académico, montos Q |
| **H11–H14** | ✅ prod | deploy A–E 13-ago |
| **H13** | ✅ prod | `edit.blade.php` — preview-first + mammoth |
| **Batch Stephany WA** | ✅ prod | `InformeWordPdfPaginas.php`, `InformeWordAnexos.php`, `InformeWordXml.php` |

**Manifiestos:** `SprintH_parcial_*` · `SprintH_H8_*` · `SprintH_H9-H13_*` · `SprintH_Stephany_WA_*`  
**Scripts deploy:** `scripts/deploy_sprint_h_*.sh`

### ⚠️ Limitación conocida (no bloquea go-live)

**PDF papelería embebido en anexos Word:** iPage no tiene Imagick / `pdftoppm` / Ghostscript → fallback texto `[PDF] …` (sin crash). Imágenes de papelería sí se embeben. Fix crítico desplegado: `\shell_exec` en namespace `App\Support`.

### Pendiente post-Sprint H

| ID | Tema |
|----|------|
| **H16** | Generación PDF final automático — acordado: Word manual → subir PDF (esperar Stephany) |
| **H0** | Migración servidor — borrador `H0_MIGRACION_SERVIDOR_2026-08-13.md` · reunión sábado |

**Usuario UAT temp activo:** `uat.g1.browser@repro.local` / `UAT.G1Word2026!` — eliminar cuando Stephany termine de validar.

---

## ✅ SPRINT G — EN PROD (13 ago 2026)

**Estado:** Código desplegado · smoke prod 27/27 · **UAT 13-ago reabre G1.3, G2.1, G3.2**

---

## 🔴 SPRINT G — referencia (12 ago 2026)

**Cliente:** Stephany Castro / REPRO · pruebas 12-ago bloqueadas por informes  
**Prioridad acordada:** informes finales primero; ayuda/tutorial + videollamada **después**.  
**Primer paso:** G0 — reproducir Word en blanco. Stephany (12-ago noche): descarga **desde la orden**; **vista previa nunca funcionó**; **todos los evaluados creados hoy** fallan (no un caso aislado).

### Reportes críticos (12-ago)

| # | Problema | Causa probable en código |
|---|----------|--------------------------|
| 1 | Word preempleo polígrafo vacío + silueta negra (foto) | Relleno v2 / foto / datos no vinculados al evaluado del caso |
| 2 | “Informe” = PDF formulario candidato; debe ser final/preliminar subido | ✅ **G2.1** — partial `_informes_evaluado_empresa`, formulario en `<details>` referencia |
| 3 | Vista previa no genera informe tras editar tablas | Modal `edit.blade.php` solo enlaza PDF cuestionario + Word |
| 4 | Empresa sin historial ni observaciones REPRO | Historial en vista unificada solo `role_as >= 2`; config `historial_visible_empresa` no aplicada a empresa |

### Sprints G (resumen)

| Bloque | Alcance | Prioridad |
|--------|---------|-----------|
| **G0** | Diagnóstico caso real Stephany | ✅ P0 — causa PCLZip confirmada |
| **G1** | Word relleno + tablas faltantes + vista previa / generar final | G1.1 ✅ prod · G1.2/G1.3 pendiente |
| **G2** | UX informe empresa + historial + observaciones | G2.1 ✅ código · G2.2/G2.3 pendiente |
| **G3** | Motivo reprogramación visible, rehabilitar vencido, estado evaluación | P1 |
| **G4** | Select Seleccione, firma Infornet en PDF | P2 |
| **G5** | Unificación datos formulario → informe (`InformeDatos`) | ✅ prod 13-ago |

**Archivos Word clave:** `app/Support/InformeWord*.php` · `resources/templates/informe-*-v2.docx` · tablas REPRO en `InformePreempleo` + `tablas-informe-preempleo.blade.php`.

---

## ✅ SPRINT F — EN PROD (11 ago 2026)

**Cliente:** Stephany Castro / REPRO · post-UAT + formatos Word finales  
**Smoke prod:** Word polígrafo/VSA/socio OK (~441/408/471 KB) con evaluados demo — **no cubrió el caso del 12-ago**.  
**Dudas:** (1) ✅ «Son las mismas» · (2) ⏸️ dominio/servidor · (3) ✅ ayuda al final.  


 
 
**Audios (transcritos):** Word no volcaba datos; 1 informe por servicio como autorizaciones; cambios casi solo diseño.  
**Fuentes:** `docs/repro/cambios agosto/` — `ULTIMOS CAMBIOS.pdf`, `FORMATOS.pdf`, 7× docx.

| Sprint | Estado |
|--------|--------|
| **F0** Fixes seguros | ✅ |
| **F1** Word relleno celdas / datos editados | ✅ |
| **F2** Formulario/ops (académico 2 niveles, motivo reprogramación, ocultar poligrafista, PDF estudios) | ✅ |
| **F3** Siete plantillas Word + FORMATOS (VSA/socio/foto/preguntas) | ✅ |
| **Deploy** iPage + `motivo_reprogramacion` | ✅ 11-ago |

**Word activo:** matriz `InformeWordPlantillas` → `informe-*-v2.docx` (7 archivos). Layout `DATOS GENERALES` + marcadores por texto concatenado.  
**Tests:** `SprintF0*`, `SprintF2*`, `InformeWordSprintF1*`, `InformeWordPlantillasV2*`, `InformeWordFormatosF3*`, foto v2 en `InformeWordExportTest`.  
**No improvisar:** no reescribir motor formularios; Word solo REPRO; permisos empresa → `PERMISOS_EMPRESA_CLIENTE.md`.

---

## ✅ REVISIÓN CLIENTE AGOSTO 2026 — CERRADA (A–E)

**Cliente:** Stephany Castro / REPRO · feedback UAT ago 2026  
**Prioridad cliente (5 ago):** (1) formulario · (2) informe Word  
**Fuentes oficiales:** `docs/repro/cambios agosto/` — PDFs `correcciones.pdf`, `CAMBIOS PARA LOS REPORTES.pdf`, `USUARIO DE CLIENTE (2).pdf`

### Sprint A — ✅ cerrado y desplegado (6 ago 2026)

| Área | Qué quedó en prod | Verificado |
|------|-------------------|------------|
| **Word** | Bloques 1→6 (Laboral→Judicial); descarga `.docx` | HTTP 200 `/ordenes/135/evaluados/115/informe-word` |
| **Word hosting** | `InformeWordZip.php` — fallback PCLZip si falta `ZipArchive` (iPage) | ✅ |
| **Word bug 404** | Cast `(int)` en `OrdenesController`; `EvaluadoOrden.orden_id` → integer | ✅ |
| **Admin cuestionario** | Guardado borrador, vista previa PDF + enlace Word, minFilas condicional | ✅ |
| **Formulario E1** | Labels jefe/RRHH; selects «Seleccione…»; date_range Fechas laboradas | ✅ |
| **Aviso legal** | Formulario solo aplicante (empresa + admin) | ✅ |
| **Permisos trabajador** | `hasPermission()` solo JSON; UI oculta sin permiso; defaults sin crear orden/reportes | ✅ FTP 6-ago |

### Sprint C — ✅ cerrado y desplegado (6 ago 2026)

| Área | Qué quedó en prod | Verificado |
|------|-------------------|------------|
| **PDF autorización** | Documento aparte del cuestionario (admin + empresa) | ✅ HTTP 200 admin cuestionario #32 |
| **Anexos papelería Word** | Checkboxes en edit/show admin | ✅ cuestionario #15 |
| **Preguntas poligráficas** | Tabla editable + relleno última hoja Word | ✅ 6 filas en evaluado #112 |
| **Fix Word 500** | `InformeWordXml::establecerTextoCelda` — `preg_replace_callback` evita `$11` | ✅ `.docx` ~390 KB evaluado #112 |
| **UAT pendiente empresa** | PDF autorización vista empresa | ⏳ requiere `resultados_visibles_empresa` + contenido liberado (evaluado #112 aún NO) |

**Archivos clave Sprint C:**

```
app/Support/InformeWordPreguntasPoligraficas.php
app/Support/InformeWordAnexosPapeleria.php
app/Support/InformeWordRelleno.php          ← rellenarPoligraficaTabla
app/Support/InformeWordXml.php              ← fix establecerTextoCelda (ago 2026)
resources/views/admin/cuestionarios/partials/preguntas-poligraficas-word.blade.php
resources/views/admin/cuestionarios/partials/anexos-word-papeleria.blade.php
resources/views/admin/cuestionarios/pdf-autorizacion.blade.php
tests/Unit/InformeWordSprintCTest.php
```

### Sprint D — 🔄 en curso (3.1 Mis Órdenes)

| Cambio | Archivos | Estado |
|--------|----------|--------|
| Mis Órdenes = vista REPRO (`admin/ordenes/index`) | `EmpresaController`, sidebar, `admin/ordenes/*` | ✅ prod |
| Estado de Procesos = vista REPRO cuestionarios | `CuestionariosIndexSupport`, `admin/cuestionarios/index` | ✅ prod |

**No tocar sin leer spec:** permisos portal empresa → `PERMISOS_EMPRESA_CLIENTE.md`

### Resumen sprints B–E

| Sprint | Alcance | Estado |
|--------|---------|--------|
| **B** | Formulario completo (estudia actualmente, socio refs, vivienda→económica, periódica/específica) | ✅ **Prod 6-ago-2026** |
| **C** | Informe avanzado (autorización aparte, anexos papelería, tabla preguntas poligráficas) | ✅ **Prod 6-ago-2026** (fix `$11` en `InformeWordXml`) |
| **D** | Mis Órdenes + Estado de Procesos = misma vista REPRO (§3.1–3.2) | ✅ **Prod 6-ago-2026** |
| **E** | Confidencialidad reclutadores (§3.10), WhatsApp 77637811 (§3.8) | ✅ **UAT cerrado 10-ago** |

### ⚠️ Bug corregido en UAT Sprint E (10-ago-2026)

`EmpresaVisibilidadReclutadoresSupport::filtrarQueryOrdenesEmpresa/filtrarQueryEvaluadosEmpresa` comparaban `$user->role_as !== 1` con **comparación estricta**. Como `role_as` llega como `string "1"` desde MySQL, el filtro de confidencialidad **nunca se aplicaba en los listados** `/ordenes` y `/cuestionarios` (el detalle sí bloqueaba con 403 correctamente vía `puedeVerOrden()`, que ya casteaba a int). Fix desplegado: cast `(int) $user->role_as` en ambos métodos. **Lección para agentes:** siempre castear `role_as`/`principal`/ids a `(int)` antes de comparaciones estrictas — Eloquent no garantiza el tipo nativo en todos los contextos de query builder.

### Reglas para agentes (no improvisar)

1. **Portal empresa / permisos:** principal (`principal=1`) = MAPA completo; trabajador (`principal=0`) = **solo** `permisos_empresa` JSON — **ignora rol Spatie**.
2. **REPRO-only:** informe Word, edición cuestionario admin — nunca exponer a empresa aunque conozcan URL.
3. **Formularios:** alinear con PDFs en `docs/repro/cambios agosto/` y `docs/ejemplos de formularios reales/` — checklist en `PLAN_REVISION_AGOSTO_2026.md`.
4. **Deploy prod:** FTP iPage → `app/`, `resources/`; limpiar caché vía `public/clear_cache.php?key=REPRO_DEPLOY_2026_SECURE_KEY`.
5. **Tests locales:** `docker compose exec app php -d memory_limit=512M vendor/bin/phpunit` — permisos: `EmpresaPermisosTrabajadorTest`, `RevisionAgosto2026SupportTest`.

### Archivos clave Sprint A + permisos

```
app/Support/EmpresaPermisosSupport.php      ← MAPA permisos + defaults trabajador
app/Support/InformeWordZip.php              ← ZipArchive / PCLZip iPage
app/Models/User.php                         ← hasPermission(), tienePermisoEmpresa()
app/Http/Controllers/Admin/OrdenesController.php  ← informeWord (cast int)
app/Models/EvaluadoOrden.php                ← cast orden_id integer
app/Support/InformeWordBloquesEvaluador.php ← orden bloques cliente
resources/views/layouts/incempresa/sidebar.blade.php
resources/views/empresa/ordenes/*.blade.php
resources/views/shared/partials/aviso-formulario-solo-candidato.blade.php
```

---

## CONTEXTO RÁPIDO PARA AGENTES

### 🎯 PROPÓSITO DEL SISTEMA
REPRO Guatemala es un sistema web para gestionar evaluaciones poligráficas, VSA y socioeconómicas para empresas. Los usuarios empresariales crean órdenes con múltiples evaluados, los evaluados completan cuestionarios digitales, y REPRO realiza las evaluaciones y entrega resultados.

### ⚡ ESTADO ACTUAL (Julio 2026)
- ✅ **PRODUCCIÓN:** Fase 18 + Fase 19 + Fase 20 desplegadas en iPage (`reproappv2.szystems.com`)
- 🔴 **TRABAJO ACTIVO:** Alinear preguntas literales con formularios reales del cliente — ver `docs/business/PLAN_ALINEACION_FORMULARIOS_REALES_2026-07-29.md`
- ✅ **P0 literal PDF (30-jul-2026):** 19 laborales · 16 judiciales · 8 complementarias
- ✅ **P1 literal PDF (30-jul-2026):** salud/hábitos/sustancias · datos económicos (sin cuadrícula legacy)
- ✅ **P2 (30-jul-2026):** tabla empleos PDF · retirados motivo_busqueda y textarea legacy
- ✅ **Multi-tipo literal PDF (30-jul-2026):** periódica/específica 31 preg + §5 salud · socio §4 patrimonio + §6 vivienda/gastos PDF
- ✅ **FASE F FORMULARIOS:** E1–E6 + F7 Word · QA Fase G cerrado · **periódica 31 preg OK**
- ⏸️ **Deploy Fase F post-QA:** bloqueado hasta cerrar alineación A–J
- ✅ **4 ESTADOS INDEPENDIENTES:** Formulario / Programación / Evaluación / Orden (Fase 18)
- ✅ **FASE 19:** Fix duplicación órdenes, capacidad por sede, historial empresa, archivar órdenes, búsqueda DPI/nombre
- ✅ **TESTS:** **808 tests OK** — F7 Word tabular cerrado 20-jul
- ✅ **SEGURIDAD:** Permisos granulares + middleware `role` / `permission`
- ✅ **NOTIFICACIONES:** In-app ampliadas (creador, empresa, colaboradores — Fase 18)
- ⏳ **PENDIENTE OPS:** Cron iPage para auto-transiciones formulario 24h/30d (o fallback on-access)

### 📚 Mapa de contexto (mantener sincronizado)

| Documento | Cuándo actualizar |
|-----------|-------------------|
| `PROGRESS.md` | **Siempre** — sección 🔴 al inicio (fase activa, progreso E1, siguiente paso) |
| `docs/business/PLAN_IMPLEMENTACION_FORMULARIOS_2026-06-22.md` | Al cerrar cada punto E1–E7 (marcar `[x]`, estado global al final) |
| `docs/status/CONTEXTO_AGENTES.md` | **Este archivo** — al cerrar sesión o punto relevante (estado, tests, Docker, E1) |
| `docs/business/ANALISIS_FORMULARIOS_E_INFORME_2026-06-22.md` | Solo si cambian decisiones comerciales o spec |
| `docs/business/COTIZACION_EXTRAS_JUNIO_2026_CLIENTE.md` | Solo si cambia pricing o extras aprobados |

**Regla (Otto):** no dejar código/documentación desincronizados — contexto actualizado en la misma sesión del cambio.

| Documento | Uso |
|-----------|-----|
| `PROGRESS.md` | Seguimiento activo por fase |
| `docs/repro/cambios agosto/PLAN_REVISION_AGOSTO_2026.md` | **🔴 PLAN ACTIVO** — revisión cliente ago-2026, checklist sprints A–E |
| `docs/repro/cambios agosto/PERMISOS_EMPRESA_CLIENTE.md` | **Spec permisos** principal vs trabajador — no improvisar |
| `docs/business/PLAN_ALINEACION_FORMULARIOS_REALES_2026-07-29.md` | Plan alineación literal A→J (fase anterior) |
| `docs/ejemplos de formularios reales/` | Formularios reales ago-2025 (POLIGRAFO PRESENCIAL, SOCIO, etc.) |
| `docs/business/PLAN_IMPLEMENTACION_FORMULARIOS_2026-06-22.md` | Plan histórico E1–E7 punto por punto |
| `docs/business/ANALISIS_FORMULARIOS_E_INFORME_2026-06-22.md` | Spec formularios + informe Word + decisiones comerciales |
| `docs/business/COTIZACION_EXTRAS_JUNIO_2026_CLIENTE.md` | Word Q 1,600 · 1B · WhatsApp |
| `docs/status/CONTEXTO_AGENTES.md` | Contexto técnico para agentes IA |
| `docs/Fase19_Alcance_Definitivo_2026-06-12.md` | Alcance Fase 19 aprobado |
| `docs/deployment/Fase19_deploy_manifest.txt` | Manifiesto último deploy mayor |

### ✅ Fase F E1 — CERRADA (23-jun-2026)

Motor base 1.1–1.9: tabla dinámica, condicionales, autosave, catálogo GT, foto, instrucciones, precarga, notas evaluador (`evaluador_notas`), tests motor.

### ✅ Fase F E2 — CERRADA (2-jul-2026)

| Completado | Siguiente |
|------------|-----------|
| Pre-empleo 2.1–2.21 (5 secciones matriz) | **E3.1** UI espacios internos evaluador |
| 740 tests OK · **QA manual flujo completo OK** | E3.2–3.4 mapeo informe + permisos |

**Correcciones post-QA manual (2-jul):**
- **2.8** `HistorialAcademico` + `formacion-academica.js` — filas por nivel al seleccionar último grado.
- **2.12** Detalle condicional económico (vehículos, propiedades, SAT, etc.) en `situacion-economica.blade.php`.
- **2.13** Detalle condicional salud en `antecedentes.blade.php`.
- Validación legible: `CuestionarioValidacionLabels`, mensajes en `SaludHabitosCampos` / `SituacionEconomicaCampos`.
- Pantalla `completado.blade.php`: cierre con SweetAlert (pestaña manual).
- Sesión previa: foto/licencia/spinner, badge «Confidencial», autosave.

**Demo manual Pre-empleo:** `DemoPruebaManualE1Seeder` → `http://localhost:8000/cuestionario/e1demo2026pruebamanualtokenrepr0` · DPI `2405617300105`

**Tests E2:** `CuestionarioPreempleoSeccion2ExtendidaTest` · `CuestionarioPreempleoSecciones345Test` · `HistorialAcademicoTest`

### ✅ Fase F E3 — CERRADA (8-jul-2026)

| Completado | Detalle |
|------------|---------|
| **3.1–3.4** | Tablas informe (`InformePreempleo`), notas evaluador, filtro empresa (`CamposInternosPreempleo`), tests visibilidad |
| Portal empresa | Vista estilizada reutilizando partials admin (`shared/cuestionario/seccion-lectura`) — solo lectura |
| PDF empresa | Agrupación por sección/subsección (`pdf-secciones-empresa`) |
| QA post-E2 | Scroll tablas dinámicas, fix condicionales deudas |

**Archivos clave E3:**
- `app/Support/InformePreempleo.php` · `CamposInternosPreempleo.php` · `CuestionarioPresentacionEmpresa.php`
- `resources/views/admin/cuestionarios/partials/tablas-informe-preempleo.blade.php`
- `resources/views/shared/cuestionario/seccion-lectura.blade.php` · `pdf-secciones-empresa.blade.php`
- `resources/views/empresa/cuestionarios/show.blade.php` (pestañas estilizadas)

**Tests E3:** `InformePreempleoTest` · `InformePreempleoVisibilidadTest` · `CuestionarioTablaDinamicaTest`

### ✅ Fase F E4 — CERRADA (14-jul-2026)

| Completado | Detalle |
|------------|---------|
| **4.1–4.7** | 6 secciones socio (`tipo_formulario=socioeconomico`), refs/bienes/presupuesto/vivienda |
| PDF empresa | Sec. 6 socio en `pdf-secciones-empresa` |
| Informe admin | Refs fam/pers editables; vecinales/vivienda ocultos empresa |
| Validación | Unificada tablas dinámicas + mensajes legibles (todas las secciones) |

**Demo manual E4:** `DemoPruebaManualE4Seeder` → token `e4demo2026pruebamanualtokenrepr0` · DPI `2405617300205` (13 dígitos)  
**Empresa demo:** `demo-empresa-e4@repro.local` / `empresa1234`

**Tests E4:** `CuestionarioSocioeconomicoTest` (5) · `InformePreempleoVisibilidadTest` · `InformePreempleoTest`

### ✅ Fase F E5 — CERRADA (18-jul-2026)

| Completado | Detalle |
|------------|---------|
| **5.1–5.3** | Periódica 5 sec.: omisiones IGSS/NIT/hermanos/complementaria; laboral **31 preguntas** PDF; §5 salud completa; solo DPI |
| **5.4–5.6** | Específica = base periódica; académica solo último grado; pregunta 1 caso/hecho amplia (max 8000) |
| PDF flujo | `shared/pdf/flujo-pagina` — sin saltos forzados con huecos |

**Demo Periódica:** `DemoPruebaManualE5PeriodicaSeeder` → token `e5demo2026periodicatokenrepr0` · DPI `2405617300305`  
**Demo Específica:** `DemoPruebaManualE5EspecificaSeeder` → token `e5demo2026especificatokenrepr0` · DPI `2405617300405`

### ✅ Fase A — Legal (18-jul-2026)

- ✅ **A.1** 7 plantillas (`config/autorizaciones_legales.php` + `AutorizacionesLegales`)
- ✅ **A.2** Infornet pre-empleo (`/infornet`, misma firma)
- ✅ **A.3** `motivo_hecho_evaluacion` en evaluado + form admin
- ✅ **A.5** PDF cuestionario con snapshot autorización + Infornet
- ⏳ **Swap textos oficiales pendiente:** los textos en `config/autorizaciones_legales.php` son funcionales (no copia literal del paquete del cliente). En repo solo está `docs/formularios/autorizacion-general.pdf` (Socio/VSA + Infornet, 1 pág.); **no están versionadas las 7 plantillas definitivas** que el cliente mencionó en jun-2026.
- 📣 **Al entregar versión de pruebas al cliente:** pedir **directamente** (WhatsApp/correo/reunión) el paquete oficial: **7 autorizaciones legales + Infornet definitivos** (PDF/DOCX o texto editable). Incluir la misma solicitud en el **informe de entrega / informe final** para que quede documentado y no se olvide antes del cierre.

### ✅ E7 — Word .docx (18-jul base · F7 tabular 20-jul-2026)

- ✅ Botón «Descargar informe Word» por evaluado (`InformeWordExport`)
- ✅ **7.1 plantillas jul-2026:** `informe-poligrafo-preempleo.docx` + `informe-poligrafo-periodica.docx` (origen `docs/Plantillas Word/`)
- ✅ Selector servicio×formulario (`InformeWordPlantillas`); VSA/Socio/Específica reutilizan base con etiqueta de proceso
- ✅ Relleno tabular sin destruir diseño (`InformeWordRelleno`, `InformeWordXml`, `InformeWordDatos`)
- ✅ Encabezado + tablas auto (familiar, académico, laboral, deudas, complementaria/periódica)
- ✅ Foto evaluado en cuerpo (encima tabla Proceso): proporción, altura máx., espaciado compacto, sin anclaje legacy
- ✅ Anexos tatuaje (`InformeWordAnexos`) · totales deudas · `keepNext` títulos sección · `[Content_Types].xml` JPG
- ✅ Admin: foto + documentos en editar/show cuestionario (`AdminCuestionarioFotoEditTest`)
- ✅ **F7 fase D (7.6):** narrativas REPRO — salud, hábitos, drogas, judicial, info complementaria Q&A, poligráfica (NDI/DI), recomendaciones, conclusiones (nombre), APA (`InformeWordNarrativas`)
- ⏳ Plantillas VSA/Socio dedicadas si el cliente las entrega
- 📦 **Deprecado:** `informe-repro.docx` (ago-2025 · data ejemplo Jorge Luis)

**Demo Word:** `docker compose exec app php artisan db:seed --class=DemoPruebaWordMultiservicioSeeder --force`  
→ orden `ORD-DEMO-WORD-2026` · login `http://localhost:8000/login` → `admin@repro.com` / `admin1234` (el seeder crea el usuario si falta)

### ✅ Fase F E6 — CERRADA (Integración, 18-jul-2026)

- ✅ **6.1** Matriz servicio→formulario (`MatrizFormularioServicio`, UI create/edit, `OrdenFormRequest`)
- ✅ **6.2** Mensajes «Información Importante» por tipo (`MensajesInformacionImportante`, partial reutilizable)
- ✅ **6.3** Jotform workaround — marcar completado manual solo socio (`EvaluadoOrden::puedeMarcarFormularioCompletadoManualSocio`)
- ✅ **6.4** Papelería post-envío — subida con mismo token hasta `token_expira_at` (~30 días); partial `documentos-candidato` en `completado` + `finalizar`
- ✅ **6.5** Regresión PHPUnit **796/796**
- ✅ **QA manual** navegador 18-jul: matriz, mensajes, Jotform, hermanos, foto, documentos post-envío

**Archivos clave E6:** `MatrizFormularioServicio.php` · `MensajesInformacionImportante.php` · `documentos-candidato.blade.php` · `EvaluadoOrden::enlaceCuestionarioVigente()` / `puedeSubirDocumentosConEnlace()` · migración tipos doc socio · `E6PapeleriaPostEnvioTest`

**Demos E5:**
- Periódica: `e5demo2026periodicatokenrepr0` · DPI `2405617300305`
- Específica: `e5demo2026especificatokenrepr0` · DPI `2405617300405`

### 🐳 Desarrollo local (Docker)

```bash
cd /home/szott/proyectos/repro && docker compose up -d          # levantar stack
docker compose up -d nginx   # si localhost:8000 no responde (nginx caído)
docker compose exec app php artisan migrate --force
docker compose exec app php -d memory_limit=512M vendor/bin/phpunit
```

- App: http://localhost:8000 · phpMyAdmin: http://localhost:8080
- **Demo manual Pre-empleo:** `docker compose exec app php artisan db:seed --class=DemoPruebaManualE1Seeder --force` → token `e1demo2026pruebamanualtokenrepr0` · DPI `2405617300105`
- **Demo Word multiservicio:** `docker compose exec app php artisan db:seed --class=DemoPruebaWordMultiservicioSeeder --force` → orden `ORD-DEMO-WORD-2026` · login `/login` → `admin@repro.com` / `admin1234`
- Contenedores: `repro-app`, `repro-db`, `repro-nginx`, `repro-phpmyadmin`
- Formulario actual (depto/municipio residencia): `resources/views/cuestionario/secciones/datos-personales.blade.php` + `<x-depto-municipio-select>`

---

## 🆕 ALCANCE NUEVO RECIBIDO (21-jun-2026) — LEER ANTES DE TOCAR FORMULARIOS

La cliente entregó la **especificación funcional completa de formularios** (`CREACIÓN FORMULARIOS DE SISTEMA.pdf`, 46 pág.) + un **informe de ejemplo** para el ítem Word. Análisis detallado en `docs/business/ANALISIS_FORMULARIOS_E_INFORME_2026-06-22.md`.

**Es una REINGENIERÍA del motor de cuestionarios, no "campos nuevos".** Hoy el sistema guarda respuestas como clave→valor en `cuestionario_respuestas` con Blade hardcodeado por sección. La especificación exige:
- Tablas dinámicas ilimitadas (hijos, hermanos, empleos, deudas, tatuajes, referencias, bienes…).
- Campos condicionales extensivos; tabla académica autogenerada.
- **Campos internos del evaluador** separados de las respuestas del candidato.
- Generación automática de tablas hacia el informe final (editables por evaluador).
- Foto del candidato + anexos con imágenes; catálogos Deptos/Municipios GT dependientes.
- 4 formularios diferenciados: **Pre-empleo (matriz, 5 secciones)**, **Socioeconómico (5 + 1 exclusiva)**, **Periódica**, **Específica**.

**⚠️ DECISIÓN CLAVE (22-jun): los formularios NO se cobran aparte.** Se verificó que los formularios originales entregados por la cliente al inicio del proyecto (ago-2025: `POLIGRAFO PRESENCIAL.pdf`, `SOCIOECONOMICO...pdf`, `PERIODICO ESPECIFICO.pdf`) **ya contenían todo el contenido** (preguntas, tablas familia/empleos/deudas, drogas, foto). El sistema en producción implementó solo ~70–90 campos (una fracción). Por tanto, **completar los formularios = CIERRE DEL PROYECTO** (alcance original) y desbloquea el **saldo Q 10,000**. Las menciones previas a "Fase F cobrable / Q 14,500-16,000" quedan **anuladas**.

**Decisiones comerciales vigentes (cliente):**
- ✅ **Completar formularios (4 tipos) + motor** → **sin cobro aparte**, es cierre del proyecto (saldo Q 10,000).
- ✅ **Word editable (.docx)** aprobado — **Q 1,600** (50% anticipo). Versión base ahora; "rica" depende del motor completo.
- 🕐 **1B Agregar servicio** — **Q 5,200**, programado a **2–3 meses**.
- 🕐 **WhatsApp API** — **Q 3,800**, pospuesto.
- ✅ Fase A legal (7 autorizaciones + Infornet + corrección Específica) → dentro del **saldo Q 10,000**.
- ✅ **Ajuste temporal Socioeconómico:** permitir marcar "Formulario Completado" manual **solo** para servicio Socioeconómico (usan Jotform mientras tanto).
- Estructura: **5 secciones** matriz + 1 exclusiva Socio.
- Campos internos del evaluador: **solo REPRO** los edita; empresa solo sube info general.

**Plan de trabajo ordenado:** `docs/business/PLAN_IMPLEMENTACION_FORMULARIOS_2026-06-22.md`
**Pendientes del cliente:**
- **7 autorizaciones legales + Infornet definitivos** — pedir al entregar versión de pruebas **y** repetir en informe final (swap en `config/autorizaciones_legales.php`).
- Confirmar si `resources/templates/informe-repro.docx` (`PERIODICO ESPECIFICO.docx`) aplica a **todos** los tipos servicio/formulario o hay variantes.
- Auditoría de campos internos (se piden por etapa).
- Foto obligatoria: **decidido sí** (cámara o subir).

---

## ARQUITECTURA CLAVE

### Stack Tecnológico
```
Laravel 12.37.0 + PHP 8.3.16 + MySQL 8.0
Frontend: Blade + Bootstrap 5 + jQuery
Auth: Laravel Sanctum + Sistema Roles/Permisos
PDF: DomPDF con branding REPRO
```

### Usuarios del Sistema
```
ADMIN (role_as = 3) → 25 permisos → Control total
REPRO (role_as = 2) → 14 permisos → Evaluaciones + Reportes
EMPRESA (role_as = 1) → 6 permisos → Sus órdenes + Ver resultados
EVALUADOS → NO SON USUARIOS → Acceso por token único
```

### 🔥 REGLA CRÍTICA:
**❌ NUNCA crear usuarios con role_as = 0**
**✅ Los evaluados van en tabla `evaluados_orden` con token único**

---

## MÓDULOS COMPLETADOS

### 1. SEGURIDAD ✅
- Sistema dual: `role_as` (legacy) + `roles/permissions` (nuevo)
- Middleware: auth, role, permission, redirect.role
- 26 permisos distribuidos en 8 módulos

### 2. EMPRESAS ✅
- CRUD completo + PDFs con branding REPRO
- Relación 1:N con usuarios y órdenes

### 3. CONFIGURACIÓN ✅
- Configuración global del sistema
- Logo, email, moneda, redes sociales

### 4. ÓRDENES ✅
- CRUD completo con múltiples evaluados
- Códigos únicos: ORD-YYYY-NNNN
- PDF de orden con evaluados
- Cambio de estados con observaciones e historial (`estado_historial`)
- **Fase 18:** 4 estados independientes por candidato (ver abajo)
- **Fase 19:** Editar orden sin duplicar candidatos · archivar (solo admin, no borrar)
- **Fase 19:** Filtro órdenes archivadas (admin)

**Estados por candidato (Fase 18 — modelo vigente):**
```
estado_formulario    → 5 valores (link_enviado, pendiente_de_llenar, formulario_completado_y_recibido, etc.)
estado_programacion  → 8 valores (contactando, programado, inasistencia, proceso_realizado, etc.)
estado_evaluacion    → 7 valores (pendiente_de_evaluacion → en_proceso → en_revision → informe_final_enviado)
Orden.estado         → 4 valores automáticos: orden_recibida, en_proceso, entregado, cancelado
```

**Sinergia vigente (Fase 19):**
- S4: En Proceso exige formulario completado
- S5: En Proceso exige haber estado Programado
- S2 eliminado: Virtual puede programarse sin formulario
- Capacidad de citas por `sedes.capacidad`, no por poligrafista

### 5. CUESTIONARIOS (ADMIN) ✅
- Ver, editar, marcar completo
- PDF con branding REPRO
- Acceso desde listado de evaluados en orden
- **NUEVO:** 6 tarjetas estadísticas
- **NUEVO:** Link directo a orden
- **NUEVO:** Columna contacto (email, tel, cel)
- **NUEVO:** Columna servicio/formulario
- **NUEVO:** Reenvío manual de correos

### 6. CUESTIONARIOS (PÚBLICO) ✅
- Ruta: `GET /cuestionario/{token}` (`cuestionario.mostrar`) — **no** `/cuestionarios/` (admin)
- Acceso por token sin autenticación; exige `token_expira_at > now()`
- **Fase 20:** vista `enlace-invalido` distingue token inexistente vs expirado; log `Acceso a cuestionario rechazado`
- Vigencia: `Config::diasVigenciaTokenEnlace()` (mín. 1 día; 0 en BD → 30)
- Verificación de identidad por DPI
- Navegación por secciones
- Guardado automático
- Página de confirmación

### 7. DASHBOARD ✅
- Estadísticas diferenciadas por rol (Admin/REPRO vs Empresa)
- **Fase 19:** Búsqueda de candidatos por DPI o nombre (dashboard empresa)

**Ruta:** `GET /dashboard`

### 8. REPORTES ✅ (NUEVO)
- Reporte de Evaluaciones con filtros
- Reporte de Empresas (Admin/REPRO)
- Exportación PDF con branding REPRO (logo horizontal)
- Exportación Excel con columna Tipo de Formulario

**Rutas:**
```
GET /reportes/evaluaciones       - Reporte evaluaciones
GET /reportes/empresas           - Reporte empresas
GET /reportes/evaluaciones/pdf   - Exportar PDF
GET /reportes/evaluaciones/excel - Exportar Excel
```
**Tests:** 10 tests pasando

### 9. PORTAL EMPRESA ✅
- Dashboard con búsqueda de candidatos (Fase 19)
- Navegación: órdenes, evaluados, cuestionarios
- **Fase 19:** Historial de estados visible (config `historial_visible_empresa`, default ON)
- Visualización de resultados cuando `resultados_visibles_empresa` activo + orden `entregado`
- **E3 (8-jul):** Cuestionario en portal con pestañas estilizadas (cards/tablas, solo lectura) y PDF agrupado
- No ve órdenes archivadas · no ve campos internos del evaluador

**Controlador:** `EmpresaController.php` · `AdminController.php` (dashboard empresa)

### 10. NOTIFICACIONES EMAIL ✅
- Email al asignar evaluado (automático)
- Email recordatorio diario (8:00 AM)
- Email confirmación al completar
- Reenvío manual desde UI

**Mailables:**
```
EvaluadoAsignadoMail        - Al crear evaluado
RecordatorioCuestionarioMail - Recordatorio diario
CuestionarioCompletadoMail   - Al completar
```

**Comando:** `php artisan cuestionarios:enviar-recordatorios`
**Tests:** 8 tests pasando

---

## FLUJO PRINCIPAL

```
1. EMPRESA/REPRO crea ORDEN con evaluados
   ├── Múltiples evaluados por orden
   ├── Tipos: Polígrafo, VSA, Socioeconómico  
   └── Formularios: Pre-empleo, Periódica, Específica

2. Sistema genera código único (ORD-2026-NNNN)
   └── Crea tokens únicos para cada evaluado

3. EVALUADO accede con token
   ├── Verifica identidad con DPI
   ├── Completa cuestionario por secciones
   └── Finaliza y firma

4. ADMIN/REPRO ve cuestionario completado
   ├── Puede editar respuestas si necesario
   └── Genera PDF del cuestionario

5. Orden avanza por estados hasta "entregado"
```

---

## BASE DE DATOS CLAVE

### Tablas Principales
```sql
users           -- Usuarios: admin, repro, empresa (NO evaluados)
empresas        -- Empresas clientes
ordenes         -- Órdenes de evaluación
evaluados_orden -- Evaluados con token único (NO son users)
cuestionarios   -- Respuestas JSON por evaluado
roles           -- admin, repro, empresa
permissions     -- 26 permisos granulares
```

### Relaciones
```
Empresa → hasMany → User (role_as = 1)
Empresa → hasMany → Orden
Orden → hasMany → EvaluadoOrden
EvaluadoOrden → hasOne → Cuestionario
```

---

## BRANDING REPRO (PDFs)

### Colores
```css
--color-principal: #000555;  /* Azul oscuro */
--color-secundario: #ffb000; /* Amarillo */
--color-terciario: #ffcc33;  /* Amarillo claro */
--color-fondo: #f8f9fa;      /* Gris claro */
```

### Estructura Header
```html
<div class="repro-header" style="background: #000555;">
    <div class="repro-logo-container" style="background: #f8f9fa;">
        <img src="logoreproxelahorizontal.png" />
    </div>
    <h1 style="color: #ffb000;">Título</h1>
</div>
```

---

## ARCHIVOS CLAVE

### Controladores
```
app/Http/Controllers/Admin/
├── OrdenesController.php        # CRUD + PDF + cambiar estado + reenviar correo
├── CuestionariosController.php  # Ver/editar + PDF
├── EmpresasController.php       # CRUD + PDFs
├── UsersController.php          # CRUD + PDFs
├── DashboardController.php      # Dashboard por rol
├── ReportesController.php       # Reportes + exportación PDF/Excel
└── ConfigController.php

app/Http/Controllers/
├── CuestionarioController.php   # Flujo público evaluados + notificaciones
└── EmpresaController.php        # Portal empresa (verOrden, verCuestionario)
```

### Vistas Principales
```
resources/views/admin/
├── ordenes/       # index, show, create, edit, pdf
├── cuestionarios/ # index (mejorado), show, edit, pdf
├── dashboard/     # index
├── reportes/      # evaluaciones, empresas, pdf/
├── empresa/       # CRUD + PDFs
└── user/          # CRUD + PDFs

resources/views/empresa/
├── ordenes/       # index, show (portal empresa)
└── cuestionarios/ # show (portal empresa — pestañas estilizadas E3)

resources/views/shared/cuestionario/
├── seccion-lectura.blade.php      # Lectura empresa (reutiliza partials admin)
└── pdf-secciones-empresa.blade.php # PDF agrupado empresa

resources/views/cuestionario/
├── verificar-identidad.blade.php
├── seccion.blade.php
├── finalizar.blade.php
└── completado.blade.php

resources/views/emails/
├── evaluado-asignado.blade.php
├── recordatorio-cuestionario.blade.php
└── cuestionario-completado.blade.php

resources/views/layouts/
└── cuestionario.blade.php  # Layout público
```

### Mailables (NUEVO)
```
app/Mail/
├── EvaluadoAsignadoMail.php
├── RecordatorioCuestionarioMail.php
└── CuestionarioCompletadoMail.php
```

### Comandos Artisan (NUEVO)
```
app/Console/Commands/
└── EnviarRecordatoriosCuestionario.php  # Diario 8:00 AM
```

### Modelos
```
app/Models/
├── Orden.php           # Estados, código único
├── EvaluadoOrden.php   # Token, cuestionario_completado
├── Cuestionario.php    # Respuestas JSON
├── Empresa.php
└── User.php            # HasRolesAndPermissions
```

---

## RUTAS IMPORTANTES

### Admin (requiere auth)
```php
// Órdenes
Route::resource('ordenes', OrdenesController::class);
Route::patch('ordenes/{orden}/cambiar-estado', ...);
Route::get('ordenes/{orden}/pdf', ...);

// Cuestionarios
Route::get('cuestionarios', ...);
Route::get('cuestionarios/{id}', ...);
Route::get('cuestionarios/{id}/pdf', ...);

// Dashboard
Route::get('dashboard', [DashboardController::class, 'index']);

// Reportes
Route::get('reportes/evaluaciones', ...);
Route::get('reportes/evaluaciones/pdf', ...);
Route::get('reportes/evaluaciones/excel', ...);
Route::get('reportes/empresas', ...);

// Reenviar correo
Route::post('evaluados/{evaluado}/reenviar-correo', ...);
```

### Portal Empresa (requiere auth + role empresa)
```php
// Órdenes de empresa
Route::get('empresa/ordenes', [EmpresaController::class, 'ordenes']);
Route::get('empresa/ordenes/{id}', [EmpresaController::class, 'verOrden']);

// Cuestionarios de empresa (si resultados disponibles)
Route::get('empresa/cuestionarios/{id}', [EmpresaController::class, 'verCuestionario']);
```

### Público (sin auth)
```php
Route::get('cuestionario/{token}', ...);
Route::post('cuestionario/{token}/verificar', ...);
Route::get('cuestionario/{token}/seccion/{n}', ...);
Route::post('cuestionario/{token}/seccion/{n}', ...);
Route::get('cuestionario/{token}/finalizar', ...);
Route::post('cuestionario/{token}/completar', ...);
```

---

## PRÓXIMOS MÓDULOS A IMPLEMENTAR

### 1. CALENDARIO/AGENDA (Prioridad Alta)
- Vista de evaluaciones programadas
- Agenda para poligrafistas
- Filtros por fecha y poligrafista

### 2. AUDITORÍA/LOGS (Prioridad Alta)
- Registro de acciones de usuarios
- Historial de cambios en órdenes
- Trazabilidad completa

### 3. GESTIÓN DE POLIGRAFISTAS (Prioridad Media)
- Asignación de evaluaciones
- Carga de trabajo
- Disponibilidad

### 4. RESULTADOS DE EVALUACIONES (Prioridad Media)
- Carga de resultados poligráficos
- Generación de informes finales
- Firma digital

### 5. API REST (Prioridad Baja)
- Endpoints para consulta
- Webhooks
- Documentación

---

## COMANDOS ÚTILES

```bash
# Servidor de desarrollo
php artisan serve

# Ejecutar tests
php artisan test                           # Todos los tests
php artisan test --filter=Dashboard        # Tests de dashboard
php artisan test --filter=Reportes         # Tests de reportes
php artisan test --filter=Notificaciones   # Tests de notificaciones
php artisan test --filter=Ordenes          # Tests de órdenes

# Enviar recordatorios manualmente
php artisan cuestionarios:enviar-recordatorios

# Tinker para debugging
php artisan tinker

# Limpiar cache
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

---

## RESUMEN DE TESTS

| Área | Tests | Estado |
|------|-------|--------|
| Suite completa | 796 | ✅ Pasando (2026-07-18, Fase A + E7) |
| Fase 19 | `Fase19Sprint3Test` | ✅ Historial, archivar, búsqueda |
| Sinergia | `Fase18SinergiaReglasSemana3Test` | ✅ S4, S5, S2 eliminado |
| Calendario | `CalendarioTest` | ✅ Capacidad sede |

**Ejecutar:** `docker exec repro-app php -d memory_limit=512M vendor/bin/phpunit`

---

## 📋 AUDITORÍA LITERAL PDF (30-jul-2026)

**Fuente:** `docs/ejemplos de formularios reales/POLIGRAFO PRESENCIAL (2).pdf`  
**Extracción:** `pdftotext` → `/tmp/poligrafo_presencial.txt`

| Bloque | Estado | Archivo |
|--------|--------|---------|
| 19 laborales (orden + texto) | ✅ P0 aplicado | `HistorialLaboralIntegridad.php` |
| 16 judiciales | ✅ P0 aplicado | `AntecedentesJudiciales.php` |
| 8 complementarias | ✅ P1 aplicado | `InformacionComplementaria.php` |
| Salud/hábitos/sustancias | ✅ P1 aplicado | `SaludHabitosCampos.php`, `antecedentes.blade.php` |
| Económica (cuadrícula legacy) | ✅ P1 aplicado | `SituacionEconomicaCampos.php`, `situacion-economica.blade.php` |
| Tabla empleos extra (jefe, RRHH) | ✅ P2 aplicado | `TablaDinamica::columnasEmpleosPreempleo()` |

### Periódica / Específica (`PERIODICO ESPECIFICO.pdf`)

| Bloque | Estado | Archivo |
|--------|--------|---------|
| 31 preguntas laborales (orden + texto) | ✅ aplicado | `HistorialLaboralPeriodico.php` |
| Tabla empleo actual (5 cols PDF) | ✅ aplicado | `TablaDinamica::columnasEmpleoActualPeriodico()` |
| §5 salud/hábitos/judicial/complementaria | ✅ aplicado | `CuestionarioController` → `antecedentes.blade.php` |
| §1–§2 (hermanos omitidos) | ✅ hereda matriz | `InformacionFamiliarRequest` / vistas compartidas |

### Socioeconómico (`SOCIOECONOMICO...pdf`)

| Bloque | Estado | Archivo |
|--------|--------|---------|
| §1–§5 matriz pre-empleo | ✅ hereda P0/P1 | mismas clases que pre-empleo |
| §4 patrimonio | ✅ aplicado | `econ_patrimonio_aprox` en `situacion-economica.blade.php` |
| §6 referencias / vivienda / gastos 10 rubros | ✅ aplicado | `socioeconomico-complementaria.blade.php`, `SocioeconomicoComplementariaCampos.php` |

**Tests literales:** `tests/Unit/FormularioLiteralesClienteTest.php` · suites: `CuestionarioPeriodicaTest`, `CuestionarioSocioeconomicoTest`, `CuestionarioPreempleoSecciones345Test`.

**⚠️ Claves reordenadas:** `integridad_01…19` y `periodico_01…31`; respuestas guardadas antes del 30-jul pueden no corresponder semánticamente a la misma pregunta.

**Tokens QA local:** pre-empleo §3 `browserqa2026preempleoalign01` · §5 `aq0BoPHPkpnG6pM3Lhkm7NtX3vSqEItBZ1u0VTQs6vR10haeVIuHzdmLR2Gd86py` · periódica `e5demo2026periodicatokenrepr0` · socio §6 `browserqa2026socioalign00001`

---

## 📁 REGLAS DE ORGANIZACIÓN

**❌ NUNCA crear archivos .md en la raíz del proyecto**
**✅ SIEMPRE usar carpetas en docs/ según categoría:**
```
docs/status/     → Estados y auditorías
docs/technical/  → Documentación técnica
docs/business/   → Documentos de negocio
docs/security/   → Seguridad
docs/database/   → Base de datos
docs/guides/     → Guías de usuario
docs/deployment/ → Despliegue
```

---

## LÓGICA DE NEGOCIO IMPORTANTE

### Visibilidad de Resultados para Empresa
```php
// En modelo Orden.php
public function resultadosDisponiblesParaEmpresa(): bool
{
    return $this->resultados_visibles_empresa == 1;
}
```
- El campo `resultados_visibles_empresa` en la tabla `ordenes` controla si los usuarios empresa pueden ver los cuestionarios completados
- El reporte de evaluaciones NO usa este filtro (muestra todos los evaluados)
- El acceso al cuestionario individual SÍ usa este filtro

### Redirección por Rol después de CRUD
```php
// OrdenesController.php - store(), update(), destroy()
if (Auth::user()->role_as == 1) {
    return redirect()->route('empresa.ordenes')->with('status', '...');
}
return redirect()->route('ordenes.index')->with('status', '...');
```

---

## DESPLIEGUE EN PRODUCCIÓN

### Hosting: iPage
- **URL:** https://reproappv2.szystems.com
- **Guía:** `docs/deployment/IPAGE_DEPLOY.md`
- **Manifiesto Fase 19:** `docs/deployment/Fase19_deploy_manifest.txt` (58 archivos)
- **Último deploy:** 2026-06-13 · commits `8093ab0a`, `14a95f47` · migraciones batch 111

### Carpetas típicas a subir (FTP):
```
app/, database/, resources/, routes/  (+ vendor/ en deploy completo)
```

### Migraciones Fase 19 (ya aplicadas en prod):
```
2026_06_10_120000_add_historial_visible_empresa_to_configs_table
2026_06_10_120001_add_archivada_fields_to_ordenes_table
```

---

## CONTACTO

**Desarrollador:** Otto Szarata (szystems@hotmail.com)  
**Sistema:** REPRO Guatemala  
**Repositorio:** repro (branch: master)  

---

## PRÓXIMAS REVISIONES (post-F7, jul-2026)

### Portal empresa / dashboard cliente

**Alcance esperado:** los cambios F7 (Word, narrativas, foto admin, notas evaluador) son **internos REPRO**. El cliente **no** debe verlos en su portal; solo resultados liberados (PDF cuestionario filtrado, informe preliminar HTML/archivo, documentos papelería).

| Área | Qué revisar | Archivos clave |
|------|-------------|----------------|
| **Visibilidad resultados** | ✅ UI empresa usa `resultadosDisponiblesParaEmpresa()`; mensaje «en validación» si flag activo pero orden no entregada | `empresa/ordenes/show.blade.php` |
| **Informe Word empresa** | ✅ Bloqueado en backend (`role_as >= 2`) aunque conozcan la URL | `OrdenesController::informeWord()` |
| **Informe preliminar HTML** | ¿Debe ocultarse hasta `entregado`? Hoy puede verse antes que los archivos | `empresa/ordenes/show.blade.php` |
| **Enlaces admin en vistas empresa** | Algunas rutas apuntan a `ordenes.show` (admin) en lugar de `empresa.ordenes.show` | `empresa/cuestionarios/show.blade.php` · `admin/reportes/evaluaciones.blade.php` |
| **Permisos sub-usuario** | ✅ `User::hasPermission()` — trabajador solo JSON; principal todo MAPA. Spec: `docs/repro/cambios agosto/PERMISOS_EMPRESA_CLIENTE.md` | `EmpresaPermisosSupport.php` |
| **Campos internos PDF empresa** | Confirmar que salud/judicial/notas no filtran al PDF — tests en `InformePreempleoVisibilidadTest` | `CuestionarioPresentacionEmpresa.php` |
| **Word vs cliente** | ✅ Sin botón Word en portal empresa; ruta bloqueada para `role_as < 2` | `OrdenesController::informeWord()` |

### Deploy y cierre proyecto

1. Deploy iPage: Fase F + A + E7 (plantillas `resources/templates/`, seeders no en prod)
2. Swap textos legales definitivos (`config/autorizaciones_legales.php`)
3. Plantillas Word VSA/Socio dedicadas (si el cliente las entrega)
4. Campo opcional `registro_apa` en User para párrafo APA

---

**Última actualización:** 27 de agosto de 2026 (noche)  
**Estado:** 🟡 **Sprint N lote A en prod** · N-F0/N-F1 esperan Stephany · no migrar Hetzner
