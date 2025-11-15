# SCRIPTS DE UTILIDAD - REPRO GUATEMALA

Esta carpeta contiene scripts automatizados para facilitar el desarrollo y deployment del proyecto.

## 📁 Contenido

### 🔄 **Migraciones y Base de Datos**
- **`consolidate_migrations.sh`** - Script Linux/Mac para consolidar migraciones
- **`consolidate_migrations.bat`** - Script Windows para consolidar migraciones

### 🚀 **Deployment y Servidor**  
- **`serve.sh`** - Script para levantar servidor de desarrollo

## 🛠️ Uso de Scripts

### Consolidación de Migraciones
```bash
# En Linux/Mac
./scripts/consolidate_migrations.sh

# En Windows
scripts\consolidate_migrations.bat
```

### Servidor de Desarrollo
```bash
# Levantar servidor
./scripts/serve.sh
```

## ⚠️ Notas Importantes

- **Consolidación de migraciones:** Solo ejecutar en desarrollo, nunca en producción
- **Servidor de desarrollo:** Para uso local únicamente
- **Permisos:** En sistemas Unix, dar permisos de ejecución: `chmod +x scripts/*.sh`

## 📞 Soporte

Si algún script presenta problemas, contacta a:
- **Desarrollador:** Otto Szarata
- **Email:** szystems@hotmail.com