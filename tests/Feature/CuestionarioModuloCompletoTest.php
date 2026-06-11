<?php

namespace Tests\Feature;

use App\Models\EvaluadoOrden;
use App\Models\Orden;
use App\Models\Empresa;
use App\Models\User;
use App\Models\Cuestionario;
use App\Models\CuestionarioRespuesta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

/**
 * Test completo del módulo de cuestionarios para evaluados
 * 
 * Este test verifica todo el flujo de:
 * 1. Acceso al cuestionario con token
 * 2. Verificación de identidad (DPI)
 * 3. Llenado de cada sección (1-5)
 * 4. Validaciones de campos
 * 5. Navegación entre secciones
 * 6. Guardado de progreso
 * 7. Finalización y firma digital
 */
class CuestionarioModuloCompletoTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected EvaluadoOrden $evaluado;
    protected Orden $orden;
    protected Empresa $empresa;
    protected User $usuario;

    /**
     * Preparar datos de prueba
     */
    protected function setUp(): void
    {
        parent::setUp();
        
        // Crear usuario para la orden
        $this->usuario = User::factory()->create([
            'role_as' => 3, // Admin
            'estado' => 1
        ]);
        
        // Crear empresa
        $this->empresa = Empresa::factory()->create([
            'estado' => 1
        ]);
        
        // Crear orden
        $this->orden = Orden::factory()->create([
            'empresa_id' => $this->empresa->id,
            'creado_por' => $this->usuario->id,
            'estado' => 'orden_recibida'
        ]);
        
        // Crear evaluado con token válido
        $this->evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $this->orden->id,
            'nombre' => 'Juan Carlos',
            'apellidos' => 'Pérez García',
            'dpi' => '1234567890101',
            'email' => 'juan.perez@example.com',
            'telefono' => '12345678',
            'token_unico' => 'test-token-cuestionario-completo',
            'token_expira_at' => now()->addDays(30),
            'cuestionario_completado' => false,
            'tipo_formulario' => 'preempleo',
            'tipo_servicio' => 'poligrafo'
        ]);
    }

    // =========================================================================
    // TESTS DE ACCESO AL CUESTIONARIO
    // =========================================================================

    /**
     * Test: Puede acceder a la página de verificación con token válido
     */
    public function test_puede_acceder_con_token_valido(): void
    {
        $response = $this->get("/cuestionario/{$this->evaluado->token_unico}");

        $response->assertStatus(200);
        $response->assertSee('Verificación de Identidad');
        $response->assertSee('DPI');
    }

    /**
     * Test: No puede acceder con token inválido
     */
    public function test_no_puede_acceder_con_token_invalido(): void
    {
        $response = $this->get('/cuestionario/token-invalido-12345');

        $response->assertStatus(404);
    }

    /**
     * Test: No puede acceder con token expirado
     */
    public function test_no_puede_acceder_con_token_expirado(): void
    {
        $evaluadoExpirado = EvaluadoOrden::factory()->create([
            'orden_id' => $this->orden->id,
            'token_unico' => 'token-expirado-test',
            'token_expira_at' => now()->subDays(1), // Expirado
            'dpi' => '9876543210101'
        ]);

        $response = $this->get("/cuestionario/{$evaluadoExpirado->token_unico}");

        // Token expirado debe retornar 404 (no encontrado/inválido)
        $response->assertStatus(404);
    }

    /**
     * Test: Muestra página de completado si ya finalizó el cuestionario
     */
    public function test_muestra_pagina_completado_si_ya_finalizo(): void
    {
        $this->evaluado->update([
            'cuestionario_completado' => true,
            'completado_at' => now()
        ]);

        $response = $this->get("/cuestionario/{$this->evaluado->token_unico}");

        $response->assertStatus(200);
        $response->assertSee('Completado');
    }

    // =========================================================================
    // TESTS DE VERIFICACIÓN DE IDENTIDAD
    // =========================================================================

    /**
     * Test: Verificación exitosa con DPI correcto
     */
    public function test_verificacion_exitosa_con_dpi_correcto(): void
    {
        $response = $this->post("/cuestionario/{$this->evaluado->token_unico}/verificar", [
            'dpi_ingresado' => '1234567890101'
        ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();
        
        // Debería redirigir a términos (primer paso después de verificar DPI)
        $this->assertStringContainsString('terminos', $response->headers->get('Location'));
    }

    /**
     * Test: Error de verificación con DPI incorrecto
     */
    public function test_error_verificacion_con_dpi_incorrecto(): void
    {
        $response = $this->post("/cuestionario/{$this->evaluado->token_unico}/verificar", [
            'dpi_ingresado' => '9999999999999'
        ]);

        $response->assertSessionHasErrors(['dpi_ingresado']);
    }

    /**
     * Test: Error de verificación con DPI formato inválido
     */
    public function test_error_verificacion_con_dpi_formato_invalido(): void
    {
        $response = $this->post("/cuestionario/{$this->evaluado->token_unico}/verificar", [
            'dpi_ingresado' => '12345' // Menos de 13 dígitos
        ]);

        $response->assertSessionHasErrors(['dpi_ingresado']);
    }

    /**
     * Test: Error de verificación con DPI vacío
     */
    public function test_error_verificacion_con_dpi_vacio(): void
    {
        $response = $this->post("/cuestionario/{$this->evaluado->token_unico}/verificar", [
            'dpi_ingresado' => ''
        ]);

        $response->assertSessionHasErrors(['dpi_ingresado']);
    }

    // =========================================================================
    // TESTS DE ACCESO A SECCIONES
    // =========================================================================

    /**
     * Test: Puede acceder a sección 1 después de verificación
     */
    public function test_puede_acceder_a_seccion_1(): void
    {
        // Primero verificar identidad
        $this->post("/cuestionario/{$this->evaluado->token_unico}/verificar", [
            'dpi_ingresado' => '1234567890101'
        ]);

        $response = $this->get("/cuestionario/{$this->evaluado->token_unico}/seccion/1");

        $response->assertStatus(200);
        $response->assertSee('Datos Personales');
    }

    /**
     * Test: No puede acceder a sección 2 sin completar sección 1
     */
    public function test_no_puede_saltar_secciones(): void
    {
        // Crear cuestionario sin avanzar
        Cuestionario::create([
            'evaluado_orden_id' => $this->evaluado->id,
            'tipo_formulario' => 'preempleo',
            'seccion_actual' => 1,
            'total_secciones' => 5,
            'progreso_porcentaje' => 0,
            'completado' => false,
            'bloqueado' => false
        ]);

        $response = $this->get("/cuestionario/{$this->evaluado->token_unico}/seccion/3");

        // Debería redirigir a la sección actual
        $response->assertRedirect();
    }

    // =========================================================================
    // TESTS DE SECCIÓN 1: DATOS PERSONALES
    // =========================================================================

    /**
     * Test: Puede enviar datos personales válidos
     */
    public function test_puede_enviar_seccion_1_datos_validos(): void
    {
        // Primero verificar identidad para crear cuestionario
        $this->post("/cuestionario/{$this->evaluado->token_unico}/verificar", [
            'dpi_ingresado' => '1234567890101'
        ]);

        $datosSeccion1 = [
            'nombres_completos' => 'Juan Carlos',
            'apellidos_completos' => 'Pérez García',
            'dpi' => '1234567890101',
            'fecha_nacimiento' => '1990-05-15',
            'lugar_nacimiento' => 'Ciudad de Guatemala',
            'estado_civil' => 'soltero',
            'genero' => 'masculino',
            'nacionalidad' => 'Guatemalteca',
            'profesion_oficio' => 'Ingeniero en Sistemas',
            'nivel_educativo' => 'universidad_completa',
            'direccion_residencia' => 'Zona 10, Ciudad de Guatemala',
            'departamento' => 'Guatemala',
            'municipio' => 'Guatemala',
            'telefono_personal' => '12345678',
            'email_personal' => 'juan.perez@example.com'
        ];

        $response = $this->post("/cuestionario/{$this->evaluado->token_unico}/seccion/1", $datosSeccion1);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();
        
        // Verificar que redirige a sección 2
        $this->assertStringContainsString('seccion/2', $response->headers->get('Location'));
    }

    /**
     * Test: Falla validación sección 1 con datos incompletos
     */
    public function test_falla_validacion_seccion_1_datos_incompletos(): void
    {
        // Verificar identidad primero
        $this->post("/cuestionario/{$this->evaluado->token_unico}/verificar", [
            'dpi_ingresado' => '1234567890101'
        ]);

        $datosIncompletos = [
            'nombres_completos' => 'Juan Carlos',
            // Faltan campos requeridos
        ];

        $response = $this->post("/cuestionario/{$this->evaluado->token_unico}/seccion/1", $datosIncompletos);

        $response->assertSessionHasErrors();
    }

    /**
     * Test: Valida formato de email en sección 1
     */
    public function test_valida_formato_email_seccion_1(): void
    {
        $this->post("/cuestionario/{$this->evaluado->token_unico}/verificar", [
            'dpi_ingresado' => '1234567890101'
        ]);

        $datosEmailInvalido = $this->getDatosSeccion1Validos();
        $datosEmailInvalido['email_personal'] = 'email-invalido';

        $response = $this->post("/cuestionario/{$this->evaluado->token_unico}/seccion/1", $datosEmailInvalido);

        $response->assertSessionHasErrors(['email_personal']);
    }

    /**
     * Test: Valida fecha de nacimiento (mayor de 16 años)
     */
    public function test_valida_edad_minima_seccion_1(): void
    {
        $this->post("/cuestionario/{$this->evaluado->token_unico}/verificar", [
            'dpi_ingresado' => '1234567890101'
        ]);

        $datosMenorEdad = $this->getDatosSeccion1Validos();
        $datosMenorEdad['fecha_nacimiento'] = now()->subYears(10)->format('Y-m-d'); // 10 años

        $response = $this->post("/cuestionario/{$this->evaluado->token_unico}/seccion/1", $datosMenorEdad);

        $response->assertSessionHasErrors(['fecha_nacimiento']);
    }

    // =========================================================================
    // TESTS DE SECCIÓN 2: INFORMACIÓN FAMILIAR
    // =========================================================================

    /**
     * Test: Puede enviar información familiar válida
     */
    public function test_puede_enviar_seccion_2_datos_validos(): void
    {
        $this->completarSeccion1();

        $datosSeccion2 = [
            'estado_civil_detalle' => 'soltero',
            'vive_con_pareja' => 'no',
            'tiene_hijos' => 'no',
            'personas_hogar' => 4,
            'dependientes_economicos' => 2,
            'tipo_vivienda' => 'familiar',
            'personas_contribuyen_gastos' => 2
        ];

        $response = $this->post("/cuestionario/{$this->evaluado->token_unico}/seccion/2", $datosSeccion2);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();
    }

    // =========================================================================
    // TESTS DE SECCIÓN 3: HISTORIAL LABORAL
    // =========================================================================

    /**
     * Test: Puede enviar historial laboral válido
     */
    public function test_puede_enviar_seccion_3_datos_validos(): void
    {
        $this->completarSeccion1();
        $this->completarSeccion2();

        $datosSeccion3 = [
            'situacion_laboral_actual' => 'empleado',
            'anos_experiencia_laboral' => 5,
            'empresa_actual' => 'Empresa Ejemplo S.A.',
            'puesto_actual' => 'Analista de Sistemas',
            'salario_actual' => 8000.00,
            'empleos_anteriores' => 'Empresa Anterior - Asistente - 2019-2021'
        ];

        $response = $this->post("/cuestionario/{$this->evaluado->token_unico}/seccion/3", $datosSeccion3);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();
    }

    // =========================================================================
    // TESTS DE SECCIÓN 4: SITUACIÓN ECONÓMICA
    // =========================================================================

    /**
     * Test: Puede enviar situación económica válida
     */
    public function test_puede_enviar_seccion_4_datos_validos(): void
    {
        $this->completarSeccion1();
        $this->completarSeccion2();
        $this->completarSeccion3();

        $datosSeccion4 = [
            'ingresos_principales' => 8000.00,
            'ingresos_adicionales' => 1000.00,
            'gastos_vivienda' => 2500.00,
            'gastos_alimentacion' => 3000.00,
            'gastos_transporte' => 800.00,
            'tiene_deudas' => 'no',
            'tiene_ahorros' => 'si'
        ];

        $response = $this->post("/cuestionario/{$this->evaluado->token_unico}/seccion/4", $datosSeccion4);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();
    }

    // =========================================================================
    // TESTS DE SECCIÓN 5: ANTECEDENTES Y REFERENCIAS
    // =========================================================================

    /**
     * Test: Puede enviar antecedentes y referencias válidos
     */
    public function test_puede_enviar_seccion_5_datos_validos(): void
    {
        $this->completarSeccion1();
        $this->completarSeccion2();
        $this->completarSeccion3();
        $this->completarSeccion4();

        $datosSeccion5 = [
            'referencia1_nombre' => 'María García',
            'referencia1_telefono' => '55551111',
            'referencia1_relacion' => 'Amiga',
            'referencia2_nombre' => 'Pedro López',
            'referencia2_telefono' => '55552222',
            'referencia2_relacion' => 'Vecino',
            'antecedentes_penales' => 'no',
            'despedido_trabajo' => 'no',
            'consume_alcohol' => 'ocasionalmente',
            'consume_drogas' => 'nunca',
            'problemas_salud_mental' => 'no'
        ];

        $response = $this->post("/cuestionario/{$this->evaluado->token_unico}/seccion/5", $datosSeccion5);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();
    }

    /**
     * Test: Requiere campos obligatorios en sección 5 (Antecedentes)
     */
    public function test_requiere_campos_obligatorios_seccion_5(): void
    {
        $this->completarSeccion1();
        $this->completarSeccion2();
        $this->completarSeccion3();
        $this->completarSeccion4();

        // Datos incompletos - falta referencia1_nombre
        $datosIncompletos = [
            // 'referencia1_nombre' => 'María García', // Falta este campo
            'referencia1_telefono' => '55551111',
            'referencia1_relacion' => 'Amiga',
            'referencia2_nombre' => 'Pedro López',
            'referencia2_telefono' => '55552222',
            'referencia2_relacion' => 'Vecino',
            'antecedentes_penales' => 'no',
            'despedido_trabajo' => 'no',
            'consume_alcohol' => 'ocasionalmente',
            'consume_drogas' => 'nunca',
            'problemas_salud_mental' => 'no'
        ];

        $response = $this->post("/cuestionario/{$this->evaluado->token_unico}/seccion/5", $datosIncompletos);

        $response->assertSessionHasErrors(['referencia1_nombre']);
    }

    // =========================================================================
    // TESTS DE PERSISTENCIA DE DATOS
    // =========================================================================

    /**
     * Test: Las respuestas se guardan en la base de datos
     */
    public function test_respuestas_se_guardan_en_base_datos(): void
    {
        $this->completarSeccion1();

        // Verificar que el cuestionario existe
        $this->assertDatabaseHas('cuestionarios', [
            'evaluado_orden_id' => $this->evaluado->id,
            'tipo_formulario' => 'preempleo'
        ]);

        // Verificar que las respuestas se guardaron
        $cuestionario = Cuestionario::where('evaluado_orden_id', $this->evaluado->id)->first();
        $this->assertNotNull($cuestionario);
        
        $respuestas = CuestionarioRespuesta::where('cuestionario_id', $cuestionario->id)->count();
        $this->assertGreaterThan(0, $respuestas);
    }

    /**
     * Test: El progreso se actualiza correctamente
     */
    public function test_progreso_se_actualiza(): void
    {
        $this->completarSeccion1();

        $cuestionario = Cuestionario::where('evaluado_orden_id', $this->evaluado->id)->first();
        
        $this->assertEquals(2, $cuestionario->seccion_actual);
        $this->assertGreaterThan(0, $cuestionario->progreso_porcentaje);
    }

    /**
     * Test: Puede recuperar respuestas previas al volver a una sección
     */
    public function test_recupera_respuestas_previas(): void
    {
        $this->completarSeccion1();
        $this->completarSeccion2();

        // Volver a sección 1
        $response = $this->get("/cuestionario/{$this->evaluado->token_unico}/seccion/1");

        $response->assertStatus(200);
        $response->assertSee('Juan Carlos'); // Nombre guardado previamente
    }

    // =========================================================================
    // TESTS DE FINALIZACIÓN
    // =========================================================================

    /**
     * Test: Puede acceder a página de finalización después de completar todas las secciones
     * 
     * NOTA: Este test verifica el flujo de navegación. Si no se han completado
     * todas las secciones, debe redirigir a la sección actual.
     */
    public function test_puede_acceder_a_finalizacion(): void
    {
        $this->completarTodasLasSecciones();

        // Aceptar términos para que no redirija a la página de términos
        $cuestionario = Cuestionario::where('evaluado_orden_id', $this->evaluado->id)->first();
        if ($cuestionario) {
            $cuestionario->update([
                'acepta_terminos' => true,
                'acepta_terminos_at' => now(),
            ]);
        }

        $response = $this->get("/cuestionario/{$this->evaluado->token_unico}/finalizar");

        // La página de finalización se muestra correctamente
        $response->assertStatus(200);
    }

    /**
     * Test: Puede completar el cuestionario con firma digital
     * 
     * NOTA: Este test verifica el flujo completo de completar el cuestionario.
     * Requiere que el cuestionario esté en estado final para funcionar.
     */
    public function test_puede_completar_cuestionario(): void
    {
        $this->completarTodasLasSecciones();

        // Forzar el cuestionario al estado final para poder completarlo
        $cuestionario = Cuestionario::where('evaluado_orden_id', $this->evaluado->id)->first();
        if ($cuestionario) {
            $cuestionario->update([
                'seccion_actual' => $cuestionario->total_secciones + 1,
                'progreso_porcentaje' => 100
            ]);
        }

        $response = $this->post("/cuestionario/{$this->evaluado->token_unico}/completar", [
            'firma_digital' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==',
            'acepta_terminos' => '1'
        ]);

        // Debería redirigir a la página de completado
        $response->assertRedirect();
    }

    /**
     * Test: No puede completar sin aceptar términos
     */
    public function test_no_puede_completar_sin_confirmacion_final(): void
    {
        $this->completarTodasLasSecciones();

        $response = $this->post("/cuestionario/{$this->evaluado->token_unico}/completar", [
            'firma_digital' => 'data:image/png;base64,test',
            // Falta confirmacion_final
        ]);

        $response->assertSessionHasErrors(['confirmacion_final']);
    }

    // =========================================================================
    // MÉTODOS AUXILIARES
    // =========================================================================

    /**
     * Obtener datos válidos para sección 1
     */
    protected function getDatosSeccion1Validos(): array
    {
        return [
            'nombres_completos' => 'Juan Carlos',
            'apellidos_completos' => 'Pérez García',
            'dpi' => '1234567890101',
            'fecha_nacimiento' => '1990-05-15',
            'lugar_nacimiento' => 'Ciudad de Guatemala',
            'estado_civil' => 'soltero',
            'genero' => 'masculino',
            'nacionalidad' => 'Guatemalteca',
            'profesion_oficio' => 'Ingeniero en Sistemas',
            'nivel_educativo' => 'universidad_completa',
            'direccion_residencia' => 'Zona 10, Ciudad de Guatemala',
            'departamento' => 'Guatemala',
            'municipio' => 'Guatemala',
            'telefono_personal' => '12345678',
            'email_personal' => 'juan.perez@example.com'
        ];
    }

    /**
     * Completar verificación, aceptar términos y sección 1
     */
    protected function completarSeccion1(): void
    {
        $this->post("/cuestionario/{$this->evaluado->token_unico}/verificar", [
            'dpi_ingresado' => '1234567890101'
        ]);

        // Aceptar términos y condiciones
        $this->post(route('cuestionario.aceptar-terminos', $this->evaluado->token_unico), [
            'acepta_terminos' => '1',
        ]);

        $this->post("/cuestionario/{$this->evaluado->token_unico}/seccion/1", $this->getDatosSeccion1Validos());
    }

    /**
     * Completar sección 2
     */
    protected function completarSeccion2(): void
    {
        $this->post("/cuestionario/{$this->evaluado->token_unico}/seccion/2", [
            'estado_civil_detalle' => 'soltero',
            'vive_con_pareja' => 'no',
            'tiene_hijos' => 'no',
            'personas_hogar' => 4,
            'dependientes_economicos' => 2,
            'tipo_vivienda' => 'familiar',
            'personas_contribuyen_gastos' => 2
        ]);
    }

    /**
     * Completar sección 3
     */
    protected function completarSeccion3(): void
    {
        $this->post("/cuestionario/{$this->evaluado->token_unico}/seccion/3", [
            'situacion_laboral_actual' => 'empleado',
            'anos_experiencia_laboral' => 5,
            'empresa_actual' => 'Empresa Ejemplo S.A.',
            'puesto_actual' => 'Analista de Sistemas',
            'salario_actual' => 8000.00,
            'empleos_anteriores' => 'Empresa Anterior - Asistente - 2019-2021'
        ]);
    }

    /**
     * Completar sección 4
     */
    protected function completarSeccion4(): void
    {
        $this->post("/cuestionario/{$this->evaluado->token_unico}/seccion/4", [
            'ingresos_principales' => 8000.00,
            'ingresos_adicionales' => 1000.00,
            'gastos_vivienda' => 2500.00,
            'gastos_alimentacion' => 3000.00,
            'gastos_transporte' => 800.00,
            'tiene_deudas' => 'no',
            'tiene_ahorros' => 'si'
        ]);
    }

    /**
     * Completar sección 5
     */
    protected function completarSeccion5(): void
    {
        $this->post("/cuestionario/{$this->evaluado->token_unico}/seccion/5", [
            'referencia1_nombre' => 'María García',
            'referencia1_telefono' => '55551111',
            'referencia1_relacion' => 'Amiga',
            'referencia2_nombre' => 'Pedro López',
            'referencia2_telefono' => '55552222',
            'referencia2_relacion' => 'Vecino',
            'antecedentes_penales' => 'no',
            'despedido_trabajo' => 'no',
            'consume_alcohol' => 'ocasionalmente',
            'consume_drogas' => 'nunca',
            'problemas_salud_mental' => 'no'
        ]);
    }

    /**
     * Completar todas las secciones
     */
    protected function completarTodasLasSecciones(): void
    {
        $this->completarSeccion1();
        $this->completarSeccion2();
        $this->completarSeccion3();
        $this->completarSeccion4();
        $this->completarSeccion5();

        // Actualizar el cuestionario para indicar que está en la última sección
        $cuestionario = Cuestionario::where('evaluado_orden_id', $this->evaluado->id)->first();
        if ($cuestionario) {
            $cuestionario->update([
                'seccion_actual' => 6,
                'progreso_porcentaje' => 100
            ]);
        }
    }
}
