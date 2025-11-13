# Cambios en el Modelo de Negocio - REPRO

**Fecha:** 8 de noviembre de 2025  
**Versión:** 2.0.0  
**Tipo:** Rediseño Arquitectónico

---

## 🔴 CAMBIOS CRÍTICOS

### 1. Evaluados SIN Cuenta de Usuario

#### ❌ Modelo Anterior (Incorrecto)
```
Evaluado → Usuario en tabla users (role_as = 0)
         → Puede hacer login
         → Tiene dashboard
         → Puede ver su resultado
```

#### ✅ Modelo Nuevo (Correcto)
```
Evaluado → Registro en tabla evaluados_orden (NO en users)
         → Sin cuenta de usuario
         → Acceso por token único temporal
         → NO puede hacer login
         → NO tiene dashboard
         → NO ve su resultado
```

**Razón del Cambio:**
- Cliente requiere que evaluados NO tengan acceso al sistema
- Privacidad y control total por parte de Repro
- Simplifica UX: solo completar formulario y listo

**Impacto en Desarrollo:**
- ✅ Eliminar rol `evaluado` del sistema RBAC
- ✅ Crear nueva tabla `evaluados_orden`
- ✅ Implementar sistema de tokens únicos
- ✅ Crear rutas públicas sin autenticación para cuestionarios
- ⏳ Actualizar todas las referencias al role_as=0

---

### 2. Historial de Evaluados por DPI

#### Identificador Único: DPI
**DPI (Documento Personal de Identificación)** es el identificador único de Guatemala.

**Privacidad por Tipo de Usuario:**

| Usuario | Puede Ver Historial Completo por DPI? | Justificación |
|---------|--------------------------------------|---------------|
| **Admin** | ✅ SÍ | Control total del sistema |
| **Repro** | ✅ SÍ | Necesario para evaluaciones profesionales |
| **Empresa** | ❌ NO | Privacidad: solo ven sus propias órdenes |
| **Evaluado** | ❌ NO | No tienen acceso al sistema |

**Casos de Uso:**

```php
// Repro consultando historial de un evaluado
// Ejemplo: Juan ha sido evaluado 3 veces por diferentes empresas

DPI: 1234567890123 (Juan Pérez)
├─ Evaluación 1 (2023): Empresa ABC - Aprobado
├─ Evaluación 2 (2024): Empresa XYZ - Rechazado
└─ Evaluación 3 (2025): Empresa DEF - Pendiente

// Repro puede ver todo el historial
// Empresa DEF solo puede ver la Evaluación 3
// Empresa ABC solo puede ver la Evaluación 1
```

**Beneficio:**
- Repro tiene contexto completo para mejores evaluaciones
- Empresas no pueden discriminar basándose en historial previo
- Se mantiene privacidad entre empresas competidoras

---

### 3. Tipos de Servicio y Formularios

#### Servicios Ofrecidos

| # | Servicio | Modalidad | Formularios Disponibles |
|---|----------|-----------|-------------------------|
| 1 | **Polígrafo** | Presencial | Pre-empleo, Periódica, Específica |
| 2 | **VSA** | Virtual | Pre-empleo, Periódica, Específica |
| 3 | **Socioeconómico** | Presencial/Virtual | Pre-empleo + extras |

#### Formularios Detallados

**Pre-empleo:**
- Para nuevos candidatos
- Datos personales completos
- Historial laboral
- Referencias
- Antecedentes

**Periódica:**
- Para empleados actuales
- Actualización de datos
- Situación actual
- Declaraciones de conflicto de interés

**Específica:**
- Casos particulares
- Preguntas personalizadas
- Investigación dirigida

**Socioeconómico:**
- Basado en Pre-empleo
- **Campos adicionales:**
  - Economía familiar
  - Ingresos y gastos
  - Situación habitacional
  - Referencias comunitarias
  - Historial crediticio
  - Bienes y propiedades

**Mismo Backend, Diferentes Preguntas:**
- Polígrafo y VSA usan **exactamente los mismos formularios**
- Solo cambia el tipo de evaluación (presencial vs virtual)
- Socioeconómico extiende Pre-empleo con más campos

---

### 4. Creación de Órdenes

#### Antes (Asumido)
```
Solo Admin y Repro creaban órdenes
→ Empresa tenía que solicitar por email/teléfono
→ Proceso manual y lento
```

#### Ahora (Correcto)
```
Empresa → Crea órdenes (auto-asignadas a su empresa)
Repro → Crea órdenes (selecciona empresa manualmente)
Admin → Crea órdenes (selecciona empresa manualmente)
```

**Flujo de Creación:**

```php
// Usuario EMPRESA crea orden
public function store(Request $request)
{
    $orden = new Orden($request->validated());
    
    // Auto-asignar a la empresa del usuario
    $orden->empresa_id = auth()->user()->empresa_id;
    $orden->creado_por = auth()->id();
    
    $orden->save();
    
    // Generar tokens para evaluados
    $this->generarTokensParaEvaluados($orden);
}

// Usuario REPRO crea orden
public function store(Request $request)
{
    $orden = new Orden($request->validated());
    
    // Repro debe seleccionar la empresa
    $orden->empresa_id = $request->empresa_id; // Requerido
    $orden->creado_por = auth()->id();
    
    $orden->save();
}
```

**Beneficios:**
- Empresas tienen autoservicio 24/7
- Repro puede crear órdenes para empresas sin acceso al sistema
- Trazabilidad: `creado_por` registra quién creó la orden

---

## 📊 Diagrama de Flujo Actualizado

```
┌─────────────────────────────────────────────────────────────┐
│                  CREACIÓN DE ORDEN                          │
└─────────────────────────────────────────────────────────────┘
                            │
                ┌───────────┴───────────┐
                │                       │
         Usuario EMPRESA         Usuario REPRO/ADMIN
                │                       │
                ▼                       ▼
    ┌───────────────────┐   ┌──────────────────────┐
    │ Empresa auto-     │   │ Selecciona empresa   │
    │ asignada          │   │ manualmente          │
    └───────────────────┘   └──────────────────────┘
                │                       │
                └───────────┬───────────┘
                            ▼
                ┌───────────────────────┐
                │ Selecciona:           │
                │ - Tipo de servicio    │
                │ - Tipo de formulario  │
                │ - Cantidad evaluados  │
                └───────────────────────┘
                            │
                            ▼
                ┌───────────────────────┐
                │ Agrega datos de       │
                │ evaluados:            │
                │ - Nombre              │
                │ - Email               │
                │ - DPI (único)         │
                │ - Teléfono            │
                └───────────────────────┘
                            │
                            ▼
                ┌───────────────────────┐
                │ Sistema genera:       │
                │ - Token único x eval  │
                │ - Expira en 30 días   │
                │ - Envía email c/ link │
                └───────────────────────┘
                            │
                            ▼
                ┌───────────────────────┐
                │ Evaluado accede por   │
                │ link (SIN LOGIN)      │
                │ - Completa formulario │
                │ - Firma digital       │
                │ - Formulario se bloquea│
                └───────────────────────┘
                            │
                            ▼
                ┌───────────────────────┐
                │ Repro asigna          │
                │ poligrafista y        │
                │ programa cita         │
                └───────────────────────┘
                            │
                            ▼
                ┌───────────────────────┐
                │ Se realiza evaluación │
                │ (Polígrafo/VSA/Socio) │
                └───────────────────────┘
                            │
                            ▼
                ┌───────────────────────┐
                │ Repro genera PDF      │
                │ - Empresa descarga    │
                │ - NO ve historial DPI │
                └───────────────────────┘
```

---

## 🛠️ Tareas de Desarrollo Requeridas

### Alta Prioridad
- [ ] Ejecutar `UpdateRolesRemoveEvaluadoSeeder` para eliminar rol evaluado
- [ ] Crear migración para tabla `evaluados_orden`
- [ ] Crear migración para tabla `cuestionarios`
- [ ] Crear modelo `EvaluadoOrden` (NO hereda de User)
- [ ] Implementar generación de tokens únicos
- [ ] Crear rutas públicas para cuestionarios (sin middleware auth)
- [ ] Actualizar controladores de usuario para NO permitir role_as=0

### Media Prioridad
- [ ] Crear formulario de órdenes con selección de servicio/formulario
- [ ] Implementar lógica de auto-asignación para empresas
- [ ] Crear vista de cuestionario público (sin layout de dashboard)
- [ ] Implementar guardado por secciones (draft)
- [ ] Implementar firma digital
- [ ] Crear sistema de emails con links únicos

### Baja Prioridad
- [ ] Crear panel de historial por DPI (solo para Repro/Admin)
- [ ] Implementar log de accesos a historial
- [ ] Crear reportes de órdenes por empresa
- [ ] Implementar notificaciones automáticas

---

## 📝 Notas para el Equipo

1. **NO crear más usuarios con role_as=0**: Los evaluados no son usuarios
2. **Validar DPI guatemalteco**: Formato 13 dígitos
3. **Tokens seguros**: Usar `Str::random(64)` y verificar unicidad
4. **Emails transaccionales**: Configurar SMTP para envío de links
5. **Privacidad**: Empresas NUNCA deben ver historial completo de DPI
6. **Testing**: Crear factories para `EvaluadoOrden` (no para User con role_as=0)

---

**Última actualización:** 8 de noviembre de 2025  
**Aprobado por:** Cliente REPRO Guatemala
