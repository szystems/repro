<?php

namespace Tests\Feature;

use App\Models\DocumentoEvaluado;
use App\Models\Empresa;
use App\Models\EvaluadoOrden;
use App\Models\Orden;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class Fase3CO5VistaPreviaDocumentoTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $empresaUser;
    protected Empresa $empresa;
    protected Orden $orden;
    protected EvaluadoOrden $evaluado;

    protected function setUp(): void
    {
        parent::setUp();
        Role::create(['name' => 'admin', 'display_name' => 'Administrador']);
        Role::create(['name' => 'empresa', 'display_name' => 'Empresa']);
        Role::create(['name' => 'repro', 'display_name' => 'REPRO']);
        Storage::fake('local');

        $this->empresa = Empresa::factory()->create();

        $this->adminUser = User::factory()->create(['role_as' => 3, 'estado' => 1]);
        $this->adminUser->roles()->attach(Role::where('name', 'admin')->first());

        $this->empresaUser = User::factory()->create([
            'role_as' => 1,
            'estado' => 1,
            'empresa_id' => $this->empresa->id,
        ]);
        $this->empresaUser->roles()->attach(Role::where('name', 'empresa')->first());

        $this->orden = Orden::factory()->create([
            'empresa_id' => $this->empresa->id,
            'creado_por' => $this->adminUser->id,
        ]);

        $this->evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $this->orden->id,
        ]);
    }

    /** @test */
    public function test_ruta_preview_documento_existe_y_sirve_imagen(): void
    {
        Storage::disk('local')->put('documentos_evaluados/' . $this->evaluado->id . '/foto.jpg', 'fakeimagecontent');
        $ruta = 'documentos_evaluados/' . $this->evaluado->id . '/foto.jpg';

        $doc = DocumentoEvaluado::factory()->create([
            'evaluado_orden_id'  => $this->evaluado->id,
            'ruta_archivo'       => $ruta,
            'nombre_original'    => 'foto.jpg',
            'mime_type'          => 'image/jpeg',
            'subido_por_tipo'    => 'repro',
            'subido_por_user_id' => $this->adminUser->id,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->get(route('documentos-evaluado.preview', $doc));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'image/jpeg');
    }

    /** @test */
    public function test_ruta_preview_documento_sirve_pdf_inline(): void
    {
        Storage::disk('local')->put('documentos_evaluados/' . $this->evaluado->id . '/test.pdf', '%PDF-1.4 fake content');

        $doc = DocumentoEvaluado::factory()->create([
            'evaluado_orden_id'  => $this->evaluado->id,
            'ruta_archivo'       => 'documentos_evaluados/' . $this->evaluado->id . '/test.pdf',
            'nombre_original'    => 'test.pdf',
            'mime_type'          => 'application/pdf',
            'subido_por_tipo'    => 'repro',
            'subido_por_user_id' => $this->adminUser->id,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->get(route('documentos-evaluado.preview', $doc));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
    }

    /** @test */
    public function test_empresa_puede_ver_preview_de_documento_de_su_orden(): void
    {
        Storage::disk('local')->put('documentos_evaluados/' . $this->evaluado->id . '/doc_empresa.jpg', 'fakeimagecontent');
        $ruta = 'documentos_evaluados/' . $this->evaluado->id . '/doc_empresa.jpg';

        $doc = DocumentoEvaluado::factory()->create([
            'evaluado_orden_id'  => $this->evaluado->id,
            'ruta_archivo'       => $ruta,
            'nombre_original'    => 'doc_empresa.jpg',
            'mime_type'          => 'image/jpeg',
            'subido_por_tipo'    => 'empresa',
            'subido_por_user_id' => $this->empresaUser->id,
        ]);

        $response = $this->actingAs($this->empresaUser)
            ->get(route('documentos-evaluado.preview', $doc));

        $response->assertStatus(200);
    }

    /** @test */
    public function test_preview_requiere_autenticacion(): void
    {
        Storage::disk('local')->put('documentos_evaluados/1/x.pdf', 'fake');

        $doc = DocumentoEvaluado::factory()->create([
            'evaluado_orden_id'  => $this->evaluado->id,
            'ruta_archivo'       => 'documentos_evaluados/1/x.pdf',
            'nombre_original'    => 'x.pdf',
            'mime_type'          => 'application/pdf',
            'subido_por_tipo'    => 'repro',
            'subido_por_user_id' => $this->adminUser->id,
        ]);

        $response = $this->get(route('documentos-evaluado.preview', $doc));
        $response->assertRedirect(route('login'));
    }
}
