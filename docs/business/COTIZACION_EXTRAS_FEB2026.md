# Cotización de Extras - REPRO (REVISADA)
## Sistema de Gestión de Evaluaciones Poligráficas

---

**Cliente:** REPRO Guatemala  
**Fecha:** 4 de febrero de 2026  
**Revisión:** 1.1 - Cotización corregida  
**Cotización Original del Sistema:** Q14,000.00

---

## 📋 Antecedentes

Durante la reunión del 4 de febrero de 2026, el cliente validó el correcto funcionamiento del sistema y solicitó ajustes y nuevas funcionalidades.

Tras un análisis detallado, se identificó que la mayoría de los cambios solicitados **ya estaban contemplados** en el alcance original. Solo se detectó **un módulo completamente nuevo**.

---

## ✅ Lo que está INCLUIDO sin costo adicional

Los siguientes módulos y ajustes son parte del desarrollo original o refinamientos normales:

### Ya Contemplados (Parte del proyecto original)

| # | Funcionalidad | Justificación |
|---|---------------|---------------|
| 1 | **Sistema de Documentos del Evaluado** | Parte del flujo de órdenes y cuestionarios |
| 2 | **Términos y Condiciones Digitales** | Parte integral del cuestionario legal |
| 3 | **Sistema de Resultados con Archivo** | Incluido en el flujo de entrega de resultados |
| 4 | **Agregar estados según diagrama de flujo** | Ajuste para alinear con proceso real |
| 5 | **Campo observaciones por evaluado** | Campo básico faltante |
| 6 | **Quitar código postal** | Ajuste de localización Guatemala |
| 7 | **Simplificar estado civil** | Corrección de opciones |
| 8 | **Prioridad/fecha límite solo REPRO** | Ajuste de permisos |
| 9 | **Renombrar observaciones → observaciones_internas** | Ajuste de nomenclatura |
| 10 | **Separar estado_formulario y estado_evaluacion** | Clarificación de estados |
| 11 | **Regla: socioeconómico → solo formulario preempleo** | Regla de negocio |
| 12 | **Agregar requerimientos_generales en empresas** | Configuración por empresa |
| 13 | **Guardar tipo_creador en orden** | Trazabilidad básica |
| 14 | **Campos faltantes en formularios** | Completar formularios existentes |

**Costo de items 1-14: Q0 (incluido en proyecto original)**

---

## 💎 Funcionalidad Extra (NUEVO)

### E1. Módulo de Calendario de Programación de Evaluaciones
**Inversión: Q4,500.00**

Este es un **módulo completamente nuevo** que no estaba contemplado en el alcance original. Consiste en un sistema completo de agenda para la programación y seguimiento de evaluaciones físicas.

**Incluye:**

#### Vista Mensual
- ✅ Calendario mensual interactivo estilo Google Calendar
- ✅ Navegación entre meses
- ✅ Contador de evaluaciones por día
- ✅ Indicadores visuales de carga de trabajo

#### Vista Diaria
- ✅ Vista por horas (8:00 AM - 6:00 PM)
- ✅ Bloques de tiempo por evaluación
- ✅ Información resumida de cada evaluación
- ✅ Click para ver detalle completo

#### Código de Colores por Tipo de Servicio
- 🔵 Polígrafo (presencial en sede)
- 🟢 VSA (virtual)
- 🟡 Socioeconómico (visita domiciliar)

#### Gestión de Programaciones
- ✅ Crear nueva programación
- ✅ Editar programación existente
- ✅ Cancelar/reprogramar
- ✅ Vinculación automática con evaluados existentes
- ✅ Selección de poligrafista disponible

#### Funcionalidades Avanzadas
- ✅ Filtros por tipo de servicio y estado
- ✅ Detección de conflictos de horario
- ✅ Vista de disponibilidad de poligrafistas
- ✅ Notificación visual de evaluaciones sin programar

#### Integración con Sistema Actual
- ✅ Conexión con `evaluados_orden.fecha_programada`
- ✅ Actualización automática de estados
- ✅ Acceso desde el menú principal (usuarios REPRO)

---

## 💰 Resumen de Inversión

| # | Concepto | Inversión |
|---|----------|-----------|
| E1 | Módulo de Calendario de Programación | Q4,500.00 |
| | **TOTAL EXTRAS** | **Q4,500.00** |

---

## 📊 Inversión Total del Proyecto

| Concepto | Monto |
|----------|-------|
| Sistema original | Q14,000.00 |
| Módulo Calendario (EXTRA) | Q4,500.00 |
| **INVERSIÓN TOTAL** | **Q18,500.00** |

---

## ⏱️ Tiempo de Entrega

**Estimación total:** 3 semanas a partir de la aprobación

| Fase | Duración | Entregables |
|------|----------|-------------|
| **Fase 1** | 1 semana | Ajustes contemplados (estados, campos, permisos) |
| **Fase 2** | 1.5 semanas | Módulo Calendario de Programación |
| **Fase 3** | 0.5 semana | Sistema de documentos + términos + resultados |

---

## 🚫 Sobre la Integración con JotForm

El cliente consultó sobre la posibilidad de integrar el sistema con JotForm.

### Análisis Técnico

Después de revisar la API de JotForm, se determinó que **NO ES RECOMENDABLE** la integración por las siguientes razones:

| Aspecto | Nuestro Sistema | JotForm |
|---------|-----------------|---------|
| **Tokens únicos por evaluado** | ✅ Sí, vinculados a DPI | ❌ No tiene este concepto |
| **Progreso parcial** | ✅ Guarda cada sección | ❌ Envía todo al final |
| **Firma digital integrada** | ✅ Nativa | ⚠️ Requiere widget externo |
| **Control de seguridad** | ✅ Todo en nuestro servidor | ⚠️ Datos en servidores externos |
| **Límites** | ✅ Sin límites | ⚠️ 1,000 requests/día (plan básico) |
| **Costo mensual** | ✅ Q0 adicional | ⚠️ $34-$99/mes según plan |
| **Dependencia** | ✅ Independiente | ⚠️ Si JotForm falla, afecta operación |

### Recomendación

**NO integrar JotForm**, pero SÍ usar los formularios existentes en JotForm como **referencia** para:
1. Identificar campos faltantes
2. Copiar la estructura de preguntas
3. Implementarlos en nuestro sistema propio

Esto se puede hacer **sin costo adicional** como parte del item 14 (campos faltantes en formularios).

---

## 📝 Condiciones

1. **Forma de pago:** 50% al aprobar, 50% al entregar
2. **Validez de cotización:** 30 días
3. **Garantía:** 30 días después de entrega para corrección de errores
4. **No incluye:** Hosting, dominio, capacitación adicional

---

## ✅ Para Aprobar

- [ ] Aprobación del Módulo Calendario de Programación (Q4,500.00)
- [ ] Confirmación para iniciar los ajustes contemplados (sin costo)
- [ ] Acceso a JotForm para revisar campos faltantes en formularios

---

**Atentamente,**

*Equipo de Desarrollo*

---

*Documento generado: 4 de febrero de 2026*
*Revisión: 1.1 - Cotización corregida tras análisis detallado*
