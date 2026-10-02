<?php

namespace Tests\Feature;

use App\Models\Empresa;
use App\Models\EvaluadoOrden;
use App\Models\Orden;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
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
        $response->assertSee('Resultado:</th><th>Observaciones:', false);
        $response->assertSee('<tbody><tr><td><br></td><td><br></td></tr></tbody>', false);
        $response->assertDontSee('<tbody><tr><td><br></td><td><br></td></tr><tr><td><br></td><td><br></td></tr></tbody>', false);
        $response->assertSee('Color de letra');
        $response->assertSee('repro-swatch-color');
        $response->assertSee('reproTabla');
        $response->assertSee('attributors/style/color', false);
        $response->assertSee('attributors/style/background', false);
        $response->assertSee('foreColor');
        $response->assertSee('permitirPortapapelesEnTabla');
    }

    /** @test */
    public function q_q1_conserva_color_y_tablas_y_limpia_xss(): void
    {
        $html = '<p><span style="color: rgb(255, 0, 0); font-weight: bold; background-image: url(javascript:alert(1))">Rojo</span></p>'
            .'<p><font color="#e60000"><b>Negrita roja</b></font></p>'
            .'<p><span class="ql-color-#0033cc ql-bg-#ffff00">Clase</span></p>'
            .'<table><tr><td onclick="alert(1)"><b style="font-weight: bold">Celda</b></td></tr></table>'
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
        $this->assertStringContainsString('<b>Negrita roja</b>', $guardado);
        $this->assertStringContainsString('color: #e60000', $guardado);
        $this->assertStringContainsString('color: #0033cc', $guardado);
        $this->assertStringContainsString('background-color: #ffff00', $guardado);
        $this->assertStringContainsString('font-weight: bold', $guardado);
        $this->assertStringNotContainsString('<font', $guardado);
        $this->assertStringNotContainsString('ql-color', $guardado);
        $this->assertStringNotContainsString('onclick', $guardado);
        $this->assertStringNotContainsString('<img', $guardado);
        $this->assertStringNotContainsString('javascript', $guardado);
        $this->assertStringNotContainsString('background-image', $guardado);
    }

    /** @test */
    public function co6_guarda_la_hora_de_la_tabla_y_la_muestra_en_la_ficha(): void
    {
        $this->travelTo(Carbon::parse('2026-10-02 10:31:00'));

        $this->actingAs($this->admin)
            ->patch(route('evaluados.guardar-informe-preliminar', $this->evaluado->id), [
                'texto_informe_preliminar' => '<p>Tabla de resultado</p>',
            ])
            ->assertRedirect();

        $this->assertSame(
            '2026-10-02 10:31:00',
            $this->evaluado->fresh()->informe_preliminar_at?->format('Y-m-d H:i:s')
        );

        $this->travelTo(Carbon::parse('2026-10-02 15:00:00'));

        $this->actingAs($this->admin)
            ->patch(route('evaluados.guardar-informe-preliminar', $this->evaluado->id), [
                'texto_informe_preliminar' => '<p>Tabla corregida</p>',
            ])
            ->assertRedirect();

        $fresco = $this->evaluado->fresh();
        $this->assertStringContainsString('Tabla corregida', (string) $fresco->texto_informe_preliminar);
        $this->assertSame('2026-10-02 10:31:00', $fresco->informe_preliminar_at?->format('Y-m-d H:i:s'));

        $this->actingAs($this->admin)
            ->get(route('ordenes.show', $this->orden->id))
            ->assertOk()
            ->assertSee('Generado el 02/10/2026 10:31', false);

        $this->actingAs($this->admin)
            ->patch(route('evaluados.guardar-informe-preliminar', $this->evaluado->id), [
                'texto_informe_preliminar' => null,
            ])
            ->assertRedirect();

        $this->assertNull($this->evaluado->fresh()->informe_preliminar_at);
    }

    /** @test */
    public function co6_no_inventa_fecha_en_un_preliminar_que_ya_existia(): void
    {
        $this->evaluado->update([
            'texto_informe_preliminar' => '<p>Ya existía</p>',
            'informe_preliminar_at' => null,
        ]);
        $this->travelTo(Carbon::parse('2026-10-02 10:31:00'));

        $this->actingAs($this->admin)
            ->patch(route('evaluados.guardar-informe-preliminar', $this->evaluado->id), [
                'texto_informe_preliminar' => '<p>Ya existía, editado</p>',
            ])
            ->assertRedirect();

        $this->assertNull($this->evaluado->fresh()->informe_preliminar_at);
    }

    /** @test */
    public function co6_el_cliente_ve_la_fecha_de_la_tabla_y_la_del_archivo(): void
    {
        $this->orden->update([
            'resultados_visibles_empresa' => true,
            'estado' => 'entregado',
        ]);
        $this->evaluado->update([
            'cuestionario_completado' => true,
            'texto_informe_preliminar' => '<p>Informe de polígrafo</p>',
            'informe_preliminar_at' => '2026-10-02 10:31:00',
            'archivo_resultado_preliminar' => 'resultados/1/prelim.pdf',
            'resultado_preliminar_at' => '2026-10-02 11:05:00',
        ]);

        $this->actingAs($this->empresaUser)
            ->get(route('empresa.cuestionarios.show', $this->evaluado))
            ->assertOk()
            ->assertSee('Fecha y hora: 02/10/2026 10:31', false)
            ->assertSee('Subido el 02/10/2026 11:05', false);
    }
}
