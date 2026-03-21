<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Primero ejecutar el seeder de roles y permisos
        $seeder = new \Database\Seeders\RolesAndPermissionsSeeder();
        $seeder->run();

        // Asignar roles a usuarios existentes según su role_as
        $roleMapping = [
            1 => 'empresa',
            2 => 'repro',
            3 => 'admin',
        ];

        foreach ($roleMapping as $roleAs => $roleName) {
            $role = DB::table('roles')->where('name', $roleName)->first();
            if (!$role) {
                continue;
            }

            $userIds = DB::table('users')->where('role_as', $roleAs)->pluck('id');

            foreach ($userIds as $userId) {
                DB::table('user_role')->insertOrIgnore([
                    'user_id' => $userId,
                    'role_id' => $role->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        DB::table('user_role')->truncate();
    }
};
