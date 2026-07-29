# QA manual — Fase G (formularios alineados a la especificación)

**Fecha de apertura:** 2026-07-28  
**Estado:** 🟡 **EN CURSO**  
**Última sesión:** 2026-07-28 — retoma post-respuestas cliente (commit `106b4fe0`)

> Marcar `[x]` al pasar. Anotar hallazgos al final.

---

## Resumen de avance

| Bloque | Estado | Notas |
|--------|--------|-------|
| **A** Login colaborador/empresa | ⬜ | |
| **B — Pre-empleo candidato** | 🟡 | #37 flujo base OK · pendiente rama experiencia + extranjero + hermanos + docs >10MB |
| **C — Socioeconómico candidato** | ⬜ | |
| **D — Periódica candidato** | ⬜ | token `worddemo2026poligrafoperiod0` |
| **E — Específica candidato** | ⬜ | token `worddemo2026poligrafoespecif0` |
| **F — Admin cuestionario** | ⬜ | bloques Word, filtros sede |
| **G — Respuestas cliente jul-28** | ✅ | G5, I-5, H-8, refs texto 3, bloques Word — commit `106b4fe0` |
| **H — Portal empresa I-5** | ⬜ | informe por evaluado sin esperar orden entregada |

---

## B — Pre-empleo (candidato)

### B.1 Flujo base (sin experiencia laboral previa) — ✅ 28-jul
- [x] Evaluado #37 Bradford — secciones 1–5 + socio + finalizar
- [x] Header por tipo (no dice «Socioeconómico» en pre-empleo)
- [x] Pantalla completado con títulos PDF

### B.2 Rama con experiencia laboral previa — 🔄 en curso
- [ ] Evaluado #36 Abbigail — marcar experiencia previa = Sí
- [ ] Tabla empleos (mínimo 1 fila)
- [ ] 19 preguntas complementarias laborales visibles y obligatorias
- [ ] Guardar sección 3 y continuar

### B.3 Identificación extranjera — 🔄 parcial (28-jul)
- [x] Verificación con pasaporte (#36, 13 dígitos)
- [x] Sección 1: tipo «Pasaporte» precargado + campos extranjero en DOM
- [ ] Pantalla verificar-identidad aún pide solo «DPI» (mejora UX pendiente)

### Autorizaciones en términos — verificado 28-jul
- [x] Texto oficial polígrafo pre-empleo + Infornet en flujo pre-empleo
- [x] Hallazgo #3: `:empresa:` mal mapeado → corregido en config

### B.4 Tabla hermanos (familiar)
- [ ] Agregar filas hermanos
- [ ] Eliminar fila extra con confirmación

### B.5 Documentos adjuntos >10 MB
- [ ] Rechazo con mensaje claro (>10 MB)

---

## Hallazgos

| # | Fecha | Tipo | Descripción | Estado |
|---|-------|------|-------------|--------|
| 1 | 28-jul | UI | Header «Cuestionario Socioeconómico» en todos los tipos | ✅ Corregido |
| 2 | 28-jul | UI | Completado listaba nombres viejos de sección | ✅ Corregido |
| 3 | 28-jul | G5 | «Empresa [nombre candidato]» en autorización (placeholder mal reemplazado) | ✅ Corregido |

---

## Tokens útiles (local)

| Evaluado | Token | DPI |
|----------|-------|-----|
| #37 Bradford (completado) | `efhJ2lw0Bw2f3uypWCaNqCacmiSZFpyHZKYvOU6eiGfuDu1vDpMojL5M2bsupNiO` | `3543166297864` |
| #36 Abbigail (en progreso) | `tbMtcszmu3tnc3YM3CaEqEwaePJXRvt2Woky9oU1FxZUvRnHlIgu5pYddWp5mcEW` | `5320259437049` (pasaporte) |
| Admin | `admin@repro.com` / `admin1234` | |
