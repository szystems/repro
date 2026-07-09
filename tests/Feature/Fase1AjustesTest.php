<?php

namespace Tests\Feature;

use App\Models\Empresa;
use App\Models\EvaluadoOrden;
use App\Models\Orden;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Mail;
use Tests\Feature\Concerns\CreatesRolesAndPermissions;
use Tests\TestCase;

class Fase1AjustesTest extends TestCase
{
    use RefreshDatabase, WithFaker, CreatesRolesAndPermissions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpRolesAndPermissions();
        Mail::fake();
    }

    private function crearUsuarioRepro(): User
    {
        $user = User::factory()->create(['role_as' => 3, 'estado' => 1]);
        $user->roles()->attach(Role::where('name', 'admin')->first());
        return $user;
    }

    private function crearUsuarioEmpresa(Empresa $empresa): User
    {
        $user = User::factory()->create([
            'role_as' => 1,
            'estado' => 1,
            'empresa_id' => $empresa->id,
        ]);
        $user->roles()->attach(Role::where('name', 'empresa')->first());
        return $user;
    }

    // ─── 1. Tabla ordenes: observaciones_internas ───

    public function test_orden_usa_observaciones_internas(): void
    {
        $user = $this->crearUsuarioRepro();
        $empresa = Empresa::factory()->create();

        $orden = Orden::factory()->create([
            'empresa_id' => $empresa->id,
            'creado_por' => $user->id,
            'observaciones_internas' => 'Nota interna de prueba',
        ]);

        $this->assertEquals('Nota interna de prueba', $orden->observaciones_internas);
    }

    // ─── 2. tipo_creador en ordenes ───

    public function test_orden_tiene_tipo_creador(): void
    {
        $user = $this->crearUsuarioRepro();
        $empresa = Empresa::factory()->create();

        $orden = Orden::factory()->create([
            'empresa_id' => $empresa->id,
            'creado_por' => $user->id,
            'tipo_creador' => 'repro',
        ]);

        $this->assertEquals('repro', $orden->tipo_creador);
    }

    public function test_orden_tiene_requerimientos_generales(): void
    {
        $user = $this->crearUsuarioRepro();
        $empresa = Empresa::factory()->create();

        $orden = Orden::factory()->create([
            'empresa_id' => $empresa->id,
            'creado_por' => $user->id,
            'requerimientos_generales' => 'Requerimiento de prueba',
        ]);

        $this->assertEquals('Requerimiento de prueba', $orden->requerimientos_generales);
    }

    // ─── 3. Observaciones por evaluado (ya en fillable) ───

    public function test_evaluado_orden_tiene_observaciones_en_fillable(): void
    {
        $user = $this->crearUsuarioRepro();
        $empresa = Empresa::factory()->create();
        $orden = Orden::factory()->create([
            'empresa_id' => $empresa->id,
            'creado_por' => $user->id,
        ]);

        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'observaciones' => 'Evaluado llegó tarde',
        ]);

        $this->assertEquals('Evaluado llegó tarde', $evaluado->observaciones);
    }

    // ─── 4. Prioridad/fecha_limite solo REPRO ───

    public function test_repro_ve_prioridad_en_ordenes_index(): void
    {
        $user = $this->crearUsuarioRepro();
        $empresa = Empresa::factory()->create();
        Orden::factory()->create([
            'empresa_id' => $empresa->id,
            'creado_por' => $user->id,
            'prioridad' => 'urgente',
        ]);

        $response = $this->actingAs($user)->get(route('ordenes.index'));
        $response->assertStatus(200);
        $response->assertSee('Prioridad');
    }

    public function test_empresa_no_ve_prioridad_en_ordenes_index(): void
    {
        $empresa = Empresa::factory()->create();
        $user = $this->crearUsuarioEmpresa($empresa);
        Orden::factory()->create([
            'empresa_id' => $empresa->id,
            'creado_por' => $user->id,
            'prioridad' => 'urgente',
        ]);

        $response = $this->actingAs($user)->get(route('ordenes.index'));
        $response->assertStatus(200);
        $response->assertDontSee('<th>Prioridad</th>', false);
    }

    public function test_empresa_no_ve_observaciones_internas_en_show(): void
    {
        $empresa = Empresa::factory()->create();
        $user = $this->crearUsuarioEmpresa($empresa);
        $orden = Orden::factory()->create([
            'empresa_id' => $empresa->id,
            'creado_por' => $user->id,
            'observaciones_internas' => 'Nota secreta solo REPRO',
        ]);

        $response = $this->actingAs($user)->get(route('ordenes.show', $orden));
        $response->assertStatus(200);
        $response->assertDontSee('Nota secreta solo REPRO');
    }

    public function test_repro_ve_observaciones_internas_en_show(): void
    {
        $user = $this->crearUsuarioRepro();
        $empresa = Empresa::factory()->create();
        $orden = Orden::factory()->create([
            'empresa_id' => $empresa->id,
            'creado_por' => $user->id,
            'observaciones_internas' => 'Nota interna visible',
        ]);

        $response = $this->actingAs($user)->get(route('ordenes.show', $orden));
        $response->assertStatus(200);
        $response->assertSee('Nota interna visible');
    }

    // ─── 5. Clasificación de colores de resultados ───

    public function test_resultado_poligrafo_colores(): void
    {
        $user = $this->crearUsuarioRepro();
        $empresa = Empresa::factory()->create();
        $orden = Orden::factory()->create([
            'empresa_id' => $empresa->id,
            'creado_por' => $user->id,
        ]);

        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'tipo_servicio' => 'poligrafo',
            'resultado' => 'aprobado',
        ]);
        $this->assertEquals('success', $evaluado->resultado_color);
        $this->assertEquals('Aprobado / Sin Observaciones', $evaluado->resultado_texto);

        $evaluado->resultado = 'aprobado_con_obs';
        $this->assertEquals('warning', $evaluado->resultado_color);

        $evaluado->resultado = 'no_aprobado';
        $this->assertEquals('danger', $evaluado->resultado_color);
    }

    public function test_resultado_socioeconomico_colores(): void
    {
        $user = $this->crearUsuarioRepro();
        $empresa = Empresa::factory()->create();
        $orden = Orden::factory()->create([
            'empresa_id' => $empresa->id,
            'creado_por' => $user->id,
        ]);

        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'tipo_servicio' => 'socioeconomico',
            'resultado' => 'tipo_a',
        ]);
        $this->assertEquals('success', $evaluado->resultado_color);
        $this->assertEquals('Tipo A', $evaluado->resultado_texto);

        $evaluado->resultado = 'a_condicionado';
        $this->assertEquals('warning', $evaluado->resultado_color);

        $evaluado->resultado = 'tipo_b';
        $this->assertEquals('orange', $evaluado->resultado_color);

        $evaluado->resultado = 'tipo_c';
        $this->assertEquals('danger', $evaluado->resultado_color);
    }

    public function test_resultados_por_tipo_servicio_poligrafo(): void
    {
        $resultados = EvaluadoOrden::resultadosPorTipoServicio('poligrafo');

        $this->assertArrayHasKey('aprobado', $resultados);
        $this->assertArrayHasKey('aprobado_con_obs', $resultados);
        $this->assertArrayHasKey('aprobado_excepcion', $resultados);
        $this->assertArrayHasKey('no_aprobado', $resultados);
        $this->assertArrayNotHasKey('tipo_a', $resultados);
    }

    public function test_resultados_por_tipo_servicio_socioeconomico(): void
    {
        $resultados = EvaluadoOrden::resultadosPorTipoServicio('socioeconomico');

        $this->assertArrayHasKey('tipo_a', $resultados);
        $this->assertArrayHasKey('a_condicionado', $resultados);
        $this->assertArrayHasKey('tipo_b', $resultados);
        $this->assertArrayHasKey('tipo_c', $resultados);
        $this->assertArrayNotHasKey('aprobado', $resultados);
    }

    // ─── 6. Regla socioeconómico → preempleo ───

    public function test_socioeconomico_evaluado_preempleo_cuestionario_socio(): void
    {
        $user = $this->crearUsuarioRepro();
        $empresa = Empresa::factory()->create();

        $this->actingAs($user)->post(route('ordenes.store'), [
            'empresa_id' => $empresa->id,
            'evaluados' => [
                [
                    'nombre' => 'Juan',
                    'apellidos' => 'Pérez',
                    'dpi' => '1234567890123',
                    'email' => 'juan@test.com',
                    'tipo_servicio' => 'socioeconomico',
                    'tipo_formulario' => 'periodica',
                ],
            ],
        ]);

        $evaluado = EvaluadoOrden::where('dpi', '1234567890123')->first();
        $this->assertNotNull($evaluado);
        $this->assertEquals('preempleo', $evaluado->tipo_formulario);
        $this->assertEquals('socioeconomico', $evaluado->tipoFormularioCuestionario());
    }

    // ─── 7. Store auto-calcula tipo_creador ───

    public function test_store_auto_calcula_tipo_creador_repro(): void
    {
        $user = $this->crearUsuarioRepro();
        $empresa = Empresa::factory()->create();

        $this->actingAs($user)->post(route('ordenes.store'), [
            'empresa_id' => $empresa->id,
            'evaluados' => [
                [
                    'nombre' => 'Carlos',
                    'apellidos' => 'López',
                    'dpi' => '9876543210123',
                    'email' => 'carlos@test.com',
                    'tipo_servicio' => 'poligrafo',
                    'tipo_formulario' => 'preempleo',
                ],
            ],
        ]);

        $orden = Orden::latest()->first();
        $this->assertNotNull($orden);
        $this->assertEquals('repro', $orden->tipo_creador);
    }

    // ─── 8. Validación estado_civil simplificado ───

    public function test_estado_civil_detalle_acepta_casado_simple(): void
    {
        $request = new \App\Http\Requests\Cuestionario\InformacionFamiliarRequest();
        $rules = $request->rules();

        $this->assertStringContainsString('casado', $rules['estado_civil_detalle']);
        $this->assertStringNotContainsString('casado_civil', $rules['estado_civil_detalle']);
        $this->assertStringNotContainsString('casado_religioso', $rules['estado_civil_detalle']);
        $this->assertStringNotContainsString('separado', $rules['estado_civil_detalle']);
    }

    // ─── 9. Validación codigo_postal eliminado ───

    public function test_codigo_postal_eliminado_de_validacion(): void
    {
        $request = new \App\Http\Requests\Cuestionario\DatosPersonalesRequest();
        $rules = $request->rules();

        $this->assertArrayNotHasKey('codigo_postal', $rules);
    }
}
