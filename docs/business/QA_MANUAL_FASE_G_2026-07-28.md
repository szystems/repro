# QA manual — Fase G (formularios alineados a la especificación)

**Fecha de apertura:** 2026-07-28  
**Estado:** 🟢 **CERRADO** (29-jul)  
**Última sesión:** 2026-07-29 — B.4, C (#17), D/E smoke, A login, F filtros, H I-5

> Marcar `[x]` al pasar. Anotar hallazgos al final.  
> Runner local reutilizable: `docker exec repro-app php storage/app/qa_fase_g_runner.php`

---

## Resumen de avance

| Bloque | Estado | Notas |
|--------|--------|-------|
| **A** Login colaborador/empresa | ✅ | admin@repro.com + ilemke@example.org → dashboard |
| **B — Pre-empleo candidato** | ✅ | #37, #36, #14 (hermanos) |
| **C — Socioeconómico candidato** | ✅ | #17 Jarrett — 6 secciones + completado |
| **D — Periódica candidato** | ✅ | Demo #6 — pantalla completado + tests PHPUnit |
| **E — Específica candidato** | ✅ | Demo #7 — pantalla completado + tests PHPUnit |
| **F — Admin cuestionario** | ✅ | Filtro `asignacion_sede` + 6 bloques Word (unit test) |
| **G — Respuestas cliente jul-28** | ✅ | G5, I-5, H-8, refs texto 3, bloques Word — commit `106b4fe0` |
| **H — Portal empresa I-5** | ✅ | Informe evaluado listo con orden `en_proceso` (orden demo #5) |

---

## A — Login

- [x] Admin `admin@repro.com` / `admin1234` → dashboard (curl + navegador)
- [x] Empresa `ilemke@example.org` / `password` → dashboard (orden demo Word)

---

## B — Pre-empleo (candidato)

### B.1 Flujo base (sin experiencia laboral previa) — ✅ 28-jul
- [x] Evaluado #37 Bradford — secciones 1–5 + finalizar
- [x] Header por tipo (no dice «Socioeconómico» en pre-empleo)
- [x] Pantalla completado con títulos PDF

### B.2 Rama con experiencia laboral previa — ✅ 29-jul
- [x] Evaluado #36 Abbigail — experiencia previa = Sí, tabla empleos, 19 preguntas integridad
- [x] Flujo completo hasta completado

### B.3 Identificación extranjera — ✅ 29-jul
- [x] Pasaporte #36 (13 dígitos), sección 1 completa, flujo hasta completado
- [x] Pantalla verificar-identidad adaptada a pasaporte/cédula/DPI (textos dinámicos)

### B.4 Tabla hermanos (familiar) — ✅ 29-jul
- [x] Evaluado #14 Carlos Demo — 2 filas hermanos guardadas (Ana + Luis)
- [x] UI sección 2: tabla visible, botón «Agregar hermano», «Quitar hermano» habilitado con 2 filas
- [x] JS `tabla-dinamica.js` confirma eliminación (SweetAlert / confirm)

### B.5 Documentos adjuntos >10 MB — ✅ 29-jul
- [x] PDF 11 MB rechazado en subida (#36)
- [x] Mensaje claro: «El archivo no debe exceder 10 MB.»

### Autorizaciones en términos — ✅ 28-jul
- [x] Textos oficiales + Infornet pre-empleo; hallazgo #3 `:empresa:` corregido

---

## C — Socioeconómico (candidato)

- [x] Evaluado #17 Jarrett Barton — servicio `socioeconomico`, cuestionario sincronizado a 6 secciones
- [x] Secciones 1–5 (pre-empleo base) + sección 6 complementaria (refs, bienes, presupuesto, vivienda)
- [x] Finalizar → completado (`cuestionario_completado = true`)
- [x] Tests `CuestionarioSocioeconomicoTest` — 43 tests relacionados OK en suite QA

---

## D — Periódica (candidato)

- [x] Token demo `worddemo2026poligrafoperiod0` — pantalla completado muestra «Cuestionario Periódico»
- [x] Tests `CuestionarioPeriodicaTest` — 5 secciones, sin hermanos en sec. 2, vista laboral propia

---

## E — Específica (candidato)

- [x] Token demo `worddemo2026poligrafoespecif0` — pantalla completado muestra «Cuestionario Específico»
- [x] Tests `CuestionarioEspecificaTest` — 5 secciones, antecedentes relevantes sec. 5

---

## F — Admin cuestionarios

- [x] **H-8** Filtro `asignacion_sede=sin_sede` en `/cuestionarios` — 37 evaluados sin sede (local)
- [x] Filtro `con_sede` operativo (0 en BD local actual)
- [x] **Punto 6** — `InformeWordBloquesEvaluadorTest`: 6 slugs obligatorios antes de informe final

---

## G — Respuestas cliente (jul-28)

- [x] G5 autorizaciones oficiales + Infornet solo pre-empleo
- [x] I-5 visibilidad por evaluado (`resultadosDisponiblesParaEmpresa`)
- [x] H-8 filtro sede en gestión cuestionarios
- [x] Referencias socio: UI «mínimo 3», validación 2
- [x] Bloqueo informe Word sin 6 bloques evaluador

---

## H — Portal empresa I-5

- [x] Orden demo #5 (`en_proceso`, `resultados_visibles_empresa=true`)
- [x] Evaluado #5 Roberto — informe preliminar visible en `/empresa/ordenes/5` sin esperar orden entregada
- [x] Test `ResultadosVisibilidadTest::test_empresa_ve_informe_de_evaluado_listo_aunque_orden_no_este_entregada` — OK

---

## Hallazgos

| # | Fecha | Tipo | Descripción | Estado |
|---|-------|------|-------------|--------|
| 1 | 28-jul | UI | Header «Cuestionario Socioeconómico» en todos los tipos | ✅ Corregido |
| 2 | 28-jul | UI | Completado listaba nombres viejos de sección | ✅ Corregido |
| 3 | 28-jul | G5 | «Empresa [nombre candidato]» en autorización | ✅ Corregido |
| 4 | 29-jul | UX | Verificar-identidad decía «DPI» aunque acepta pasaporte | ✅ Corregido |
| 5 | 29-jul | UX | Rechazo >10 MB decía «10240 kilobytes» | ✅ Corregido |

---

## Tokens útiles (local)

| Evaluado | Token | DPI / notas |
|----------|-------|---------------|
| #37 Bradford (completado) | `efhJ2lw0Bw2f3uypWCaNqCacmiSZFpyHZKYvOU6eiGfuDu1vDpMojL5M2bsupNiO` | `3543166297864` |
| #36 Abbigail (completado) | `tbMtcszmu3tnc3YM3CaEqEwaePJXRvt2Woky9oU1FxZUvRnHlIgu5pYddWp5mcEW` | pasaporte `5320259437049` |
| #14 Carlos Demo (sec. 3, hermanos OK) | `aq0BoPHPkpnG6pM3Lhkm7NtX3vSqEItBZ1u0VTQs6vR10haeVIuHzdmLR2Gd86py` | `1324654688787` |
| #17 Jarrett (socio completado) | `GpWv3aJ1J6TcljI7w81jRYXOGArdT1iVu6siNFudvRPH5EnA0z942BvrJdftKEFW` | `4789323146781` |
| Periódica demo #6 | `worddemo2026poligrafoperiod0` | `2405617300602` |
| Específica demo #7 | `worddemo2026poligrafoespecif0` | `2405617300603` |
| Socio demo #9 | `worddemo2026socioeconomico0` | `2405617300605` |
| Admin | `admin@repro.com` / `admin1234` | |
| Empresa demo Word | `ilemke@example.org` / `password` | orden #5 |

---

## Automatizado (29-jul)

```bash
docker exec repro-app php vendor/bin/phpunit \
  tests/Feature/ResultadosVisibilidadTest.php \
  tests/Unit/InformeWordBloquesEvaluadorTest.php \
  tests/Feature/CuestionarioPeriodicaTest.php \
  tests/Feature/CuestionarioEspecificaTest.php \
  tests/Feature/CuestionarioSocioeconomicoTest.php \
  tests/Feature/CuestionarioTablaDinamicaTest.php
# → 43 tests OK
```
