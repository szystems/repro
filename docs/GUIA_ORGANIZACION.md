# 📁 GUÍA DE ORGANIZACIÓN DE ARCHIVOS - REPRO GUATEMALA

**Propósito:** Mantener la estructura de archivos organizada y consistente  
**Audiencia:** Desarrolladores y agentes de IA  
**Fecha:** 15 de noviembre de 2025  

---

## 🎯 **PRINCIPIOS DE ORGANIZACIÓN**

### ✅ **Regla de Oro:**
> **"Cada archivo en su lugar correcto desde su creación"**

### ✅ **Beneficios:**
- Estructura predecible para todos los desarrolladores
- Fácil localización de documentos
- Mantenimiento automático del orden
- Onboarding rápido de nuevos desarrolladores

---

## 📁 **ESTRUCTURA OBLIGATORIA**

### **Raíz del Proyecto (SOLO archivos esenciales)**
```
REPRO/
├── README.md ✅ (principal del proyecto)
├── composer.json ✅ (dependencias PHP)
├── package.json ✅ (dependencias Node)
├── artisan ✅ (CLI Laravel)
├── phpunit.xml ✅ (configuración tests)
├── webpack.mix.js ✅ (compilación assets)
├── boost.json ✅ (configuración Laravel Boost)
├── server.php ✅ (servidor PHP)
└── [carpetas del framework Laravel] ✅
```

### **❌ NO CREAR EN RAÍZ:**
- Archivos .md de documentación
- Scripts personalizados
- Archivos de configuración temporal
- Documentos de auditoría
- Guías o manuales

---

## 📂 **CATEGORIZACIÓN POR TIPO DE ARCHIVO**

### 📊 **`docs/status/`** - Estado del Proyecto
**¿Cuándo usar?**
- Reportes de auditoría
- Estados actuales del sistema
- Contexto para agentes de IA
- Certificaciones y aprobaciones

**Ejemplos:**
- `AUDITORIA_[FECHA].md`
- `ESTADO_ACTUAL.md`
- `CONTEXTO_AGENTES.md`
- `CERTIFICACION_PRODUCCION.md`

### 🏗️ **`docs/technical/`** - Documentación Técnica
**¿Cuándo usar?**
- Arquitectura del sistema
- APIs y endpoints
- Especificaciones técnicas
- Diagramas de sistema

**Ejemplos:**
- `ARCHITECTURE.md`
- `API.md`
- `DATABASE_SCHEMA.md`
- `INTEGRACIONES.md`

### 📋 **`docs/business/`** - Documentos de Negocio
**¿Cuándo usar?**
- Requisitos del producto
- Cambios del modelo de negocio
- Historiales de cambios
- Documentos de cliente

**Ejemplos:**
- `PRD.md`
- `CHANGELOG.md`
- `REQUISITOS_CLIENTE.md`
- `FLUJOS_NEGOCIO.md`

### 🚀 **`docs/deployment/`** - Despliegue y DevOps
**¿Cuándo usar?**
- Guías de despliegue
- Configuraciones de servidor
- Procedimientos de deployment
- Documentación de infraestructura

**Ejemplos:**
- `DEPLOYMENT.md`
- `SERVER_CONFIG.md`
- `CI_CD_SETUP.md`
- `BACKUP_PROCEDURES.md`

### 📖 **`docs/guides/`** - Guías de Usuario
**¿Cuándo usar?**
- Guías de inicio rápido
- Tutoriales paso a paso
- Manuales de usuario
- FAQs

**Ejemplos:**
- `QUICK_START.md`
- `USER_MANUAL.md`
- `FAQ.md`
- `TROUBLESHOOTING.md`

### 🔐 **`docs/security/`** - Seguridad y Auditoría
**¿Cuándo usar?**
- Auditorías de seguridad
- Documentación de permisos
- Políticas de seguridad
- Reportes de vulnerabilidades

**Ejemplos:**
- `AUDITORIA_SEGURIDAD.md`
- `ROLES_Y_PERMISOS.md`
- `POLITICAS_SEGURIDAD.md`
- `VULNERABILIDADES.md`

### 🗄️ **`docs/database/`** - Base de Datos
**¿Cuándo usar?**
- Documentación de migraciones
- Esquemas de base de datos
- Procedimientos de respaldo
- Optimizaciones de BD

**Ejemplos:**
- `MIGRATION_GUIDE.md`
- `SCHEMA_CHANGES.md`
- `BACKUP_PROCEDURES.md`
- `PERFORMANCE_TUNING.md`

### 🛠️ **`scripts/`** - Scripts y Automatización
**¿Cuándo usar?**
- Scripts de deployment
- Herramientas de desarrollo
- Automatización de tareas
- Utilidades del proyecto

**Ejemplos:**
- `deploy.sh`
- `setup_dev.sh`
- `backup.sh`
- `migrate_data.sh`

---

## 🤖 **GUÍA PARA AGENTES DE IA**

### **Al Crear Documentación:**
```markdown
1. ¿Es sobre el estado actual del proyecto? → docs/status/
2. ¿Es documentación técnica/API? → docs/technical/
3. ¿Es sobre requisitos de negocio? → docs/business/
4. ¿Es sobre despliegue/DevOps? → docs/deployment/
5. ¿Es una guía para usuarios? → docs/guides/
6. ¿Es sobre seguridad/auditoría? → docs/security/
7. ¿Es sobre base de datos? → docs/database/
8. ¿Es un script ejecutable? → scripts/
```

### **Al Crear Scripts:**
```bash
# Crear en scripts/ con permisos adecuados
touch scripts/nuevo_script.sh
chmod +x scripts/nuevo_script.sh

# Documentar en scripts/README.md
echo "## nuevo_script.sh - Descripción" >> scripts/README.md
```

### **Convenciones de Nombres:**
```
✅ CORRECTO:
- AUDITORIA_NOVIEMBRE_2025.md
- DEPLOYMENT_PRODUCTION.md
- MIGRATION_CONSOLIDATION.md
- API_ENDPOINTS_V1.md

❌ INCORRECTO:
- auditoria.md (muy genérico)
- doc1.md (sin contexto)
- temp_file.md (temporal en docs)
- script.sh (sin descripción)
```

---

## 📝 **PLANTILLAS PARA NUEVOS ARCHIVOS**

### **Plantilla Documentación Técnica:**
```markdown
# [TÍTULO DEL DOCUMENTO]

**Fecha:** [DD/MM/YYYY]
**Autor:** [Nombre/IA Agent]
**Versión:** [1.0]
**Estado:** [Draft/Review/Approved]

## Propósito
[Descripción clara del propósito]

## Contenido
[Desarrollo del contenido]

## Referencias
[Enlaces a documentos relacionados]
```

### **Plantilla Script:**
```bash
#!/bin/bash
# [NOMBRE DEL SCRIPT]
# Propósito: [Descripción clara]
# Autor: [Nombre/IA Agent]
# Fecha: [DD/MM/YYYY]

set -e  # Salir en caso de error

echo "🚀 Iniciando [NOMBRE DEL SCRIPT]..."
# [Contenido del script]
echo "✅ [NOMBRE DEL SCRIPT] completado"
```

---

## 🔄 **PROCESO DE MANTENIMIENTO**

### **Revisión Semanal:**
1. Verificar que no hay archivos .md en raíz
2. Confirmar que scripts están en `scripts/`
3. Actualizar `docs/README_DOCS.md` si hay nuevos archivos
4. Limpiar archivos temporales

### **Al Agregar Nuevo Archivo:**
1. **Determinar categoría** según guías arriba
2. **Crear en carpeta correcta**
3. **Actualizar índice** correspondiente
4. **Documentar en README** de la carpeta si es necesario

### **Comando de Verificación:**
```bash
# Verificar estructura
find . -name "*.md" -not -path "./docs/*" -not -path "./vendor/*" -not -path "./.git/*"

# Debería estar vacío (sin archivos .md fuera de docs/)
```

---

## 🎯 **CHECKLIST PARA AGENTES**

### **Antes de Crear Archivo:**
- [ ] ¿Es realmente necesario un nuevo archivo?
- [ ] ¿Puede actualizarse un archivo existente?
- [ ] ¿En qué categoría va?
- [ ] ¿El nombre es descriptivo y único?

### **Después de Crear Archivo:**
- [ ] Está en la carpeta correcta
- [ ] Sigue las convenciones de nombre
- [ ] Tiene header con metadata
- [ ] Se actualizó el índice correspondiente

### **Para Scripts:**
- [ ] Está en carpeta `scripts/`
- [ ] Tiene permisos de ejecución
- [ ] Está documentado en `scripts/README.md`
- [ ] Incluye manejo de errores

---

## 📞 **Contacto y Soporte**

**Para dudas sobre organización:**
- Consultar esta guía primero
- Revisar `docs/README_DOCS.md`
- Mantener consistencia con archivos existentes

**Desarrollador Principal:**
- Otto Szarata (szystems@hotmail.com)

---

🎯 **RECUERDA:** Una estructura organizada desde el inicio evita horas de reorganización posterior y mejora la productividad de todo el equipo.