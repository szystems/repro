<?php

namespace Tests\Feature;

use App\Models\Empresa;
use App\Models\Orden;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesRolesAndPermissions;
use Tests\TestCase;

class Fase3T1PdfInformeTest extends TestCase
{
    use RefreshDatabase, CreatesRolesAndPermissions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpRolesAndPermissions();
    }

    private function crearAdmin(): User
    {
        $user = User::factory()->create(['role_as' => 3, 'estado' => 1]);
        $user->roles()->attach(Role::where('name', 'admin')->first());
        return $user;
    }

    private function crearOrdenConEvaluado(): Orden
    {
        $empresa = Empresa::factory()->create();
        return Orden::factory()
            ->hasEvaluados(1)
            ->create(['empresa_id' => $empresa->id]);
    }

    // T1a: Ruta ordenes.pdf existe y es accesible (Orden de Servicio)
    public function test_ruta_pdf_orden_servicio_existe(): void
    {
        $admin = $this->crearAdmin();
        $orden = $this->crearOrdenConEvaluado();

        $response = $this->actingAs($admin)->get(route('ordenes.pdf', $orden));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
    }

    // T1b: Ruta ordenes.pdf-informe existe y es accesible (Informe Candidatos)
    public function test_ruta_pdf_informe_candidatos_existe(): void
    {
        $admin = $this->crearAdmin();
        $orden = $this->crearOrdenConEvaluado();

        $response = $this->actingAs($admin)->get(route('ordenes.pdf-informe', $orden));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
    }

    // T1c: Empresa puede acceder al PDF de informe de su propia orden
    public function test_empresa_puede_acceder_pdf_informe_de_su_orden(): void
    {
        $empresa = Empresa::factory()->create();
        $user = User::factory()->create(['role_as' => 1, 'estado' => 1, 'empresa_id' => $empresa->id]);
        $user->roles()->attach(Role::where('name', 'empresa')->first());

        $orden = Orden::factory()->hasEvaluados(1)->create(['empresa_id' => $empresa->id]);

        $response = $this->actingAs($user)->get(route('ordenes.pdf-informe', $orden));

        $response->assertOk();
    }

    // T1d: La vista de detalle muestra el botón PDF de Orden de Servicio (Informe Candidatos eliminado)
    public function test_vista_show_muestra_boton_pdf_orden_de_servicio(): void
    {
        $admin = $this->crearAdmin();
        $orden = $this->crearOrdenConEvaluado();

        $response = $this->actingAs($admin)->get(route('ordenes.show', $orden));

        $response->assertOk();
        $response->assertSee(route('ordenes.pdf', $orden));
        $response->assertSee('Orden de Servicio');
        $response->assertDontSee('Informe Candidatos');
    }
}
