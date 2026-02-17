# COTIZACIÓN ACTUALIZADA - REPRO Guatemala

**Fecha:** 06/02/2026  
**Versión:** 2 (Actualización de cotización del 04/02/2026)  
**Empresa:** REPRO Guatemala  
**Atención:** Licda. Estephany Castro  
**Referencia:** Sistema de Gestión de Evaluaciones Poligráficas  

---

## 1. FUNCIONALIDADES INCLUIDAS SIN COSTO ADICIONAL

Tras el análisis detallado de los requerimientos (reunión del 4 de febrero 2026 y retroalimentación posterior del 6 de febrero 2026), las siguientes funcionalidades están contempladas en el alcance original y se implementarán **sin costo adicional**:

| No. | Funcionalidad | Descripción Detallada |
|-----|--------------|----------------------|
| 1 | Sistema de Documentos | Módulo para carga de antecedentes, CV, licencias, DPI y cartas laborales. |
| 2 | Términos y Condiciones | Pantalla de autorización obligatoria con captura de firma digital. |
| 3 | Gestión de Resultados | Sistema de carga, almacenamiento y visualización de archivos de resultados. |
| 4 | Flujo de Estados | Implementación de nuevos estados basados en el diagrama de proceso real. |
| 5 | Observaciones por Evaluado | Campo de texto libre para notas específicas de cada candidato. Editable solo por usuarios REPRO. Visible para empresa (solo lectura). Permite detallar situaciones como cancelaciones, avisos o cualquier comunicación hacia el cliente. |
| 6 | Localización (Guatemala) | Eliminación del campo de Código Postal por falta de aplicabilidad técnica. |
| 7 | Optimización de Datos | Simplificación del catálogo de opciones para el Estado Civil. |
| 8 | Control de Prioridades | Permisos de visualización de prioridad y fechas límite exclusivos para REPRO. |
| 9 | Bitácora Interna | Campo de observaciones restringido para uso administrativo interno. |
| 10 | Segmentación de Estados | Separación lógica entre el avance del formulario y el estatus de evaluación. |
| 11 | Lógica de Negocio | Restricción: el estudio Socioeconómico solo permite formulario Pre-empleo. |
| 12 | Configuración por Cliente | Personalización de requerimientos generales según la empresa contratante. |
| 13 | Trazabilidad de Órdenes | Registro automático del usuario creador de la orden para auditoría. |
| 14 | Integridad de Datos | Incorporación de campos faltantes basados en formularios de referencia. |
| 15 | **Notificación Email al Activar Resultados** | Al activar el switch de "Resultados Visibles para Empresa", se envía automáticamente un correo electrónico a los usuarios de esa empresa notificando que los resultados ya están disponibles para su visualización. |
| 16 | **Rehabilitación de Cuestionario** | Los usuarios REPRO podrán volver a habilitar un cuestionario ya completado y bloqueado, permitiendo que el evaluado corrija o edite sus datos. Se regenera el acceso por token con nueva fecha de expiración. |
| 17 | **Archivos de Resultado Doble** | Dos campos de archivo por evaluado: (1) resultado preliminar y (2) resultado final. Ambos archivos estarán disponibles para la empresa/cliente cuando el switch de resultados esté activado. |
| 18 | **Clasificación de Colores de Resultados** | Implementación de la clasificación oficial de REPRO con colores reservados exclusivamente para resultados de evaluaciones (NO se usan en calendario ni otros módulos): |

### Clasificación de Resultados - Polígrafo y VSA

| Clasificación | Descripción | Color |
|--------------|-------------|-------|
| Aprobado / Sin Observaciones | No hay indicación de mentira. El evaluado cumple con los criterios de confiabilidad y seguridad. | 🟢 Verde |
| Aprobado / Con Observación Leve | No hay indicación de mentira. Se identifican observaciones menores que no afectan el nivel de confiabilidad, pero requieren seguimiento. | 🟡 Amarillo |
| Aprobado con Excepción | No hay indicación de mentira. Se identifican aspectos específicos que deben ser analizados antes de una decisión final. | 🟡 Amarillo |
| No Aprobado / Indicación de Mentira | Se detectó carga emocional y reacciones psicofisiológicas relevantes indicadores a mentira. | 🔴 Rojo |

### Clasificación de Resultados - Socioeconómico

| Clasificación | Descripción | Color |
|--------------|-------------|-------|
| Tipo A | Cumple con los requisitos; no se detectaron inconsistencias o riesgos relevantes. | 🟢 Verde |
| A - Condicionado | El candidato cumple con los requisitos generales; sin embargo, presenta información pendiente de validar u observaciones que deben ser consideradas. | 🟡 Amarillo |
| Tipo B | Presenta aspectos que requieren análisis y seguimiento antes de tomar una decisión. | 🟠 Naranja |
| Tipo C | Se identificaron inconsistencias, riesgos o condiciones que no cumplen con los criterios establecidos. | 🔴 Rojo |

**Inversión ítems 1-18: Q0.00 (incluido en proyecto original)**

---

## 2. MÓDULO ADICIONAL: CALENDARIO DE PROGRAMACIÓN DE EVALUACIONES (E2)

Este módulo no estaba contemplado en el alcance original y consiste en un sistema completo de agenda para la programación y seguimiento de evaluaciones físicas.

### Características Incluidas:

**Vista Mensual**
- Calendario interactivo estilo Google Calendar
- Navegación entre meses
- Contador de evaluaciones programadas por día
- Indicadores visuales de carga de trabajo

**Vista Diaria**
- Visualización por horas (8:00 AM - 6:00 PM)
- Bloques de tiempo por cada evaluación
- Información resumida de cada cita
- Acceso al detalle completo con un click

**Identificación por Tipo de Servicio** *(colores diferentes a los de resultados)*
- Polígrafo: Color azul (presencial en sede)
- VSA: Color verde agua / teal (virtual)
- Socioeconómico: Color púrpura (visita domiciliar)

**Gestión de Programaciones**
- Crear, editar y cancelar programaciones
- Reprogramación de citas
- Vinculación automática con evaluados existentes
- Asignación de poligrafista/evaluador

**Funcionalidades Avanzadas**
- Filtros por tipo de servicio y estado
- Detección de conflictos de horario
- Vista de disponibilidad por poligrafista
- Alertas de evaluaciones sin programar

**Inversión: Q4,500.00**

---

## 3. MÓDULO ADICIONAL: GESTIÓN DE SEDES REPRO (E5 — NUEVO)

Módulo complementario al Calendario de Programación que permite administrar múltiples sedes físicas de REPRO y controlar la programación de evaluaciones por sede y evaluador, evitando conflictos de horario.

### Características Incluidas:

**Administración de Sedes (CRUD Completo)**
- Crear, editar, activar/desactivar y eliminar sedes de REPRO
- Datos de cada sede: nombre, dirección, teléfono, capacidad, estado (activa/inactiva)
- Listado de sedes con filtros y búsqueda

**Integración con Evaluados y Órdenes**
- Campo de sede asignada en cada evaluado/programación
- Campo de evaluador asignado en cada evaluado/programación
- Filtros por sede en listados de evaluados y órdenes

**Integración con Módulo de Calendario**
- Calendarización de evaluaciones por sede y evaluador
- Vista de calendario filtrable por sede
- Vista de disponibilidad por sede y evaluador

**Sistema de Validación Anti-Traslape**
- ❌ **Misma sede + misma hora + mismo evaluador** → Conflicto (NO se permite)
- ✅ **Misma hora + diferente sede** → Permitido (son ubicaciones distintas)
- ✅ **Misma sede + misma hora + diferente evaluador** → Permitido (pueden atender en paralelo)
- Alertas visuales cuando se detecta un conflicto al programar

**Migraciones y Estructura de Datos**
- Nueva tabla `sedes` con campos completos
- Campos `sede_id` y `evaluador_id` agregados a tabla de evaluados
- Relaciones Eloquent entre Sede ↔ Evaluados ↔ Calendario
- Seeders y factories para datos de prueba

**Inversión: Q3,500.00**

---

## 4. RESUMEN DE INVERSIÓN

| Concepto | Detalle | Monto |
|----------|---------|-------|
| Desarrollo de Sistema Original | Valor total de la plataforma base | Q 14,000.00 |
| Actualización de Funcionalidades (1-18) | Implementación de ítems incluidos | INCLUIDO |
| E2: Módulo de Calendario de Programación | Herramienta de programación y agenda | Q 4,500.00 |
| **E5: Módulo de Sedes REPRO** | **Gestión de sedes + integración calendario + anti-traslape** | **Q 3,500.00** |
| | | |
| **SUBTOTAL PROYECTO** | Suma de servicios solicitados | **Q 22,000.00** |
| ABONO PREVIO | Pago realizado (Anticipo/Saldo cubierto) | - Q 4,500.00 |
| | | |
| **SALDO TOTAL A PAGAR** | Monto final pendiente | **Q 17,500.00** |

> **Nota:** Los extras E1 (Documentos Q3,500), E3 (Términos y Condiciones Q1,500) y E4 (Resultados con Archivo Q1,200) de la cotización anterior están ahora **incluidos sin costo** en los ítems 1-18 como parte del alcance original.

---

## 5. TIEMPO DE ENTREGA

**Estimación total aproximada: 5 semanas a partir de la aprobación**

| Fase | Período | Descripción de Entregables |
|------|---------|---------------------------|
| Fase 1: Estructura y Datos | Semana 1 | Ajustes de campos (Guatemala), optimización de estado civil, reglas de negocio, trazabilidad de órdenes, campo observaciones por evaluado, clasificación de colores de resultados. |
| Fase 2: Gestión de Documentación | Semana 2 | Implementación del sistema de carga de documentos (CV, DPI, etc.), términos y condiciones con firma digital, archivos de resultado doble (preliminar + final), notificación email al activar resultados, rehabilitación de cuestionarios. |
| Fase 3: Módulo de Sedes | Semana 3 | CRUD completo de sedes, campos de sede y evaluador en evaluados, migraciones, seeders y relaciones. |
| Fase 4: Calendario y Agenda | Semana 4 | Desarrollo e integración del Módulo de Calendario de Programación, integración con sedes, sistema anti-traslape, configuración de prioridades para usuarios REPRO. |
| Fase 5: Flujos y Cierre | Semana 5 | Configuración final de estados según diagrama de flujo, separación de estados de formulario/evaluación, control de calidad final, pruebas de integración. |

---

## 6. CONDICIONES COMERCIALES

1. **Forma de pago:** 50% al aprobar la cotización, 50% al entregar
2. **Validez:** Esta cotización es válida por 30 días calendario
3. **Garantía:** 30 días después de la entrega para corrección de errores
4. **No incluye:** Hosting, dominio ni capacitación adicional

---

## 7. SOBRE LA INTEGRACIÓN CON JOTFORM

Respecto a la consulta sobre la posible integración con JotForm, se preparó un documento técnico separado con el análisis detallado de viabilidad. La conclusión es que no es recomendable la integración directa, pero sí se utilizarán los formularios existentes como referencia para completar los campos faltantes en el sistema.

---

## 8. PARA APROBAR

Para iniciar el desarrollo, se solicita:

- [ ] Aprobación del Módulo Calendario de Programación (Q4,500.00)
- [ ] Aprobación del Módulo de Sedes REPRO (Q3,500.00)
- [ ] Confirmación para iniciar los ajustes incluidos (sin costo)
- [ ] Acceso a JotForm para revisar campos faltantes en formularios

---

**Atentamente,**

**Otto Szarata**  
Szystems  
Teléfono: 42153288
