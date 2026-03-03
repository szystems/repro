# Plan de Trabajo - Febrero 2026

## 📋 Contexto

**Fecha de Reunión con Cliente:** 4 de febrero de 2026
**Estado del Sistema:** Funcionando según lo esperado, requiere ajustes para alinear con flujo de trabajo real de REPRO.

---

## 🎯 Resumen Ejecutivo

El cliente validó que el sistema funciona correctamente pero identificó ajustes necesarios para alinear el sistema con su proceso operativo real. Se identificaron dos categorías:

1. **Ajustes Contemplados** - Refinamientos normales del desarrollo original
2. **Módulos/Funcionalidades Extras** - Nuevas funcionalidades no contempladas inicialmente

---

## 📊 Análisis del Diagrama de Flujo

### Proceso Actual de REPRO (según diagrama)

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                    FLUJO DE CITACIÓN Y PROGRAMACIÓN                         │
├─────────────────────────────────────────────────────────────────────────────┤
│                                                                             │
│  1. RECEPCIÓN                                                               │
│     └─> Recepción de solicitud (empresa)                                    │
│     └─> Validación información (empresa + candidato)                        │
│     └─> Ingreso a registro interno                                          │
│                                                                             │
│  2. DEFINICIÓN DE PROCESO                                                   │
│     └─> Polígrafo (presencial en sede)                                      │
│     └─> VSA (virtual)                                                       │
│     └─> Socioeconómico (visita domiciliar)                                  │
│                                                                             │
│  3. PROGRAMACIÓN                                                            │
│     └─> Validación espacios en agenda ← [CALENDARIO NUEVO]                  │
│     └─> Contacto al candidato                                               │
│     └─> Envío links digitales y autorizaciones                              │
│     └─> Confirmación del candidato                                          │
│     └─> Citación confirmada                                                 │
│                                                                             │
│  4. DÍA DE LA EVALUACIÓN                                                    │
│     ├─> Polígrafo/VSA:                                                      │
│     │   └─> Recepción en sede                                               │
│     │   └─> Validación documentos/formulario/autorización                   │
│     │   └─> Registro de llegada → Actualizar agenda                         │
│     │   └─> Validación políticas cliente                                    │
│     │   └─> Se realiza el proceso                                           │
│     │   └─> Traslado a operaciones                                          │
│     │                                                                       │
│     └─> Socioeconómico:                                                     │
│         └─> Entrevista virtual o presencial                                 │
│         └─> Verificación documentos                                         │
│                                                                             │
│  5. INCIDENCIAS                                                             │
│     └─> Inasistencia → Contactar → Reprogramar/Desistió                     │
│     └─> Documentos faltantes → Solicitar corrección                         │
│     └─> No responde → Avisar a empresa                                      │
│                                                                             │
│  6. CIERRE                                                                  │
│     └─> Hoja de observaciones                                               │
│     └─> Registro de incidencias                                             │
│     └─> Reporte y cierre → Informar al cliente                              │
│                                                                             │
└─────────────────────────────────────────────────────────────────────────────┘
```

### Estados Requeridos vs Actuales

#### Tabla `ordenes` - Estados

| Estado Diagrama | Estado Sistema | ¿Existe? | Acción |
|-----------------|----------------|----------|--------|
| Solicitud recibida | `solicitud` | ✅ | - |
| Validación información | - | ❌ | Agregar `validacion` |
| Registrado internamente | - | ❌ | Agregar `registrado` |
| Autorización | `autorizacion` | ✅ | - |
| Requisitos pendientes | `requisito` | ✅ | - |
| Programación | `programacion` | ✅ | - |
| En proceso | `en_proceso` | ✅ | - |
| En operaciones | - | ❌ | Agregar `operaciones` |
| Análisis | `analisis` | ✅ | - |
| Preliminar | `preliminar` | ✅ | - |
| Final | `final` | ✅ | - |
| Entregado | `entregado` | ✅ | - |
| Cancelado | `cancelado` | ✅ | - |

#### Tabla `evaluados_orden` - Estados de Evaluación

| Estado Diagrama | Estado Sistema | ¿Existe? | Acción |
|-----------------|----------------|----------|--------|
| Pendiente | `pendiente` | ✅ | - |
| Contactando | - | ❌ | Agregar `contactando` |
| Contactado | `contactado` | ✅ | - |
| Link enviado | - | ❌ | Agregar `link_enviado` |
| Confirmado | - | ❌ | Agregar `confirmado` |
| Programado | `programado` | ✅ | - |
| En sede | - | ❌ | Agregar `en_sede` |
| Documentos pendientes | - | ❌ | Agregar `docs_pendientes` |
| En proceso | `en_proceso` | ✅ | - |
| Inasistencia | - | ❌ | Agregar `inasistencia` |
| Completado | `completado` | ✅ | - |
| Reprogramado | `reprogramado` | ✅ | - |
| Cancelado | `cancelado` | ✅ | - |
| Desistió | - | ❌ | Agregar `desistio` |

---

## 📝 Lista Completa de Cambios Solicitados

### PUNTO 1: Alineación con Diagrama de Flujo
- [ ] Agregar nuevos estados a `ordenes`
- [ ] Agregar nuevos estados a `evaluados_orden`
- [ ] Lógica de transición de estados

### PUNTO 2: Sistema de Documentos del Evaluado
- [ ] Nueva tabla `evaluado_documentos`
- [ ] Tipos: antecedentes_penales, antecedentes_policiacos, cv, constancia_estudios, licencia_auto, licencia_moto, dpi_archivo, pasaporte, carta_laboral, tatuaje
- [ ] Subida desde: Empresa, REPRO, Evaluado
- [ ] Vista de documentos en cuestionario
- [ ] Verificación por REPRO

### PUNTO 3: Resultado del Cuestionario con Archivo
- [ ] Campo `resultado_archivo` en cuestionario
- [ ] Subida de archivo por REPRO
- [ ] Visualización del resultado

### PUNTO 4: Términos y Condiciones con Firma
- [ ] Pantalla de autorización al INICIO del cuestionario
- [ ] Contenido basado en `autorizacion-general.pdf`
- [ ] Checkbox de aceptación
- [ ] Firma digital en esta sección (no al final)
- [ ] Selección de proceso: Socioeconómico / VSA / Ambos

### PUNTO 5: Ajustes en Formulario de Órdenes
- [ ] `prioridad` - Solo visible para REPRO
- [ ] `fecha_limite` - Solo visible para REPRO
- [ ] Renombrar `observaciones` → `observaciones_internas` (solo REPRO)
- [ ] Agregar `requerimientos_generales` en Empresa (solo REPRO edita)
- [ ] Guardar `tipo_creador` (empresa/repro) en orden
- [ ] **Campo `observaciones` por cada evaluado**

### PUNTO 6: Ajustes en Campos
- [ ] Quitar campo `codigo_postal` (no se usa en Guatemala)
- [ ] Simplificar `estado_civil`: solo "casado" (quitar variantes)

### PUNTO 7: Campos Faltantes en Formularios
- [ ] Nombres de hijos
- [ ] Información de padres
- [ ] Campos adicionales en información laboral
- [ ] **PENDIENTE:** Esperar formularios del cliente

### PUNTO 8: Separar Estados
- [ ] `estado_formulario` - Estado del cuestionario digital
- [ ] `estado_evaluacion` - Estado de la evaluación física
- [ ] Campo `resultado` con archivo adjunto

### PUNTO 9: Módulo Calendario de Programación
- [ ] Vista mensual con contador por día
- [ ] Vista diaria por horas
- [ ] CRUD de programaciones
- [ ] Conexión con `evaluados_orden.fecha_programada`
- [ ] Filtros por tipo de servicio
- [ ] Indicadores de estado

---

## 💰 Clasificación: Contemplado vs Extra

### ✅ CONTEMPLADO (Ajustes normales del desarrollo)

Estos cambios son refinamientos esperados durante el desarrollo y pruebas:

| # | Cambio | Justificación |
|---|--------|---------------|
| 1 | Agregar estados según diagrama de flujo | Ajuste para alinear con proceso real |
| 2 | Campo observaciones por evaluado | Campo básico faltante |
| 3 | Quitar código postal | Ajuste de localización |
| 4 | Simplificar estado civil | Corrección de opciones |
| 5 | Prioridad/fecha límite solo REPRO | Ajuste de permisos |
| 6 | Renombrar observaciones → observaciones_internas | Ajuste de nomenclatura |
| 7 | Guardar tipo_creador en orden | Trazabilidad básica |
| 8 | Separar estado_formulario y estado_evaluacion | Clarificación de estados |
| 9 | Tipo formulario según tipo servicio | Regla de negocio (socio→preempleo) |
| 10 | Campos faltantes en formularios | Completar formularios existentes |

**Costo: Q0 (incluido en proyecto original)**

---

### 💎 EXTRAS (Nuevas funcionalidades no contempladas)

**NOTA:** Tras análisis detallado, se determinó que la mayoría de items originalmente considerados como extras **ya estaban contemplados** en el proyecto original.

| # | Módulo/Funcionalidad | Descripción | Estado |
|---|---------------------|-------------|--------|
| ~~E1~~ | ~~Sistema de Documentos del Evaluado~~ | Ya contemplado en flujo original | ✅ INCLUIDO |
| **E2** | **Módulo Calendario de Programación** | Sistema completo de agenda | **Q4,500 (ÚNICO EXTRA)** |
| ~~E3~~ | ~~Términos y Condiciones Digitales~~ | Ya contemplado (cuestionario legal) | ✅ INCLUIDO |
| ~~E4~~ | ~~Sistema de Resultados con Archivo~~ | Ya contemplado (entrega resultados) | ✅ INCLUIDO |

---

## 📊 Resumen de Cotización Extra (REVISADA)

| Concepto | Monto |
|----------|-------|
| **E2:** Módulo Calendario de Programación | Q4,500 |
| **TOTAL EXTRAS** | **Q4,500** |

### Comparación con Proyecto Original

| Concepto | Monto |
|----------|-------|
| Cotización original del sistema | Q14,000 |
| Extras solicitados | Q4,500 |
| **Inversión Total** | **Q18,500** |

---

## 🚫 Análisis de Integración JotForm

El cliente consultó sobre integrar con JotForm (www.jotform.com).

### Resultado del Análisis: **NO RECOMENDADO**

| Aspecto | Nuestro Sistema | JotForm |
|---------|-----------------|---------|
| Tokens únicos por evaluado | ✅ Vinculados a DPI | ❌ No existe |
| Progreso parcial | ✅ Cada sección | ❌ Solo al final |
| Firma digital | ✅ Nativa | ⚠️ Widget externo |
| Seguridad | ✅ Servidor propio | ⚠️ Servidores externos |
| Límites API | ✅ Sin límites | ⚠️ 1,000/día (básico) |
| Costo mensual | ✅ Q0 | ⚠️ $34-99/mes |

**Recomendación:** Usar JotForm como REFERENCIA para copiar campos faltantes, no como integración.

---

## 📅 Plan de Trabajo Priorizado (Según Cotización v2)

### FASE 1: Estructura y Datos (Semana 1) ✅ COMPLETADA
**Ajustes de campos, localización, reglas de negocio, trazabilidad, colores resultado**

| # | Tarea | Estado | Commit |
|---|-------|--------|--------|
| 1.1 | Quitar campo `codigo_postal` (4 archivos: vista, request, admin, pdf) | ✅ | `fase1` |
| 1.2 | Simplificar `estado_civil_detalle` (merge casado_civil/religioso → casado, quitar separado) | ✅ | `fase1` |
| 1.3 | Campo `observaciones` por evaluado — agregar a `$fillable` de EvaluadoOrden + hacer funcional | ✅ | `fase1` |
| 1.4 | Agregar `tipo_creador` (empresa/repro) a tabla `ordenes` — migración + modelo + auto-fill | ✅ | `fase1` |
| 1.5 | Renombrar `observaciones` → `observaciones_internas` en `ordenes` — migración + modelo + controller + vistas | ✅ | `fase1` |
| 1.6 | `prioridad` y `fecha_limite` solo visibles/editables para REPRO (role_as >= 2) | ✅ | `fase1` |
| 1.7 | Clasificación de colores de resultados (polígrafo/VSA + socioeconómico) — accessor + vistas | ✅ | `fase1` |
| 1.8 | Regla socioeconómico → solo formulario preempleo (validación en controller/request) | ✅ | `fase1` |
| 1.9 | Tests para todos los cambios de Fase 1 (16 tests, 40 assertions) | ✅ | `fase1` |

### FASE 2: Gestión de Documentación (Semana 2) ✅ COMPLETADA
**Documentos, términos, archivos resultado, email, rehabilitación**

| # | Tarea | Estado | Commit |
|---|-------|--------|--------|
| 2.1 | Crear tabla `documento_evaluados` + modelo DocumentoEvaluado + factory (12 tipos) | ✅ | `34639bed` |
| 2.2 | Subida de documentos desde Empresa y REPRO (admin controller + vistas) | ✅ | `34639bed` |
| 2.3 | Subida de documentos desde Evaluado (en cuestionario, página de finalización) | ✅ | `34639bed` |
| 2.4 | Pantalla de Términos y Condiciones con checkbox de aceptación | ✅ | `34639bed` |
| 2.5 | Archivos de resultado doble (preliminar + final) por evaluado | ✅ | `34639bed` |
| 2.6 | Notificación email al activar resultados visibles para empresa | ✅ | `34639bed` |
| 2.7 | Rehabilitación + deshabilitación de cuestionario completado | ✅ | `34639bed` |
| 2.8 | Tests para Fase 2 (31 tests, 92 assertions) | ✅ | `34639bed` |

#### Ajustes post-Fase 2 (mejoras UI/UX)
| # | Tarea | Estado | Detalle |
|---|-------|--------|--------|
| 2.9 | Firma solo en página de finalización (quitada de términos) | ✅ | Obs. 1 |
| 2.10 | Vista orden: evaluados + docs + resultados en acordeón unificado | ✅ | Obs. 2 |
| 2.11 | Vista cuestionario admin: sección de documentos con verificación | ✅ | Obs. 3 |
| 2.12 | Botón "Deshabilitar" cuestionario (reversa de rehabilitar) | ✅ | Obs. 4 |

### FASE 3: Módulo de Sedes (Semana 3) ✅ COMPLETADA
**CRUD completo de sedes, integración con evaluados**

| # | Tarea | Estado | Commit |
|---|-------|--------|--------|
| 3.1 | Migración tabla `sedes` + FK `sede_id` en evaluados_orden | ✅ | `b8caf8e5` |
| 3.2 | Modelo Sede con relaciones, scopes, tieneTraslape | ✅ | `b8caf8e5` |
| 3.3 | SedeFormRequest + SedesController CRUD completo | ✅ | `b8caf8e5` |
| 3.4 | 5 vistas Blade (index, create, edit, show, _form) | ✅ | `b8caf8e5` |
| 3.5 | Rutas (8) + Sidebar link | ✅ | `b8caf8e5` |
| 3.6 | EvaluadoOrden: sede_id fillable + relación | ✅ | `b8caf8e5` |
| 3.7 | Factory + 15 tests (33 assertions) | ✅ | `b8caf8e5` |

### FASE 4: Calendario y Agenda (Semana 4) — Extra E2 (Q4,500)
**Calendario de programación sobre `evaluados_orden` (sin tabla nueva) + slots de 30 min + anti-traslape**

> **Decisión arquitectónica:** Se usa directamente `evaluados_orden.fecha_programada` (convertida de `date` a `datetime`) en lugar de crear una tabla `programaciones` separada. Esto evita duplicación de datos y riesgo de desincronización. La programación se puede hacer desde el calendario Y desde la vista de la orden.

| # | Tarea | Estado | Commit |
|---|-------|--------|--------|
| 4.1 | Migración: `fecha_programada` de `date` → `datetime` + `fecha_hora_fin` + índices | ✅ | |
| 4.2 | Modelo: scopes calendario + `programarEvaluacion()` con inicio/fin/sede | ✅ | |
| 4.3 | `User::scopePoligrafistas()` + CalendarioController + ProgramarCitaRequest | ✅ | |
| 4.4 | Rutas calendario + sidebar + modal programar cita en vista orden | ✅ | |
| 4.5 | Vista mensual del calendario (contador por día, colores por tipo) | ✅ | |
| 4.6 | Vista diaria por horas (8AM-6PM, slots 30 min, colores por tipo servicio) | ✅ | |
| 4.7 | Programar desde calendario (click slot → modal asignar evaluado) | ✅ | |
| 4.8 | Filtros por tipo servicio, sede, poligrafista | ✅ | |
| 4.9 | Reprogramar + cancelar cita (ambas vistas) | ✅ | |
| 4.10 | Tests para Fase 4 (31 tests, 74 assertions) | ✅ | |

### FASE 5: Flujos y Cierre (Semana 5)
**Estados según diagrama de flujo, separación estados, QA final**

| # | Tarea | Estado | Commit |
|---|-------|--------|--------|
| 5.1 | Nuevos estados ordenes: `validacion`, `registrado`, `operaciones` | ⬜ | |
| 5.2 | Nuevos estados evaluados: `contactando`, `link_enviado`, `confirmado`, `en_sede`, `docs_pendientes`, `inasistencia`, `desistio` | ⬜ | |
| 5.3 | Lógica de transición de estados (máquina de estados) | ⬜ | |
| 5.4 | Separar `estado_formulario` / `estado_evaluacion` | ⬜ | |
| 5.5 | Actualizar vistas con nuevos estados y transiciones | ⬜ | |
| 5.6 | Pruebas integrales de todo el sistema | ⬜ | |

---

## 📈 Progreso General

| Fase | Semana | Estado | Progreso |
|------|--------|--------|----------|
| Fase 1: Estructura y Datos | Semana 1 | **✅ Completada** | **9/9** |
| Fase 2: Documentación | Semana 2 | **✅ Completada** | **12/12** |
| **Fase 3: Sedes** | **Semana 3** | **✅ Completada** | **7/7** |
| Fase 4: Calendario (E2) | Semana 4 | **✅ Completada** | **10/10** |
| Fase 5: Flujos y Cierre | Semana 5 | ⬜ Pendiente | 0/6 |

---

## 📎 Documentos de Referencia

- [Diagrama de Flujo](../diagrama%20de%20flujo/PROCESO%20DE%20CITACION%20Y%20PROGRAMACION%20(1).pdf)
- [Autorización General](../formularios/autorizacion-general.pdf)
- [Cotización v2](cotizacion_actualizada_v2_2026.md)
- **PENDIENTE:** Formularios actuales del cliente (para campos faltantes)

---

## ✅ Historial de Cambios

| Fecha | Acción | Detalle |
|-------|--------|---------|
| 2026-03-03 | Fase 4 completada | Calendario y Agenda — 31 tests, 74 assertions — hora inicio/fin, anti-traslape rangos, dual entry |
| 2026-03-03 | Inicio Fase 4 | Calendario y Agenda — decisión: usar evaluados_orden directo, slots 30 min, dual entry |
| 2026-03-02 | Ajustes UI/UX Fase 2 | Firma solo al final, acordeón evaluados, docs en cuestionario admin, botón deshabilitar |
| 2026-02-27 | Fase 2 completada | Gestión de Documentación — 31 tests, 92 assertions — commit `34639bed` |
| 2026-02-25 | Fase 3 completada | Módulo Sedes REPRO (E5) — commit `b8caf8e5` |
| 2026-02-26 | Fase 1 completada | Estructura y Datos — 16 tests, 40 assertions — commit `59f11c25` |
| 2026-02-26 | Inicio Fase 1 | Ajustes de campos, localización, reglas de negocio |
| 2026-02-04 | Documento creado | Reunión con cliente, plan inicial |

---

*Documento creado: 4 de febrero de 2026*
*Última actualización: 3 de marzo de 2026*
