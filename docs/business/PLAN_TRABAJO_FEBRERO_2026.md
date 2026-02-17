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

## 📅 Plan de Trabajo Priorizado

### FASE 1: Crítica (Semana 1-2)
**Prioridad: Diagrama de Flujo + Calendario**

| Día | Tarea | Estado |
|-----|-------|--------|
| 1-2 | Migraciones: nuevos estados orden/evaluado | ⬜ |
| 3-4 | Actualizar modelos y controladores con nuevos estados | ⬜ |
| 5-6 | Crear tabla `programaciones` para calendario | ⬜ |
| 7-8 | Vista mensual del calendario | ⬜ |
| 9-10 | Vista diaria por horas | ⬜ |
| 11-12 | CRUD de programaciones | ⬜ |
| 13-14 | Pruebas y ajustes calendario | ⬜ |

### FASE 2: Documentos y Términos (Semana 3)

| Día | Tarea | Estado |
|-----|-------|--------|
| 15-16 | Crear tabla `evaluado_documentos` | ⬜ |
| 17-18 | Subida de documentos (empresa/repro) | ⬜ |
| 19-20 | Subida de documentos (evaluado en cuestionario) | ⬜ |
| 21 | Pantalla de términos y condiciones | ⬜ |

### FASE 3: Resultados y Ajustes (Semana 4)

| Día | Tarea | Estado |
|-----|-------|--------|
| 22-23 | Sistema de resultados con archivo | ⬜ |
| 24-25 | Ajustes en formularios (campos faltantes) | ⬜ |
| 26-27 | Ajustes de permisos y nomenclatura | ⬜ |
| 28 | Pruebas integrales | ⬜ |

---

## 📎 Documentos de Referencia

- [Diagrama de Flujo](../diagrama%20de%20flujo/PROCESO%20DE%20CITACION%20Y%20PROGRAMACION%20(1).pdf)
- [Autorización General](../formularios/autorizacion-general.pdf)
- **PENDIENTE:** Formularios actuales del cliente (para campos faltantes)

---

## ✅ Próximos Pasos

1. **Aprobación del cliente** de la cotización de extras
2. **Recibir formularios** del cliente para identificar campos faltantes
3. **Iniciar Fase 1** con migraciones y calendario

---

*Documento creado: 4 de febrero de 2026*
*Última actualización: 4 de febrero de 2026*
