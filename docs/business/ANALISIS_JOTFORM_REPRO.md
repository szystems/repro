# ANÁLISIS DE VIABILIDAD: INTEGRACIÓN CON API DE JOTFORM

---

**REPRO Guatemala**  
**Atención:** Licda. Estephany Castro  
**Fecha:** 4 de febrero de 2026  
**Documento:** TECH-REPRO-2026-001  
**Asunto:** Evaluación Técnica de Integración con JotForm  

---

## Resumen Ejecutivo

Se evaluó la viabilidad de integrar el Sistema de Gestión de Evaluaciones Poligráficas de REPRO con la plataforma JotForm (jotform.com) para el manejo de cuestionarios. 

**Conclusión: NO SE RECOMIENDA la integración directa con JotForm.**

Sin embargo, **SÍ utilizaremos los formularios existentes de JotForm como referencia** para asegurar que nuestro sistema incluya todos los campos necesarios que actualmente manejan en sus cuestionarios.

---

## 1. CONTEXTO DEL ANÁLISIS

### Situación Actual
REPRO utiliza actualmente formularios de JotForm para recopilar información de los evaluados antes de sus citas. Estos formularios funcionan de manera independiente al nuevo sistema en desarrollo.

### Requerimiento Evaluado
Se analizó la posibilidad de conectar el sistema desarrollado directamente con JotForm para:
- Sincronizar respuestas automáticamente
- Continuar usando los formularios existentes de JotForm
- Evitar duplicar la captura de datos

---

## 2. HALLAZGOS TÉCNICOS

### 2.1 Ausencia de Sistema de Tokens

**Problema Crítico:** El API de JotForm no soporta un sistema de tokens únicos para vincular respuestas con registros específicos del sistema.

| Característica | Nuestro Sistema | JotForm |
|----------------|-----------------|---------|
| Enlace único por evaluado | ✅ Soportado | ❌ No soportado |
| Control de acceso por token | ✅ Implementado | ❌ No disponible |
| Vinculación automática | ✅ Directa | ❌ Requiere procesos manuales |
| Expiración de acceso | ✅ Configurable | ❌ No aplica |

**Impacto:** Sin tokens, no hay forma confiable de vincular automáticamente las respuestas de JotForm con el evaluado correcto en nuestra base de datos.

### 2.2 No Hay Guardado de Progreso Parcial

**Problema:** JotForm no guarda respuestas parciales automáticamente.

- Si el evaluado cierra el navegador accidentalmente, pierde todo su progreso
- Formularios largos (como los de REPRO) requieren tiempo considerable
- Nuestro sistema guarda automáticamente cada respuesta

### 2.3 Dependencia de Servicio Externo

| Riesgo | Descripción |
|--------|-------------|
| Disponibilidad | Si JotForm tiene problemas, el sistema REPRO se afecta |
| Cambios de API | JotForm puede cambiar su API sin previo aviso |
| Términos de servicio | Pueden cambiar políticas que afecten la integración |
| Seguridad | Los datos sensibles viajan a servidores externos |

### 2.4 Costos Recurrentes

Para un uso profesional con el volumen de REPRO, JotForm requiere planes pagados:

| Plan | Costo Mensual | Envíos/mes |
|------|---------------|------------|
| Bronze | $34 USD | 25 |
| Silver | $39 USD | 100 |
| Gold | $99 USD | 1,000 |

**Costo anual estimado:** $408 - $1,188 USD adicionales

### 2.5 Límites del API

- 1,000 llamadas diarias al API (plan gratuito)
- Límites más altos requieren planes empresariales
- Restricciones de ancho de banda

---

## 3. COMPARATIVA DE OPCIONES

| Aspecto | Integración JotForm | Sistema Propio |
|---------|---------------------|----------------|
| Costo inicial | Requiere desarrollo adicional | Ya incluido |
| Costo mensual | $34-99 USD | Q0 |
| Vinculación con evaluados | Compleja, poco confiable | Automática y directa |
| Guardado parcial | No disponible | Incluido |
| Control de datos | Servidores externos | Base de datos propia |
| Personalización | Limitada | Total |
| Mantenimiento | Depende de terceros | Control total |
| Seguridad | Datos en servidores USA | Datos en servidor propio |

---

## 4. NUESTRA RECOMENDACIÓN

### Lo que SÍ haremos:

✅ **Usar los formularios de JotForm como referencia**
- Revisaremos cada campo de sus formularios actuales
- Agregaremos todos los campos faltantes a nuestro sistema
- Garantizaremos paridad de información

✅ **Mantener la funcionalidad actual mejorada**
- Los cuestionarios funcionan con tokens únicos
- Guardado automático de progreso
- Reportes integrados
- Control total de datos

### Lo que NO recomendamos:

❌ **Integración directa con API de JotForm**
- Complejidad técnica innecesaria
- Costos recurrentes evitables
- Dependencia de servicio externo
- Riesgos de seguridad adicionales

---

## 5. PROCESO PROPUESTO

Para garantizar que el sistema capture toda la información que actualmente manejan en JotForm:

```
1. REPRO proporciona acceso a formularios JotForm actuales
                    ↓
2. Documentamos todos los campos existentes
                    ↓
3. Comparamos con campos actuales del sistema
                    ↓
4. Agregamos campos faltantes
                    ↓
5. Validamos con REPRO que no falte nada
                    ↓
6. Sistema completo sin depender de JotForm
```

---

## 6. BENEFICIOS DE NUESTRA PROPUESTA

| Beneficio | Descripción |
|-----------|-------------|
| **Sin costos adicionales** | No hay suscripciones mensuales |
| **Datos seguros** | Información sensible en su servidor |
| **Integración nativa** | Respuestas vinculadas automáticamente |
| **Guardado automático** | El evaluado nunca pierde progreso |
| **Reportes unificados** | Todo en un solo sistema |
| **Independencia total** | Sin depender de terceros |

---

## 7. CONCLUSIÓN

La integración directa con JotForm introduciría complejidad, costos y riesgos innecesarios cuando ya contamos con un sistema de cuestionarios robusto y funcional.

**Nuestra propuesta:** Utilizar los formularios de JotForm únicamente como referencia para completar los campos faltantes en nuestro sistema, logrando así la misma captura de información sin las desventajas de una integración externa.

---

## 8. SIGUIENTE PASO

Para avanzar con esta propuesta, solicitamos:

1. **Acceso de lectura a los formularios de JotForm** para documentar todos los campos
2. **Confirmación de que este enfoque es aceptable** para REPRO

---

**Equipo de Desarrollo**  
Guatemala, 4 de febrero de 2026

---

*Documento: TECH-REPRO-2026-001*  
*Clasificación: Análisis Técnico*  
*Versión: 1.0*
