  <?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UpdateRolesRemoveEvaluadoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Este seeder elimina el rol "evaluado" del sistema ya que los evaluados
     * NO tendrán cuentas de usuario, solo acceso temporal por token único.
     */
    public function run(): void
    {
        $this->command->info('🔄 Eliminando rol "evaluado" del sistema...');

        // 1. Obtener el rol evaluado
        $evaluadoRole = DB::table('roles')->where('name', 'evaluado')->first();
        
        if (!$evaluadoRole) {
            $this->command->warn('⚠️  El rol "evaluado" no existe en la base de datos.');
            return;
        }

        // 2. Eliminar asignaciones de usuarios a este rol
        $deletedUserRoles = DB::table('user_role')
            ->where('role_id', $evaluadoRole->id)
            ->delete();
        
        $this->command->info("   ✓ Eliminadas {$deletedUserRoles} asignaciones de usuarios al rol evaluado");

        // 3. Eliminar permisos asignados a este rol
        $deletedRolePermissions = DB::table('role_permission')
            ->where('role_id', $evaluadoRole->id)
            ->delete();
        
        $this->command->info("   ✓ Eliminados {$deletedRolePermissions} permisos del rol evaluado");

        // 4. Eliminar el rol
        DB::table('roles')->where('id', $evaluadoRole->id)->delete();
        $this->command->info('   ✓ Rol "evaluado" eliminado');

        // 5. Opcional: Actualizar descripción de otros roles
        DB::table('roles')->where('name', 'repro')->update([
            'description' => 'Personal interno de Repro que realiza las pruebas. Puede crear órdenes y consultar historial completo por DPI.',
            'updated_at' => now()
        ]);

        DB::table('roles')->where('name', 'empresa')->update([
            'description' => 'Usuario de empresa cliente que solicita pruebas. NO tiene acceso a historial previo de evaluados.',
            'updated_at' => now()
        ]);

        $this->command->info('   ✓ Descripciones de roles actualizadas');

        // 6. Opcional: Remover permiso cuestionarios.completar si ya no se usa
        $cuestionarioPermission = DB::table('permissions')
            ->where('name', 'cuestionarios.completar')
            ->first();

        if ($cuestionarioPermission) {
            // Verificar si algún otro rol tiene este permiso
            $otherRolesWithPermission = DB::table('role_permission')
                ->where('permission_id', $cuestionarioPermission->id)
                ->count();

            if ($otherRolesWithPermission === 0) {
                DB::table('permissions')->where('id', $cuestionarioPermission->id)->delete();
                $this->command->info('   ✓ Permiso "cuestionarios.completar" eliminado (no usado por ningún rol)');
            }
        }

        $this->command->newLine();
        $this->command->info('✅ Actualización completada!');
        $this->command->newLine();
        $this->command->info('📋 Sistema de Roles actualizado:');
        $this->command->info('   • Admin: Acceso completo, historial por DPI');
        $this->command->info('   • Repro: Crear órdenes para empresas, historial por DPI');
        $this->command->info('   • Empresa: Crear órdenes auto-asignadas, SIN historial');
        $this->command->info('   ✗ Evaluado: ELIMINADO (acceso solo por token)');
    }
}
