# Auditoría completa — Formularios vs aplicación REPRO

**Fecha:** 31 de julio de 2026  
**Alcance:** (1) Comparación formularios originales ↔ app por tipo de servicio; (2) Alineación transversal de módulos, roles, reportes e informes.  
**Estado:** 🟡 **P0 cerrado (31 jul 2026)** — periódica/específica §5 y admin PDF unificado. Pendiente: spec jun-2026, partials legacy, P2.

---

## Metodología

| Fuente | Ruta | Uso |
|--------|------|-----|
| PDF pre-empleo real | `docs/ejemplos de formularios reales/POLIGRAFO PRESENCIAL (2).pdf` | Literales y orden §3–§5 |
| PDF socio real | `docs/ejemplos de formularios reales/SOCIOECONOMICO QUE LLENAN LOS CANDIDATOS.pdf` | §6 referencias, bienes, presupuesto |
| PDF periódica real | `docs/ejemplos de formularios reales/PERIODICO ESPECIFICO.pdf` | §3 labor (31), §5 judicial |
| Spec jun-2026 | `docs/repro/CREACIÓN FORMULARIOS DE SISTEMA.pdf` | Reglas modernas, redacción refinada |
| Plan alineación | `docs/business/PLAN_ALINEACION_FORMULARIOS_REALES_2026-07-29.md` | Checklist etapas A–J (**PENDIENTE**) |
| QA cerrado | `QA_MANUAL_FASE_G_2026-07-28.md`, `QA_MANUAL_DASHBOARD_CUESTIONARIOS_2026-07-30.md` | Flujos estructurales OK |
| Peticiones cliente | `docs/Observaciones cliente/`, `docs/REQUERIMIENTOS_CLIENTE_2026-05.md`, QA G jul-28 | I-5, G5, refs, Word, etc. |
| Código fuente | `app/Support/*`, `CuestionarioController`, `CuestionarioPresentacionDashboard`, vistas | Implementación actual |
| Tests literales | `tests/Unit/FormularioLiteralesClienteTest.php` | App **bloqueada al PDF ago-2025**, no al spec jun-2026 |

**Conflicto de autoridad:** El plan jul-29 dice usar spec jun-2026 salvo confirmación del cliente. La app y sus tests están alineados al **PDF real ago-2025**. El plan etapas A–J siguen **sin marcar `[x]`**.

---

## Resumen ejecutivo

| Área | Veredicto | Comentario |
|------|-----------|------------|
| **Pre-empleo (candidato)** | 🟢 Alineado al PDF | 19 integridad, 16 judicial, 8 complementaria, tablas dinámicas |
| **Socioeconómico (candidato)** | 🟢 Alineado al PDF | 6 secciones, refs min 3, bienes/presupuesto |
| **Periódica (candidato)** | 🟢 Alineado al PDF | §5 solo judicial vía `antecedentes-recientes` + `AntecedentesRecientesRequest` |
| **Específica (candidato)** | 🟢 Alineado al PDF | Misma §5 judicial; pregunta 1 y académico reducido OK |
| **Admin show/edit** | 🟢 OK | `CuestionarioPresentacionDashboard` con branch §5 por tipo |
| **Admin PDF** | 🟢 Unificado | `pdf-seccion-contenido-admin` + Dashboard (sin mapa legacy) |
| **Empresa show** | 🟢 OK pre/socio/periódica | Filtro interno; §5 sin complementaria periódica/específica |
| **Empresa PDF** | 🟢 OK §5 por tipo | Sin bloque complementaria en periódica/específica |
| **Informe Word / tablas REPRO** | 🟢 OK | Pre/socio; bloques evaluador; no aplica periódica labor |
| **Reportes** | 🟢 N/A | Solo metadatos (`cuestionario_completado`), no campos de formulario |
| **Plan alineación A–J** | 🔴 Pendiente | Refinamientos spec jun-2026 no implementados |

**Defecto #1 (crítico):** ~~Existe `antecedentes-recientes.blade.php` + `AntecedentesRecientesRequest` pero no cableados~~ **Corregido 31-jul-2026** — controller, Dashboard, tests y PDF admin/empresa alineados.

---

## PARTE 1 — Comparación por tipo de servicio

### Leyenda severidad

- 🔴 **Crítico** — El candidato ve/guarda distinto al formulario original o hay fuga de datos internos.
- 🟠 **Alto** — Desalineación literal/estructural relevante para entrega al cliente.
- 🟡 **Medio** — Diferencia menor, legacy en módulos secundarios, o spec vs PDF sin decidir.
- 🟢 **OK** — Coincide con PDF real o petición cerrada.

---

### 1. Pre-empleo (`preempleo` — polígrafo / VSA)

| Sección | PDF real | App | Estado |
|---------|----------|-----|--------|
| §1 Datos personales | Foto, DPI, deptos, IGSS, NIT, licencia | `DatosPersonalesCampos` + `datos-personales.blade.php` | 🟢 |
| §2 Familiar | Hijos, hermanos, pareja, exparejas | Tablas dinámicas + `InformacionFamiliar*` | 🟢 |
| §3 Laboral | Empleos 5 cols PDF; 19 preguntas complementarias | `HistorialLaboralIntegridad` (orden PDF) | 🟢 vs PDF; 🟡 vs spec jun-2026 |
| §4 Económica | 11 preguntas + tabla deudas | `SituacionEconomicaCampos` | 🟢 |
| §5 Salud/hábitos/judicial/complementaria | 4 bloques | `antecedentes.blade.php` + Support classes | 🟢 vs PDF |
| Firma / términos | Autorización legal | `AutorizacionesLegales`, Infornet pre-empleo | 🟢 (QA G5) |

**Inconsistencias vs spec jun-2026 (plan etapa A–E, no implementado):**

| Tema | PDF / app hoy | Spec jun-2026 | Sev. |
|------|---------------|---------------|------|
| Orden 19 integridad | Problema serio #1, corporación #2… | Corporación #1, efectivo #2… | 🟡 |
| Redacción integridad | Coloquial PDF («robar», «policiacos») | Vocabulario REPRO refinado + «Explique» | 🟡 |
| Judicial | 16 preguntas | 22 preguntas | 🟠 |
| Salud | Campos actuales | + `salud_situacion_emocional`, `salud_ideacion_dano` en UI | 🟡 |
| Complementaria | 8 campos sin «Explique» | Sufijos «Explique» en varios | 🟡 |
| Pareja | PDF menciona calidad/número relación | Campo «número relación» no explícito | 🟡 |
| Datos históricos | — | Reorden `integridad_*` puede desalinear respuestas viejas | 🟠 |

**Tests:** `FormularioLiteralesClienteTest` fija literales al PDF — cualquier cambio al spec requerirá actualizar tests + migración.

---

### 2. Socioeconómico (`socioeconomico`)

| Sección | PDF real | App | Estado |
|---------|----------|-----|--------|
| §1–§5 | Igual base pre-empleo | Misma base | 🟢 |
| §6 Complementaria | Refs (min 3 fam/pers, min 1 vec), bienes, presupuesto, vivienda | `SocioeconomicoComplementariaCampos` + vista §6 | 🟢 |
| Ref. vecinales / vivienda empresa | Ocultas del informe externo | `CamposInternosPreempleo` filtra `viv_*`, `referencias_vecinales` | 🟢 |
| Min referencias | PDF UI «mínimo 3» | Validación `min:3` | 🟢 (spec decía min 2; PDF gana) |

**Inconsistencias menores:** vivienda duplicada §4 vs §6 (por diseño). Columnas extra empleos en spec no aplicadas a pre-empleo PDF.

---

### 3. Periódica (`periodica`)

| Sección | PDF / spec | App | Estado |
|---------|------------|-----|--------|
| §1 | Sin IGSS/NIT obligatorios | Condicional en vista | 🟢 |
| §2 | Sin hermanos | Tabla hermanos omitida | 🟢 |
| §3 | 31 preguntas labor + empleo actual + formación | `HistorialLaboralPeriodico` (31+info adicional) | 🟢 |
| §4 | Económica estándar | `SituacionEconomicaCampos` | 🟢 |
| §5 | **Solo aspecto judicial** (+ info adicional final) | `antecedentes-recientes.blade.php` + `AntecedentesRecientesRequest` | 🟢 |
| Documentos | Solo DPI | Implementado | 🟢 |
| Validación §5 | Solo judicial | `AntecedentesRecientesRequest` | 🟢 |
| Vista preparada | `antecedentes-recientes.blade.php` | Conectada en `CuestionarioController` | 🟢 |

**Evidencia código:**

```991:991:app/Http/Controllers/CuestionarioController.php
                5 => 'cuestionario.secciones.antecedentes',
```

```1049:1049:app/Http/Controllers/CuestionarioController.php
                5 => AntecedentesRequest::class,
```

**Test que consolida el bug:** `CuestionarioPeriodicaTest::test_seccion_5_periodica_muestra_antecedentes_no_generica` exige ver «Aspectos de salud» y «Aspecto judicial» — contradice PDF/spec de solo judicial.

---

### 4. Específica (`especifica`)

| Sección | Spec / PDF | App | Estado |
|---------|------------|-----|--------|
| Base | = Periódica | Mismo routing que periódica | 🟢 |
| §3 pregunta 1 | Texto amplio, min caracteres | `labelPregunta1(true)` + validación | 🟢 |
| §3 académico | Solo último grado | Sin tabla formación en específica | 🟢 |
| §5 | Solo judicial | `antecedentes-recientes` + Dashboard branch | 🟢 |
| Labor → Word auto | Spec: no auto | Verificar `InformeWordNarrativas` | 🟡 (no auditado a fondo) |

---

## PARTE 2 — Alineación por módulo y rol

### Matriz módulo × fuente de verdad

| Módulo | Ruta / clase | Usa Support + Presentación | Problemas | Sev. |
|--------|--------------|----------------------------|-----------|------|
| **Candidato token** | `CuestionarioController` | Sí (vistas secciones) | §5 periódica/específica incorrecta | 🔴 |
| **Admin show** | `admin/cuestionarios/show` → `seccion-contenido` | `CuestionarioPresentacionDashboard` | §5 igual para todos los tipos | 🔴 |
| **Admin edit** | `edit` → `seccion-edicion` | Dashboard | Idem §5; tablas solo lectura (by design) | 🔴 / ℹ️ |
| **Admin PDF** | `admin/cuestionarios/pdf.blade.php` | **Legacy** | Labels fantasma; sin filtro interno unificado | 🔴 |
| **Empresa show** | `empresa/cuestionarios/show` | `CuestionarioPresentacionEmpresa` | §5 empresa muestra complementaria siempre | 🟡 |
| **Empresa PDF** | `pdf/cuestionario_empresa` → `pdf-secciones-empresa` | Parcial + lógica duplicada | §5 complementaria todos tipos; no usa bloques shared | 🟡 |
| **Informe pre-empleo tablas** | `InformePreempleo`, partials informe | Support + overrides REPRO | OK pre/socio | 🟢 |
| **Informe Word** | `InformeWord*` | Bloques evaluador 6 slugs | QA G punto 6 OK | 🟢 |
| **Notas evaluador** | `EvaluadorNotasSupport` | Slugs por sección | OK | 🟢 |
| **Reportes evaluaciones** | `ReportesController` | Metadatos orden/evaluado | No exporta campos formulario | 🟢 N/A |
| **Historial DPI** | `historial-dpi` | Historial evaluados | No replay paridad campos | 🟡 |
| **Gestión cuestionarios listado** | `CuestionariosController::index` | Filtros incl. sede | QA H-8 OK | 🟢 |
| **Ordenes / evaluados admin** | `OrdenesController` | Matriz servicio | `MatrizFormularioServicio` coherente | 🟢 |
| **Precarga cuestionario** | `CuestionarioPrecarga` | Mapeo campos | Depende de keys actuales | 🟡 |
| **Legacy partials** | `admin/.../partials/seccion_*`, `editar_seccion_*` | **Huérfanos** | No referenciados por show/edit actual | 🟡 |
| **CamposInternosPreempleo** | Filtro empresa | Claves integridad/judicial/salud | Correcto; inútil si periódica guarda salud en §5 | 🟡 |

### Filtrado empresa (I-5 / campos internos)

`CamposInternosPreempleo::filtrarRespuestasParaEmpresa()` oculta integridad, judicial, salud, hábitos, sustancias. **Complementaria (`comp_*`) sí es visible** — correcto para pre-empleo.

Para periódica/específica, si el candidato completa §5 como pre-empleo, se almacenan campos internos de salud que **no deberían existir** en ese tipo; la empresa no los ve por filtro, pero REPRO show/edit los muestra como si fueran periódica estándar.

---

## PARTE 3 — Peticiones del cliente vs implementación

| Petición | Fuente | Estado | Notas |
|----------|--------|--------|-------|
| Formularios = PDFs reales 4 tipos | Plan jul-29, QA G | **Parcial** | Pre + socio OK; periódica §5 NO |
| 5 secciones + socio §6 | Spec | ✅ | |
| Tablas dinámicas | Spec | ✅ | `TablaDinamica.php` |
| Campos internos solo REPRO | Spec, I-5 | ✅ show empresa; 🟡 PDF admin |
| Informe Word editable + 6 bloques | Cotización, QA G | ✅ | |
| Autorizaciones G5 + Infornet | QA G | ✅ | |
| Foto candidato | Cliente jun-22 | ✅ | |
| Periódica §5 solo judicial | PDF + plan H.1.3 | ❌ | Vista existe, no cableada |
| Específica pregunta 1 + académico reducido | Spec | ✅ | |
| Referencias socio min 3 | PDF | ✅ | |
| Empresa no ve internos | I-5 | ✅ show; 🟡 PDF |
| Admin PDF = show | Dashboard QA pendiente | ❌ | |
| Eliminar legacy §5 (refs, antecedentes_penales) | Plan D | Parcial | Candidato OK; admin PDF no |
| Literales spec jun-2026 (integridad orden, judicial 22) | Plan A–B | ❌ | App = PDF ago-2025 |
| Migración datos tras reorden keys | Plan, QA hallazgo #5 | ❌ | |
| Visibilidad resultados por evaluado | I-5 | ✅ | |
| Filtro sede gestión cuestionarios | H-8 | ✅ | |
| Dos servicios misma orden (1B) | Cotización | ❌ | Fuera alcance formularios |
| Reutilización datos entre servicios | Cotización | ❌ | |
| WhatsApp API | Cliente | ❌ | Pospuesto |

---

## PARTE 4 — Conflicto documentación interna

| Documento | Dice | Realidad código |
|-----------|------|-----------------|
| `PLAN_ALINEACION` etapa H | «Periódica §5 ya usa `antecedentes-recientes`» | **Falso** — sigue `antecedentes` |
| `PLAN_ALINEACION` estado | PENDIENTE A–J | Correcto |
| `QA_MANUAL_FASE_G` | CERRADO | Flujos OK; no cubre §5 periódica vs PDF |
| `QA_MANUAL_DASHBOARD` | CERRADO | Show/edit unificado OK; §5 sin branch tipo |
| `FormularioLiteralesClienteTest` | PDF ago-2025 | Bloquea cambios al spec sin decisión |

**Decisión requerida del cliente:** ¿Autoridad final = **PDF impreso ago-2025** o **spec jun-2026 refinado**? Hoy la app implementa lo primero; el plan pendiente pide lo segundo.

---

## PARTE 5 — Prioridades recomendadas

### P0 — Crítico ~~(corregir antes de entrega)~~ ✅ Cerrado 31-jul-2026

1. ~~**Conectar periódica/específica §5 a judicial-only**~~ ✅
2. ~~**Admin PDF unificado**~~ ✅ (`shared/cuestionario/pdf-seccion-contenido-admin.blade.php`)

### P1 — Alto

3. **Decisión PDF vs spec** + ejecutar plan A–B si eligen spec (integridad orden, judicial 22, salud expandida).

4. **Migración datos** si se reordenan keys `integridad_*` / `periodico_*`.

5. **Eliminar partials huérfanos** (`seccion_*.blade.php`, `editar_seccion_*.blade.php` en admin).

### P2 — Medio

6. Unificar empresa PDF con `seccion-contenido` (reducir duplicación `pdf-secciones-empresa`).

7. Campos pareja «número de relación» si el PDF lo exige literalmente.

8. UI campos salud spec (`salud_situacion_emocional`, `salud_ideacion_dano`).

9. Actualizar `PLAN_ALINEACION` estado o marcar etapas completadas honestamente.

### P3 — Bajo

10. Títulos de sección cosméticos vs PDF.  
11. Typos PDF bloqueados en tests (`policiacos`, etc.) — solo corregir con OK cliente.

---

## PARTE 6 — Comandos de verificación

```bash
# Literales bloqueados al PDF
docker exec repro-app php artisan test --filter=FormularioLiteralesClienteTest

# Periódica / específica
docker exec repro-app php artisan test --filter='CuestionarioPeriodicaTest|CuestionarioEspecificaTest'

# Recorrido completo demo
docker exec repro-app php artisan test --filter=RecorridoCompletoFormulariosDemoTest

# Dashboard + empresa + informe
docker exec repro-app php artisan test --filter='InformePreempleoVisibilidadTest|EmpresaModulosTest|AdminCuestionarioFotoEditTest'
```

---

## Índice de archivos clave

| Rol | Archivo |
|-----|---------|
| Slugs secciones | `app/Support/CuestionarioSecciones.php` |
| Integridad 19 | `app/Support/HistorialLaboralIntegridad.php` |
| Periódico 31 | `app/Support/HistorialLaboralPeriodico.php` |
| Judicial 16 | `app/Support/AntecedentesJudiciales.php` |
| Complementaria 8 | `app/Support/InformacionComplementaria.php` |
| Salud/hábitos | `app/Support/SaludHabitosCampos.php` |
| Socio §6 | `app/Support/SocioeconomicoComplementariaCampos.php` |
| Filtro empresa | `app/Support/CamposInternosPreempleo.php` |
| Presentación admin | `app/Support/CuestionarioPresentacionDashboard.php` |
| §5 completo (pre) | `resources/views/cuestionario/secciones/antecedentes.blade.php` |
| §5 judicial (sin usar) | `resources/views/cuestionario/secciones/antecedentes-recientes.blade.php` |
| Admin PDF legacy | `resources/views/admin/cuestionarios/pdf.blade.php` |
| Empresa PDF | `resources/views/shared/cuestionario/pdf-secciones-empresa.blade.php` |

---

**Conclusión:** Pre-empleo y socioeconómico están bien alineados al PDF real. El **mayor gap funcional** es periódica/específica §5 (código judicial-only ya escrito pero desconectado). El **mayor gap documental** es el plan jul-29 (spec jun-2026) pendiente mientras QA marca flujos como cerrados. Admin PDF y partials legacy son la principal deriva transversal restante.
