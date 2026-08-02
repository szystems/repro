# Estado de entrega — Formularios REPRO

**Fecha:** 1 de agosto de 2026  
**Audiencia:** Cliente REPRO / Stephany  
**Deploy:** Pendiente — migración desde GitHub al nuevo servidor cuando se confirme el 100%.

---

## Resumen para el cliente

Los cuatro tipos de formulario web están alineados a los **PDFs reales (ago-2025)** que REPRO usa hoy con candidatos. Admin, portal empresa y PDFs usan la misma capa de presentación unificada.

| Formulario | Estado | Notas |
|------------|--------|-------|
| Pre-empleo (polígrafo/VSA) | ✅ Listo | 5 secciones + salud/hábitos/judicial/complementaria |
| Socioeconómico | ✅ Listo | 6 secciones + referencias, bienes, presupuesto |
| Periódica | ✅ Listo | §5 **solo aspecto judicial** (como PDF PERIODICO ESPECIFICO) |
| Específica | ✅ Listo | Pregunta 1 amplia + académico reducido + §5 judicial |

---

## Qué se corrigió recientemente

1. **Periódica y específica §5** — El candidato ya no ve salud/hábitos/complementaria; solo antecedentes judiciales + información adicional final.
2. **Admin show/edit/PDF** — Misma estructura y etiquetas en los tres módulos; eliminados campos fantasma del PDF admin (`referencia1_*`, etc.).
3. **Portal empresa** — PDF sin bloque complementaria en periódica/específica; campos internos siguen ocultos.
4. **Autorizaciones legales** — Bloque **Consentimiento adicional** para polígrafo y VSA en términos y PDF.
5. **Títulos de sección** — Periódica/específica §5 renombrados a «Antecedentes recientes» / «Antecedentes relevantes».
6. **Limpieza técnica** — Eliminados partials legacy de admin que ya no se usaban.

---

## Qué NO está incluido (requiere decisión del cliente)

| Tema | Situación |
|------|-----------|
| Spec jun-2026 refinado | Plan jul-29 pendiente: orden 19 integridad, judicial 22 preguntas, redacción «Explique», etc. |
| App actual | Bloqueada al **PDF ago-2025** por tests de literales |
| Dos servicios misma orden | Fuera de alcance formularios |
| WhatsApp API | Pospuesto |

**Recomendación:** Mantener PDF ago-2025 como autoridad hasta que el cliente confirme por escrito el cambio al spec jun-2026.

---

## Verificación antes de avisar al cliente

```bash
# Suite formularios
docker exec repro-app php artisan test --filter='CuestionarioPeriodicaTest|CuestionarioEspecificaTest|RecorridoCompletoFormulariosDemoTest|InformePreempleoVisibilidadTest|FormularioLiteralesClienteTest'

# PDF y autorizaciones
docker exec repro-app php artisan test --filter='Phase8DPdfDocumentosTest|CuestionarioEvaluadorNotasTest'
```

**QA manual:** `docs/business/QA_MANUAL_DASHBOARD_CUESTIONARIOS_2026-07-30.md`  
**Auditoría técnica:** `docs/business/AUDITORIA_COMPLETA_FORMULARIOS_2026-07-31.md`

---

## Próximo paso sugerido

1. ~~QA manual con tokens demo E1–E5 en local.~~ ✅ Re-verificado 01-ago (navegador + PHPUnit).
2. ~~Revisión interna REPRO (show admin + PDF + portal empresa).~~ ✅ #17 edit, #18/#19 §5, empresa #5/#9.
3. ~~Push a GitHub~~ ✅ 01-ago — **deploy al nuevo servidor pendiente** (sin avisar al cliente aún).
4. Avisar al cliente cuando el entorno de pruebas en el nuevo servidor esté validado.
