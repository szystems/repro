<?php

namespace Tests\Feature;

use App\Models\Empresa;
use App\Models\Role;
use App\Models\User;
use App\Support\EmpresaPermisosSupport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesRolesAndPermissions;
use Tests\TestCase;

/** Permisos portal empresa — spec cliente ago-2026 (PERMISOS_EMPRESA_CLIENTE.md). */
class EmpresaPermisosTrabajadorTest extends TestCase
{
    use RefreshDatabase, CreatesRolesAndPermissions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpRolesAndPermissions();
    }

    public function test_trabajador_ignora_rol_spatie_y_usa_solo_json(): void
    {
        $empresa = Empresa::factory()->create();
        $trabajador = User::factory()->create([
            'empresa_id' => $empresa->id,
            'role_as' => 1,
            'principal' => 0,
            'permisos' => json_encode(EmpresaPermisosSupport::permisosDefaultTrabajador()),
        ]);
        $trabajador->roles()->attach(Role::where('name', 'empresa')->first());

        $this->assertFalse($trabajador->hasPermission('ordenes.crear'));
        $this->assertTrue($trabajador->hasPermission('documentos.subir'));
        $this->assertTrue($trabajador->hasPermission('ordenes.ver'));
    }

    public function test_usuario_principal_tiene_todos_los_permisos_del_mapa(): void
    {
        $empresa = Empresa::factory()->create();
        $principal = User::factory()->create([
            'empresa_id' => $empresa->id,
            'role_as' => 1,
            'principal' => 1,
        ]);

        $this->assertTrue($principal->hasPermission('ordenes.crear'));
        $this->assertTrue($principal->hasPermission('documentos.subir'));
        $this->assertFalse($principal->hasPermission('sedes.ver'));
    }
}
