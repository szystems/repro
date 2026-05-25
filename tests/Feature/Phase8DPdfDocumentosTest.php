<?php

namespace Tests\Feature;

use App\Models\Cuestionario;
use App\Models\DocumentoEvaluado;
use App\Models\Empresa;
use App\Models\EvaluadoOrden;
use App\Models\Orden;
use App\Models\Role;
use App\Models\Sede;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\Feature\Concerns\CreatesRolesAndPermissions;
use Tests\TestCase;

class Phase8DPdfDocumentosTest extends TestCase
{
    use RefreshDatabase, WithFaker, CreatesRolesAndPermissions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpRolesAndPermissions();
    }

    // ─── Helpers ───

    private function crearAdmin(): User
    {
        $user = User::factory()->create(['role_as' => 3, 'estado' => 1]);
        $user->roles()->attach(Role::where('name', 'admin')->first());
        return $user;
    }

    private function crearRepro(?Sede $sede = null): User
    {
        $user = User::factory()->create([
            'role_as' => 2,
            'estado' => 1,
            'sede_id' => $sede?->id,
            'cargo' => 'Poligrafista Senior',
        ]);
        $user->roles()->attach(Role::where('name', 'repro')->first());
        return $user;
    }

    private function crearEmpresa(?Empresa $empresa = null): User
    {
        $empresa ??= Empresa::factory()->create();
        $user = User::factory()->create([
            'role_as' => 1,
            'empresa_id' => $empresa->id,
            'principal' => true,
        ]);
        $user->roles()->attach(Role::where('name', 'empresa')->first());
        return $user;
    }

    private function crearOrdenConEvaluado(?Empresa $empresa = null, ?User $responsable = null): array
    {
        $empresa ??= Empresa::factory()->create();
        $admin = $this->crearAdmin();

        $orden = Orden::factory()->create([
            'empresa_id' => $empresa->id,
            'creado_por' => $admin->id,
        ]);

        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'tipo_servicio' => 'poligrafo',
            'tipo_formulario' => 'preempleo',
            'responsable_id' => $responsable?->id,
        ]);

        return [$orden, $evaluado, $admin];
    }

    // ─── 8D.3: Campo responsable_id ───

    public function test_evaluado_puede_tener_responsable(): void
    {
        $repro = $this->crearRepro();
        $empresa = Empresa::factory()->create();
        [$orden, $evaluado] = $this->crearOrdenConEvaluado($empresa, $repro);

        $this->assertDatabaseHas('evaluados_orden', [
            'id' => $evaluado->id,
            'responsable_id' => $repro->id,
        ]);

        $evaluado->refresh();
        $this->assertNotNull($evaluado->responsable);
        $this->assertEquals($repro->id, $evaluado->responsable->id);
    }

    public function test_responsable_id_es_nullable(): void
    {
        $empresa = Empresa::factory()->create();
        [$orden, $evaluado] = $this->crearOrdenConEvaluado($empresa);

        $this->assertDatabaseHas('evaluados_orden', [
            'id' => $evaluado->id,
            'responsable_id' => null,
        ]);
    }

    public function test_responsable_se_muestra_en_show_orden(): void
    {
        $responsable = $this->crearRepro();
        $responsable->update(['cargo' => 'Poligrafista']);
        $empresa = Empresa::factory()->create();
        [$orden, $evaluado, $admin] = $this->crearOrdenConEvaluado($empresa, $responsable);

        $response = $this->actingAs($admin)->get(route('ordenes.show', $orden));

        $response->assertStatus(200);
        $response->assertSee('Resp: ' . $responsable->name);
    }

    public function test_responsable_select_en_modal_programar(): void
    {
        $empresa = Empresa::factory()->create();
        [$orden, $evaluado, $admin] = $this->crearOrdenConEvaluado($empresa);

        $response = $this->actingAs($admin)->get(route('ordenes.show', $orden));

        $response->assertStatus(200);
        $response->assertSee('Responsable del Proceso');
    }

    public function test_programar_evaluacion_guarda_responsable(): void
    {
        $repro = $this->crearRepro();
        $empresa = Empresa::factory()->create();
        [$orden, $evaluado] = $this->crearOrdenConEvaluado($empresa);

        $evaluado->programarEvaluacion(
            now()->addDay()->setTime(9, 0)->format('Y-m-d H:i:s'),
            now()->addDay()->setTime(11, 0)->format('Y-m-d H:i:s'),
            $repro->id,
            null,
            'presencial',
            $repro->id
        );

        $evaluado->refresh();
        $this->assertEquals($repro->id, $evaluado->responsable_id);
        $this->assertEquals('programado', $evaluado->estado_evaluacion);
    }

    public function test_reprogramar_evaluacion_actualiza_responsable(): void
    {
        $repro1 = $this->crearRepro();
        $repro2 = $this->crearRepro();
        $empresa = Empresa::factory()->create();
        [$orden, $evaluado] = $this->crearOrdenConEvaluado($empresa, $repro1);

        $evaluado->reprogramarEvaluacion(
            now()->addDays(2)->setTime(10, 0)->format('Y-m-d H:i:s'),
            now()->addDays(2)->setTime(12, 0)->format('Y-m-d H:i:s'),
            $repro2->id,
            null,
            'virtual',
            $repro2->id
        );

        $evaluado->refresh();
        $this->assertEquals($repro2->id, $evaluado->responsable_id);
    }

    // ─── 8D.1: Autorización/términos en PDF ───

    public function test_pdf_cuestionario_incluye_autorizacion(): void
    {
        $empresa = Empresa::factory()->create();
        [$orden, $evaluado, $admin] = $this->crearOrdenConEvaluado($empresa);

        $cuestionario = Cuestionario::create([
            'evaluado_orden_id' => $evaluado->id,
            'tipo_formulario' => 'preempleo',
            'seccion_actual' => 5,
            'total_secciones' => 5,
            'progreso_porcentaje' => 100,
            'completado' => true,
            'acepta_terminos' => true,
            'acepta_terminos_at' => now()->subDay(),
            'firma_digital' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwADhQGAWjR9awAAAABJRU5ErkJggg==',
            'completado_at' => now()->subDay(),
        ]);

        // Use the PDF view directly with the data that would be passed
        $view = view('admin.cuestionarios.pdf', [
            'cuestionario' => $cuestionario->load(['evaluadoOrden.orden.empresa', 'evaluadoOrden.documentos', 'evaluadoOrden.responsable']),
            'respuestasPorSeccion' => collect(),
        ])->render();

        $this->assertStringContainsString('AUTORIZACIÓN PARA EVALUACIÓN', $view);
        $this->assertStringContainsString($evaluado->nombre, $view);
        $this->assertStringContainsString($evaluado->dpi, $view);
        $this->assertStringContainsString('voluntaria', $view);
        $this->assertStringContainsString('Firmado digitalmente', $view);
    }

    public function test_pdf_cuestionario_incluye_consentimiento_poligrafo(): void
    {
        $empresa = Empresa::factory()->create();
        [$orden, $evaluado, $admin] = $this->crearOrdenConEvaluado($empresa);
        $evaluado->update(['tipo_servicio' => 'poligrafo']);

        $cuestionario = Cuestionario::create([
            'evaluado_orden_id' => $evaluado->id,
            'tipo_formulario' => 'preempleo',
            'seccion_actual' => 5,
            'total_secciones' => 5,
            'progreso_porcentaje' => 100,
            'completado' => true,
            'acepta_terminos' => true,
            'firma_digital' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwADhQGAWjR9awAAAABJRU5ErkJggg==',
            'completado_at' => now()->subDay(),
        ]);

        $view = view('admin.cuestionarios.pdf', [
            'cuestionario' => $cuestionario->load(['evaluadoOrden.orden.empresa', 'evaluadoOrden.documentos', 'evaluadoOrden.responsable']),
            'respuestasPorSeccion' => collect(),
        ])->render();

        $this->assertStringContainsString('Consentimiento adicional', $view);
        $this->assertStringContainsString('polígrafo', $view);
    }

    // ─── 8D.2: Documentos verificados en PDF ───

    public function test_pdf_cuestionario_incluye_documentos_verificados(): void
    {
        $empresa = Empresa::factory()->create();
        [$orden, $evaluado, $admin] = $this->crearOrdenConEvaluado($empresa);

        // Crear documentos con diferentes estados
        DocumentoEvaluado::factory()->create([
            'evaluado_orden_id' => $evaluado->id,
            'tipo_documento' => 'dpi_archivo',
            'nombre_original' => 'dpi_archivo.pdf',
            'estado_verificacion' => 'aprobado',
        ]);
        DocumentoEvaluado::factory()->create([
            'evaluado_orden_id' => $evaluado->id,
            'tipo_documento' => 'antecedentes_penales',
            'nombre_original' => 'antecedentes.pdf',
            'estado_verificacion' => 'rechazado',
        ]);
        DocumentoEvaluado::factory()->create([
            'evaluado_orden_id' => $evaluado->id,
            'tipo_documento' => 'cv',
            'nombre_original' => 'curriculum.pdf',
            'estado_verificacion' => 'pendiente',
        ]);

        $cuestionario = Cuestionario::create([
            'evaluado_orden_id' => $evaluado->id,
            'tipo_formulario' => 'preempleo',
            'total_secciones' => 5,
            'completado' => true,
            'completado_at' => now()->subDay(),
        ]);

        $view = view('admin.cuestionarios.pdf', [
            'cuestionario' => $cuestionario->load(['evaluadoOrden.orden.empresa', 'evaluadoOrden.documentos', 'evaluadoOrden.responsable']),
            'respuestasPorSeccion' => collect(),
        ])->render();

        $this->assertStringContainsString('Documentos del Evaluado', $view);
        $this->assertStringContainsString('dpi_archivo.pdf', $view);
        $this->assertStringContainsString('antecedentes.pdf', $view);
        $this->assertStringContainsString('curriculum.pdf', $view);
        $this->assertStringContainsString('Aprobado', $view);
        $this->assertStringContainsString('Rechazado', $view);
        $this->assertStringContainsString('Pendiente', $view);
    }

    public function test_pdf_cuestionario_sin_documentos_no_muestra_seccion(): void
    {
        $empresa = Empresa::factory()->create();
        [$orden, $evaluado, $admin] = $this->crearOrdenConEvaluado($empresa);

        $cuestionario = Cuestionario::create([
            'evaluado_orden_id' => $evaluado->id,
            'tipo_formulario' => 'preempleo',
            'total_secciones' => 5,
            'completado' => true,
            'completado_at' => now()->subDay(),
        ]);

        $view = view('admin.cuestionarios.pdf', [
            'cuestionario' => $cuestionario->load(['evaluadoOrden.orden.empresa', 'evaluadoOrden.documentos', 'evaluadoOrden.responsable']),
            'respuestasPorSeccion' => collect(),
        ])->render();

        $this->assertStringNotContainsString('Documentos del Evaluado', $view);
    }

    // ─── 8D.4: Firma/nombre del responsable en PDF ───

    public function test_pdf_cuestionario_incluye_responsable(): void
    {
        $responsable = $this->crearRepro();
        $responsable->update(['cargo' => 'Poligrafista Senior']);
        $empresa = Empresa::factory()->create();
        [$orden, $evaluado, $admin] = $this->crearOrdenConEvaluado($empresa, $responsable);

        $cuestionario = Cuestionario::create([
            'evaluado_orden_id' => $evaluado->id,
            'tipo_formulario' => 'preempleo',
            'total_secciones' => 5,
            'completado' => true,
            'acepta_terminos' => true,
            'firma_digital' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwADhQGAWjR9awAAAABJRU5ErkJggg==',
            'completado_at' => now()->subDay(),
        ]);

        $view = view('admin.cuestionarios.pdf', [
            'cuestionario' => $cuestionario->load(['evaluadoOrden.orden.empresa', 'evaluadoOrden.documentos', 'evaluadoOrden.responsable']),
            'respuestasPorSeccion' => collect(),
        ])->render();

        $this->assertStringContainsString($responsable->name, $view);
        $this->assertStringContainsString('Poligrafista Senior', $view);
        $this->assertStringContainsString('Responsable del Proceso', $view);
    }

    public function test_pdf_cuestionario_sin_responsable_no_muestra_firma(): void
    {
        $empresa = Empresa::factory()->create();
        [$orden, $evaluado, $admin] = $this->crearOrdenConEvaluado($empresa);

        $cuestionario = Cuestionario::create([
            'evaluado_orden_id' => $evaluado->id,
            'tipo_formulario' => 'preempleo',
            'total_secciones' => 5,
            'completado' => true,
            'acepta_terminos' => true,
            'firma_digital' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwADhQGAWjR9awAAAABJRU5ErkJggg==',
            'completado_at' => now()->subDay(),
        ]);

        $view = view('admin.cuestionarios.pdf', [
            'cuestionario' => $cuestionario->load(['evaluadoOrden.orden.empresa', 'evaluadoOrden.documentos', 'evaluadoOrden.responsable']),
            'respuestasPorSeccion' => collect(),
        ])->render();

        $this->assertStringNotContainsString('Responsable del Proceso', $view);
    }

    // ─── PDF de Orden con responsable ───

    public function test_pdf_orden_incluye_columna_responsable(): void
    {
        $responsable = $this->crearRepro();
        $responsable->update(['cargo' => 'Evaluador']);
        $empresa = Empresa::factory()->create();
        [$orden, $evaluado, $admin] = $this->crearOrdenConEvaluado($empresa, $responsable);

        $orden->load(['empresa', 'creador', 'evaluados.poligrafista', 'evaluados.responsable']);
        $estados = Orden::estadosDisponibles();

        $view = view('admin.ordenes.pdf', compact('orden', 'estados'))->render();

        $this->assertStringContainsString('Responsable', $view);
        $this->assertStringContainsString($responsable->name, $view);
        $this->assertStringContainsString('Evaluador', $view);
    }

    // ─── Validación de ProgramarCitaRequest ───

    public function test_validacion_responsable_id_en_programar_cita(): void
    {
        $repro = $this->crearRepro();
        $sede = Sede::factory()->create(['estado' => 1]);
        $empresa = Empresa::factory()->create();
        [$orden, $evaluado] = $this->crearOrdenConEvaluado($empresa);

        $response = $this->actingAs($repro)->post(route('calendario.programar', $evaluado), [
            'evaluado_orden_id' => $evaluado->id,
            'fecha' => now()->addDay()->format('Y-m-d'),
            'hora_inicio' => '09:00',
            'hora_fin' => '11:00',
            'poligrafista_id' => $repro->id,
            'sede_id' => $sede->id,
            'modalidad' => 'presencial',
            'responsable_id' => $repro->id,
        ]);

        // Should redirect (302) on success, not 422 validation error
        $this->assertNotEquals(422, $response->getStatusCode());
    }

    public function test_validacion_responsable_id_invalido_falla(): void
    {
        $repro = $this->crearRepro();
        $sede = Sede::factory()->create(['estado' => 1]);
        $empresa = Empresa::factory()->create();
        [$orden, $evaluado] = $this->crearOrdenConEvaluado($empresa);

        $response = $this->actingAs($repro)->post(route('calendario.programar', $evaluado), [
            'evaluado_orden_id' => $evaluado->id,
            'fecha' => now()->addDay()->format('Y-m-d'),
            'hora_inicio' => '09:00',
            'hora_fin' => '11:00',
            'poligrafista_id' => $repro->id,
            'sede_id' => $sede->id,
            'responsable_id' => 99999, // No existe
        ]);

        $response->assertSessionHasErrors('responsable_id');
    }
}
