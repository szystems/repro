<?php

namespace Tests\Feature;

use App\Models\Empresa;
use App\Models\EvaluadoOrden;
use App\Models\Orden;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesRolesAndPermissions;
use Tests\TestCase;

/**
 * Tests para Fase 7: CO6 — Editor de texto enriquecido para informe preliminar.
 */
class Fase7EditorInformePreliminarTest extends TestCase
{
    use RefreshDatabase, CreatesRolesAndPermissions;

    protected User $admin;
    protected User $repro;
    protected User $empresaUser;
    protected Empresa $empresa;
    protected Orden $orden;
    protected EvaluadoOrden $evaluado;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpRolesAndPermissions();

        $this->admin       = User::factory()->create(['role_as' => 3]);
        $this->repro       = User::factory()->create(['role_as' => 2]);
        $this->repro->roles()->attach(Role::where('name', 'repro')->first());
        $this->empresa     = Empresa::factory()->create(['estado' => 1]);
        $this->empresaUser = User::factory()->create(['role_as' => 1, 'empresa_id' => $this->empresa->id]);
        $this->empresaUser->roles()->attach(Role::where('name', 'empresa')->first());

        $this->orden = Orden::factory()->create([
            'empresa_id' => $this->empresa->id,
            'estado'     => 'en_proceso',
        ]);

        $this->evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $this->orden->id,
        ]);
    }

    /** @test */
    public function co6_admin_puede_guardar_informe_preliminar(): void
    {
        $texto = '<p>Este es el <strong>informe preliminar</strong> del evaluado.</p>';

        $response = $this->actingAs($this->admin)
            ->patch(route('evaluados.guardar-informe-preliminar', $this->evaluado->id), [
                'texto_informe_preliminar' => $texto,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('evaluados_orden', [
            'id'                       => $this->evaluado->id,
            'texto_informe_preliminar' => $texto,
        ]);
    }

    /** @test */
    public function co6_repro_puede_guardar_informe_preliminar(): void
    {
        $texto = '<p>Informe redactado por repro.</p>';

        $response = $this->actingAs($this->repro)
            ->patch(route('evaluados.guardar-informe-preliminar', $this->evaluado->id), [
                'texto_informe_preliminar' => $texto,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('evaluados_orden', [
            'id'                       => $this->evaluado->id,
            'texto_informe_preliminar' => $texto,
        ]);
    }

    /** @test */
    public function co6_empresa_no_puede_guardar_informe_preliminar(): void
    {
        $response = $this->actingAs($this->empresaUser)
            ->patch(route('evaluados.guardar-informe-preliminar', $this->evaluado->id), [
                'texto_informe_preliminar' => '<p>Intento empresa.</p>',
            ]);

        // El middleware bloquea con 403 (empresa no tiene permiso informe_preliminar.editar)
        $response->assertForbidden();

        $this->assertDatabaseMissing('evaluados_orden', [
            'id'                       => $this->evaluado->id,
            'texto_informe_preliminar' => '<p>Intento empresa.</p>',
        ]);
    }

    /** @test */
    public function co6_informe_puede_guardarse_vacio(): void
    {
        $this->evaluado->update(['texto_informe_preliminar' => '<p>Texto previo</p>']);

        $response = $this->actingAs($this->admin)
            ->patch(route('evaluados.guardar-informe-preliminar', $this->evaluado->id), [
                'texto_informe_preliminar' => null,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('evaluados_orden', [
            'id'                       => $this->evaluado->id,
            'texto_informe_preliminar' => null,
        ]);
    }

    /** @test */
    public function co6_ruta_requiere_autenticacion(): void
    {
        $response = $this->patch(route('evaluados.guardar-informe-preliminar', $this->evaluado->id), [
            'texto_informe_preliminar' => '<p>Test</p>',
        ]);

        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function co6_show_orden_muestra_editor_para_admin(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('ordenes.show', $this->orden->id));

        $response->assertStatus(200);
        $response->assertSee('Informe Preliminar');
        $response->assertSee('— ' . trim($this->evaluado->nombre.' '.$this->evaluado->apellidos), false);
        $response->assertSee('editor-preliminar-' . $this->evaluado->id);
        $response->assertSee("color': []", false);
        $response->assertSee('Insertar tabla');
        $response->assertSee('Agregar fila');
        $response->assertSee('Eliminar fila');
        $response->assertSee('Color de letra');
        $response->assertSee('repro-swatch-color');
        $response->assertSee('reproTabla');
        $response->assertSee('foreColor');
    }

    /** @test */
    public function q_q1_conserva_color_y_tablas_y_limpia_xss(): void
    {
        $html = '<p><span style="color: rgb(255, 0, 0); font-weight: bold; background-image: url(javascript:alert(1))">Rojo</span></p>'
            .'<table><tr><td onclick="alert(1)">Celda</td></tr></table>'
            .'<img src=x onerror=alert(1)>';

        $response = $this->actingAs($this->admin)
            ->patch(route('evaluados.guardar-informe-preliminar', $this->evaluado->id), [
                'texto_informe_preliminar' => $html,
            ]);

        $response->assertRedirect();
        $guardado = $this->evaluado->fresh()->texto_informe_preliminar;

        $this->assertStringContainsString('<table>', $guardado);
        $this->assertStringContainsString('Celda', $guardado);
        $this->assertStringContainsString('color: rgb(255, 0, 0)', $guardado);
        $this->assertStringNotContainsString('onclick', $guardado);
        $this->assertStringNotContainsString('<img', $guardado);
        $this->assertStringNotContainsString('javascript', $guardado);
        $this->assertStringNotContainsString('background-image', $guardado);
    }
}
