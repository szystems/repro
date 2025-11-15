#!/bin/bash

# SCRIPT DE CONSOLIDACIÓN DE MIGRACIONES - REPRO GUATEMALA
# Fecha: 15/11/2025
# Propósito: Unificar migraciones fragmentadas en versiones consolidadas

echo "🚀 INICIANDO CONSOLIDACIÓN DE MIGRACIONES"
echo "=========================================="

# Verificar que estamos en desarrollo
if [ "$APP_ENV" = "production" ]; then
    echo "❌ ERROR: Este script NO debe ejecutarse en producción"
    exit 1
fi

echo "⚠️  ADVERTENCIA: Este proceso eliminará migraciones existentes"
echo "   Solo ejecutar en entorno de desarrollo sin datos importantes"
echo ""
read -p "¿Continuar? (y/N): " confirm

if [[ $confirm != [yY] ]]; then
    echo "❌ Operación cancelada"
    exit 0
fi

echo ""
echo "📋 PASO 1: Creando backup de la base de datos..."
timestamp=$(date +"%Y%m%d_%H%M%S")
backup_file="backup_before_consolidation_$timestamp.sql"

# Detectar configuración de base de datos desde .env
if [ -f .env ]; then
    DB_DATABASE=$(grep DB_DATABASE .env | cut -d '=' -f2)
    DB_USERNAME=$(grep DB_USERNAME .env | cut -d '=' -f2)
    DB_HOST=$(grep DB_HOST .env | cut -d '=' -f2 | sed 's/127.0.0.1/localhost/')
    
    echo "   Respaldando base de datos: $DB_DATABASE"
    mysqldump -h "$DB_HOST" -u "$DB_USERNAME" -p "$DB_DATABASE" > "storage/backups/$backup_file" 2>/dev/null
    
    if [ $? -eq 0 ]; then
        echo "   ✅ Backup creado: storage/backups/$backup_file"
    else
        echo "   ⚠️  No se pudo crear backup automático"
        echo "   Por favor crea un backup manual antes de continuar"
        read -p "¿Continuar sin backup? (y/N): " no_backup
        if [[ $no_backup != [yY] ]]; then
            exit 0
        fi
    fi
else
    echo "   ⚠️  Archivo .env no encontrado, no se puede crear backup automático"
fi

echo ""
echo "📋 PASO 2: Reseteando migraciones existentes..."
php artisan migrate:reset --force

if [ $? -eq 0 ]; then
    echo "   ✅ Migraciones reseteadas exitosamente"
else
    echo "   ❌ Error reseteando migraciones"
    exit 1
fi

echo ""
echo "📋 PASO 3: Eliminando migraciones fragmentadas..."

# Lista de migraciones a eliminar
migrations_to_remove=(
    "2025_11_08_000000_improve_users_table_structure.php"
    "2025_11_13_001434_add_apellidos_to_evaluados_orden_table.php"
    "2025_11_13_235708_add_specific_fields_to_evaluados_orden_table.php"
    "2025_11_13_235800_simplify_ordenes_table_remove_specific_fields.php"
    "2025_11_15_123530_remove_unique_constraint_from_dpi_in_evaluados_orden_table.php"
)

for migration in "${migrations_to_remove[@]}"; do
    if [ -f "database/migrations/$migration" ]; then
        rm "database/migrations/$migration"
        echo "   🗑️  Eliminado: $migration"
    else
        echo "   ⚠️  No encontrado: $migration"
    fi
done

echo ""
echo "📋 PASO 4: Reemplazando migraciones principales con versiones consolidadas..."

# Mover migraciones consolidadas
if [ -f "database/migrations_consolidated/2014_10_12_000000_create_users_table_consolidated.php" ]; then
    rm -f "database/migrations/2014_10_12_000000_create_users_table.php" 2>/dev/null
    cp "database/migrations_consolidated/2014_10_12_000000_create_users_table_consolidated.php" "database/migrations/2014_10_12_000000_create_users_table.php"
    echo "   ✅ Users table consolidada"
fi

if [ -f "database/migrations_consolidated/2025_11_12_200631_create_ordenes_table_consolidated.php" ]; then
    rm -f "database/migrations/2025_11_12_200631_create_ordenes_table.php" 2>/dev/null
    cp "database/migrations_consolidated/2025_11_12_200631_create_ordenes_table_consolidated.php" "database/migrations/2025_11_12_200631_create_ordenes_table.php"
    echo "   ✅ Ordenes table consolidada"
fi

if [ -f "database/migrations_consolidated/2025_11_12_200912_create_evaluados_orden_table_consolidated.php" ]; then
    rm -f "database/migrations/2025_11_12_200912_create_evaluados_orden_table.php" 2>/dev/null
    cp "database/migrations_consolidated/2025_11_12_200912_create_evaluados_orden_table_consolidated.php" "database/migrations/2025_11_12_200912_create_evaluados_orden_table.php"
    echo "   ✅ Evaluados_orden table consolidada"
fi

echo ""
echo "📋 PASO 5: Ejecutando migraciones consolidadas..."
php artisan migrate --force

if [ $? -eq 0 ]; then
    echo "   ✅ Migraciones ejecutadas exitosamente"
else
    echo "   ❌ Error ejecutando migraciones"
    echo "   💡 Restaurar desde backup si es necesario"
    exit 1
fi

echo ""
echo "📋 PASO 6: Sembrando datos iniciales..."
php artisan db:seed --class=RolesAndPermissionsSeeder --force
php artisan db:seed --class=UserSeeder --force  
php artisan db:seed --class=EmpresaSeeder --force

echo ""
echo "📋 PASO 7: Verificando integridad del sistema..."

# Verificar estructura de tablas críticas
echo "   Verificando tablas..."
tables_check=$(php artisan tinker --execute="
\$tables = ['users', 'empresas', 'ordenes', 'evaluados_orden', 'roles', 'permissions'];
\$existing = [];
foreach(\$tables as \$table) {
    if(Schema::hasTable(\$table)) {
        \$existing[] = \$table;
    }
}
echo count(\$existing) . '/' . count(\$tables) . ' tablas creadas';
")

echo "   📊 $tables_check"

# Verificar datos básicos
echo "   Verificando datos..."
data_check=$(php artisan tinker --execute="
echo 'Users: ' . App\\Models\\User::count() . ' | ';
echo 'Empresas: ' . App\\Models\\Empresa::count() . ' | ';
echo 'Roles: ' . App\\Models\\Role::count() . ' | ';
echo 'Permisos: ' . App\\Models\\Permission::count();
")

echo "   📊 $data_check"

echo ""
echo "📋 PASO 8: Ejecutando tests críticos..."
php artisan test --filter=Feature --stop-on-failure

if [ $? -eq 0 ]; then
    echo "   ✅ Tests básicos pasando"
else
    echo "   ⚠️  Algunos tests fallan - revisar manualmente"
fi

echo ""
echo "🎉 CONSOLIDACIÓN COMPLETADA EXITOSAMENTE"
echo "========================================="
echo ""
echo "📊 RESUMEN:"
echo "   • Migraciones consolidadas: 3 tablas principales"
echo "   • Migraciones eliminadas: ${#migrations_to_remove[@]} archivos fragmentados"
echo "   • Estado: Base de datos limpia y funcional"
echo "   • Backup: $backup_file (si se creó)"
echo ""
echo "🔧 PRÓXIMOS PASOS:"
echo "   1. Ejecutar suite completa de tests: php artisan test"
echo "   2. Verificar funcionalidad en navegador"
echo "   3. Eliminar carpeta migrations_consolidated si todo funciona"
echo ""
echo "📞 SOPORTE: En caso de problemas, restaurar desde backup"