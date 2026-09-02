<?php

namespace Tests\Feature;

use App\Exports\OrdenesExport;
use App\Models\Config;
use App\Models\Empresa;
use App\Models\EvaluadoOrden;
use App\Models\Orden;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Support\EmpresaPermisosSupport;
use App\Support\ExportacionesSupport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Maatwebsite\Excel\Facades\Excel;
use Tests\Feature\Concerns\CreatesRolesAndPermissions;
use Tests\TestCase;

class ExportacionesPermisosTest extends TestCase
{
    use RefreshDatabase, CreatesRolesAndPermissions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpRolesAndPermissions();
    }

    public function test_repro_sin_generar_no_descarga_excel_de_ordenes_ni_informes(): void
    {
        $repro = $this->crearReproSinPermiso('reportes.generar');

        $this->actingAs($repro)->get(route('ordenes.excel'))->assertForbidden();
        $this->actingAs($repro)->get(route('reportes.evaluaciones.excel'))->assertForbidden();
        $this->actingAs($repro)->get(route('reportes.empresas.excel'))->assertForbidden();
        $this->actingAs($repro)->get(route('ordenes.index'))->assertOk()->assertDontSee('Exportar Excel');
    }

    public function test_repro_con_generar_si_descarga_excel_de_ordenes(): void
    {
        $repro = User::factory()->create(['role_as' => 2, 'estado' => 1]);
        $repro->roles()->attach(Role::where('name', 'repro')->first());

        $this->actingAs($repro)->get(route('ordenes.excel'))->assertOk();
        $this->actingAs($repro)->get(route('ordenes.index'))->assertOk()->assertSee('Exportar Excel');
    }

    public function test_cliente_sigue_pudiendo_bajar_excel_de_sus_ordenes(): void
    {
        $empresa = Empresa::factory()->create();
        $cliente = User::factory()->create([
            'role_as' => 1,
            'estado' => 1,
            'empresa_id' => $empresa->id,
            'principal' => 1,
        ]);
        $cliente->roles()->attach(Role::where('name', 'empresa')->first());

        $this->actingAs($cliente)->get(route('ordenes.excel'))->assertOk();
    }

    public function test_excel_cliente_omite_orden_confidencial_ajena(): void
    {
        $empresa = Empresa::factory()->create();
        $role = Role::where('name', 'empresa')->first();
        $principal = User::factory()->create([
            'role_as' => 1,
            'empresa_id' => $empresa->id,
            'principal' => 1,
            'estado' => 1,
        ]);
        $principal->roles()->attach($role);

        $trabajador = User::factory()->create([
            'role_as' => 1,
            'empresa_id' => $empresa->id,
            'principal' => 0,
            'estado' => 1,
            'permisos' => json_encode(EmpresaPermisosSupport::PERMISOS_DEFAULT_TRABAJADOR),
        ]);
        $trabajador->roles()->attach($role);

        $visible = Orden::factory()->create([
            'empresa_id' => $empresa->id,
            'creado_por' => $principal->id,
            'confidencial' => false,
            'codigo_orden' => 'ORD-VIS-0001',
        ]);
        $oculta = Orden::factory()->create([
            'empresa_id' => $empresa->id,
            'creado_por' => $principal->id,
            'confidencial' => true,
            'codigo_orden' => 'ORD-HID-0002',
        ]);
        EvaluadoOrden::factory()->create(['orden_id' => $visible->id]);
        EvaluadoOrden::factory()->create(['orden_id' => $oculta->id]);

        Excel::fake();
        $this->actingAs($trabajador)->get(route('ordenes.excel'))->assertOk();
        Excel::assertDownloaded(
            'listado-ordenes-'.now()->format('Y-m-d').'.xlsx',
            function (OrdenesExport $export) {
                $codigos = $export->collection()->pluck('codigo_orden')->all();

                return in_array('ORD-VIS-0001', $codigos, true)
                    && ! in_array('ORD-HID-0002', $codigos, true);
            }
        );
    }

    public function test_repro_no_descarga_padron_empresas_sin_permiso(): void
    {
        $repro = User::factory()->create(['role_as' => 2, 'estado' => 1]);
        $repro->roles()->attach(Role::where('name', 'repro')->first());

        $this->assertFalse(ExportacionesSupport::puedeExportarPadronEmpresas($repro));
        $this->actingAs($repro)->get(route('empresas.pdf'))->assertForbidden();
        $this->actingAs($repro)->get(route('empresas.excel'))->assertForbidden();
        $this->actingAs($repro)->get(route('empresas.index'))->assertOk()->assertDontSee(route('empresas.excel'), false);
    }

    public function test_admin_descarga_padron_pdf_y_excel(): void
    {
        $admin = User::factory()->create(['role_as' => 3, 'estado' => 1]);
        $admin->roles()->attach(Role::where('name', 'admin')->first());
        Empresa::factory()->create(['nombre' => 'PADRON TEST SA', 'estado' => 1]);
        Config::create([
            'currency' => 'GTQ Q',
            'currency_simbol' => 'Q',
            'email' => 'test@repro.gt',
        ]);

        $this->actingAs($admin)->get(route('empresas.pdf'))->assertOk();
        $excel = $this->actingAs($admin)->get(route('empresas.excel'))->assertOk();
        $disposition = (string) $excel->headers->get('content-disposition');
        $this->assertTrue(str_contains($disposition, '.xls') || str_contains($disposition, '.xlsx'));
        if (str_contains($disposition, '.xls') && ! str_contains($disposition, '.xlsx')) {
            $this->assertStringContainsString('PADRON TEST SA', $excel->getContent());
        }
    }

    private function crearReproSinPermiso(string $permiso): User
    {
        $repro = User::factory()->create(['role_as' => 2, 'estado' => 1]);
        $repro->roles()->attach(Role::where('name', 'repro')->first());
        $perm = Permission::where('name', $permiso)->firstOrFail();
        Role::where('name', 'repro')->first()->permissions()->detach($perm->id);

        return $repro->fresh();
    }
}
