<?php

namespace Tests\Feature;

use App\Models\EvaluadoOrden;
use App\Models\Orden;
use App\Models\Role;
use App\Models\User;
use App\Support\InformeWordExport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesRolesAndPermissions;
use Tests\TestCase;

class InformeWordExportTest extends TestCase
{
    use RefreshDatabase, CreatesRolesAndPermissions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpRolesAndPermissions();
    }

    public function test_genera_docx_descargable(): void
    {
        $orden = Orden::factory()->create();
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'texto_informe_preliminar' => 'Conclusión preliminar de prueba.',
        ]);

        $path = InformeWordExport::generar($orden, $evaluado);

        $this->assertFileExists($path);
        $this->assertStringEndsWith('.docx', $path);

        @unlink($path);
    }

    public function test_ruta_informe_word_responde_ok(): void
    {
        $admin = User::factory()->create(['role_as' => 3]);
        $admin->roles()->attach(Role::where('name', 'admin')->first());

        $orden = Orden::factory()->create();
        $evaluado = EvaluadoOrden::factory()->create(['orden_id' => $orden->id]);

        $response = $this->actingAs($admin)->get(route('ordenes.informe-word', [$orden, $evaluado]));

        $response->assertOk();
        $response->assertHeader('content-disposition');
    }
}
