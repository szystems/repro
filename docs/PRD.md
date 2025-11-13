# PRD - Sistema de Gestión de Pruebas de Polígrafo REPRO

## 📋 Información del Proyecto

**Nombre:** REPRO - Sistema de Gestión de Evaluaciones Poligráficas
**Cliente:** REPRO Guatemala
**Versión Actual:** 2.0.0 (Laravel 12)
**Fecha de Inicio:** Proyecto original Laravel 8
**Última Actualización:** 8 de noviembre de 2025

---

## 🎯 Visión del Producto

### Descripción General
Sistema web integral para digitalizar y automatizar el proceso completo de pruebas de polígrafo para empresas clientes, desde la solicitud hasta la entrega de resultados.

### Problema que Resuelve
- **Antes:** Proceso manual en papel, pérdida de documentos, falta de trazabilidad
- **Después:** Sistema digital completo con trazabilidad, reportes automáticos y acceso 24/7

### Objetivos del Negocio
1. Reducir tiempo de procesamiento de órdenes en 60%
2. Eliminar pérdida de documentos físicos
3. Permitir autoservicio a clientes (solicitar, consultar, descargar)
4. Mejorar experiencia del evaluado con cuestionario digital
5. Generar reportes automáticos y estadísticas

---

## 👥 Usuarios del Sistema

### 1. Administrador (role_as = 3, role: 'admin')
**Descripción:** Personal técnico de REPRO con acceso total al sistema

**Necesidades:**
- Gestionar empresas clientes
- Gestionar usuarios de todos los tipos
- Configurar el sistema
- Ver reportes completos
- Gestionar roles y permisos

**Permisos:** TODOS (25 permisos)

### 2. Personal REPRO (role_as = 2, role: 'repro')
**Descripción:** Poligrafistas y personal operativo que realizan las pruebas

**Necesidades:**
- Ver órdenes asignadas
- Crear y gestionar evaluaciones
- Registrar resultados de pruebas
- Ver cuestionarios completados
- Generar reportes

**Permisos:** 13 permisos
- `ordenes.ver`
- `evaluaciones.*` (todos)
- `resultados.*` (todos)
- `cuestionarios.ver`
- `empresas.ver`
- `usuarios.ver`
- `reportes.*` (todos)

### 3. Usuario Empresa (role_as = 1, role: 'empresa')
**Descripción:** Personal de empresas clientes que solicitan pruebas

**Necesidades:**
- Crear órdenes de pruebas
- Ver estado de sus órdenes
- Agregar personas a evaluar
- Descargar resultados de pruebas
- Gestionar usuarios de su empresa

**Permisos:** 7 permisos
- `ordenes.ver`, `ordenes.crear`
- `evaluaciones.ver`
- `resultados.ver`, `resultados.descargar`
- `usuarios.ver`, `usuarios.crear`, `usuarios.editar`

### 4. Persona Evaluada (⚠️ SIN CUENTA DE USUARIO)
**Descripción:** Personas que se realizarán la prueba de polígrafo

**🔄 CAMBIO IMPORTANTE:**
- **NO tienen cuenta en el sistema**
- **NO tienen usuario ni contraseña**
- **NO pueden iniciar sesión**
- **NO tienen panel de control**

**Método de Acceso:**
- Reciben link único por email/SMS con token temporal
- Acceso directo al formulario sin autenticación
- Token de un solo uso que expira al completar

**Identificación Única:**
- **DPI (Documento Personal de Identificación)** es el identificador único
- Repro usa DPI para rastrear historial completo del evaluado
- Permite ver evaluaciones previas de la misma persona
- **Las empresas NO tienen acceso a historial previo**

**Proceso de Completado:**
1. Empresa o Repro crea orden → ingresa datos básicos (nombre, email, DPI)
2. Sistema genera link único con token
3. Evaluado recibe email con link
4. Evaluado completa formulario sin login
5. Al finalizar → formulario se bloquea permanentemente
6. No puede volver a acceder al formulario

**Restricciones:**
- No pueden ver resultados de su evaluación
- No pueden consultar estado de su orden
- Solo acceso temporal a vistas públicas del cuestionario

---

**Usuario Principal vs Secundario (Solo aplica a usuarios Empresa):**
- **Principal (principal = 1):** Primer usuario de la empresa, puede gestionar otros usuarios
- **Secundario (principal = 0):** Usuarios adicionales con permisos limitados

---

## 🔄 Flujo Principal del Sistema

### Flujo Completo de una Evaluación

```
1. CREACIÓN DE ORDEN
   ├─> Usuario Empresa crea orden → auto-asignada a su empresa
   │   └─> Selecciona tipo de servicio (Polígrafo/VSA/Socioeconómico)
   │   └─> Selecciona tipo de formulario (Pre-empleo/Periódica/Específica)
   │   └─> Agrega datos de evaluados (nombre, email, DPI, teléfono)
   │
   └─> Usuario Repro crea orden → selecciona empresa manualmente
       └─> Mismo proceso de selección de servicio y formulario
       └─> Agrega datos de evaluados

2. SISTEMA GENERA TOKENS ÚNICOS
   └─> Por cada evaluado se genera token único
   └─> Token vinculado a: DPI + email + orden_id
   └─> Se envía email automático con link único
   └─> Token válido por 30 días

3. EVALUADO COMPLETA CUESTIONARIO (SIN LOGIN)
   └─> Accede mediante link único recibido por email/SMS
   └─> NO requiere crear cuenta ni iniciar sesión
   └─> Completa formulario por secciones
   └─> Guardado automático (draft) en cada sección
   └─> Firma digital al finalizar
   └─> Formulario se bloquea permanentemente
   └─> Token expira (no puede reutilizarse)

4. REPRO REVISA Y PROGRAMA
   └─> Personal Repro ve cuestionarios completados
   └─> Asigna poligrafista
   └─> Programa fecha y hora de evaluación
   └─> Puede consultar historial del evaluado por DPI

5. SE REALIZA LA EVALUACIÓN
   ├─> Polígrafo Presencial: Prueba en instalaciones de Repro
   ├─> VSA: Análisis vocal (puede ser remoto)
   └─> Socioeconómico: Estudio completo
   └─> Poligrafista registra resultados en sistema
   └─> Sube gráficos/documentos de evidencia

6. ENTREGA DE RESULTADOS
   └─> Sistema genera reporte PDF automático
   └─> Empresa puede descargar resultados
   └─> Empresa NO puede ver historial previo del evaluado
   └─> Repro SÍ puede consultar evaluaciones previas del mismo DPI
   └─> Notificación automática por email
```

### Roles en la Creación de Órdenes

| Usuario | Creación de Orden | Asignación de Empresa | Acceso a Historial de DPI |
|---------|-------------------|----------------------|---------------------------|
| **Empresa** | ✅ Sí | Automática (su empresa) | ❌ No |
| **Repro** | ✅ Sí | Manual (elige empresa) | ✅ Sí |
| **Admin** | ✅ Sí | Manual (elige empresa) | ✅ Sí |
| **Evaluado** | ❌ No | N/A | ❌ No |

---

## 📊 Módulos del Sistema

### 1. Módulo de Gestión de Empresas
**Estado:** ✅ Implementado
**Funcionalidades:**
- CRUD completo de empresas
- Campos: nombre, NIT, dirección, contacto, logo
- Estado activo/inactivo
- Generación de PDF con datos de empresa
- Relación con usuarios tipo empresa

### 2. Módulo de Gestión de Usuarios
**Estado:** 🔄 En actualización
**Funcionalidades:**
- CRUD completo de usuarios
- 4 tipos de usuarios con permisos diferenciados
- Sistema de roles y permisos (nuevo)
- Gestión de fotografías
- Envío de credenciales por email
- Reset de contraseña
- Generación de PDF

**Cambios Recientes:**
- ✅ Sistema de roles y permisos implementado
- ✅ Migración de tabla users con nuevos campos
- ✅ Campos de documento para evaluados
- 🔄 Vistas pendientes de actualizar

### 3. Módulo de Órdenes
**Estado:** ⏳ Pendiente

**Tipos de Servicio Ofrecidos:**

#### 3.1 Prueba de Polígrafo (Presencial)
- Evaluación tradicional con polígrafo físico
- Requiere cita presencial
- Formularios disponibles:
  - **Pre-empleo**: Para candidatos nuevos
  - **Periódica**: Para procesos internos de la empresa
  - **Específica**: Para casos particulares

#### 3.2 VSA (Voice Stress Analysis - Virtual)
- Análisis de estrés vocal
- Se puede realizar remotamente
- Usa los **mismos formularios** que Polígrafo:
  - Pre-empleo
  - Periódica
  - Específica

#### 3.3 Estudio Socioeconómico
- Evaluación del contexto social y económico
- Formulario basado en **pre-empleo** con campos adicionales:
  - Información económica familiar
  - Situación habitacional
  - Referencias comunitarias
  - Historial crediticio

**Creación de Órdenes:**
- **Usuario Empresa:** Crea órdenes → automáticamente asignadas a su empresa
- **Usuario Repro:** Crea órdenes → debe seleccionar la empresa manualmente

**Funcionalidades Planeadas:**
- Seleccionar tipo de servicio (Polígrafo/VSA/Socioeconómico)
- Seleccionar tipo de formulario (Pre-empleo/Periódica/Específica)
- Especificar cantidad de evaluaciones
- Agregar datos de evaluados (nombre, email, DPI, teléfono)
- Estados: Pendiente, En Proceso, Completada, Cancelada
- Asignación a poligrafista
- Fechas programadas
- Generación automática de tokens únicos para cuestionarios
- Envío automático de emails con links

### 4. Módulo de Cuestionarios
**Estado:** ⏳ Pendiente

**⚠️ Sin Autenticación Requerida**

**Tipos de Formularios:**

#### 4.1 Formulario Pre-empleo
- Para candidatos nuevos
- Usado por: Polígrafo y VSA
- Secciones: Datos personales, historial laboral, referencias, antecedentes

#### 4.2 Formulario Periódica
- Para procesos internos
- Usado por: Polígrafo y VSA
- Secciones: Actualización de datos, situación actual, declaraciones

#### 4.3 Formulario Específica
- Para casos particulares
- Usado por: Polígrafo y VSA
- Secciones: Personalizadas según necesidad

#### 4.4 Formulario Socioeconómico
- Basado en pre-empleo + campos extras
- Solo para: Estudio Socioeconómico
- Secciones adicionales: Economía familiar, vivienda, referencias comunitarias

**Funcionalidades Planeadas:**
- Acceso público por token único (sin login)
- Token de un solo uso vinculado a DPI
- Múltiples secciones con navegación
- Guardado automático por sección (draft)
- Validaciones progresivas
- Firma digital del evaluado
- Almacenamiento en JSON
- Bloqueo permanente al completar
- Expiración de token después de 30 días

### 5. Módulo de Evaluaciones
**Estado:** ⏳ Pendiente
**Funcionalidades Planeadas:**
- Registro de datos de la prueba
- Upload de archivos/gráficos
- Observaciones del poligrafista
- Estados: Programada, En Curso, Completada
- Vinculación con orden y cuestionario
- Historial de cambios

### 6. Módulo de Resultados
**Estado:** ⏳ Pendiente
**Funcionalidades Planeadas:**
- Generación automática de reportes PDF
- Descarga para empresas
- Control de acceso (solo empresa dueña)
- Versionado de resultados
- Marca de agua
- Log de descargas

### 7. Módulo de Reportes
**Estado:** ⏳ Pendiente
**Funcionalidades Planeadas:**
- Dashboard con estadísticas
- Reportes por empresa
- Reportes por poligrafista
- Reportes por período
- Exportación a Excel/PDF
- Gráficos de tendencias

### 8. Módulo de Configuración
**Estado:** ✅ Implementado
**Funcionalidades:**
- Logo de la empresa
- Datos de contacto
- Configuración de emails
- Moneda y formatos
- Redes sociales

---

## 🏗️ Arquitectura Técnica

### Stack Tecnológico
- **Backend:** Laravel 12.37.0
- **PHP:** 8.3.16
- **Base de Datos:** MySQL
- **Frontend:** Blade Templates + Bootstrap
- **PDF:** barryvdh/laravel-dompdf 3.1.1
- **Excel:** maatwebsite/excel 3.1.67
- **Auth:** Laravel Sanctum 4.2.0
- **Testing:** PHPUnit 11.5.43
- **Dev Tools:** Laravel Boost (MCP), Laravel Sail

### Patrones de Arquitectura
- **MVC:** Model-View-Controller
- **Repository Pattern:** Considerado para módulos complejos
- **Service Layer:** Para lógica de negocio compleja
- **Form Requests:** Validación centralizada
- **Policies:** Control de acceso granular

---

## 🗄️ Estructura de Base de Datos

### Tablas Principales Implementadas

**users** (Actualizada)
- Campos básicos: id, name, email, password
- Campos de rol: role_as, empresa_id, principal, estado
- Nuevos campos: documento_identidad, tipo_documento, cuestionario_completado
- Permisos legacy: permisos (JSON)

**empresas**
- Datos de empresa cliente
- Relación one-to-many con users

**roles** (Nuevo)
- Sistema de roles: admin, repro, empresa, evaluado

**permissions** (Nuevo)
- 25 permisos organizados en 8 módulos

**role_permission** (Nuevo)
- Relación many-to-many roles-permisos

**user_role** (Nuevo)
- Relación many-to-many users-roles

**configs**
- Configuración global del sistema

### Tablas Pendientes

**ordenes**
- Órdenes de pruebas solicitadas por empresas

**evaluaciones**
- Registros de pruebas realizadas

**cuestionarios**
- Respuestas de cuestionarios pre-evaluación

**resultados**
- Resultados de evaluaciones

**documentos**
- Archivos adjuntos (gráficos, evidencias)

---

## 🎨 Interfaz de Usuario

### Layouts Implementados
1. **incadmin:** Panel administrativo (Admin, Repro)
2. **incempresa:** Panel de empresas
3. **incevaluado:** Panel público para evaluados

### Componentes Comunes
- Navbar responsive con logo
- Sidebar colapsable
- Footer con información de contacto
- Alertas y notificaciones
- Tablas con paginación
- Formularios con validación en tiempo real

---

## 🔐 Sistema de Seguridad

### Autenticación
- Laravel Sanctum para API
- Session-based para web
- Password reset via email
- Rate limiting en login

### Autorización

**Sistema Dual (Transición):**

1. **Sistema Legacy (role_as)**
   - Campo integer: 0, 1, 2, 3
   - Usado en código existente
   - Se mantiene para compatibilidad

2. **Sistema Nuevo (roles y permisos)**
   - Tablas normalizadas
   - Permisos granulares
   - Múltiples roles por usuario
   - Middleware: `role:admin,repro` y `permission:ordenes.crear`

### Protección de Datos
- Passwords hasheados con Bcrypt
- CSRF protection habilitado
- SQL injection prevention (Eloquent)
- XSS protection (Blade)
- Rate limiting en rutas sensibles

---

## 📈 Métricas de Éxito

### KPIs Técnicos
- [ ] Tiempo de carga < 2 segundos
- [ ] Disponibilidad > 99%
- [ ] 0 errores críticos en producción
- [ ] Cobertura de tests > 70%

### KPIs de Negocio
- [ ] Reducir tiempo de procesamiento 60%
- [ ] 100% de órdenes digitalizadas
- [ ] Satisfacción de clientes > 8/10
- [ ] 0 pérdida de documentos

---

## 🚀 Roadmap

### Fase 1: Fundación ✅ (COMPLETADO)
- ✅ Actualización a Laravel 12
- ✅ Actualización a PHP 8.3
- ✅ Sistema de roles y permisos
- ✅ Módulo de empresas
- ✅ Módulo de usuarios base
- ✅ Layouts responsive

### Fase 2: Core Features 🔄 (EN PROGRESO)
- 🔄 Actualizar vistas de usuarios
- ⏳ Módulo de órdenes
- ⏳ Módulo de cuestionarios
- ⏳ Acceso público al cuestionario

### Fase 3: Evaluaciones ⏳ (PENDIENTE)
- ⏳ Módulo de evaluaciones
- ⏳ Upload de documentos
- ⏳ Generación de resultados
- ⏳ Sistema de notificaciones

### Fase 4: Reportes y Analytics ⏳ (PENDIENTE)
- ⏳ Dashboard de estadísticas
- ⏳ Reportes por empresa
- ⏳ Exportación a Excel
- ⏳ Gráficos y visualizaciones

### Fase 5: Optimización ⏳ (PENDIENTE)
- ⏳ Testing completo
- ⏳ Optimización de performance
- ⏳ SEO y accesibilidad
- ⏳ Documentación completa

---

## 📝 Decisiones Técnicas Importantes

### 1. Laravel 8 → Laravel 12
**Decisión:** Mantener estructura Laravel 10 (no migrar a estructura Laravel 12)
**Razón:** Recomendación oficial de Laravel, evita refactorización innecesaria
**Impacto:** Middleware en `app/Http/Kernel.php`, no en `bootstrap/app.php`

### 2. Sistema Dual de Roles
**Decisión:** Mantener `role_as` + nuevo sistema de roles
**Razón:** Compatibilidad con código existente, migración gradual
**Impacto:** Métodos como `isAdmin()` verifican ambos sistemas

### 3. Documentos para Evaluados
**Decisión:** Agregar campos `documento_identidad` y `tipo_documento`
**Razón:** Identificación única, requerimiento legal
**Impacto:** Validación obligatoria para role_as = 0

### 4. Cuestionario sin Autenticación
**Decisión:** Acceso público con código único
**Razón:** Facilita acceso a evaluados, reduce fricción
**Impacto:** Seguridad basada en tokens únicos de un solo uso

### 5. Laravel Boost
**Decisión:** Implementar Laravel Boost con GitHub Copilot
**Razón:** Mejora significativa en calidad de sugerencias de código
**Impacto:** Copilot tiene contexto completo del proyecto en tiempo real

---

## 🔧 Deuda Técnica

### Alta Prioridad
1. ❗ Actualizar vistas de usuarios con nuevo sistema de roles
2. ❗ Crear seeder para asignar roles a usuarios existentes
3. ❗ Implementar tests para sistema de permisos

### Media Prioridad
4. ⚠️ Refactorizar PDFs con templates más modernos
5. ⚠️ Implementar cache para permisos de usuario
6. ⚠️ Agregar logs de auditoría para cambios de roles

### Baja Prioridad
7. 📌 Migrar middleware de Kernel a bootstrap/app.php (futuro)
8. 📌 Implementar API REST completa
9. 📌 Agregar tests de integración E2E

---

## 📚 Documentación Relacionada

- [ROLES_Y_PERMISOS.md](./ROLES_Y_PERMISOS.md) - Documentación del sistema de roles
- [ACTUALIZACION_USUARIOS.md](./ACTUALIZACION_USUARIOS.md) - Guía de actualización del módulo usuarios
- [README.md](./README.md) - Información general del proyecto
- [API.md](./docs/API.md) - Documentación de API (pendiente)
- [ARCHITECTURE.md](./docs/ARCHITECTURE.md) - Arquitectura detallada (pendiente)

---

## 🤝 Contacto y Equipo

**Cliente:** REPRO Guatemala
**Desarrollador:** Szotto (szotto18@gmail.com)
**IA Assistant:** GitHub Copilot + Laravel Boost
**Repositorio:** github.com/szystems/repro

---

## 📅 Historial de Cambios

### 2025-11-08 - Actualización Mayor
- ✅ Upgrade Laravel 8 → Laravel 12
- ✅ Upgrade PHP 7.4 → PHP 8.3
- ✅ Implementación sistema roles y permisos
- ✅ Instalación Laravel Boost
- ✅ Actualización controladores de usuarios
- 🔄 Actualización de vistas (en progreso)

### 2024-XX-XX - Versión Inicial
- ✅ Creación del proyecto Laravel 8
- ✅ Módulo de empresas
- ✅ Módulo de usuarios básico
- ✅ Sistema de autenticación

---

**Última actualización:** 8 de noviembre de 2025
**Versión del documento:** 1.0
