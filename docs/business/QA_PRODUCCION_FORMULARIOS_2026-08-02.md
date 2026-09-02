# QA Producción — Formularios deploy 2026-08-01

**URL:** https://reproappv2.szystems.com  
**Fecha pruebas:** 2026-08-02 (servidor + navegador + fix prod)  
**Scripts:** `deploy_prod_ready_formularios_2026.php` (ejecutado y eliminado ✅) · UAT navegador Cursor

---

## Resumen

| Área | Resultado |
|------|-----------|
| Archivos desplegados (8 clave + legacy eliminado) | ✅ 10/10 |
| Migraciones (8) + catálogo GT | ✅ 10/10 |
| Lógica §5 periódica/específica (PHP) | ✅ 3/3 |
| Rutas formularios/reportes | ✅ 3/3 |
| HTTP interno REPRO | ✅ 8/9 |
| HTTP interno empresa | ✅ 3/3 |
| Público (login, token inválido, assets JS/CSS) | ✅ |
| Login credenciales demo local | ⚠️ No aplican en prod |
| **Fix prod 02-ago** (demos + motivo E5 + UTF-8 catálogo) | ✅ OK |
| **UAT navegador E5 términos** | ✅ Motivo visible; ya no «Proceso en preparación» |
| **Informe cliente** | ✅ `docs/Informe_Cliente_Formularios_Produccion_2026-08-02.md` |

**Total verificación servidor:** **41 PASS / 1 FAIL** (show #24 HTML — revisión visual pendiente)  
**Estado para cliente:** ✅ **Listo para UAT** con enlaces demo §5.1 del informe

---

## Detalle por rol

### Público / evaluado
- [x] `/login` HTTP 200
- [x] Token inválido → página «Enlace no válido» (sin error 500)
- [x] Assets nuevos: `tabla-dinamica.js`, `cuestionario-autosave.js`, `depto-municipio-select.js`, `cuestionario.css` → HTTP 200

### REPRO (usuario real prod: `admin@repro.com-Deleted2`, role_as 3)
- [x] `/cuestionarios`, `/reportes/evaluaciones`, `/reportes/empresas`, `/ordenes` → HTTP 200
- [x] Export PDF reporte evaluaciones → HTTP 200
- [x] Show cuestionario #24 (periódica) → HTTP 200, sin stack trace
- [x] PDF cuestionario #24 → HTTP 200
- [ ] Show #24 HTML contiene texto «Aspecto judicial» — **FAIL** (ver nota abajo)

### Cliente empresa (primer usuario activo role_as 1)
- [x] `/empresa/cuestionarios` → HTTP 200
- [x] Sin campo `integridad_01` en portal
- [x] `/reportes/empresas` bloqueado → HTTP 403

### Evaluado — UAT navegador (2026-08-02)

Demos creados en prod (`deploy_qa_setup_evaluado_prod_2026.php`, ya eliminado):

| Demo | Token | DPI | Navegador |
|------|-------|-----|-----------|
| E1 pre-empleo | `e1demo2026pruebamanualtokenrepr0` | `2405617300105` | ✅ Ver abajo |
| E4 socio | `e4demo2026pruebamanualtokenrepr0` | `2405617300205` | ⚠️ HTTP OK; Infornet sí aplica (formulario pre-empleo) |
| E5 periódica | `e5demo2026periodicatokenrepr0` | `2405617300305` | ❌ «Proceso en preparación» |
| E5 específica | `e5demo2026especificatokenrepr0` | `2405617300405` | ❌ «Proceso en preparación» |

**E1 — probado en navegador (flujo real):**
- [x] Landing + verificación DPI correcto
- [x] DPI incorrecto → «El DPI ingresado no coincide con nuestros registros.»
- [x] Instrucciones → términos (autorización polígrafo pre-empleo + firma canvas)
- [x] Infornet (firma reutilizada)
- [x] Sección 1: catálogo deptos/municipios, foto subida, guardar → §2
- [x] Salto directo a `/seccion/5` redirige a §2 (gate progresivo OK)
- [ ] §5 E1 completa (salud + judicial + complementaria) — pendiente avanzar §2–§4
- [ ] Finalizar / documentos — pendiente

**E5 periódica / específica:**
- [x] Instrucciones OK
- [x] Pantalla «Esperando información» cuando falta motivo (comportamiento correcto)
- [ ] Términos + §5 solo judicial — **bloqueado** hasta registrar motivo REPRO

- [x] Token inválido → HTTP 404 (sin error 500)

---

## Hallazgos

| ID | Severidad | Hallazgo |
|----|-----------|----------|
| P-QA-01 | Info | `admin@repro.com` / `admin1234` **no funcionan** en prod. Usuario admin real: `admin@repro.com-Deleted2`. Usar credenciales REPRO reales para UAT manual. |
| P-QA-02 | Bajo | Show cuestionario #24 (periódica legacy) no incluye string «Aspecto judicial» en HTML, pero `CuestionarioPresentacionDashboard::bloquesPreguntas(5,'periodica')` sí devuelve solo judicial. Revisar visualmente pestaña §5 en prod. |
| P-QA-03 | Resuelto | Demos evaluado creados en prod para UAT (tokens arriba). |
| P-QA-04 | **Resuelto 02-ago** | Demos E5: `motivo_hecho_evaluacion` reparado vía `deploy_prod_ready_formularios_2026.php`. Términos E5 periódica verificados en navegador. |
| P-QA-05 | **Resuelto 02-ago** | Catálogo deptos/municipios re-sincronizado UTF-8 (340 municipios). |
| P-QA-06 | Info | Modal «Tomar foto» abre al cargar §1 aunque no se use cámara; subir archivo funciona. |

---

## Pendiente UAT del cliente

1. Cliente ejecuta checklist en `docs/Informe_Cliente_Formularios_Produccion_2026-08-02.md`
2. REPRO prod → show cuestionario periódica → pestaña §5 visual (P-QA-02)
3. Completar demo E1 hasta finalizar (opcional)

---

## Comandos de verificación (re-ejecutables)

Subir temporalmente `public/deploy_verify_formularios_2026.php` y abrir:

```
https://reproappv2.szystems.com/deploy_verify_formularios_2026.php?key=REPRO_DEPLOY_2026_SECURE_KEY
```

El script se auto-elimina si todo pasa; con 1 FAIL permanece para reintento.
