# QA manual — Dashboard show/edit cuestionarios (vistas unificadas)

**Fecha de apertura:** 2026-07-30  
**Re-verificación:** 2026-08-01 (post P0 §5 periódica/específica + PDF admin unificado)  
**Estado:** 🟢 **CERRADO** (verificación navegador + PHPUnit)  
**Alcance:** Admin REPRO (show/edit), portal empresa (lectura/PDF), smoke candidato demo

> Marcar `[x]` al pasar. Anotar hallazgos al final.  
> Base URL local: `http://localhost:8000`

---

## Resumen de avance

| Bloque | Estado | Notas |
|--------|--------|-------|
| **A** Login admin / empresa | ✅ | Navegador 30-jul |
| **B** Admin show (4 tipos) | ✅ | #18/#19 §5 solo judicial; títulos «Antecedentes recientes/relevantes» |
| **C** Admin edit | ✅ | #17 pre-empleo — capa unificada + foto + bloques editables |
| **D** Portal empresa show | ✅ | #5 pre complementaria OK; sin integridad/judicial interno |
| **E** PDF admin / empresa | ✅ | Admin PDF unificado (HTTP 200); empresa PDF #5 OK |
| **F** Smoke candidato demo E1–E5 | ✅ | Recorrido PHPUnit 4/4 (01-ago) |
| **G** Recorrido completo guardado | ✅ | `RecorridoCompletoFormulariosDemoTest` |

---

## A — Login

- [x] Admin `admin@repro.com` / `admin1234` → dashboard REPRO
- [x] Empresa `ilemke@example.org` / `password` → portal empresas

---

## B — Admin show (`/cuestionarios/{id}`)

Rutas: **no** llevan prefijo `/admin`; usar `/cuestionarios/{id}`.

### B.1 Pre-empleo (#17 — demo Word)

- [x] 5 pestañas con títulos PDF
- [x] Fotografía del candidato
- [x] Tablas dinámicas (hijos, hermanos, empleos, deudas)
- [x] Preguntas de integridad laboral (19)
- [x] Aspectos de salud + aspecto judicial + complementaria
- [x] Tablas informe REPRO + notas evaluador (solo REPRO)
- [x] Documentos evaluado + observaciones administrativas

### B.2 Socioeconómico (#21 — demo Word)

- [x] 6 pestañas incl. «Información Socioeconómica Complementaria»
- [x] Referencias (familiares, personales, vecinales, laborales)
- [x] Bienes y presupuesto con totales (Q40,000 / Q8,500 en demo)
- [x] Secciones 1–5 igual que pre-empleo donde aplica

### B.3 Periódica (#18 — demo Word)

- [x] 5 pestañas con nombres periódicos (Actualización de Datos, Cambios Familiares, …)
- [x] §2 sin tabla hermanos (solo hijos)
- [x] §3: empleo actual + preguntas complementarias laborales
- [x] §5: **solo «Aspecto judicial»** (título «Antecedentes recientes»; sin salud ni complementaria) — re-verificado 01-ago

### B.4 Específica (#19 — demo Word)

- [x] 5 pestañas (Datos Básicos, …, Información Laboral y Caso)
- [x] §3: empleo actual + complementarias + información laboral adicional
- [x] §5: **solo «Aspecto judicial»** (título «Antecedentes relevantes») — re-verificado 01-ago

---

## C — Admin edit (`/cuestionarios/{id}/editar`)

Requiere permiso `evaluaciones.editar` (rol admin/repro con permisos seed).

### C.1 Pre-empleo (#17)

- [x] Modo corrección si completado
- [x] Componente `<x-foto-candidato>` («Fotografía del candidato»)
- [x] Campos escalares editables vía `respuestas_campo[slug][campo]`
- [x] Bloques integridad / salud / judicial / complementaria editables
- [x] Tablas dinámicas en solo lectura + aviso informativo
- [x] Tablas informe Word editables (partial legacy conservado)
- [x] Documentos evaluado al pie del formulario

### C.2 Socioeconómico (#21)

- [x] Pestaña 6 presente en edit (antes faltaba con partials legacy)
- [x] Misma estructura de bloques que show

### C.3 Guardado

- [x] PHPUnit: `AdminCuestionarioFotoEditTest::test_update_guarda_foto_candidato`
- [ ] Smoke manual guardar corrección escalar en navegador (opcional; cubierto por tests)

---

## D — Portal empresa (`/empresa/cuestionarios/{evaluado_id}`)

Evaluados demo Word (orden `ORD-DEMO-WORD-2026`), resultados liberados (`en_revision` + informe preliminar).

### D.1 Pre-empleo (evaluado #5 → cuestionario #17)

- [x] 5 pestañas visibles
- [x] **No** muestra: integridad, aspecto judicial, aspectos de salud internos, tablas informe REPRO
- [x] Muestra: datos personales/familiares, formación, empleos, deudas, complementaria empresa
- [x] Enlaces: Ver Orden Completa, PDF orden, PDF cuestionario

### D.2 Socioeconómico (evaluado #9 → cuestionario #21)

- [x] 6 pestañas incl. §6
- [x] Referencias, bienes, presupuesto visibles
- [x] **No** muestra integridad ni bloques internos REPRO

---

## E — PDF

- [x] Admin: `GET /cuestionarios/17/pdf` → 200 (PDF binario)
- [x] Admin: sin campos legacy `referencia1_*` en HTML renderizado
- [x] Empresa: `GET /empresa/cuestionarios/5/pdf` → 200
- [x] Admin PDF usa `pdf-seccion-contenido-admin` + `CuestionarioPresentacionDashboard` — 01-ago

---

## F — Smoke candidato (token demo E1)

| Campo | Valor |
|-------|-------|
| Token | `e1demo2026pruebamanualtokenrepr0` |
| DPI | `2405617300105` |
| Seeder | `DemoPruebaManualE1Seeder` |

- [x] `/cuestionario/{token}` — verificar DPI OK
- [x] Redirección a instrucciones pre-empleo
- [x] Términos con texto legal y nombre empresa demo
- [x] `/seccion/1` sin términos → redirige a términos (gate correcto)
- [ ] Recorrido manual completo E1–E5 en navegador (opcional; cubierto por PHPUnit recorrido)

---

## G — Automatizado (01-ago)

```bash
docker exec repro-app php artisan test --filter='AdminCuestionarioFotoEditTest|InformePreempleoVisibilidadTest|RecorridoCompletoFormulariosDemoTest|EmpresaModulosTest|CuestionarioPeriodicaTest|CuestionarioEspecificaTest|Phase8DPdfDocumentosTest'
```

Resultado: **88+ passed** (suite ampliada formularios).

Seeders demo manuales:

```bash
docker exec repro-app php artisan db:seed --class=DemoPruebaManualE1Seeder --force
docker exec repro-app php artisan db:seed --class=DemoPruebaManualE4Seeder --force
docker exec repro-app php artisan db:seed --class=DemoPruebaManualE5PeriodicaSeeder --force
docker exec repro-app php artisan db:seed --class=DemoPruebaManualE5EspecificaSeeder --force
```

---

## Tokens útiles (local)

| Uso | ID / token | Credenciales |
|-----|------------|--------------|
| Admin show/edit Word pre | Cuestionario **#17** | `admin@repro.com` / `admin1234` |
| Admin show/edit Word socio | Cuestionario **#21** | idem |
| Admin show Word periódica | Cuestionario **#18** | idem |
| Admin show Word específica | Cuestionario **#19** | idem |
| Empresa show pre | Evaluado **#5** | `ilemke@example.org` / `password` |
| Empresa show socio | Evaluado **#9** | idem |
| Candidato demo E1 | `e1demo2026pruebamanualtokenrepr0` | DPI `2405617300105` |
| Candidato demo E4 socio | `e4demo2026pruebamanualtokenrepr0` | DPI `2405617300205` |
| Candidato demo E5 periódica | `e5demo2026periodicatokenrepr0` | DPI `2405617300305` |
| Candidato demo E5 específica | `e5demo2026especificatokenrepr0` | DPI `2405617300405` |

---

## Hallazgos

| # | Fecha | Área | Hallazgo | Estado |
|---|-------|------|----------|--------|
| 1 | 30-jul | Edit admin | Partial legacy `editar_seccion_*` reemplazado por `shared/cuestionario/seccion-edicion` | ✅ Resuelto |
| 2 | 30-jul | Edit admin | Foto candidato perdía etiqueta «Fotografía del candidato» | ✅ Restaurado con `<x-foto-candidato>` |
| 3 | 30-jul | Empresa | Link orden corregido a `empresa.ordenes.show` | ✅ Resuelto |
| 4 | 30-jul | Edit admin | Tablas dinámicas solo lectura + aviso (diseño acordado) | ℹ️ By design |
| 5 | 30-jul | Datos legacy | Respuestas antes del reorden `integridad_*` / `periodico_*` pueden desalinearse | ⚠️ Monitorear en prod |
| 6 | 01-ago | Periódica/específica §5 | Candidato y admin show solo judicial; títulos actualizados | ✅ Resuelto |
| 7 | 01-ago | Admin PDF | Migrado a capa unificada Dashboard | ✅ Resuelto |
| 8 | 01-ago | Autorizaciones | Consentimiento adicional polígrafo/VSA en términos y PDF | ✅ Resuelto |

---

## Pendientes recomendados (post-cierre)

1. ~~**PDF admin**~~ — ✅ Unificado con `pdf-seccion-contenido-admin` (01-ago).
2. **PDF empresa** — Evaluar reutilizar bloques shared en `pdf-secciones-empresa.blade.php` (hoy lógica propia; tests pasan).
3. **Limpieza legacy** — ✅ Eliminados partials `seccion_*` / `editar_seccion_*` (01-ago).
4. **Edición tablas dinámicas en admin** — Solo si el cliente lo pide; hoy corrección vía flujo candidato.
5. ~~**GitHub**~~ — ✅ Push a `origin/master` (01-ago); deploy al nuevo servidor pendiente de validación interna.
6. **Producción** — Verificar cuestionarios completados **antes** del cambio de claves integridad/periódico; migración de datos si aplica.

---

## Arquitectura (referencia rápida)

```
Candidato (token)     → Support::* + cuestionario/secciones/*
Admin show/edit       → CuestionarioPresentacionDashboard + shared/cuestionario/*
Empresa show          → CuestionarioPresentacionEmpresa → Dashboard (soloEmpresa=true)
Empresa PDF           → pdf-secciones-empresa (parcialmente independiente)
Admin PDF             → pdf-seccion-contenido-admin + CuestionarioPresentacionDashboard
```
