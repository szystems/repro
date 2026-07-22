<?php

namespace Tests\Feature;

use App\Models\Cuestionario;
use App\Models\CuestionarioRespuesta;
use App\Models\Empresa;
use App\Models\EvaluadoOrden;
use App\Models\Orden;
use App\Models\Role;
use App\Models\User;
use App\Support\CuestionarioFotoCandidato;
use App\Support\CuestionarioSecciones;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\Feature\Concerns\CreatesRolesAndPermissions;
use Tests\Support\FakeImage;
use Tests\TestCase;

class AdminCuestionarioFotoEditTest extends TestCase
{
    use RefreshDatabase, CreatesRolesAndPermissions;

    private Cuestionario $cuestionario;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpRolesAndPermissions();
        Storage::fake('local');

        $empresa = Empresa::factory()->create();
        $orden = Orden::factory()->create(['empresa_id' => $empresa->id]);
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'tipo_formulario' => 'preempleo',
        ]);

        $this->cuestionario = Cuestionario::create([
            'evaluado_orden_id' => $evaluado->id,
            'tipo_formulario' => 'preempleo',
            'seccion_actual' => 1,
            'total_secciones' => 6,
            'progreso_porcentaje' => 10,
            'estado' => 'en_progreso',
            'completado' => false,
        ]);
    }

    private function crearRepro(): User
    {
        $user = User::factory()->create(['role_as' => 2, 'estado' => 1]);
        $user->roles()->attach(Role::where('name', 'repro')->first());

        return $user;
    }

    public function test_edit_muestra_foto_y_documentos(): void
    {
        $repro = $this->crearRepro();
        $seccionSlug = CuestionarioSecciones::slug(1, 'preempleo');
        $ruta = 'cuestionarios/fotos/' . $this->cuestionario->id . '/foto_candidato.jpg';
        Storage::disk('local')->put($ruta, FakeImage::jpeg());

        CuestionarioRespuesta::create([
            'cuestionario_id' => $this->cuestionario->id,
            'seccion' => $seccionSlug,
            'campo' => CuestionarioFotoCandidato::CAMPO,
            'valor' => $ruta,
            'tipo_campo' => 'file',
            'requerido' => true,
        ]);

        $this->actingAs($repro)
            ->get(route('admin.cuestionarios.edit', $this->cuestionario->id))
            ->assertOk()
            ->assertSee('Fotografía del candidato', false)
            ->assertSee(route('admin.cuestionarios.foto-candidato', $this->cuestionario), false)
            ->assertSee('Documentos —', false);
    }

    public function test_show_muestra_foto_candidato(): void
    {
        $repro = $this->crearRepro();
        $seccionSlug = CuestionarioSecciones::slug(1, 'preempleo');
        $ruta = 'cuestionarios/fotos/' . $this->cuestionario->id . '/foto_candidato.jpg';
        Storage::disk('local')->put($ruta, FakeImage::jpeg());

        CuestionarioRespuesta::create([
            'cuestionario_id' => $this->cuestionario->id,
            'seccion' => $seccionSlug,
            'campo' => CuestionarioFotoCandidato::CAMPO,
            'valor' => $ruta,
            'tipo_campo' => 'file',
            'requerido' => true,
        ]);

        $this->actingAs($repro)
            ->get(route('admin.cuestionarios.show', $this->cuestionario->id))
            ->assertOk()
            ->assertSee('Fotografía del candidato', false)
            ->assertSee(route('admin.cuestionarios.foto-candidato', $this->cuestionario), false);
    }

    public function test_update_guarda_foto_candidato(): void
    {
        $repro = $this->crearRepro();
        $archivo = FakeImage::jpeg('foto-nueva.jpg');

        $this->actingAs($repro)
            ->put(route('admin.cuestionarios.update', $this->cuestionario->id), [
                'foto_candidato' => $archivo,
            ])
            ->assertRedirect(route('admin.cuestionarios.show', $this->cuestionario->id));

        $seccionSlug = CuestionarioSecciones::slug(1, 'preempleo');
        $ruta = CuestionarioFotoCandidato::obtenerRuta($this->cuestionario->id, $seccionSlug);

        $this->assertNotNull($ruta);
        Storage::disk('local')->assertExists($ruta);

        $this->actingAs($repro)
            ->get(route('admin.cuestionarios.foto-candidato', $this->cuestionario->id))
            ->assertOk();
    }
}
