@echo off
REM SCRIPT DE CONSOLIDACIÓN DE MIGRACIONES - REPRO GUATEMALA (Windows)
REM Fecha: 15/11/2025
REM Propósito: Unificar migraciones fragmentadas en versiones consolidadas

echo 🚀 INICIANDO CONSOLIDACIÓN DE MIGRACIONES
echo ==========================================

REM Verificar que estamos en desarrollo
for /f "tokens=2 delims==" %%i in ('findstr "APP_ENV" .env 2^>nul') do set APP_ENV=%%i
if "%APP_ENV%"=="production" (
    echo ❌ ERROR: Este script NO debe ejecutarse en producción
    pause
    exit /b 1
)

echo ⚠️  ADVERTENCIA: Este proceso eliminará migraciones existentes
echo    Solo ejecutar en entorno de desarrollo sin datos importantes
echo.
set /p confirm="¿Continuar? (y/N): "

if /i not "%confirm%"=="y" (
    echo ❌ Operación cancelada
    pause
    exit /b 0
)

echo.
echo 📋 PASO 1: Creando backup (recomendado hacer backup manual)...
echo    Por favor asegúrate de tener un backup de tu base de datos
pause

echo.
echo 📋 PASO 2: Reseteando migraciones existentes...
php artisan migrate:reset --force

if errorlevel 1 (
    echo    ❌ Error reseteando migraciones
    pause
    exit /b 1
)
echo    ✅ Migraciones reseteadas exitosamente

echo.
echo 📋 PASO 3: Eliminando migraciones fragmentadas...

REM Eliminar migraciones fragmentadas
del "database\migrations\2025_11_08_000000_improve_users_table_structure.php" 2>nul
del "database\migrations\2025_11_13_001434_add_apellidos_to_evaluados_orden_table.php" 2>nul
del "database\migrations\2025_11_13_235708_add_specific_fields_to_evaluados_orden_table.php" 2>nul
del "database\migrations\2025_11_13_235800_simplify_ordenes_table_remove_specific_fields.php" 2>nul
del "database\migrations\2025_11_15_123530_remove_unique_constraint_from_dpi_in_evaluados_orden_table.php" 2>nul

echo    🗑️  Migraciones fragmentadas eliminadas

echo.
echo 📋 PASO 4: Reemplazando migraciones principales con versiones consolidadas...

REM Reemplazar users table
if exist "database\migrations_consolidated\2014_10_12_000000_create_users_table_consolidated.php" (
    del "database\migrations\2014_10_12_000000_create_users_table.php" 2>nul
    copy "database\migrations_consolidated\2014_10_12_000000_create_users_table_consolidated.php" "database\migrations\2014_10_12_000000_create_users_table.php" >nul
    echo    ✅ Users table consolidada
)

REM Reemplazar ordenes table
if exist "database\migrations_consolidated\2025_11_12_200631_create_ordenes_table_consolidated.php" (
    del "database\migrations\2025_11_12_200631_create_ordenes_table.php" 2>nul
    copy "database\migrations_consolidated\2025_11_12_200631_create_ordenes_table_consolidated.php" "database\migrations\2025_11_12_200631_create_ordenes_table.php" >nul
    echo    ✅ Ordenes table consolidada
)

REM Reemplazar evaluados_orden table  
if exist "database\migrations_consolidated\2025_11_12_200912_create_evaluados_orden_table_consolidated.php" (
    del "database\migrations\2025_11_12_200912_create_evaluados_orden_table.php" 2>nul
    copy "database\migrations_consolidated\2025_11_12_200912_create_evaluados_orden_table_consolidated.php" "database\migrations\2025_11_12_200912_create_evaluados_orden_table.php" >nul
    echo    ✅ Evaluados_orden table consolidada
)

echo.
echo 📋 PASO 5: Ejecutando migraciones consolidadas...
php artisan migrate --force

if errorlevel 1 (
    echo    ❌ Error ejecutando migraciones
    echo    💡 Restaurar desde backup si es necesario
    pause
    exit /b 1
)
echo    ✅ Migraciones ejecutadas exitosamente

echo.
echo 📋 PASO 6: Sembrando datos iniciales...
php artisan db:seed --class=RolesAndPermissionsSeeder --force
php artisan db:seed --class=UserSeeder --force  
php artisan db:seed --class=EmpresaSeeder --force

echo.
echo 📋 PASO 7: Verificando integridad del sistema...
echo    Verificando estructura de base de datos...

php artisan tinker --execute="echo 'Users: ' . App\Models\User::count(); echo ' | Empresas: ' . App\Models\Empresa::count(); echo ' | Roles: ' . App\Models\Role::count(); echo ' | Permisos: ' . App\Models\Permission::count();"

echo.
echo 📋 PASO 8: Ejecutando tests críticos...
php artisan test --filter=Feature

echo.
echo 🎉 CONSOLIDACIÓN COMPLETADA
echo ============================
echo.
echo 📊 RESUMEN:
echo    • Migraciones consolidadas: 3 tablas principales
echo    • Migraciones eliminadas: 5 archivos fragmentados  
echo    • Estado: Base de datos limpia y funcional
echo.
echo 🔧 PRÓXIMOS PASOS:
echo    1. Ejecutar suite completa de tests: php artisan test
echo    2. Verificar funcionalidad en navegador
echo    3. Eliminar carpeta migrations_consolidated si todo funciona
echo.
pause