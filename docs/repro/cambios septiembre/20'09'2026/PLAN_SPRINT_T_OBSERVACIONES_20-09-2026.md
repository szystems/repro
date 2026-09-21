# Sprint T — Stephany 20-sep-2026 (Excel/KPI, preliminar, Word preempleo)

**Cliente:** Stephany Castro / REPRO  
**Canal:** WhatsApp 19–20 sep 2026 + doc Word adjunto  
**Prod:** https://portal.reprogt.com · Coolify **repro-portal** · auto-deploy `master`  
**iPage:** `reproappv2.szystems.com` sigue **503** — no descongelar  

**Evidencia:**  
- `CAMBIOS PARA WORD DE POLIGRAFO Y VSA PREEMPLEO.docx` (esta carpeta)  
- Capturas WA: preliminar 1ª hoja; consulta pago Resend / límite correos  

**Repo (20-sep-2026):** local = `origin/master` tras pull · último merge relevante `3f6092a6` (correos/alerta Resend/migraciones en start Coolify).

**No:** `migrate:fresh` · resetear claves de Stephany · regenerar Word NEVERIA/CORALSA/PERCO · Coolify Upgrade · tocar Asonata/ControClinic/portal.szystems.com.

---

## Estado resumido (qué ya está vs qué falta)

| Bloque | Estado | Notas |
|--------|--------|--------|
| **T-KPI** Excel reclutador + sede cliente; calendario informe final; autoasignación informe final | ✅ **Prod** | Commit `f42a0d50` · migración `2026_09_15_120000_add_informe_final_roles…` batch **121** |
| **T-MAIL** Pago Resend Pro + team REPRO Portal | ⏸ **Pendiente Otto** | Checkout Stripe abierto; cuenta `szystemscorreos@outlook.com` · mover `reprogt.com` + API key Coolify después del pago |
| **T-PRE** Preliminar no se actualiza al corregir 1ª hoja | ✅ **Prod** (commit `75497e58`) | Flag `informe_preliminar_editado_manual` + `sincronizarDesdeWord` |
| **T-WORD** Cambios Word Polígrafo + VSA **solo preempleo** | 🔧 **Parcial** | T-W1/W2/W3/W4/W6 en código · **T-W5** plantillas pendiente revisión Stephany |

---

## Cómo lo dijo ella (WhatsApp)

| # | Pedido | Lectura técnica |
|---|--------|-----------------|
| W1 | No guardan cambios en **1ª hoja** para el **preliminar**; al revisar en finalización el preliminar sigue con lo viejo | Sync `InformePreliminarDesdeWord` solo si preliminar **vacío** → ver **T-PRE** |
| W2 | ¿Logró el **pago** Resend? Muchas órdenes/correos hoy sin mensaje de **límite** | Free = 100/día UTC; banner persistente solo al **100%** (PR reciente). No pagado Pro aún → **T-MAIL** |
| W3 | Doc Word: cambios **Polígrafo y VSA preempleo** | Matriz **T-WORD** · plantillas en `docs/Plantillas Word/` |

---

## T-PRE — Informe preliminar ↔ 1ª hoja Word (prioridad 1)

### Comportamiento actual (no es bug de “no guardar” en cuestionario)

- **1ª hoja** = Gestión de cuestionarios → tarjeta *Resultado de evaluación (primera y última hoja)*: `resultado_informe`, `word_observaciones`, detalles mentira/excepción.  
- Al guardar cuestionario: `InformePreliminarDesdeWord::copiarTablaSiPreliminarVacio()` (`app/Support/InformePreliminarDesdeWord.php`).  
- **Solo copia** a `evaluados_orden.texto_informe_preliminar` si el preliminar está **vacío** (tests: `CuestionarioEvaluadorNotasTest::test_guardar_resultado_word_no_borra_preliminar_existente`).

### Fix quirúrgico propuesto (elegir uno en implementación)

| Opción | Descripción | Riesgo |
|--------|-------------|--------|
| **T-PRE-A** (recomendada) | Regenerar tabla HTML del preliminar desde 1ª hoja en cada guardado de `resultado_informe` / `evaluador_notas` **salvo** preliminar editado manualmente en ficha orden | Bajo si hay flag o heurística clara |
| **T-PRE-B** | Botón en ficha orden: *Actualizar preliminar desde 1ª hoja* | Mínimo riesgo; un paso manual |

### Archivos tocados (estimado)

- `app/Support/InformePreliminarDesdeWord.php`  
- `app/Http/Controllers/Admin/CuestionariosController.php` (post-guardado)  
- Tests: `InformePreliminarDesdeWordTest`, `CuestionarioEvaluadorNotasTest`  
- Ayuda: `ordenes-detalle-evaluado` o `informe-preliminar` (1 párrafo)

### Verificación UAT

1. Cuestionario → 1ª hoja: resultado + observaciones → guardar → preliminar en ficha orden muestra tabla.  
2. Corregir observaciones → guardar → **preliminar debe reflejar corrección** (hoy falla).  
3. Editar preliminar a mano en ficha → guardar cuestionario → **no pisar** edición manual (si T-PRE-A).

---

## T-MAIL — Resend / correos (ops + producto)

| Paso | Responsable | Detalle |
|------|-------------|---------|
| T-MAIL-1 | Otto | Completar pago Pro · team **REPRO Portal** · `szystemscorreos@outlook.com` |
| T-MAIL-2 | Dev post-pago | Mover dominio `reprogt.com` al team REPRO · nueva API key → Coolify `MAIL_PASSWORD` · smoke `noreply@reprogt.com` |
| T-MAIL-3 | Otto → Stephany | Invitar Admin (su login Resend) · ella pone tarjeta · Otto quita la suya |
| T-MAIL-4 | Opcional | Aviso al ~90% del límite diario (hoy solo corte al 100%) |

**Contexto cuota Free:** ~100 emails/día, reset **00:00 UTC** (= 18:00 Guatemala). Error SMTP: `550 … daily email sending quota`.

---

## T-WORD — Matriz (solo Polígrafo preempleo + VSA preempleo)

**Fuente:** `CAMBIOS PARA WORD DE POLIGRAFO Y VSA PREEMPLEO.docx`  
**Alcance:** NO periódico, NO específico, NO socio — salvo que Stephany diga lo contrario.

| ID | Pedido en doc | Enfoque | Archivos / área | Riesgo | Orden |
|----|---------------|---------|-----------------|--------|-------|
| **T-W1** | HIJOS vacíos: fila única **«No tiene»** (como exparejas, no «No aplica») | Relleno tabla | `InformeWordRelleno::rellenarTablaHijos` + tests | Bajo | 2 |
| **T-W2** | Fila **Validación de constancia de estudios** bajo nivel académico | Plantilla `.docx` + fila en formulario/tabla si falta dato | Plantilla preempleo poli/VSA · `InformeWordRelleno` (ya busca texto «Validación de constancia») | Medio | 3 |
| **T-W3** | **«Aspecto laboral» → «Ampliación de información laboral»** y bloque **pegado** a tabla empleos (como aspecto económico) | Título + `rellenarAspectoLaboralPreempleo` | `InformeWordRelleno.php` · plantillas | Medio | 2 |
| **T-W4** | **Despegar** tablas aspectos judiciales e información complementaria | Espaciado XML | `InformeWordXml` / relleno | Bajo–medio | 2 |
| **T-W5** | Fuente al editar Word descargado: **Helvetica 12**; **criterios interpretación = 9**; deudas/tatuajes **11** | `docDefaults` / estilos en **plantilla** | `docs/Plantillas Word/poligrafo preempleo.docx` · VSA preempleo equivalente | Medio | 4 |
| **T-W6** | Papelería en Word: **habilitar**; tabla solo **nombre** doc; **solo imágenes** (no PDF); sin fila observaciones | UI + `InformeWordAnexosPapeleria` · reproduir «no nos deja» | Cuestionario `anexos-word-papeleria` · orden documentos | Medio–alto | 5 |
| **T-W7** | Textos de ejemplo en doc (salud, judicial web, recomendaciones licencia) | **No hardcodear** salvo confirmación de que son boilerplate fijo | Narrativas = `word_*` del evaluador | Alto si se automatiza mal | ❌ hasta aclarar |

**Regla de oro (ella):** *«Por favor no vaya a cambiar nada más, ya que todo lo demás está bien.»*

### Verificación Word (cada lote)

- PHPUnit: tests de plantilla existentes (`InformeWord*Test`, observaciones 16/17 ago) + caso nuevo por ítem.  
- Descargar Word **un** polígrafo preempleo + **un** VSA preempleo en prod/UAT → revisión Stephany.  
- No regresión: socio, periódico, específico (diff mínimo en código: guards por `tipo_servicio` / `tipo_formulario`).

---

## Orden de desarrollo (canónico — no saltar sin cerrar verificación)

```
1. [x] T-PRE     Preliminar ↔ 1ª hoja (+ tests + UAT)
2. [x] T-W1      Hijos «No tiene»
3. [x] T-W3      Ampliación laboral (título + layout)
4. [x] T-W4      Despegar tablas judicial / complementaria
5. [x] T-W2      Validación constancia estudios (plantilla + datos)
6. [ ] T-W5      Helvetica / tamaños plantillas preempleo poli+VSA
7. [x] T-W6      Papelería anexos (diagnóstico + fix)
— paralelo ops —
8. [ ] T-MAIL    Pago Resend + cutover dominio/API (Otto + dev post-pago)
```

**T-KPI** ya desplegado — no reabrir salvo bug reportado.

---

## T-KPI — Referencia (cerrado prod)

| Entrega | Detalle |
|---------|---------|
| Excel informes empresas | Columnas **Reclutador**, **Sede/Región empresa (cliente)** |
| Excel listado órdenes | **Reclutador** (ya) + **Sede/Región empresa (cliente)** |
| Calendario + Excel calendario | **Informe final (responsable)**, **Subió informe final** |
| Ficha orden REPRO | **Autoasignarme informe final** · `informe_final_responsable_id` · `informe_final_subido_por` |

---

## Lo que NO es / no mezclar

| Confusión | Correcto |
|-----------|----------|
| Sede en Excel/calendario columna «Sede» | Es **Sede REPRO** (`sede_id`). Cliente = **`sede_region_empresa`** |
| Preliminar Quill en ficha orden vs 1ª hoja Word | W1 es sync **Word 1ª hoja → preliminar HTML**; otro bug sería JS Quill en ficha (investigar aparte si persiste) |
| Cambiar todas las plantillas Word | Solo **preempleo polígrafo + VSA** en este sprint |
| Subir plan Resend = arreglar preliminar | Independientes |

---

## Comandos útiles (agentes)

```bash
# Tras cambios con migración (no fresh)
php artisan migrate --force

# Tests foco Sprint T
php artisan test --filter='InformePreliminar|CuestionarioEvaluadorNotas|InformeWord'
```

**SSH prod (solo lectura / migrate acordado):** `deploy@5.78.235.235` · contenedor app `ot2bqft*` (nombre cambia en redeploy).

---

## Siguiente acción recomendada

**Deploy:** `75497e58` en `master` (Coolify auto-deploy). Stephany: UAT 1ª hoja ↔ preliminar + Word preempleo poli/VSA. Pendiente **T-W5** (tamaños en `.docx`) y **T-MAIL** (Otto).
