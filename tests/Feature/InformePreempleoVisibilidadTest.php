<?php

namespace Tests\Feature;

use App\Models\Cuestionario;
use App\Models\CuestionarioRespuesta;
use App\Models\Empresa;
use App\Models\EvaluadoOrden;
use App\Models\EvaluadorNota;
use App\Models\Orden;
use App\Models\Role;
use App\Models\User;
use App\Support\InformePreempleo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesRolesAndPermissions;
use Tests\TestCase;

class InformePreempleoVisibilidadTest extends TestCase
{
    use RefreshDatabase, CreatesRolesAndPermissions;

    private Cuestionario $cuestionario;

    private Empresa $empresaCliente;

    private EvaluadoOrden $evaluado;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpRolesAndPermissions();

        $this->empresaCliente = Empresa::factory()->create();
        $orden = Orden::factory()->create([
            'empresa_id' => $this->empresaCliente->id,
            'estado' => 'entregado',
            'resultados_visibles_empresa' => true,
        ]);
        $this->evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'tipo_formulario' => 'preempleo',
            'cuestionario_completado' => true,
            'cuestionario_completado_at' => now(),
        ]);

        $this->cuestionario = Cuestionario::create([
            'evaluado_orden_id' => $this->evaluado->id,
            'tipo_formulario' => 'preempleo',
            'seccion_actual' => 6,
            'total_secciones' => 6,
            'progreso_porcentaje' => 100,
            'estado' => 'completado',
            'completado' => true,
            'completado_at' => now(),
        ]);

        CuestionarioRespuesta::guardarRespuestas($this->cuestionario->id, 'historial_laboral', [
            'integridad_01' => 'Texto confidencial integridad',
            'motivo_busqueda' => 'Crecimiento profesional',
        ]);

        CuestionarioRespuesta::guardarTabla($this->cuestionario->id, 'historial_laboral', 'formacion_academica', [
            ['institucion' => 'Universidad Demo', 'titulo' => 'Licenciatura', 'anio' => '2020'],
        ]);

        CuestionarioRespuesta::guardarRespuestas($this->cuestionario->id, 'antecedentes', [
            'salud_estado_general' => 'Confidencial salud',
            'comp_metas' => 'Metas visibles para empresa',
        ]);
    }

    private function crearRepro(): User
    {
        $user = User::factory()->create(['role_as' => 2, 'estado' => 1]);
        $user->roles()->attach(Role::where('name', 'repro')->first());

        return $user;
    }

    private function crearEmpresa(): User
    {
        $user = User::factory()->create([
            'role_as' => 1,
            'estado' => 1,
            'empresa_id' => $this->empresaCliente->id,
        ]);
        $user->roles()->attach(Role::where('name', 'empresa')->first());

        return $user;
    }

    public function test_repro_ve_tablas_informe_en_show_y_edit(): void
    {
        $repro = $this->crearRepro();

        $this->actingAs($repro)
            ->get(route('admin.cuestionarios.show', $this->cuestionario->id))
            ->assertOk()
            ->assertSee('Tablas para informe')
            ->assertSee('Formación académica');

        $this->actingAs($repro)
            ->get(route('admin.cuestionarios.edit', $this->cuestionario->id))
            ->assertOk()
            ->assertSee('name="informe_tablas[complementaria]', false);
    }

    public function test_repro_guarda_override_tabla_informe(): void
    {
        $repro = $this->crearRepro();

        $this->actingAs($repro)
            ->put(route('admin.cuestionarios.update', $this->cuestionario->id), [
                'informe_tablas' => [
                    'complementaria' => [
                        ['pregunta' => '¿Metas?', 'respuesta' => 'Editado por evaluador'],
                    ],
                ],
            ])
            ->assertRedirect(route('admin.cuestionarios.show', $this->cuestionario->id));

        $this->assertDatabaseHas('evaluador_notas', [
            'evaluado_orden_id' => $this->cuestionario->evaluado_orden_id,
            'seccion' => InformePreempleo::SECCION_NOTAS,
            'campo' => 'complementaria',
        ]);

        $tablas = InformePreempleo::tablasParaAdmin($this->cuestionario);
        $this->assertSame('Editado por evaluador', $tablas['complementaria'][0]['respuesta'] ?? null);
    }

    public function test_empresa_no_ve_campos_internos_ni_tablas_informe(): void
    {
        EvaluadorNota::guardarNota(
            $this->cuestionario->evaluado_orden_id,
            InformePreempleo::SECCION_NOTAS,
            'academico',
            json_encode([['institucion' => 'Nota interna academia']]),
            $this->crearRepro()->id
        );

        $empresaUser = $this->crearEmpresa();

        $this->actingAs($empresaUser)
            ->get(route('empresa.cuestionarios.show', $this->evaluado->id))
            ->assertOk()
            ->assertDontSee('Tablas para informe')
            ->assertDontSee('Texto confidencial integridad')
            ->assertDontSee('Confidencial salud')
            ->assertSee('Metas visibles para empresa')
            ->assertSee('section-title', false)
            ->assertSee('Universidad Demo')
            ->assertSee('Formación académica');
    }

    public function test_pdf_empresa_no_incluye_campos_internos(): void
    {
        $html = view('pdf.cuestionario_empresa', [
            'evaluado' => $this->evaluado->load(['cuestionario', 'documentos', 'responsable', 'orden.empresa']),
        ])->render();

        $this->assertStringNotContainsString('Texto confidencial integridad', $html);
        $this->assertStringNotContainsString('Confidencial salud', $html);
        $this->assertStringContainsString('Metas visibles para empresa', $html);
        $this->assertStringContainsString('Universidad Demo', $html);
        $this->assertStringContainsString('Formación académica', $html);
        $this->assertStringContainsString('subseccion-titulo', $html);
    }

    public function test_pdf_empresa_socio_incluye_seccion_6_referencias(): void
    {
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $this->evaluado->orden_id,
            'tipo_servicio' => 'socioeconomico',
            'tipo_formulario' => 'preempleo',
            'cuestionario_completado' => true,
        ]);

        $cuestionario = Cuestionario::create([
            'evaluado_orden_id' => $evaluado->id,
            'tipo_formulario' => 'socioeconomico',
            'seccion_actual' => 6,
            'total_secciones' => 6,
            'progreso_porcentaje' => 100,
            'estado' => 'completado',
            'completado' => true,
        ]);

        CuestionarioRespuesta::guardarTabla($cuestionario->id, 'informacion_socioeconomica_complementaria', 'referencias_familiares', [
            ['nombre' => 'Fam PDF', 'parentesco' => 'Tía', 'telefono' => '50233333333', 'direccion' => 'Zona 10'],
        ]);
        CuestionarioRespuesta::guardarTabla($cuestionario->id, 'informacion_socioeconomica_complementaria', 'referencias_vecinales', [
            ['nombre' => 'Vecino Oculto', 'telefono' => '50244444444', 'direccion' => 'Colonia X', 'tiempo_conocerlo' => '1 año'],
        ]);
        CuestionarioRespuesta::guardarRespuestas($cuestionario->id, 'informacion_socioeconomica_complementaria', [
            'viv_tiempo_residencia' => '2 años',
            'bienes_total' => '15000',
        ]);

        $html = view('pdf.cuestionario_empresa', [
            'evaluado' => $evaluado->load(['cuestionario', 'documentos', 'responsable', 'orden.empresa']),
        ])->render();

        $this->assertStringContainsString('Sección 6:', $html);
        $this->assertStringContainsString('Referencias familiares', $html);
        $this->assertStringContainsString('Fam PDF', $html);
        $this->assertStringNotContainsString('Vecino Oculto', $html);
        $this->assertStringNotContainsString('viv_tiempo_residencia', $html);
    }

    public function test_empresa_no_puede_guardar_tablas_informe(): void
    {
        $empresaUser = User::factory()->create(['role_as' => 1, 'estado' => 1]);
        $empresaUser->roles()->attach(Role::where('name', 'empresa')->first());

        $this->actingAs($empresaUser)
            ->put(route('admin.cuestionarios.update', $this->cuestionario->id), [
                'informe_tablas' => ['deudas' => [['entidad' => 'Hack']]],
            ])
            ->assertForbidden();
    }
}
