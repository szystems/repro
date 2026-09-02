# Informe al cliente — Formularios en producción

**Para:** Stephany / equipo REPRO  
**De:** SZ Systems  
**Fecha:** 2 de agosto de 2026  
**Plataforma:** https://reproappv2.szystems.com  
**Estado:** ✅ **Listo para pruebas de aceptación (UAT) del cliente**

---

## 1. Resumen ejecutivo

Se completó el despliegue de la **Fase Formularios** en el servidor de producción. Los cuatro tipos de formulario que REPRO usa con candidatos (pre-empleo, socioeconómico, periódica y específica) quedaron alineados a los **PDFs impresos reales (agosto 2025)** que hoy son la referencia operativa de REPRO.

**Qué significa para ustedes:** el candidato, el trabajador REPRO y la empresa cliente ven **las mismas preguntas, secciones y restricciones** que en los formularios originales, con mejoras de sistema (firma digital, guardado por sección, catálogo de departamentos/municipios, autorizaciones legales, informe Word).

**Acción solicitada:** usar la **lista de pruebas del §6** con los enlaces demo incluidos. Cualquier diferencia respecto al PDF impreso, anótela con captura de pantalla y número de sección.

---

## 2. Autoridad de referencia

| Documento | Rol |
|-----------|-----|
| **PDFs reales ago-2025** (`POLIGRAFO PRESENCIAL`, `SOCIOECONOMICO…`, `PERIODICO ESPECIFICO`) | ✅ **Autoridad actual** — lo que la app reproduce |
| Spec refinado jun-2026 (`CREACIÓN FORMULARIOS DE SISTEMA.pdf`) | ⏸ **Pendiente de decisión** — cambios de redacción/orden adicionales no implementados |

La aplicación incluye **tests automáticos de literales** que bloquean desvíos accidentales respecto al PDF ago-2025. Si REPRO desea migrar al spec jun-2026 (22 preguntas judiciales, nuevo orden de integridad, etc.), requiere **confirmación por escrito** y una fase de cambio planificada.

---

## 3. Qué se cambió respecto a los formularios originales — y por qué

### 3.1 Cambios funcionales importantes (correcciones respecto a versiones anteriores del sistema)

Estos puntos **no estaban bien** en versiones previas de la plataforma; **ahora sí coinciden con el PDF**:

| Tema | Antes (sistema antiguo) | Ahora (como PDF original) | Por qué |
|------|-------------------------|---------------------------|---------|
| **Periódica §5** | Mostraba salud, hábitos y complementaria (como pre-empleo) | **Solo «Aspecto judicial»** + información adicional final | El PDF `PERIODICO ESPECIFICO` no incluye bloques de salud/complementaria en §5 |
| **Específica §5** | Igual que pre-empleo completo | **Solo judicial** + información adicional | Misma regla que periódica en el PDF |
| **Admin / PDF REPRO** | Vistas legacy con campos fantasma (`referencia1_*`, etc.) | Una sola capa `CuestionarioPresentacionDashboard` en show, edit y PDF | Evitar que REPRO vea datos que no existen en el formulario real |
| **Portal empresa PDF** | §5 periódica/específica podía mostrar complementaria | Filtrado por tipo; sin campos internos REPRO | Confidencialidad (petición I-5) |
| **Periódica §2** | Tabla de hermanos visible | **Oculta** (PDF no la pide) | Alineación estructural |
| **Periódica §3** | Preguntas genéricas de pre-empleo | **31 preguntas laborales** del PDF periódico | `HistorialLaboralPeriodico` |
| **Socioeconómico** | A veces 5 secciones como pre-empleo | **6 secciones** con referencias (mín. 3), bienes y presupuesto | PDF `SOCIOECONOMICO QUE LLENAN LOS CANDIDATOS` |
| **Motivo/hecho (periódica y específica)** | No existía en flujo evaluado | REPRO lo registra en la orden; el candidato **no avanza** sin ese dato | Requerimiento Fase A / propuesta jun-2026 §5.3 |
| **Autorizaciones legales** | Texto genérico | **7 plantillas** según servicio + formulario; **Infornet** en pre-empleo; **Consentimiento adicional** polígrafo/VSA | Cumplimiento legal acordado |
| **Departamentos / municipios** | Texto libre o listas incompletas | Catálogo **22 deptos / 340 municipios** + «Otro (extranjero)» | PDF pre-empleo y datos estandarizados |

### 3.2 Lo que se mantiene igual al PDF (sin cambiar redacción)

| Área | Detalle |
|------|---------|
| Pre-empleo §3 integridad | **19 preguntas** en el **orden del PDF** (incluye redacción coloquial: «robar», «policiacos», etc.) |
| Pre-empleo §5 judicial | **16 preguntas** del PDF (no 22 del spec jun-2026) |
| Pre-empleo §5 complementaria | **8 campos** del PDF |
| Salud y hábitos | Bloques del PDF pre-empleo |
| Tablas dinámicas | Empleos, hijos, hermanos, deudas, formación — columnas según PDF |
| **Fechas laboradas (empleos)** | Texto libre (formato ambiguo) | **Desde / Hasta** + «Sigue laborando»; guardado `dd/mm/yyyy al …` | Evita errores de validación y alinea al formato guatemalteco |

### 3.3 Mejoras de sistema (no están en el PDF papel, pero no alteran el contenido)

| Mejora | Beneficio |
|--------|-----------|
| Formulario web por enlace (sin login candidato) | Verificación DPI + token único |
| Firma digital en términos e Infornet | Trazabilidad legal |
| Guardar borrador / continuar por sección | Experiencia móvil |
| Foto del candidato (cámara o archivo) | Sustituye foto pegada en papel |
| Export **informe Word (.docx)** por evaluado | Entrega editable a empresa |
| Notificaciones y estados de orden | Operación REPRO |

### 3.4 Lo que **no** está incluido (requiere nueva decisión / presupuesto)

- Cambios del **spec jun-2026** (orden distinto de integridad, 22 judiciales, campos de salud emocional, sufijos «Explique», etc.)
- Dos servicios distintos en la misma orden
- Integración WhatsApp API

---

## 4. Comparación rápida por tipo de formulario

| Tipo | Secciones candidato | §5 candidato | Autorización | Infornet |
|------|---------------------|--------------|--------------|----------|
| **Pre-empleo** (polígrafo/VSA) | 5 | Salud + judicial + complementaria | Según servicio + consentimiento adicional | ✅ Sí |
| **Socioeconómico** | 6 | Igual base + §6 refs/bienes/presupuesto | Texto socioeconómico | ✅ Sí (formulario pre-empleo) |
| **Periódica** | 5 | **Solo judicial** | Polígrafo periódica + motivo REPRO | No |
| **Específica** | 4 (+ §5 judicial) | **Solo judicial** | Polígrafo específica + motivo REPRO | No |

**Cómo comprobar §5 periódica/específica (punto crítico):**  
En el enlace del candidato, sección 5 debe mostrar **«Aspecto judicial»** y **no** debe aparecer «Aspectos de salud» ni «Información complementaria» (salvo el campo libre final «Información adicional»).

---

## 5. Cómo comprobarlo ustedes mismos

### 5.1 Enlaces demo en producción (sin login — rol evaluado)

Abrir en celular o PC. En cada enlace ingresar **solo el DPI indicado**.

| Formulario | URL | DPI |
|------------|-----|-----|
| Pre-empleo | https://reproappv2.szystems.com/cuestionario/e1demo2026pruebamanualtokenrepr0 | `2405617300105` |
| Socioeconómico | https://reproappv2.szystems.com/cuestionario/e4demo2026pruebamanualtokenrepr0 | `2405617300205` |
| Periódica | https://reproappv2.szystems.com/cuestionario/e5demo2026periodicatokenrepr0 | `2405617300305` |
| Específica | https://reproappv2.szystems.com/cuestionario/e5demo2026especificatokenrepr0 | `2405617300405` |

**Flujo esperado (todos):**  
DPI → Instrucciones → Términos (leer + firmar) → *(solo pre-empleo/socio: Infornet)* → Secciones → Finalizar.

**Periódica / específica:** en términos debe verse el **motivo de la evaluación** registrado por REPRO (texto bajo «Por motivo de:»).

### 5.2 REPRO (panel administrativo)

Ingresar con su **usuario REPRO de producción** (no usar `admin@repro.com` / `admin1234` de demo local).

| Qué revisar | Dónde | Qué validar |
|-------------|-------|-------------|
| Lista de cuestionarios | `/cuestionarios` | Carga sin error |
| Orden demo E1 | `/ordenes/126` (aprox.) | Evaluado con enlace activo |
| Motivo evaluación E5 | Show orden → evaluado periódica/específica | Campo editable «Motivo / hecho de la evaluación» |
| Show cuestionario periódica | Buscar cuestionario periódica reciente | Pestaña §5: **solo judicial** |
| PDF cuestionario | Botón PDF en show | Misma estructura; autorización + Infornet si aplica |
| Informe Word | Botón «Descargar informe Word (.docx)» | Archivo abre en Word |
| Reportes | `/reportes/evaluaciones`, `/reportes/empresas` | Export PDF funciona |

### 5.3 Empresa cliente

Usuario empresa de producción → `/empresa/cuestionarios`

| Qué validar |
|-------------|
| Ver cuestionario completado sin campos internos REPRO |
| PDF empresa sin bloque complementaria en periódica/específica |
| `/reportes/empresas` **bloqueado** (403) — solo REPRO |

---

## 6. Lista de pruebas para el cliente (checklist UAT)

Marque ✅ al pasar. Anote ❌ con captura y sección.

### A — Evaluado (enlaces demo §5.1)

#### A.1 General (todos los enlaces)

- [ ] El enlace abre sin error 500
- [ ] DPI incorrecto muestra mensaje de error (no avanza)
- [ ] DPI correcto avanza a instrucciones
- [ ] Checkbox instrucciones + Continuar
- [ ] Términos muestran nombre, DPI y empresa del evaluado demo
- [ ] Firma digital obligatoria en términos
- [ ] Tras términos, flujo coherente (Infornet o sección 1)

#### A.2 Pre-empleo (`e1demo…` / DPI `2405617300105`)

- [ ] Autorización menciona **Polígrafo — Pre-empleo**
- [ ] Bloque **Consentimiento adicional** (polígrafo)
- [ ] Paso **Infornet** con firma reutilizada
- [ ] §1: foto, departamento/municipio (nombres con tildes correctas, ej. Sacatepéquez)
- [ ] §5: aparecen **salud**, **judicial** y **complementaria**
- [ ] No se puede abrir §5 sin completar secciones anteriores

#### A.3 Socioeconómico (`e4demo…` / DPI `2405617300205`)

- [ ] **6 secciones** en el progreso
- [ ] §6: referencias (mínimo 3), bienes, presupuesto
- [ ] §5 igual que pre-empleo (salud + judicial + complementaria)

#### A.4 Periódica (`e5demo…periodica…` / DPI `2405617300305`) — **CRÍTICO**

- [ ] Términos muestran **motivo** de la evaluación
- [ ] §2 **sin** tabla de hermanos
- [ ] §3: preguntas laborales periódicas (no las 19 de integridad pre-empleo)
- [ ] §5 título **«Antecedentes recientes»**
- [ ] §5: bloque **«Aspecto judicial»** presente
- [ ] §5: **NO** «Aspectos de salud»
- [ ] §5: **NO** «Información complementaria» (solo campo libre al final si aplica)

#### A.5 Específica (`e5demo…especifica…` / DPI `2405617300405`) — **CRÍTICO**

- [ ] Términos con motivo registrado
- [ ] §5 título **«Antecedentes relevantes»**
- [ ] §5: **solo judicial** (misma regla que periódica)
- [ ] Pregunta 1 / académico reducido según PDF específica

#### A.6 Finalización (opcional en demo si completan el formulario)

- [ ] Resumen por sección antes de enviar
- [ ] Carga opcional de documentos
- [ ] Firma final y confirmación de veracidad
- [ ] Pantalla «completado» tras envío

---

### B — REPRO (panel)

- [ ] Login con usuario real de producción
- [ ] `/cuestionarios` — listado OK
- [ ] Abrir cuestionario pre-empleo demo → 5 secciones completas en show
- [ ] Abrir cuestionario periódica → §5 admin **solo judicial**
- [ ] PDF cuestionario periódica → §5 **solo judicial**
- [ ] Editar cuestionario §1 → subir/cambiar foto
- [ ] Orden con evaluado periódica → registrar/editar **motivo/hecho**
- [ ] Descargar **informe Word (.docx)**
- [ ] Reporte evaluaciones → export PDF

---

### C — Empresa cliente

- [ ] Login empresa
- [ ] Ver cuestionario (sin campos internos: integridad oculta en vista empresa si aplica)
- [ ] PDF empresa periódica → §5 sin complementaria
- [ ] Intentar `/reportes/empresas` → acceso denegado

---

## 7. Reparaciones aplicadas en producción (2 ago 2026)

Para dejar el entorno listo para este UAT se ejecutó en servidor:

1. Re-seed de demos E1, E4, E5 (tokens y DPI de la tabla §5.1)
2. Registro de **motivo/hecho** en evaluados demo E5 periódica y específica
3. Sincronización UTF-8 del catálogo **22 departamentos / 340 municipios**
4. Verificación: demos activos, catálogo cargado, términos E5 accesibles
5. **Fechas laboradas (§3 — historial de empleos):** el campo de texto libre se reemplazó por **Desde / Hasta** (selector de fecha) y la casilla **«Sigue laborando»**. Al guardar se almacena en formato **`dd/mm/yyyy al dd/mm/yyyy`** (o `… al Actual`), como en el PDF.

---

## 8. Contacto y seguimiento

Si alguna prueba falla, envíen:

1. URL o rol (evaluado / REPRO / empresa)  
2. Número de sección o pantalla  
3. Captura de pantalla  
4. Qué esperaban según el **PDF impreso**  

Con eso se clasifica como bug de alineación PDF o como mejora futura (spec jun-2026).

---

*Documento generado tras deploy Formularios 2026-08-01 · Verificación técnica interna: `docs/business/QA_PRODUCCION_FORMULARIOS_2026-08-02.md`*
