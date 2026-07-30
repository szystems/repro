# Plan de alineación — Formularios reales del cliente

**Fecha:** 29 de julio de 2026  
**Objetivo:** Dejar el sistema **literal y estructuralmente equivalente** a los formularios que REPRO usa hoy con sus candidatos, para evitar reclamos en la entrega final.  
**Estado:** 🔴 **PENDIENTE DE IMPLEMENTACIÓN** — usar este documento como checklist único hasta cerrar.

---

## 0. Fuentes de verdad (orden de prioridad)

| Prioridad | Archivo | Qué define |
|-----------|---------|------------|
| **1** | `docs/ejemplos de formularios reales/POLIGRAFO PRESENCIAL (2).pdf` | Pre-empleo polígrafo — **preguntas literales que ven los candidatos** |
| **1** | `docs/ejemplos de formularios reales/SOCIOECONOMICO QUE LLENAN LOS CANDIDATOS.pdf` | Socio pre-empleo + referencias |
| **2** | `docs/repro/CREACIÓN FORMULARIOS DE SISTEMA.pdf` | Spec jun-2026 (tablas, validaciones, reglas modernas) |
| **3** | `docs/ejemplos de formularios reales/periodica poligrafo (1).docx` | Solo **informe evaluador** periódica (NO formulario web) |
| **3** | `docs/ejemplos de formularios reales/poligrafo preempleo (1).docx` | Solo **informe evaluador** pre-empleo |
| **3** | `docs/ejemplos de formularios reales/PERIODICO ESPECIFICO.docx` | Informe específico (narrativa evaluador) |

**No usar como formulario web:** Word/PDF de informes (`MOISES ESAU COYOY…`, `Cristina Abigail…` llenos, `SOCIOECONOMICOS QUE SE ENTREGA A LA EMPRESA.docx`).

**Regla de redacción:** Si hay diferencia menor entre PDF real ago-2025 y spec jun-2026, usar **spec jun-2026** salvo que el cliente haya confirmado lo contrario. Siempre conservar el **sentido y vocabulario REPRO** (efectivo, faltante, actas administrativas, Q.200, etc.).

---

## 1. Reglas de implementación (no negociables)

- [ ] **Una sola fuente de literales:** cada pregunta vive en una clase `app/Support/*` con `PREGUNTAS` o constantes; las vistas solo las renderizan.
- [ ] **Test por bloque:** cada archivo Support con preguntas tiene test que compara **cada `label`** contra constantes extraídas del plan (como `CuestionarioPeriodicaTest::test_preguntas_periodicas_coinciden_con_pdf`).
- [ ] **Migración de datos:** al cambiar `key` de campos, mapear respuestas existentes en BD (migración o alias en lectura); no perder datos de evaluados ya capturados.
- [ ] **Campos internos vs informe:** respetar `CamposInternosPreempleo` — integridad, judicial, salud/hábitos = internos; complementaria = va al informe.
- [ ] **Sin campos fantasma:** eliminar todo lo que no exista en formulario real (referencias personales en pre-empleo sección 5, antecedentes sí/no legacy, etc.).
- [ ] **Commit por sub-etapa** (A→H abajo); suite completa verde antes de marcar `[x]`.
- [ ] **QA manual** con tokens demo re-ejecutar al cerrar (checklist en §8).
- [ ] **Actualizar** `PROGRESS.md`, este plan y `CONTEXTO_AGENTES.md` al cerrar cada sub-etapa.

---

## 2. Mapa: formulario real → sistema

| Formulario real del cliente | `tipo_formulario` en sistema | Secciones |
|----------------------------|------------------------------|-----------|
| POLIGRAFO PRESENCIAL | `preempleo` (servicio polígrafo/vsa) | 5 + firma |
| SOCIOECONOMICO candidatos | `socioeconomico` | 6 + firma |
| Periódica (spec; sin PDF candidato en carpeta) | `periodica` | 5 + firma |
| Específica (spec) | `especifica` | 5 + firma |

---

## 3. Etapas de trabajo

### ETAPA A — Preguntas laborales complementarias (19) 🔴 CRÍTICO

**Archivos:** `app/Support/HistorialLaboralIntegridad.php`, `historial-laboral.blade.php`, `HistorialLaboralRequest.php`, `CompletaFlujoCuestionario.php`, `DemoWordCuestionarioBuilder.php`, tests.

**Problema:** Hoy hay 19 preguntas **genéricas incorrectas** («¿Ha mentido en currículum?»). El cliente usa las del PDF real.

**Reemplazar `HistorialLaboralIntegridad::PREGUNTAS` por estas 19 literales** (formulario real + spec; orden spec jun-2026):

| # | key sugerida | Texto literal (fuente: POLIGRAFO PRESENCIAL + PDF spec) |
|---|--------------|------------------------------------------------------|
| 1 | `integridad_01` | ¿Ha trabajado en alguna corporación policial o militar? ¿Cuál? |
| 2 | `integridad_02` | ¿Cuál fue la cantidad máxima de efectivo que manejó en sus empleos? |
| 3 | `integridad_03` | ¿Cuál fue la cantidad máxima de producto, inventario o mercadería que tuvo bajo su responsabilidad en sus empleos? |
| 4 | `integridad_04` | ¿Cuál fue el faltante más grande que tuvo y cómo lo resolvió? |
| 5 | `integridad_05` | ¿Cuál fue el sobrante más grande que tuvo y cómo lo resolvió? |
| 6 | `integridad_06` | ¿Cuál fue el problema más serio que tuvo en sus empleos y cómo lo resolvió? |
| 7 | `integridad_07` | ¿Cuántas veces alteró documentos, registros o facturas en sus empleos para no meterse en problemas? Explique |
| 8 | `integridad_08` | Cuando solicitemos referencias laborales, ¿considera que algún empleador o compañero podría brindar una referencia negativa sobre usted? ¿Por qué motivo? |
| 9 | `integridad_09` | ¿Cuál ha sido la cantidad máxima de efectivo, producto, material promocional o recurso de la empresa que conservó para uso personal? |
| 10 | `integridad_10` | ¿Cuál fue el soborno, beneficio o favor más grande que aceptó en sus empleos? |
| 11 | `integridad_11` | ¿En qué empleo fue acusado de deshonestidad y cuál fue la situación? |
| 12 | `integridad_12` | ¿Ha tomado alguna vez dinero, producto o recursos de una empresa sin autorización? |
| 13 | `integridad_13` | Si tuviera que reponer dinero, producto o recursos tomados sin autorización, ¿a cuánto ascendería aproximadamente el monto? |
| 14 | `integridad_14` | ¿Cuántas actas administrativas, llamados de atención o sanciones recibió en sus empleos y cuál fue el motivo? |
| 15 | `integridad_15` | ¿Algún compañero le enseñó o sugirió cómo obtener beneficios no autorizados o sustraer producto o efectivo en sus empleos? Explique. |
| 16 | `integridad_16` | ¿Cuántas veces omitió reportar una conducta incorrecta de un compañero por pena, amistad o para evitar problemas? Explique. |
| 17 | `integridad_17` | ¿Ha abandonado algún empleo sin previo aviso? ¿Cuál fue? |
| 18 | `integridad_18` | ¿Alguna vez utilizó, prestó o tomó dinero de una empresa sin autorización con la intención de devolverlo posteriormente? |
| 19 | `integridad_19` | ¿Existe algún empleo que no haya registrado en este formulario? ¿Cuál? |

**Título del bloque en vista:** «Preguntas complementarias laborales» (no «de integridad» genérico), badge Confidencial.

**Campo observaciones laborales** (antes del bloque): texto literal del real:
> Si desea agregar alguna información laboral, lo puede hacer en este espacio: (lagunas de tiempo, ampliación del motivo de retiro, complementos, entre otros…)

**Pregunta experiencia previa** — ampliar label a:
> ¿Posee experiencia laboral previa, incluyendo empleos formales, informales, temporales, independientes, prácticas, pasantías o apoyo en negocios familiares?

**Checklist A:**
- [ ] A.1 Reemplazar constantes + validación
- [ ] A.2 Migración/alias keys si cambian nombres
- [ ] A.3 Actualizar título bloque + observaciones laborales
- [ ] A.4 Test `FormularioLiteralesTest` bloque integridad (19 asserts)
- [ ] A.5 Actualizar seeder demo Word + `CompletaFlujoCuestionario`
- [ ] A.6 Verificar admin evaluador muestra nuevas labels

---

### ETAPA B — Aspecto judicial (~16–22) 🔴 CRÍTICO

**Archivos:** `app/Support/AntecedentesJudiciales.php`, `antecedentes.blade.php`, `antecedentes-recientes.blade.php`, `AntecedentesRequest.php`, `AntecedentesRecientesRequest.php`, `InformeWordNarrativas.php`, tests.

**Problema:** 12 preguntas genéricas. Real + spec tienen bloque extenso de texto libre.

**Lista mínima del formulario real (16)** — implementar **todas**; PDF spec añade variantes → incluir las 22 del spec jun-2026:

| # | Texto (real / spec) |
|---|---------------------|
| 1 | ¿Cuándo fue la última vez que tramitó sus antecedentes penales y policiales? |
| 2 | ¿Tiene actualmente algún antecedente penal o policial? Explique. |
| 3 | ¿Alguna vez realizó gestiones para eliminar, cancelar o limpiar un antecedente penal o policial? Explique. |
| 4 | ¿Cuándo fue la última vez que fue detenido(a), arrestado(a) o permaneció en una cárcel, delegación policial o centro de detención? Explique. |
| 5 | ¿Ha presentado alguna demanda, denuncia o proceso legal contra una persona o empresa? Explique. |
| 6 | ¿Ha sido demandado, denunciado o sujeto de algún proceso legal? Explique. |
| 7 | ¿Alguna vez ha tenido la necesidad de ocultar su identidad o utilizar información distinta a la propia? Explique. |
| 8 | ¿Ha portado armas de fuego u otras armas? ¿Por qué motivo? Explique. |
| 9 | ¿Ha tomado alguna vez un objeto, dinero o bien ajeno sin autorización por un valor superior a Q.200.00? Explique. |
| 10 | ¿Ha tomado alguna vez un objeto, dinero o bien ajeno sin autorización por un valor igual o menor a Q.200.00? Explique. |
| 11 | ¿Ha tenido necesidad de falsificar, alterar o utilizar documentos falsos? Explique. |
| 12 | ¿Algún familiar ha estado involucrado en extorsiones, delitos o actividades ilícitas? Explique. |
| 13 | ¿Algún amigo o familiar se encuentra privado de libertad? Explique. |
| 14 | ¿Cuándo fue la última vez que visitó a una persona privada de libertad? Explique. |
| 15 | ¿Alguna vez se ha visto involucrado, aunque haya sido involuntariamente, en una actividad ilícita? Explique. |
| 16 | ¿Considera que su lugar de residencia presenta problemas de delincuencia, pandillas, extorsiones o actividades ilícitas? Explique. |

**Reglas spec:** todos texto libre obligatorio; **sin** sí/no condicional previo.

**Periódica / específica sección 5:** solo judicial (sin complementaria) — ya usa `antecedentes-recientes.blade.php`; debe heredar las mismas preguntas.

**Checklist B:**
- [ ] B.1 Reemplazar `AntecedentesJudiciales::PREGUNTAS` (16–22)
- [ ] B.2 Migración keys `judicial_01`… si cambian
- [ ] B.3 Test literales judicial
- [ ] B.4 Periódica/específica: mismas preguntas, sin complementaria
- [ ] B.5 Admin + Word narrativas actualizados

---

### ETAPA C — Información complementaria (informe) 🟠 ALTO

**Archivos:** `app/Support/InformacionComplementaria.php`, `antecedentes.blade.php`, `InformePreempleo.php`, PDF empresa, admin sección 5.

**Problema:** 7 preguntas con redacción distinta; falta condiciones laborales; sobra disponibilidad horaria.

**Reemplazar por (formulario real «Aspectos varios» + spec):**

| # | key | Texto literal |
|---|-----|---------------|
| 0 | `comp_licencia_conducir` | Tipo de licencia de conducir / vigencia |
| 1 | `comp_sindicato` | ¿En qué empleos perteneció a un sindicato? Explique. |
| 2 | `comp_familiar_empresa` | ¿Tiene algún familiar o amigo laborando en la empresa contratante? Explique. |
| 3 | `comp_como_se_entero` | ¿Cómo se enteró de esta oportunidad laboral? |
| 4 | `comp_condiciones_laborales` | ¿Está de acuerdo con las condiciones laborales que le ofrece la empresa? Explique. |
| 5 | `comp_metas` | ¿Cuáles son sus metas personales y laborales a corto, mediano y largo plazo? |
| 6 | `comp_cualidades_defectos` | Mencione sus principales cualidades y aspectos que considera debe mejorar. |
| 7 | `comp_redes_usuario` | Indique los nombres de usuario o perfiles que utiliza en redes sociales actualmente. |

**Eliminar:** `comp_disponibilidad`, `comp_cualidades` y `comp_redes_sociales` viejos (migrar datos si existen).

**Periódica / específica:** omitir bloque complementaria (spec: «Aspectos complementarios se omite»).

**Checklist C:**
- [ ] C.1 Nuevas keys + labels
- [ ] C.2 Migración respuestas antiguas → nuevas keys
- [ ] C.3 Quitar `comp_disponibilidad`
- [ ] C.4 Test literales complementaria (8 campos)
- [ ] C.5 Informe Word tabla complementaria
- [ ] C.6 Confirmar omitido en periodica/especifica

---

### ETAPA D — Eliminar legacy sección 5 pre-empleo 🔴 CRÍTICO

**Archivos:** `antecedentes.blade.php`, `AntecedentesRequest.php`, `CuestionarioValidacionLabels.php`, tests `CuestionarioModuloCompletoTest`.

**Quitar del formulario y validación** (no existen en formulario real):

- [ ] D.1 `referencia1_nombre`, `referencia1_telefono`, `referencia1_relacion`
- [ ] D.2 `referencia2_*`
- [ ] D.3 `antecedentes_penales`, `detalle_antecedentes`
- [ ] D.4 `despedido_trabajo`, `motivo_despido`
- [ ] D.5 `consume_alcohol`, `consume_drogas`
- [ ] D.6 `problemas_salud_mental`, `detalle_salud_mental`
- [ ] D.7 `observaciones_adicionales` (si duplica otros campos)
- [ ] D.8 Scripts JS legacy en `antecedentes.blade.php` (toggle antecedentes/despido/salud mental)
- [ ] D.9 Reorganizar vista: **Salud → Hábitos → Drogas → Judicial → Complementaria → Información importante**
- [ ] D.10 Auditar vistas huérfanas `seccion_5.blade.php` / `seccion_3.blade.php` — eliminar o marcar deprecated si no se usan

---

### ETAPA E — Salud, hábitos y sustancias (expansión) 🟠 ALTO

**Archivos:** `SaludHabitosCampos.php`, `antecedentes.blade.php`, `CamposInternosPreempleo.php`, `InformeWordNarrativas.php`.

**Problema:** Formulario real tiene ~30+ campos; sistema tiene versión resumida con labels abreviados.

#### E.1 Salud — labels literales (formulario real)

| Campo | Label literal |
|-------|---------------|
| `salud_preocupaciones` | ¿Cuál es el problema personal o situación que actualmente le genera mayor preocupación? |
| `salud_estado_general` | ¿Cómo considera su estado general de salud actual? — opciones: Excelente, Buena, Regular, Mala |
| `salud_atencion_psicologica` | ¿Ha recibido atención psicológica o psiquiátrica? |
| `salud_detalle_psicologica` | Amplíe la información sobre la atención psicológica o psiquiátrica recibida |
| `salud_situacion_emocional` | ¿Ha atravesado alguna situación emocional o personal que haya afectado significativamente su bienestar o sus actividades diarias? |
| `salud_detalle_emocional` | Amplíe la información sobre la situación emocional o personal *(nuevo si falta)* |
| `salud_ideacion_dano` | ¿Ha llegado a pensar en hacerse daño o en no continuar con su vida debido a alguna situación personal o emocional? |
| `salud_detalle_ideacion` | Amplíe la información |
| `salud_tipo_sangre` | Tipo de sangre — catálogo A+, A-, B+, B-, AB+, AB-, O+, O-, No lo conoce |
| `salud_peso` | Peso (libras) — **decisión:** usar libras como real o mantener kg con label «Peso (libras)» |
| `salud_estatura` | Estatura (metros) |
| `salud_practica_deporte` | ¿Practica algún deporte o actividad física? |
| `salud_detalle_deporte` | ¿Cuál deporte o actividad física practica? |
| `salud_tratamiento_medico` | ¿Actualmente recibe algún tratamiento médico? |
| `salud_detalle_tratamiento` | Describa el tratamiento médico actual |
| `salud_hospitalizaciones` | ¿Ha sido hospitalizado o sometido a alguna cirugía? |
| `salud_detalle_hospitalizaciones` | Describa la situación |
| `salud_ausencias_enfermedad` | ¿Cuántas veces faltó a sus actividades laborales o académicas por enfermedad durante el último año? Explique |
| `salud_intento_suicidio` | ¿Ha intentado suicidarse alguna vez? ¿Por qué motivo? *(nuevo)* |
| `tiene_tatuajes` | ¿Posee tatuajes? |
| `tiene_perforaciones` | ¿Posee aretes, perforaciones o piercings? |

#### E.2 Hábitos — campos a agregar/ampliar

| Campo | Label literal |
|-------|---------------|
| `habito_tiempo_libre` | ¿Qué hace en sus tiempos libres? |
| `habito_bares_frecuencia` | ¿A cada cuánto visita bares o discotecas? *(nuevo)* |
| `habito_alcohol_ultimo` | ¿Cuándo fue la última vez que consumió bebidas alcohólicas? ¿Qué y cuánto consumió? |
| `habito_alcohol_mensual` | ¿Cuántas veces consume bebidas alcohólicas al mes? *(nuevo)* |
| `habito_alcohol_detenido` | ¿Cuándo fue la última vez que estuvo detenido por consumir bebidas alcohólicas? *(nuevo)* |
| `habito_alcohol_laboral` | ¿En el último año, cuántas veces se presentó a laborar en estado de ebriedad o resaca? |
| `habito_alcohol_despido` | ¿En qué empleo fue despedido por excederse en el consumo de alcohol? *(nuevo)* |
| `habito_tabaco` | ¿Con qué frecuencia fuma? |
| `habito_juegos_azar` | ¿Qué juegos de azar practica? ¿Con qué frecuencia? |

#### E.3 Sustancias — checklist real

Texto introductorio del real (incluir en vista):
> Las estadísticas muestran que cerca del 90% de las personas han experimentado o tenido contacto con algún tipo de droga ilegal…

**Opciones checkbox** (reemplazar lista actual):
Marihuana, Cocaína, Heroína, LSC, Metanfetaminas, Popper, Hongos, Cristal, Opio, Otras, Ninguna

**Preguntas de seguimiento** (nuevas, texto libre obligatorio si marca sustancia ≠ ninguna):
- ¿Cómo ha sido su experiencia?
- ¿Cuándo fue la última vez que experimentó?
- ¿En los últimos 6 meses cuántas veces consumió?
- ¿Tiene algún amigo o familiar que las consuma?
- ¿Cuándo fue la última vez que consumieron frente a usted?
- ¿Cuándo fue la última vez que guardó, transportó o vendió alguna droga ilegal?
- ¿Alguna de ellas le ayuda a mejorar su salud o estado de ánimo? ¿Cuál?

**Checklist E:**
- [ ] E.1 Actualizar labels salud existentes
- [ ] E.2 Agregar campos nuevos + validación condicional
- [ ] E.3 Actualizar `SUSTANCIAS` + preguntas seguimiento
- [ ] E.4 Vista `antecedentes.blade.php` bloques completos
- [ ] E.5 Actualizar `CamposInternosPreempleo` claves nuevas
- [ ] E.6 Tests salud/hábitos/sustancias ampliados
- [ ] E.7 Decisión peso libras documentada en commit

---

### ETAPA F — Secciones 1–2–4 (afinación literal) 🟡 MEDIO

#### F.1 Datos personales (sección 1)

**Ya alineado en su mayoría.** Verificar:

- [ ] F.1.1 Instrucciones iniciales alinear tono con real (voluntario, N/A, ortografía, espacio final) — `config/cuestionario_instrucciones.php`
- [ ] F.1.2 Periódica/específica: **omitir** IGSS y NIT en vista (spec) — confirmar condicional en `datos-personales.blade.php`
- [ ] F.1.3 Tipo identificación: «Documento de identidad extranjero» vs «Documento extranjero» — unificar al spec
- [ ] F.1.4 Foto: texto «Tomar fotografía de medio cuerpo…» como en real

#### F.2 Información familiar (sección 2)

- [ ] F.2.1 Labels pareja: «¿Cómo describe la relación?» / «Número de la relación (1ra, 2da, 3ra)» — comparar `InformacionFamiliarPareja.php` / partials
- [ ] F.2.2 Exparejas: reglas condicionales según spec (casado/unión/hijos con expareja)
- [ ] F.2.3 Periódica/específica: omitir hermanos — **ya OK**, re-verificar
- [ ] F.2.4 Socio: incluir hermanos — **ya OK**

#### F.3 Tabla empleos (sección 3)

Columnas real: Empresa, Puesto Ocupado, Fechas Laboradas, Salario mensual, Motivo de retiro.

- [ ] F.3.1 Verificar labels tabla (`TablaDinamica::columnasEmpleos`) — «Puesto ocupado», «Salario mensual (Q.)», «Fechas laboradas» (rango o ingreso/salida)
- [ ] F.3.2 Campos extra (jefe, RRHH, constancia): spec los incluye — **mantener** si están en PDF spec jun-2026

#### F.4 Situación económica (sección 4)

Preguntas del real (verificar labels en `situacion-economica.blade.php` / `SituacionEconomicaCampos`):

- [ ] F.4.1 ¿Es fiador de alguien?
- [ ] F.4.2 ¿Tiene problemas o ha tenido problemas bancarios?
- [ ] F.4.3 ¿Dónde vive actualmente es propio o alquila? ¿Cuánto paga de alquiler?
- [ ] F.4.4 ¿Cuántas personas dependen económicamente de usted? ¿Quiénes?
- [ ] F.4.5 ¿Tiene algún ingreso adicional?
- [ ] F.4.6 ¿Tiene alguna propiedad a su nombre?
- [ ] F.4.7 ¿Tiene o tuvo alguna demanda por alguna deuda?
- [ ] F.4.8 ¿Cuál es su pretensión salarial?
- [ ] F.4.9 ¿A cuánto ascienden sus gastos mensuales?
- [ ] F.4.10 ¿Tiene algún omiso en SAT?
- [ ] F.4.11 Tabla deudas: columnas Entidad, Monto, Saldo, Cuota, Motivo, Antigüedad, Estatus, Tiempo de atraso

**Socio:** incluir bloque vivienda económica en sección 4 **y** sección 6 según spec — verificar no duplicar preguntas conflictivas.

---

### ETAPA G — Socioeconómico sección 6 🟡 MEDIO

**Referencia:** `SOCIOECONOMICO QUE LLENAN LOS CANDIDATOS.pdf`

- [ ] G.1 Instrucción referencias familiares literal: «Registre un mínimo de tres referencias familiares… No registre padres, pareja, hijos o hermanos»
- [ ] G.2 Instrucción referencias personales literal
- [ ] G.3 Referencias vecinales + laborales
- [ ] G.4 **Decisión validación:** spec dice mínimo 2 obligatorio, UI dice mínimo 3 — alinear (recomendado: validar min 3 fam/pers como dice UI del cliente real; confirmar con Stephany)
- [ ] G.5 Columnas tablas: Nombre, Teléfono, Parentesco, Lugar de trabajo, Dirección (fam); «Por qué motivo lo conoció» (pers)
- [ ] G.6 Pregunta extra real: «¿Ha laborado anteriormente para la empresa donde está aplicando?» — agregar si falta
- [ ] G.7 Documentos: constancia laboral + recibo de luz — **ya OK**

---

### ETAPA H — Periódica y específica 🟢 BAJO (mayormente OK)

#### H.1 Periódica

- [ ] H.1.1 26 preguntas laborales — **ya alineadas** (`HistorialLaboralPeriodico.php`)
- [ ] H.1.2 Tabla empleo actual 4 columnas — **OK**
- [ ] H.1.3 Sección 5: solo judicial (sin salud/complementaria) — verificar `antecedentes-recientes` no mezcla salud
- [ ] H.1.4 Documentos: solo DPI — **OK**
- [ ] H.1.5 Mensajes finales sin papelería pendiente — `MensajesInformacionImportante.php`

#### H.2 Específica

- [ ] H.2.1 Pregunta 1 ampliada — **OK**
- [ ] H.2.2 Académico: solo último grado — verificar vista `situacion-laboral-periodica` modo específica
- [ ] H.2.3 Resto = periódica — **OK**
- [ ] H.2.4 Respuestas laborales no van auto al informe Word — verificar `InformeWordNarrativas`

---

### ETAPA I — Textos transversales 🟡 MEDIO

- [ ] I.1 `MensajesInformacionImportante.php` — viñetas oficiales del PDF spec (Confidencialidad, Proceso, Papelería pendiente solo pre-empleo, Contacto, Resultados)
- [ ] I.2 `documentos-candidato.blade.php` — mensaje amarillo pre-empleo literal del spec
- [ ] I.3 Autorizaciones legales G5 — **ya cerrado**; no reabrir salvo plantillas definitivas cliente
- [ ] I.4 Títulos de sección en `Cuestionario::getSeccionesConfig()` — evaluar si cliente prefiere nombres del real vs actuales (cosmético; baja prioridad)

---

### ETAPA J — Tests y QA de entrega 🔴 OBLIGATORIO

#### J.1 Tests automatizados nuevos

Crear `tests/Unit/FormularioLiteralesClienteTest.php`:

- [ ] J.1.1 Assert cada label `HistorialLaboralIntegridad` (19)
- [ ] J.1.2 Assert cada label `AntecedentesJudiciales` (16–22)
- [ ] J.1.3 Assert cada label `InformacionComplementaria` (8)
- [ ] J.1.4 Assert muestras clave salud/hábitos (≥10 labels)
- [ ] J.1.5 Assert `HistorialLaboralPeriodico` (26) — ya parcial
- [ ] J.1.6 Test negativo: sección 5 pre-empleo **no** contiene `referencia1_nombre`
- [ ] J.1.7 Suite completa verde

#### J.2 QA manual pre-entrega cliente

Reutilizar tokens de `docs/business/QA_MANUAL_FASE_G_2026-07-28.md` + verificar:

- [ ] J.2.1 Pre-empleo: leer en pantalla las 19 laborales — coinciden con tabla Etapa A
- [ ] J.2.2 Pre-empleo: leer judicial + complementaria — coinciden
- [ ] J.2.3 Pre-empleo: **no** aparecen referencias personales #1/#2
- [ ] J.2.4 Socio: referencias + instrucciones literales
- [ ] J.2.5 Periódica: 26 preguntas + sin complementaria
- [ ] J.2.6 Específica: pregunta 1 amplia + académico reducido
- [ ] J.2.7 Captura pantalla PDF o checklist firmado internamente antes de enviar a Stephany

---

## 4. Archivos tocados (índice)

| Área | Archivos principales |
|------|---------------------|
| Integridad 19 | `HistorialLaboralIntegridad.php`, `historial-laboral.blade.php`, `HistorialLaboralRequest.php` |
| Judicial | `AntecedentesJudiciales.php`, `antecedentes.blade.php`, `antecedentes-recientes.blade.php` |
| Complementaria | `InformacionComplementaria.php`, `InformePreempleo.php`, `InformeWordNarrativas.php` |
| Legacy cleanup | `AntecedentesRequest.php`, `antecedentes.blade.php` |
| Salud/hábitos | `SaludHabitosCampos.php`, `CamposInternosPreempleo.php` |
| Socio S6 | `SocioeconomicoComplementariaCampos.php`, `socioeconomico-complementaria.blade.php`, `TablaDinamica.php` |
| Textos | `MensajesInformacionImportante.php`, `cuestionario_instrucciones.php`, `documentos-candidato.blade.php` |
| Tests | `FormularioLiteralesClienteTest.php`, `CompletaFlujoCuestionario.php`, `CuestionarioPreempleoSecciones345Test.php` |
| Demo/seed | `DemoWordCuestionarioBuilder.php` |
| Admin | `admin/cuestionarios/partials/seccion_5.blade.php` |

---

## 5. Decisiones pendientes (resolver antes o durante implementación)

| # | Tema | Opciones | Recomendación |
|---|------|----------|---------------|
| 1 | Peso | libras (real) vs kg (sistema) | **Libras** con label explícito; migrar valores existentes si hay datos |
| 2 | Referencias socio min | validar 2 (spec) vs UI 3 (real) | **Validar 3** fam + 3 pers como formulario real; texto UI ya dice mínimo 3 |
| 3 | Orden 19 laborales | real vs spec | **Orden spec jun-2026**; mismos textos |
| 4 | Judicial count | 16 real vs 22 spec | **22 del spec** (incluye todas las del real) |
| 5 | Vistas legacy | `seccion_3/5.blade.php` | Eliminar si confirmado no referenciadas |

---

## 6. Orden de ejecución recomendado

```
A (19 laborales) → B (judicial) → D (limpiar legacy) → C (complementaria)
→ E (salud/hábitos) → F (secc 1-2-4) → G (socio) → H (periodica/especifica)
→ I (textos) → J (tests + QA)
```

**Estimación:** A+B+D = entrega mínima viable para no recibir reclamo de «preguntas incorrectas». E+F = paridad completa con formulario real. G–J = cierre sin sorpresas.

---

## 7. Criterio de «listo para cliente»

Marcar **CERRADO** solo cuando:

1. Todos los `[ ]` de etapas A–J aplicables estén `[x]`
2. Suite PHPUnit 100% verde
3. QA manual J.2 completado
4. Ningún campo legacy de Etapa D visible en pre-empleo
5. Stephany recibe mensaje: «Formulario alineado a POLIGRAFO PRESENCIAL y SOCIOECONOMICO ago-2025 + spec jun-2026»

---

## 8. Historial

| Fecha | Acción |
|-------|--------|
| 2026-07-29 | Plan creado tras auditoría vs formularios reales en `docs/ejemplos de formularios reales/` |
