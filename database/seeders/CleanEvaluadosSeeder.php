<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\DB;

class CleanEvaluadosSeeder extends Seeder
{
    /**
     * Eliminar usuarios evaluados y rol evaluado del sistema
     * Los evaluados no deben ser usuarios, acceden vía token único
     */
    public function run(): void
    {
        $this->command->info('🧹 LIMPIANDO SISTEMA DE USUARIOS EVALUADOS...');
        $this->command->info('Los evaluados acceden vía token único, no como usuarios del sistema');
        $this->command->newLine();

        // 1. Eliminar usuarios con role_as = 0 (evaluados)
        $this->command->info('📋 PASO 1: Eliminando usuarios evaluados...');
        $evaluadosUsers = User::where('role_as', 0)->get();
        
        if ($evaluadosUsers->count() > 0) {
            $this->command->info("   Usuarios evaluados encontrados: {$evaluadosUsers->count()}");
            
            foreach ($evaluadosUsers as $user) {
                // Primero desconectar de roles si los tiene
                $user->roles()->detach();
                // Luego eliminar el usuario
                $user->delete();
                $this->command->info("   ❌ Eliminado: {$user->name} ({$user->email})");
            }
        } else {
            $this->command->info('   ✅ No se encontraron usuarios evaluados para eliminar');
        }

        // 2. Eliminar rol evaluado si existe
        $this->command->info('📋 PASO 2: Eliminando rol evaluado...');
        $evaluadoRole = Role::where('name', 'evaluado')->first();
        
        if ($evaluadoRole) {
            $this->command->info("   Rol encontrado: {$evaluadoRole->display_name}");
            
            // Desconectar de permisos
            $evaluadoRole->permissions()->detach();
            
            // Desconectar de usuarios (por si acaso)
            $evaluadoRole->users()->detach();
            
            // Eliminar el rol
            $evaluadoRole->delete();
            $this->command->info('   ❌ Rol evaluado eliminado');
        } else {
            $this->command->info('   ✅ Rol evaluado no encontrado (ya limpio)');
        }

        // 3. Eliminar permisos específicos de evaluados si existen
        $this->command->info('📋 PASO 3: Limpiando permisos de evaluados...');
        $permisosEvaluados = DB::table('permissions')
            ->where('name', 'LIKE', '%evaluado%')
            ->orWhere('name', 'cuestionarios.completar')
            ->get();

        if ($permisosEvaluados->count() > 0) {
            foreach ($permisosEvaluados as $permiso) {
                // Desconectar de roles
                DB::table('role_permission')->where('permission_id', $permiso->id)->delete();
                // Eliminar el permiso
                DB::table('permissions')->where('id', $permiso->id)->delete();
                $this->command->info("   ❌ Permiso eliminado: {$permiso->name}");
            }
        } else {
            $this->command->info('   ✅ No se encontraron permisos específicos de evaluados');
        }

        // 4. Mostrar estadísticas finales
        $this->command->newLine();
        $this->command->info('📊 ESTADÍSTICAS FINALES:');
        $this->command->info('=======================');
        
        $stats = [
            ['Métrica', 'Cantidad'],
            ['Total usuarios', User::count()],
            ['Usuarios Admin', User::where('role_as', 3)->count()],
            ['Usuarios Repro', User::where('role_as', 2)->count()],
            ['Usuarios Empresa', User::where('role_as', 1)->count()],
            ['Usuarios Evaluado', User::where('role_as', 0)->count()],
            ['---', '---'],
            ['Roles disponibles', Role::count()],
            ['Permisos disponibles', DB::table('permissions')->count()],
            ['Relaciones role_permission', DB::table('role_permission')->count()],
        ];
        
        $this->command->table($stats[0], array_slice($stats, 1));

        $this->command->newLine();
        $this->command->info('✅ LIMPIEZA COMPLETADA');
        $this->command->info('Los evaluados ahora acceden únicamente vía token en tabla evaluados_orden');
        $this->command->newLine();
    }
}