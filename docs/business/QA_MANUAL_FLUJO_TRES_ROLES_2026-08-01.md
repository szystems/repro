# QA manual — Flujo tres roles (evaluado / REPRO / cliente)

**Fecha:** 2026-08-01  
**Estado:** 🟢 **CERRADO** (runner HTTP + navegador + PHPUnit)  
**Alcance:** Formularios evaluado (E1–E5 + demo Word), dashboard REPRO, portal empresa, reportes  
**Base URL local:** `http://localhost:8000`

> Marcar `[x]` al pasar. Complementa `QA_MANUAL_DASHBOARD_CUESTIONARIOS_2026-07-30.md`.

---

## Resumen ejecutivo

| Rol | Método | Resultado |
|-----|--------|-----------|
| **Evaluado** (token) | Runner HTTP + navegador E5 §5 | ✅ 100% (22 checks) |
| **REPRO** (admin) | Runner HTTP + navegador | ✅ 100% (28 checks) |
| **Cliente** (empresa) | Runner HTTP | ✅ 100% (13 checks) |
| **Automatizado** | `RecorridoCompletoFormulariosDemoTest` | ✅ 4/4 demos |

**Runner reproducible:**

```bash
docker exec repro-app php scripts/qa_flujo_tres_roles.php
# Esperado: === RESUMEN: 63 PASS / 0 FAIL / 63 total ===
```

---

## Credenciales demo

| Rol | Usuario / acceso | Contraseña |
|-----|------------------|------------|
| REPRO | `admin@repro.com` | `admin1234` |
| Cliente empresa | `ilemke@example.org` | `password` |
| Evaluado | Token único + DPI | Sin login |

### Tokens evaluado (manual E1–E5)

| Demo | Token | DPI |
|------|-------|-----|
| E1 pre-empleo | `e1demo2026pruebamanualtokenrepr0` | `2405617300105` |
| E4 socioeconómico | `e4demo2026pruebamanualtokenrepr0` | `2405617300205` |
| E5 periódica | `e5demo2026periodicatokenrepr0` | `2405617300305` |
| E5 específica | `e5demo2026especificatokenrepr0` | `2405617300405` |

### Tokens demo Word (orden #5, cuestionarios #33–#37)

| Evaluado | Token | DPI |
|----------|-------|-----|
| Pre-empleo | `worddemo2026poligrafopreempl0` | `2405617300601` |
| Periódica | `worddemo2026poligrafoperiod0` | `2405617300602` |
| Específica | `worddemo2026poligrafoespecif0` | `2405617300603` |
| VSA pre | `worddemo2026vsapreempleo000` | `2405617300604` |
| Socio | `worddemo2026socioeconomico0` | `2405617300605` |

---

## A — Evaluado (formulario candidato)

### A.1 Gate inicial (todos los tokens activos)

- [x] Landing `/cuestionario/{token}` → verificación DPI (HTTP 200/302)
- [x] POST verificar DPI correcto → avanza flujo
- [x] Instrucciones / términos accesibles (acepta redirect a `terminos` o `seccion/` si ya avanzó)
- [x] Gate sección 1: acceso o redirect coherente

### A.2 E5 periódica — §5 solo judicial (crítico PDF ago-2025)

- [x] Tras verificar DPI, sección 5 muestra título **«Antecedentes recientes»**
- [x] Bloque **«Aspecto judicial»** presente (16 preguntas)
- [x] **Sin** bloques «Aspectos de salud» ni «Información complementaria» (navegador 01-ago)
- [x] Campo «Información adicional» al final de §5

### A.3 E5 específica — §5 solo judicial

- [x] Misma regla que periódica; título sección **«Antecedentes relevantes»**
- [x] Runner HTTP valida HTML §5

### A.4 Demos Word completados

- [x] Token completado redirige OK (no reabre formulario vacío)
- [x] Cuestionario marcado `completado` en BD
- [x] §5 periódica Word (#34): presentación admin solo bloque judicial (`CuestionarioPresentacionDashboard`)

### A.5 Recorrido guardado automatizado

- [x] `RecorridoCompletoFormulariosDemoTest` — preempleo, socio, periódica, específica (120 assertions)

---

## B — REPRO (trabajador / admin)

### B.1 Navegación principal

- [x] Dashboard `/dashboard` (navegador)
- [x] Gestión cuestionarios `/cuestionarios`
- [x] Orden demo Word `/ordenes/5` — 5 evaluados, formularios completados (navegador)
- [x] Reporte evaluaciones `/reportes/evaluaciones` — filtros + listado (navegador)
- [x] Reporte empresas `/reportes/empresas` (solo REPRO)

### B.2 Cuestionarios demo Word (#33, #34, #35, #37)

- [x] Show HTTP 200
- [x] PDF admin HTTP 200 (`application/pdf`)
- [x] Edit HTTP 200
- [x] Show #34 §5 pestaña «Antecedentes recientes» + solo «Aspecto judicial» (navegador)
- [x] Bloque «Redacción informe Word» muestra «Aspectos de salud» como **título de narrativa evaluador** (distinto del formulario §5 — esperado)

### B.3 Reportes y exportaciones

- [x] `/reportes/evaluaciones/pdf` → PDF
- [x] `/reportes/evaluaciones/excel` → Excel
- [x] `/reportes/empresas/pdf` → PDF

---

## C — Cliente empresa

> Validado al 100% vía runner HTTP con sesión `ilemke@example.org`.  
> En navegador, cerrar sesión REPRO antes de probar portal empresa (logout es POST).

### C.1 Portal

- [x] `/empresa/mi-empresa`
- [x] `/empresa/ordenes` y `/empresa/ordenes/5`
- [x] `/empresa/cuestionarios` — listado
- [x] `/empresa/cuestionarios/5` (pre-empleo) y `/empresa/cuestionarios/9` (socio)

### C.2 Privacidad / campos internos

- [x] Show #5 **sin** campos integridad REPRO (`integridad_01`, «Preguntas de integridad laboral»)
- [x] PDF empresa `/empresa/cuestionarios/5/pdf` → HTTP 200 PDF

### C.3 Reportes empresa

- [x] `/reportes/evaluaciones` accesible
- [x] Export PDF evaluaciones empresa
- [x] **Bloqueo** `/reportes/empresas` (403/redirect)
- [x] **Bloqueo** `/cuestionarios/33` admin (403/redirect)

---

## D — Hallazgos y notas

| ID | Severidad | Hallazgo | Acción |
|----|-----------|----------|--------|
| H-QA-01 | Info | Runner HTTP necesita `RateLimiter::clear(sha1('\|127.0.0.1'))` por throttle 60/min en rutas `/cuestionario/*` | Corregido en `scripts/qa_flujo_tres_roles.php` |
| H-QA-02 | Info | Tras varias ejecuciones QA, tokens E5 pueden saltar instrucciones → redirect directo a `seccion/5` | Aceptable; runner trata redirect como OK |
| H-QA-03 | Info | Admin show incluye «Aspectos de salud» en bloque **informe Word** (6 narrativas evaluador), no en formulario §5 periódica | No es regresión; distinguir de §5 formulario |
| H-QA-04 | Info | Logout Laravel es POST; GET `/logout` devuelve 405 | Usar botón «Cerrar sesión» o POST en automatización |
| H-QA-05 | Pendiente CI | `ReportesTest` falla por roles duplicados en BD testing si suite no aísla | Fuera de alcance QA manual local; no bloquea entrega |

---

## E — Comandos de respaldo

```bash
# Runner tres roles
docker exec repro-app php scripts/qa_flujo_tres_roles.php

# Recorrido formularios evaluado
docker exec repro-app php artisan test --filter=RecorridoCompletoFormulariosDemoTest

# Suite formularios ampliada (si BD testing limpia)
docker exec repro-app php artisan test --filter=CuestionarioModuloCompletoTest
```

---

## F — Criterio de cierre

- [x] 63/63 checks runner HTTP en verde
- [x] Flujo evaluado E5 §5 verificado visualmente (solo judicial)
- [x] REPRO: show #34, orden #5, reportes evaluaciones verificados en navegador
- [x] Cliente: portal, PDF, reportes y bloqueos verificados vía runner
- [x] Recorrido PHPUnit E1–E5 en verde
- [ ] Deploy servidor nuevo — **pendiente** (fuera de alcance hasta validación cliente)

**Responsable QA:** agente Cursor — 2026-08-01  
**Próximo paso sugerido:** revisión interna REPRO con este checklist + `ESTADO_ENTREGA_FORMULARIOS_2026-08-01.md` antes de aviso al cliente.
