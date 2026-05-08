<?php

namespace Tests\Feature;

use App\Mail\ResultadosDisponiblesMail;
use App\Models\Cuestionario;
use App\Models\DocumentoEvaluado;
use App\Models\Empresa;
use App\Models\EvaluadoOrden;
use App\Models\Orden;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class Fase2DocumentacionTest extends TestCase
{
    use RefreshDatabase, WithFaker;

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
        Mail::fake();
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

    // ══════════════════════════════════════════
    // DOCUMENTOS EVALUADO
    // ══════════════════════════════════════════

    public function test_modelo_documento_evaluado_tiene_tipos(): void
    {
        $tipos = DocumentoEvaluado::tiposDocumento();

        $this->assertIsArray($tipos);
        $this->assertArrayHasKey('dpi_archivo', $tipos);
        $this->assertArrayHasKey('antecedentes_penales', $tipos);
        $this->assertArrayHasKey('cv', $tipos);
    }

    public function test_modelo_documento_evaluado_tiene_estados(): void
    {
        $estados = DocumentoEvaluado::estadosVerificacion();

        $this->assertIsArray($estados);
        $this->assertArrayHasKey('pendiente', $estados);
        $this->assertArrayHasKey('aprobado', $estados);
        $this->assertArrayHasKey('rechazado', $estados);
    }

    public function test_admin_puede_subir_documento_evaluado(): void
    {
        $archivo = UploadedFile::fake()->create('documento.pdf', 500, 'application/pdf');

        $response = $this->actingAs($this->adminUser)->post(route('documentos-evaluado.store'), [
            'evaluado_orden_id' => $this->evaluado->id,
            'tipo_documento' => 'dpi_archivo',
            'archivo' => $archivo,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('documento_evaluados', [
            'evaluado_orden_id' => $this->evaluado->id,
            'tipo_documento' => 'dpi_archivo',
            'subido_por_tipo' => 'repro',
        ]);
    }

    public function test_subir_documento_valida_tipo_invalido(): void
    {
        $archivo = UploadedFile::fake()->create('doc.pdf', 500, 'application/pdf');

        $response = $this->actingAs($this->adminUser)->post(route('documentos-evaluado.store'), [
            'evaluado_orden_id' => $this->evaluado->id,
            'tipo_documento' => 'tipo_inexistente',
            'archivo' => $archivo,
        ]);

        $response->assertSessionHasErrors('tipo_documento');
    }

    public function test_subir_documento_valida_tamano_maximo(): void
    {
        $archivo = UploadedFile::fake()->create('grande.pdf', 11000, 'application/pdf');

        $response = $this->actingAs($this->adminUser)->post(route('documentos-evaluado.store'), [
            'evaluado_orden_id' => $this->evaluado->id,
            'tipo_documento' => 'cv',
            'archivo' => $archivo,
        ]);

        $response->assertSessionHasErrors('archivo');
    }

    public function test_admin_puede_verificar_documento(): void
    {
        $doc = DocumentoEvaluado::factory()->create([
            'evaluado_orden_id' => $this->evaluado->id,
            'estado_verificacion' => 'pendiente',
        ]);

        $response = $this->actingAs($this->adminUser)->patch(
            route('documentos-evaluado.verificar', $doc),
            ['estado_verificacion' => 'aprobado', 'notas_verificacion' => 'Todo correcto']
        );

        $response->assertRedirect();
        $doc->refresh();
        $this->assertEquals('aprobado', $doc->estado_verificacion);
        $this->assertEquals('Todo correcto', $doc->notas_verificacion);
        $this->assertEquals($this->adminUser->id, $doc->verificado_por);
    }

    public function test_empresa_no_puede_verificar_documento(): void
    {
        $doc = DocumentoEvaluado::factory()->create([
            'evaluado_orden_id' => $this->evaluado->id,
            'estado_verificacion' => 'pendiente',
        ]);

        $response = $this->actingAs($this->empresaUser)->patch(
            route('documentos-evaluado.verificar', $doc),
            ['estado' => 'aprobado']
        );

        $response->assertForbidden();
    }

    public function test_admin_puede_eliminar_documento(): void
    {
        $doc = DocumentoEvaluado::factory()->create([
            'evaluado_orden_id' => $this->evaluado->id,
        ]);

        $response = $this->actingAs($this->adminUser)->delete(
            route('documentos-evaluado.destroy', $doc)
        );

        $response->assertRedirect();
        $this->assertDatabaseMissing('documento_evaluados', ['id' => $doc->id]);
    }

    public function test_evaluado_orden_tiene_relacion_documentos(): void
    {
        $doc = DocumentoEvaluado::factory()->create([
            'evaluado_orden_id' => $this->evaluado->id,
        ]);

        $this->evaluado->refresh();
        $this->assertCount(1, $this->evaluado->documentos);
        $this->assertEquals($doc->id, $this->evaluado->documentos->first()->id);
    }

    // ══════════════════════════════════════════
    // SUBIDA DOCUMENTOS DESDE CUESTIONARIO (EVALUADO)
    // ══════════════════════════════════════════

    public function test_evaluado_puede_subir_documento_desde_cuestionario(): void
    {
        $cuestionario = Cuestionario::create([
            'evaluado_orden_id' => $this->evaluado->id,
            'tipo_formulario' => 'preempleo',
            'seccion_actual' => 1,
            'total_secciones' => 5,
            'completado' => false,
            'bloqueado' => false,
        ]);

        $archivo = UploadedFile::fake()->create('mi_dpi.pdf', 500, 'application/pdf');

        $response = $this->post(route('cuestionario.subir-documento', $this->evaluado->token_unico), [
            'tipo_documento' => 'dpi_archivo',
            'archivo' => $archivo,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('documento_evaluados', [
            'evaluado_orden_id' => $this->evaluado->id,
            'tipo_documento' => 'dpi_archivo',
            'subido_por_tipo' => 'evaluado',
        ]);
    }

    // ══════════════════════════════════════════
    // TÉRMINOS Y CONDICIONES
    // ══════════════════════════════════════════

    public function test_pagina_terminos_se_muestra(): void
    {
        $cuestionario = Cuestionario::create([
            'evaluado_orden_id' => $this->evaluado->id,
            'tipo_formulario' => 'preempleo',
            'seccion_actual' => 1,
            'total_secciones' => 5,
            'completado' => false,
            'bloqueado' => false,
            'acepta_terminos' => false,
        ]);

        $response = $this->get(route('cuestionario.terminos', $this->evaluado->token_unico));

        $response->assertOk();
        $response->assertViewIs('cuestionario.terminos');
        $response->assertSee('Autorización y Términos');
    }

    public function test_aceptar_terminos_guarda_datos(): void
    {
        $cuestionario = Cuestionario::create([
            'evaluado_orden_id' => $this->evaluado->id,
            'tipo_formulario' => 'preempleo',
            'seccion_actual' => 1,
            'total_secciones' => 5,
            'completado' => false,
            'bloqueado' => false,
            'acepta_terminos' => false,
        ]);

        $response = $this->post(route('cuestionario.aceptar-terminos', $this->evaluado->token_unico), [
            'acepta_terminos' => '1',
            'tipo_proceso' => 'socioeconomico',
            'firma_digital' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==',
        ]);

        $response->assertRedirect();
        $cuestionario->refresh();
        $this->assertTrue($cuestionario->acepta_terminos);
        $this->assertNotNull($cuestionario->acepta_terminos_at);
        $this->assertNotNull($cuestionario->ip_terminos);
    }

    public function test_aceptar_terminos_requiere_checkbox(): void
    {
        $cuestionario = Cuestionario::create([
            'evaluado_orden_id' => $this->evaluado->id,
            'tipo_formulario' => 'preempleo',
            'seccion_actual' => 1,
            'total_secciones' => 5,
            'completado' => false,
            'bloqueado' => false,
        ]);

        $response = $this->post(route('cuestionario.aceptar-terminos', $this->evaluado->token_unico), [
            // Sin checkbox de aceptación
        ]);

        $response->assertSessionHasErrors('acepta_terminos');
    }

    public function test_redirige_a_seccion_si_ya_acepto_terminos(): void
    {
        $cuestionario = Cuestionario::create([
            'evaluado_orden_id' => $this->evaluado->id,
            'tipo_formulario' => 'preempleo',
            'seccion_actual' => 1,
            'total_secciones' => 5,
            'completado' => false,
            'bloqueado' => false,
            'acepta_terminos' => true,
            'acepta_terminos_at' => now(),
        ]);

        $response = $this->get(route('cuestionario.terminos', $this->evaluado->token_unico));

        $response->assertRedirect(route('cuestionario.seccion', [
            'token' => $this->evaluado->token_unico,
            'numero' => 1,
        ]));
    }

    // ══════════════════════════════════════════
    // ARCHIVOS RESULTADO (PRELIMINAR / FINAL)
    // ══════════════════════════════════════════

    public function test_admin_puede_subir_resultado_preliminar(): void
    {
        $archivo = UploadedFile::fake()->create('resultado_prelim.pdf', 1000, 'application/pdf');

        $response = $this->actingAs($this->adminUser)->post(
            route('evaluados.subir-resultado-archivo', $this->evaluado->id),
            ['tipo_resultado' => 'preliminar', 'archivo' => $archivo]
        );

        $response->assertRedirect();
        $this->evaluado->refresh();
        $this->assertNotNull($this->evaluado->archivo_resultado_preliminar);
        $this->assertNotNull($this->evaluado->resultado_preliminar_at);
        $this->assertTrue($this->evaluado->tieneResultadoPreliminar());
    }

    public function test_admin_puede_subir_resultado_final(): void
    {
        $archivo = UploadedFile::fake()->create('resultado_final.pdf', 1000, 'application/pdf');

        $response = $this->actingAs($this->adminUser)->post(
            route('evaluados.subir-resultado-archivo', $this->evaluado->id),
            ['tipo_resultado' => 'final', 'archivo' => $archivo]
        );

        $response->assertRedirect();
        $this->evaluado->refresh();
        $this->assertNotNull($this->evaluado->archivo_resultado_final);
        $this->assertNotNull($this->evaluado->resultado_final_at);
        $this->assertTrue($this->evaluado->tieneResultadoFinal());
    }

    public function test_empresa_no_puede_subir_resultado(): void
    {
        $archivo = UploadedFile::fake()->create('resultado.pdf', 1000, 'application/pdf');

        $response = $this->actingAs($this->empresaUser)->post(
            route('evaluados.subir-resultado-archivo', $this->evaluado->id),
            ['tipo_resultado' => 'preliminar', 'archivo' => $archivo]
        );

        $response->assertForbidden();
    }

    public function test_admin_puede_eliminar_resultado(): void
    {
        $this->evaluado->update([
            'archivo_resultado_preliminar' => 'resultados/1/test.pdf',
            'resultado_preliminar_at' => now(),
        ]);

        $response = $this->actingAs($this->adminUser)->delete(
            route('evaluados.eliminar-resultado-archivo', [$this->evaluado->id, 'preliminar'])
        );

        $response->assertRedirect();
        $this->evaluado->refresh();
        $this->assertNull($this->evaluado->archivo_resultado_preliminar);
        $this->assertNull($this->evaluado->resultado_preliminar_at);
    }

    public function test_evaluado_orden_tiene_metodos_resultado(): void
    {
        $this->assertFalse($this->evaluado->tieneResultadoPreliminar());
        $this->assertFalse($this->evaluado->tieneResultadoFinal());

        $this->evaluado->update([
            'archivo_resultado_preliminar' => 'resultados/1/prelim.pdf',
            'archivo_resultado_final' => 'resultados/1/final.pdf',
        ]);
        $this->evaluado->refresh();

        $this->assertTrue($this->evaluado->tieneResultadoPreliminar());
        $this->assertTrue($this->evaluado->tieneResultadoFinal());
    }

    // ══════════════════════════════════════════
    // EMAIL NOTIFICACIÓN RESULTADOS
    // ══════════════════════════════════════════

    public function test_toggle_resultados_visibles_envia_email(): void
    {
        $this->orden->update(['resultados_visibles_empresa' => false]);

        $response = $this->actingAs($this->adminUser)->patch(
            route('ordenes.toggle-resultados-visibles', $this->orden)
        );

        $response->assertRedirect();
        $this->orden->refresh();
        $this->assertTrue($this->orden->resultados_visibles_empresa);

        Mail::assertQueued(ResultadosDisponiblesMail::class, function ($mail) {
            return $mail->orden->id === $this->orden->id;
        });
    }

    public function test_ocultar_resultados_no_envia_email(): void
    {
        $this->orden->update(['resultados_visibles_empresa' => true]);

        $response = $this->actingAs($this->adminUser)->patch(
            route('ordenes.toggle-resultados-visibles', $this->orden)
        );

        $response->assertRedirect();
        $this->orden->refresh();
        $this->assertFalse($this->orden->resultados_visibles_empresa);

        Mail::assertNotSent(ResultadosDisponiblesMail::class);
    }

    public function test_resultados_disponibles_mail_tiene_datos_orden(): void
    {
        $mail = new ResultadosDisponiblesMail($this->orden);

        $this->assertEquals($this->orden->id, $mail->orden->id);
        $this->assertStringContainsString($this->orden->codigo_orden, $mail->envelope()->subject);
    }

    // ══════════════════════════════════════════
    // REHABILITACIÓN CUESTIONARIO
    // ══════════════════════════════════════════

    public function test_admin_puede_rehabilitar_cuestionario(): void
    {
        $evaluadoCompletado = EvaluadoOrden::factory()->completado()->create([
            'orden_id' => $this->orden->id,
            'estado_evaluacion' => 'completado',
        ]);

        $cuestionario = Cuestionario::create([
            'evaluado_orden_id' => $evaluadoCompletado->id,
            'tipo_formulario' => 'preempleo',
            'seccion_actual' => 5,
            'total_secciones' => 5,
            'progreso_porcentaje' => 100,
            'completado' => true,
            'bloqueado' => true,
            'acepta_terminos' => true,
            'acepta_terminos_at' => now()->subDays(3),
            'firma_autorizacion' => 'data:image/png;base64,...',
            'firma_digital' => 'data:image/png;base64,...',
            'completado_at' => now()->subDay(),
        ]);

        $tokenAnterior = $evaluadoCompletado->token_unico;

        $response = $this->actingAs($this->adminUser)->post(
            route('evaluados.rehabilitar-cuestionario', $evaluadoCompletado->id)
        );

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $evaluadoCompletado->refresh();
        $cuestionario->refresh();

        // Evaluado resetteado
        $this->assertFalse($evaluadoCompletado->cuestionario_completado);
        $this->assertNull($evaluadoCompletado->completado_at);
        $this->assertEquals('pendiente', $evaluadoCompletado->estado_evaluacion);
        $this->assertNotEquals($tokenAnterior, $evaluadoCompletado->token_unico);

        // Cuestionario reseteado
        $this->assertFalse($cuestionario->completado);
        $this->assertFalse($cuestionario->bloqueado);
        $this->assertEquals(0, $cuestionario->progreso_porcentaje);
        $this->assertNull($cuestionario->completado_at);
        $this->assertNull($cuestionario->firma_digital);
        $this->assertFalse($cuestionario->acepta_terminos);
    }

    public function test_empresa_no_puede_rehabilitar_cuestionario(): void
    {
        $evaluadoCompletado = EvaluadoOrden::factory()->completado()->create([
            'orden_id' => $this->orden->id,
            'estado_evaluacion' => 'completado',
        ]);

        $response = $this->actingAs($this->empresaUser)->post(
            route('evaluados.rehabilitar-cuestionario', $evaluadoCompletado->id)
        );

        $response->assertForbidden();
    }

    public function test_no_se_puede_rehabilitar_cuestionario_no_completado(): void
    {
        $response = $this->actingAs($this->adminUser)->post(
            route('evaluados.rehabilitar-cuestionario', $this->evaluado->id)
        );

        $response->assertRedirect();
        $response->assertSessionHas('warning');
    }

    // ══════════════════════════════════════════
    // DESHABILITAR CUESTIONARIO
    // ══════════════════════════════════════════

    public function test_admin_puede_deshabilitar_cuestionario(): void
    {
        // Crear evaluado pendiente con cuestionario (como si se hubiera rehabilitado)
        $evaluadoPendiente = EvaluadoOrden::factory()->create([
            'orden_id' => $this->orden->id,
            'cuestionario_completado' => false,
            'estado_evaluacion' => 'pendiente',
        ]);

        $cuestionario = Cuestionario::create([
            'evaluado_orden_id' => $evaluadoPendiente->id,
            'tipo_formulario' => 'preempleo',
            'seccion_actual' => 1,
            'total_secciones' => 5,
            'completado' => false,
            'bloqueado' => false,
        ]);

        $response = $this->actingAs($this->adminUser)->post(
            route('evaluados.deshabilitar-cuestionario', $evaluadoPendiente->id)
        );

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $evaluadoPendiente->refresh();
        $cuestionario->refresh();

        $this->assertTrue($evaluadoPendiente->cuestionario_completado);
        $this->assertEquals('completado', $evaluadoPendiente->estado_evaluacion);
        $this->assertTrue($cuestionario->completado);
        $this->assertTrue($cuestionario->bloqueado);
    }

    public function test_empresa_no_puede_deshabilitar_cuestionario(): void
    {
        $evaluadoPendiente = EvaluadoOrden::factory()->create([
            'orden_id' => $this->orden->id,
            'cuestionario_completado' => false,
        ]);

        Cuestionario::create([
            'evaluado_orden_id' => $evaluadoPendiente->id,
            'tipo_formulario' => 'preempleo',
            'seccion_actual' => 1,
            'total_secciones' => 5,
            'completado' => false,
            'bloqueado' => false,
        ]);

        $response = $this->actingAs($this->empresaUser)->post(
            route('evaluados.deshabilitar-cuestionario', $evaluadoPendiente->id)
        );

        $response->assertForbidden();
    }

    public function test_no_se_puede_deshabilitar_cuestionario_ya_completado(): void
    {
        $evaluadoCompletado = EvaluadoOrden::factory()->completado()->create([
            'orden_id' => $this->orden->id,
            'estado_evaluacion' => 'completado',
        ]);

        $response = $this->actingAs($this->adminUser)->post(
            route('evaluados.deshabilitar-cuestionario', $evaluadoCompletado->id)
        );

        $response->assertRedirect();
        $response->assertSessionHas('warning');
    }

    // ══════════════════════════════════════════
    // MIGRACIONES Y ESTRUCTURA DE DATOS
    // ══════════════════════════════════════════

    public function test_tabla_documento_evaluados_existe(): void
    {
        $this->assertTrue(
            \Illuminate\Support\Facades\Schema::hasTable('documento_evaluados')
        );
    }

    public function test_tabla_cuestionarios_tiene_campos_terminos(): void
    {
        $this->assertTrue(
            \Illuminate\Support\Facades\Schema::hasColumn('cuestionarios', 'acepta_terminos')
        );
        $this->assertTrue(
            \Illuminate\Support\Facades\Schema::hasColumn('cuestionarios', 'firma_autorizacion')
        );
        $this->assertTrue(
            \Illuminate\Support\Facades\Schema::hasColumn('cuestionarios', 'ip_terminos')
        );
    }

    public function test_tabla_evaluados_orden_tiene_campos_resultado(): void
    {
        $this->assertTrue(
            \Illuminate\Support\Facades\Schema::hasColumn('evaluados_orden', 'archivo_resultado_preliminar')
        );
        $this->assertTrue(
            \Illuminate\Support\Facades\Schema::hasColumn('evaluados_orden', 'archivo_resultado_final')
        );
        $this->assertTrue(
            \Illuminate\Support\Facades\Schema::hasColumn('evaluados_orden', 'resultado_preliminar_at')
        );
        $this->assertTrue(
            \Illuminate\Support\Facades\Schema::hasColumn('evaluados_orden', 'resultado_final_at')
        );
    }

    // ══════════════════════════════════════════
    // C5 — AUTO-LIBERAR RESULTADOS AL SUBIR
    // ══════════════════════════════════════════

    public function test_subir_resultado_final_auto_libera_para_cliente(): void
    {
        Mail::fake();
        $this->orden->update(['estado' => 'en_proceso', 'resultados_visibles_empresa' => false]);
        $archivo = UploadedFile::fake()->create('informe_final.pdf', 1000, 'application/pdf');

        $this->actingAs($this->adminUser)->post(
            route('evaluados.subir-resultado-archivo', $this->evaluado->id),
            ['tipo_resultado' => 'final', 'archivo' => $archivo]
        );

        $this->orden->refresh();
        $this->assertEquals('entregado', $this->orden->estado);
        $this->assertTrue((bool) $this->orden->resultados_visibles_empresa);
    }

    public function test_subir_resultado_final_envia_notificacion_empresa(): void
    {
        Mail::fake();
        $this->orden->update(['estado' => 'en_proceso', 'resultados_visibles_empresa' => false]);
        // Crear usuario empresa para que reciba el mail
        User::factory()->create([
            'role_as' => 1,
            'estado' => 1,
            'empresa_id' => $this->empresa->id,
            'email' => 'notif@empresa.com',
        ]);
        $archivo = UploadedFile::fake()->create('informe_final.pdf', 1000, 'application/pdf');

        $this->actingAs($this->adminUser)->post(
            route('evaluados.subir-resultado-archivo', $this->evaluado->id),
            ['tipo_resultado' => 'final', 'archivo' => $archivo]
        );

        Mail::assertQueued(ResultadosDisponiblesMail::class);
    }

    public function test_subir_resultado_preliminar_avanza_estado_a_analisis(): void
    {
        $this->orden->update(['estado' => 'en_proceso', 'resultados_visibles_empresa' => false]);
        $archivo = UploadedFile::fake()->create('informe_prelim.pdf', 1000, 'application/pdf');

        $this->actingAs($this->adminUser)->post(
            route('evaluados.subir-resultado-archivo', $this->evaluado->id),
            ['tipo_resultado' => 'preliminar', 'archivo' => $archivo]
        );

        $this->orden->refresh();
        $this->assertEquals('analisis', $this->orden->estado);
        // No libera visibilidad automáticamente con preliminar
        $this->assertFalse((bool) $this->orden->resultados_visibles_empresa);
    }

    public function test_subir_resultado_preliminar_no_modifica_estado_avanzado(): void
    {
        $this->orden->update(['estado' => 'entregado', 'resultados_visibles_empresa' => true]);
        $archivo = UploadedFile::fake()->create('informe_prelim.pdf', 1000, 'application/pdf');

        $this->actingAs($this->adminUser)->post(
            route('evaluados.subir-resultado-archivo', $this->evaluado->id),
            ['tipo_resultado' => 'preliminar', 'archivo' => $archivo]
        );

        $this->orden->refresh();
        $this->assertEquals('entregado', $this->orden->estado);
        $this->assertTrue((bool) $this->orden->resultados_visibles_empresa);
    }
}

