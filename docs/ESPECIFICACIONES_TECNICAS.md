# Especificaciones Técnicas: Sistema de Gestión de Pruebas de Personal

**Para:** Equipo de Desarrollo (Agente de Programación)  
**De:** Gestión de Proyecto  
**Asunto:** Requerimientos funcionales para el sistema de gestión de evaluaciones "Repro"  
**Fecha:** 11 de noviembre de 2025

---

## 🎯 1. Objetivo del Sistema

El objetivo es desarrollar una aplicación que permita a una "Empresa" gestionar el proceso completo de evaluaciones de personal (candidatos o "entrevistados"). El sistema debe automatizar la recopilación de datos, gestionar diferentes tipos de pruebas (Polígrafo, IVSAC, Socioeconómico) y mostrar un seguimiento en tiempo real del estado del proceso.

---

## ⚙️ 2. Módulos y Funcionalidad General

### 2.1 Módulo de Inicio (Formulario Básico)

El flujo principal comienza con un **"Formulario Básico"**.

**Función Clave:** "generar Código" para el proceso o candidato.

**Campos Requeridos:**
- Nombre
- Teléfonos (marcados como "obligatorios")
- DPI (Documento Personal de Identificación - 13 dígitos)
- Correo
- Puesto

**Acciones Requeridas:**
- Debe permitir la carga (o enlace) de documentos: CV, Cartas laborales, Constancia de Estudios
- Debe tener una función para "Bloquear formulario"
- Debe consultar "Antecedentes"

---

## 📋 3. Modelos de Datos: Tipos de Servicio y Formularios

El sistema debe manejar **3 servicios principales**, cada uno con subtipos que determinan el formulario a utilizar.

### A. Servicio 1: Prueba de Polígrafo (Presencial)

- **Subtipo: Preempleo**
  - Formulario Requerido: Formulario "Preempleo" (1 formulario)

- **Subtipo: Periódica / Interno**
  - Formulario Requerido: Formulario "Periódica/Específica"

- **Subtipo: Específica**
  - Formulario Requerido: Formulario "Periódica/Específica"

### B. Servicio 2: IVSAC

**Descripción:** Se menciona como "Análisis de estrés"

- **Subtipo: Preempleo**
  - Formulario Requerido: Formulario "Preempleo"

- **Subtipo: Periódica / Por Proceso Interno**
  - Formulario Requerido: Formulario "Periódica/Específica"

### C. Servicio 3: Estudio Socioeconómico

- **Subtipo: (Único)**
  - Formulario Requerido: "Estudio Socioeconómico"
  
**Nota de implementación:** Este formulario es "igual al de Preempleo con algunos datos extra". Se puede implementar usando herencia de modelo o un formulario base extendido.

### Resumen de Formularios de ENTRADA (Input):
1. Formulario Preempleo
2. Formulario Periódica/Específica
3. Formulario Estudio Socioeconómico

### Resumen de Formularios de SALIDA (Output/Reportes):
4. Preliminar General
5. Reporte (Final)

---

## ⏳ 4. Flujo de Proceso (Línea de Tiempo)

El sistema debe tener un dashboard o vista que muestre "una lista de Pasos de Como va el Proceso Con Horarios, etc.". Este es el flujo de estados requerido:

### Estados del Proceso:

1. **Solicitud:** El proceso es creado (probablemente al llenar el formulario básico)

2. **Autorización:** Un estado de espera de aprobación

3. **Requisito:** Requiere un "documento firmado"

4. **Programación:** Se asigna fecha y hora
   - **Lógica:** El sistema debe manejar dos tipos de programación: "Virtual" y "Presencial"
   - **Campos:** fecha y hora

5. **Realización de la Prueba:** El estado en que la prueba está ocurriendo

6. **Informe:** El estado de generación de resultados
   - **Sub-estado 1:** "Preliminar" (Corresponde al formulario de salida 4)
   - **Sub-estado 2:** "Reporte final" (Corresponde al formulario de salida 5)

7. **Observaciones:** Un campo o módulo para añadir notas generales al proceso

---

## 🔐 5. Sistema de Acceso para Evaluados

### 5.1 Arquitectura de Evaluados

**IMPORTANTE:** Los evaluados NO son usuarios del sistema con cuentas permanentes.

- **No tienen cuenta ni password**
- **No pueden hacer login tradicional**
- **Acceden mediante token único temporal**
- **Se identifican por DPI (único en Guatemala)**

### 5.2 Modelo de Datos para Evaluados

**Tabla:** `evaluados_orden`

**Campos principales:**
- `id` - Identificador único del registro
- `orden_id` - Relación con la orden de evaluación
- `nombre` - Nombre completo del evaluado
- `email` - Email de contacto
- `dpi` - Documento Personal de Identificación (13 dígitos)
- `token_unico` - Token de 64 caracteres (único, temporal)
- `token_expira_at` - Fecha de expiración del token (30 días)
- `cuestionario_completado` - Boolean si completó el cuestionario
- `completado_at` - Timestamp de finalización

### 5.3 Sistema de Tokens

- **Generación:** Token único de 64 caracteres
- **Expiración:** 30 días desde creación
- **Uso único:** El token se invalida después del uso
- **URL de acceso:** `{APP_URL}/cuestionario/{token}`

### 5.4 Privacidad y Historial

- **DPI como identificador único:** Permite rastrear historial de una persona
- **Privacidad por empresa:**
  - Las empresas solo ven evaluados de SUS órdenes
  - Repro y Administradores ven historial completo por DPI
- **Historial completo:** Una persona puede tener múltiples evaluaciones en diferentes empresas

---

## 🏢 6. Sistema de Roles y Permisos

### 6.1 Roles Activos

1. **Administrador** (`role_as = 3`)
   - Acceso total al sistema
   - Gestión de usuarios y configuraciones

2. **Repro** (`role_as = 2`)
   - Personal interno de Repro
   - Puede ver historial completo de evaluados
   - Autoriza visualización de informes

3. **Empresa** (`role_as = 1`)
   - Clientes de Repro
   - Solo ven sus propias órdenes y evaluados
   - Pueden ver informes autorizados por Repro

### 6.2 Autorización de Informes

- Los informes finales son generados por Repro
- Las empresas pueden ver los informes **solo si un trabajador de Repro autoriza** la visualización
- Sistema de aprobación interno antes de entregar resultados al cliente

---

## 📊 7. Estados y Seguimiento de Órdenes

### 7.1 Dashboard de Seguimiento

El sistema debe mostrar una línea de tiempo visual con:
- Lista de pasos del proceso
- Estado actual de cada orden
- Horarios y fechas programadas
- Progreso en tiempo real

### 7.2 Estados Detallados

Cada orden debe tener un estado claramente definido que permita:
- Seguimiento por parte del cliente
- Gestión interna por Repro
- Identificación de cuellos de botella
- Reportes de rendimiento

---

## 🔧 8. Consideraciones Técnicas

### 8.1 Seguridad

- Tokens con expiración automática
- Validación de DPI guatemalteco (13 dígitos)
- Encriptación de datos sensibles
- Logs de acceso por evaluado

### 8.2 Escalabilidad

- Sistema preparado para múltiples empresas clientes
- Base de datos optimizada para consultas por DPI
- Índices en campos de búsqueda frecuente

### 8.3 Usabilidad

- Interfaz intuitiva para evaluados (no técnicos)
- Dashboard claro para empresas
- Panel administrativo completo para Repro

---

## 📝 9. Notas de Implementación

### 9.1 Migración de Datos

- **BREAKING CHANGE:** Los evaluados ya no son usuarios del sistema
- Migración de datos existentes de `users` tabla a `evaluados_orden`
- Actualización de todas las vistas para remover opciones de evaluados como usuarios

### 9.2 Formularios Dinámicos

- Sistema flexible para diferentes tipos de formularios
- Herencia o composición para formularios similares
- Validaciones específicas por tipo de servicio

### 9.3 Integración de Documentos

- Almacenamiento seguro de documentos adjuntos
- Sistema de enlaces para CV, cartas laborales, etc.
- Gestión de versiones de documentos

---

**Estado del Documento:** ✅ Implementación en progreso  
**Próximo Hito:** Desarrollo del módulo de órdenes y formularios dinámicos  
**Fecha de Actualización:** 11 de noviembre de 2025