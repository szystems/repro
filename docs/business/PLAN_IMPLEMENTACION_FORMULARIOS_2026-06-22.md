# Plan de implementación — Formularios REPRO (cierre del proyecto)

**Fecha:** 22 de junio de 2026 · **Seguimiento:** 27 ago 2026 — Sprint N lote A en prod; M A–F en prod; L cerrado (ver `PROGRESS.md`)
**Objetivo:** completar el motor de formularios y los 4 formularios (Pre-empleo matriz, Socioeconómico, Periódica, Específica) según la especificación del cliente, de forma **ordenada, por etapas y controlada** para minimizar errores.
**Spec base:** `docs/business/ANALISIS_FORMULARIOS_E_INFORME_2026-06-22.md` + `CREACIÓN FORMULARIOS DE SISTEMA.pdf` (46 pág.) + formularios originales ago-2025.
**Naturaleza:** cierre del proyecto (saldo Q 10,000), sin cobro aparte. Word es track paralelo (Q 1,600).

---

## 0. Reglas de trabajo (para avanzar controlado)

1. **Una etapa a la vez.** No se inicia la siguiente hasta cerrar la actual (código + pruebas + commit).
2. **Cada punto tiene checklist.** Se marca `[x]` solo cuando está probado.
3. **Tests primero donde aplique** (modelo/lógica). No romper los **685** tests existentes.
4. **No tocar producción** hasta cerrar una etapa completa y validar en local/staging.
5. **Compatibilidad de datos:** preservar lo ya capturado; migraciones aditivas (no destructivas).
6. **Commits pequeños y descriptivos** por punto, no por etapa completa.
7. **Responsive + guardado automático** se prueban en cada sección (la mayoría llena desde celular).
8. **Contexto siempre al día:** al marcar un punto `[x]` o avanzar de etapa, actualizar en la misma sesión `PROGRESS.md` (sección 🔴), este plan y `docs/status/CONTEXTO_AGENTES.md` (estado E1 + siguiente paso + fecha).

---

## Decisiones técnicas — ✅ RESUELTAS (22-jun, Otto)

- [x] **Almacenamiento de tablas dinámicas:** **columna `valor_json` (JSON, nullable) aditiva** en `cuestionario_respuestas`. Los campos simples siguen en `valor` (text) sin cambios → cero conflicto con datos/tests existentes. Las filas repetibles (hijos, empleos, deudas, etc.) se guardan como array en `valor_json` bajo un `campo` que identifica la tabla. Se evita alterar el `enum tipo_campo` (riesgoso en MySQL); se agrega el flag vía `metadata` o un tipo lógico. *(Criterio Otto: "lo más correcto, evitar errores y conflictos".)*
- [x] **Campos internos del evaluador:** nueva tabla **`evaluador_notas`** (`evaluado_orden_id`, `seccion`, `campo`, `contenido`, `user_id`, timestamps), separada de las respuestas del candidato. **Solo rol REPRO/ADMIN** la edita (empresa NO).
- [x] **Catálogo Deptos/Municipios GT:** tablas **`departamentos`** + **`municipios`** con seed oficial (22 deptos + sus municipios). Selects dependientes precargados (JSON en cliente, sin latencia) + opción **"Otro (extranjero)"** → texto libre. → *Implementación: punto **1.4**.*
- [x] **Foto:** **OBLIGATORIA** — el candidato debe **tomar foto (cámara) o subir archivo**. Almacenamiento en `storage`, con vista previa. *(Criterio Otto.)*

---

## ETAPA 1 — Motor base (infraestructura reutilizable)

> Sin esto, las secciones no se pueden construir bien. Es la base de todo.

- [x] **1.1** Componente Blade reutilizable de **tabla dinámica** (agregar/eliminar filas, ilimitado, móvil) con guardado en JSON. → *`<x-tabla-dinamica>`, `TablaDinamica`, `tabla-dinamica.js`, piloto tabla **hijos** en sección 2.*
- [x] **1.2** Mecanismo de **campos condicionales** (mostrar/ocultar según respuesta) reutilizable (data-attributes + JS). → *`<x-campo-condicional>`, `public/js/campos-condicionales.js`, piloto refactor `informacion-familiar.blade.php` (hijos, pareja, hipoteca/alquiler).*
- [x] **1.3** **Guardado automático/parcial** (autosave por sección, sin perder datos al cerrar navegador). → *`CuestionarioAutosave`, ruta `autosave-seccion`, JS `cuestionario-autosave.js` (debounce 2.5s + sendBeacon al cerrar); borrador usa validación permisiva.*
- [x] **1.4** **Catálogo Departamentos/Municipios** GT (seed + selects dependientes + opción "Otro (extranjero)" → texto libre). → *Tablas `departamentos`/`municipios`, seeder 22+340, componente `<x-depto-municipio-select>`, integrado en `datos-personales.blade.php`.*
- [x] **1.5** **Captura de foto** del candidato (cámara/subida) + almacenamiento + vista previa. → *`CuestionarioFotoCandidato`, `<x-foto-candidato>`, ruta `cuestionario.foto-candidato`, integrado en sección 1.*
- [x] **1.6** **Pantalla inicial de instrucciones** obligatoria (texto fijo + botón "He leído las instrucciones y deseo continuar"). → *Flujo: verificar DPI → `instrucciones` → términos → secciones. Config `cuestionario_instrucciones.php`, campos `instrucciones_leidas_at`/`ip_instrucciones`.*
- [x] **1.7** **Precarga desde la orden** (nombre, DPI, empresa, etc.) editable por el candidato + **trazabilidad de cambios**. → *`CuestionarioPrecarga`, snapshot `datos_precarga_json`, metadata `precarga` en respuestas, panel admin.*
- [x] **1.8** Modelo de **notas internas del evaluador** (`evaluador_notas`) + permisos (solo REPRO). → *`EvaluadorNotasSupport`, `CuestionarioSecciones::bloquesNotasEvaluador()`, partial accordion en admin show/edit; gate REPRO/ADMIN; PDF y portal empresa no exponen notas.*
- [x] **1.9** Migraciones aditivas + tests del motor (autosave, tablas dinámicas, condicionales). → *`CuestionarioMotorE1Test` (schema E1 + flujo integrado secciones 1–2), `CuestionarioSeccionesTest`; suite 704 tests.*

**Cierre E1:** ✅ motor probado con sección piloto; tests verdes.

---

## ETAPA 2 — Formulario Pre-empleo (matriz, 5 secciones)

> Base para Polígrafo Pre-empleo y VSA Pre-empleo.

### Sección 1 — Información Personal y Familiar
- [x] **2.1** Datos generales (21 campos): tipo ID, nacionalidad, edad autocalculada, deptos/municipios nacimiento y residencia, estado civil, IGSS, NIT, licencia. **Quitar:** género, profesión, nivel educativo. → *`DatosPersonalesCampos`, `datos-personales.blade.php`, `DatosPersonalesRequest`; tests `CuestionarioPreempleoDatosGeneralesTest`.*
- [x] **2.2** Padres (condicional "¿vive?"). → *`InformacionFamiliarPadres`, partial `datos-progenitor`, convive_con multi-select; tests `CuestionarioPreempleoPadresTest`.*
- [x] **2.3** Pareja actual (condicional por tipo de relación). → *`InformacionFamiliarPareja`, partial `datos-pareja-actual`, gate `vive_con_pareja`; tests `CuestionarioPreempleoParejaTest`.*
- [x] **2.4** Tabla dinámica **Hijos**. → *Piloto E1.1 (`TablaDinamica`, `<x-tabla-dinamica>`); gate `tiene_hijos`; admin/PDF con `tabla-dinamica-resumen`; tests `CuestionarioPreempleoHijosTest` + `CuestionarioTablaDinamicaTest`.*
- [x] **2.5** Tabla dinámica **Hermanos**. → *gate `tiene_hermanos`; admin/PDF; tests `CuestionarioPreempleoSeccion2ExtendidaTest`.*
- [x] **2.6** Subsección **Exparejas** (condicional: matrimonio/unión libre o hijos en común). → *`InformacionFamiliarExparejas`, partial `datos-expareja`.*
- [x] **2.7** Generación de **tablas resumen familiar** (para informe, editables por evaluador). → *`ResumenFamiliar::compilar()` on-the-fly; admin sección 2.*

### Sección 2 — Académica y Laboral
- [x] **2.8** Académica: "último nivel" → **tabla autogenerada** según reglas (estado, carrera, institución, año, ¿respaldo?). → *`HistorialAcademico`, tabla `formacion_academica`.*
- [x] **2.9** Laboral: "¿experiencia previa?" → **tabla dinámica de empleos** + observaciones libre. → *gate `experiencia_previa`, tabla `empleos`.*
- [x] **2.10** **19 preguntas complementarias** de integridad (texto largo) — marcadas como **internas** (no van auto al informe). → *`HistorialLaboralIntegridad`, `CamposInternosPreempleo`.*

### Sección 3 — Económica
- [x] **2.11** "¿Deudas?" → **tabla dinámica de deudas** + observaciones. → *gate `tiene_deudas`, tabla `deudas`.*
- [x] **2.12** Situación económica general (~21 campos, condicionales) — internas. → *`SituacionEconomicaCampos`.*

### Sección 4 — Salud y Hábitos
- [x] **2.13** Estado de salud (18 campos, condicionales). → *`SaludHabitosCampos`, sección 5 `antecedentes.blade.php`.*
- [x] **2.14** **Tablas dinámicas** tatuajes y perforaciones (condicionales). → *tablas `tatuajes`, `perforaciones`.*
- [x] **2.15** Hábitos personales. → *campos `habito_*`.*
- [x] **2.16** Sustancias: checklist ("Ninguna" excluyente) + preguntas complementarias — internas. → *`sustancias_usadas` almacenado como CSV.*

### Sección 5 — Aspectos Complementarios
- [x] **2.17** Aspecto judicial (~12 preguntas texto libre obligatorias) — internas. → *`AntecedentesJudiciales`.*
- [x] **2.18** Información complementaria (7 preguntas) → **sí va al informe** como tabla. → *`InformacionComplementaria`.*
- [x] **2.19** **Documentos adjuntos** (lista sugerida, máx 10 MB, solo DPI obligatorio). → *pantalla `finalizar.blade.php` (existente).*
- [x] **2.20** Mensaje "Información Importante" (versión Pre-empleo, con papelería pendiente). → *alert en `antecedentes.blade.php`.*
- [x] **2.21** Cuadro final "Si desea agregar alguna información…". → *campo `informacion_adicional_final`.*

**Cierre E2:** flujo Pre-empleo completo probado de inicio a fin (Polígrafo y VSA); tests; **QA manual OK (2-jul-2026)**; commit pendiente deploy.

---

## ETAPA 3 — Campos internos del evaluador + generación de tablas al informe

- [x] **3.1** UI de **espacios internos del evaluador** por sección (solo REPRO), separados de respuestas candidato. → *`EvaluadorNotasSupport`, accordion admin; tablas informe editables en show/edit.*
- [x] **3.2** Mapeo: qué respuestas del candidato se convierten en **tablas del informe** (familia, académico, laboral, deudas, complementaria) — editables por evaluador. → *`InformePreempleo`, partial `tablas-informe-preempleo`, overrides en `evaluador_notas`.*
- [x] **3.3** Reglas de qué NO va al informe (preguntas internas: integridad laboral, económica detallada, salud, drogas, judicial). → *`CamposInternosPreempleo`; filtro en portal empresa y PDF.*
- [x] **3.4** Tests de visibilidad/permiso (empresa NO ve ni edita internas). → *`InformePreempleoTest`, `InformePreempleoVisibilidadTest`.*

**Extra E3 (8-jul):** portal empresa con vista estilizada (`seccion-lectura`) y PDF agrupado (`pdf-secciones-empresa`).

**Cierre E3:** evaluador puede redactar análisis y armar el informe desde las respuestas; commit pendiente deploy.

---

## ETAPA 4 — Formulario Socioeconómico

- [x] **4.1** Reutiliza las 5 secciones del matriz.
- [x] **4.2** Sección exclusiva: **Referencias** familiares (mín. 2), personales (mín. 2), vecinales (mín. 1), laborales (autocompletar del historial si es posible).
- [x] **4.3** **Bienes y pertenencias** (tabla + total autocalculado).
- [x] **4.4** **Presupuesto personal** (tabla de gastos + total autocalculado).
- [x] **4.5** **Información de vivienda**.
- [x] **4.6** Reglas de qué tablas van/no van al informe (familiares/personales sí; vecinales/vivienda no).
- [x] **4.7** Documentos adjuntos socio (+ constancias laborales, recibo de luz).

**Cierre E4:** ✅ flujo Socioeconómico completo; commit `2b175bce`.

---

## ETAPA 5 — Formularios Periódica y Específica

### Periódica
- [x] **5.1** Base Pre-empleo; **omitir** IGSS, NIT (datos), Hermanos (familiar), Aspectos Complementarios.
- [x] **5.2** **Sección laboral propia** (tabla simplificada + **26 preguntas** específicas) — internas.
- [x] **5.3** Foto; documentos solo DPI; mensaje sin "papelería pendiente".

### Específica
- [x] **5.4** Base Periódica; académica = solo último grado.
- [x] **5.5** Pregunta 1 laboral = **espacio amplio del caso/hecho** (obligatorio).
- [x] **5.6** Documentos solo DPI.

**Cierre E5:** ✅ Periódica + Específica operativos (5 secciones c/u); selección automática por servicio queda en E6.

---

## ETAPA 6 — Integración con orden, mensajes y ajustes finales

- [x] **6.1** Selección automática del formulario según **servicio + tipo** en la orden (matriz §2.2 del análisis). → `MatrizFormularioServicio` + UI create/edit + validación `OrdenFormRequest`.
- [x] **6.2** Mensajes "Información Importante" por tipo (Pre-empleo con papelería; Periódica/Específica sin). → `MensajesInformacionImportante` + partial en antecedentes / socio / completado.
- [x] **6.3** **Ajuste temporal Socioeconómico:** permitir a REPRO marcar "Formulario Completado" manual **solo** para servicio Socioeconómico (workaround Jotform). → salto en máquina de estados + etiqueta en show orden.
- [x] **6.4** Papelería post-envío — mismo enlace mientras `token_expira_at` vigente (~30 días).
- [x] **6.5** Regresión suite PHPUnit — **786 tests OK** (18-jul). Smoke móvil pantalla completado OK.

**QA manual (18-jul):** matriz servicio→formulario, mensajes Información Importante, Jotform solo socio, hermanos socio/periódica, validación foto — todo OK en navegador.

**Cierre E6:** sistema completo y estable en local/staging → preparar deploy.

---

## ETAPA 7 — Informe Word (.docx) — track paralelo (Q 1,600)

> Puede avanzar en paralelo. Versión "rica" depende de E2/E3.

- [x] **7.2** Generación .docx nativo por candidato. → `InformeWordExport` + ruta `ordenes.informe-word`
- [x] **7.3** Datos base del informe (resultado, preliminar, notas). → versión base
- [x] **7.4** Pruebas. → `InformeWordExportTest`
- [x] **7.1** Plantillas oficiales REPRO (jul-2026). → `informe-poligrafo-preempleo.docx` + `informe-poligrafo-periodica.docx`
- [x] **7.5** Relleno tabular F7 fase A–C. → `InformeWordRelleno` + `InformeWordFoto` + `InformeWordAnexos` + `InformeWordXml` (encabezado, tablas BD, foto, anexos, layout, Content_Types JPG) — **cerrado 20-jul-2026**
- [x] **7.6** Narrativas manuales REPRO + variantes VSA/Socio. → `InformeWordNarrativas` + relleno en plantilla (salud, hábitos, judicial, poligráfica, recomendaciones, APA) — **cerrado 20-jul-2026**

---

## Track legal (Fase A) — paralelo, dentro de saldo Q 10,000

- [x] **A.1** 7 autorizaciones legales por servicio/formulario. → `AutorizacionesLegales` + `config/autorizaciones_legales.php`
- [x] **A.2** Infornet (Pre-empleo, misma firma). → paso `/infornet`
- [x] **A.3** Campo motivo/hecho en evaluado (REPRO). → `motivo_hecho_evaluacion`
- [x] **A.4** Corrección Específica. → cubierto por E5
- [x] **A.5** Autorizaciones firmadas en PDF cuestionario. → snapshot HTML + Infornet
- [ ] **A.6** Swap textos oficiales del cliente en `config/autorizaciones_legales.php`. → **pendiente entrega REPRO**
  - **Pedir al entregar versión de pruebas** (directo al cliente: 7 autorizaciones + Infornet definitivos).
  - **Repetir en informe final** del proyecto para no olvidar antes del cierre.

---

## Orden recomendado de ejecución

```
Decisiones técnicas → E1 (motor) → E2 (Pre-empleo) → E3 (evaluador/informe)
                                  → E4 (Socio) → E5 (Periódica/Específica) → E6 (integración)
Paralelo: Track legal (A) + E7 (Word base ahora, rico tras E3)
```

---

## Estado global

| Etapa | Descripción | Estado |
|-------|-------------|--------|
| Decisiones técnicas | Almacenamiento, catálogos, foto | ✅ Resueltas (22-jun) |
| E1 | Motor base | ✅ Cerrado (1.1–1.9) · 740 tests OK |
| E2 | Pre-empleo (5 secciones) | ✅ Completado (2.1–2.21) · QA manual OK 2-jul |
| E3 | Campos evaluador + informe | ✅ Cerrado (3.1–3.4) · portal empresa estilizado 8-jul |
| E4 | Socioeconómico | ✅ Cerrado (4.1–4.7) · commit `2b175bce` |
| E5 | Periódica + Específica | ✅ Cerrado (5.1–5.6) · 18-jul-2026 |
| E6 | Integración + ajustes | ✅ Cerrado (6.1–6.5 · deploy pendiente) |
| E7 | Word (.docx) | ✅ **7.1–7.6** completo (20-jul) |
| A | Track legal | ✅ **Cerrado** (A.1–A.5) · **A.6 swap textos** pendiente — pedir archivos al cliente en entrega pruebas + informe final |

---

*Plan vivo — se actualiza marcando cada punto al cerrarlo. Szystems · 22-jun-2026 · contexto sincronizado 20-jul-2026 (F7 tabular ✅ · 808 tests · deploy pendiente)*
