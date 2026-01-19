<?php

namespace Tests\Feature;

use App\Models\EvaluadoOrden;
use App\Models\Orden;
use App\Models\Empresa;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CuestionarioDatosPersonalesTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private function crearEvaluadoValido()
    {
        $empresa = Empresa::factory()->create();
        $orden = Orden::factory()->create(['empresa_id' => $empresa->id]);
        
        return EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'token_unico' => 'test-token-validacion',
            'token_expira_at' => now()->addDays(30),
            'cuestionario_completado' => false,
            'dpi' => '1234567890101' // DPI de prueba
        ]);
    }

    public function test_puede_enviar_formulario_con_datos_validos(): void
    {
        $evaluado = $this->crearEvaluadoValido();

        // Primero verificar identidad
        $this->post("/cuestionario/{$evaluado->token_unico}/verificar", [
            'dpi_ingresado' => '1234567890101'
        ]);

        $datosFormulario = [
            'nombres_completos' => 'Juan Carlos',
            'apellidos_completos' => 'Pérez García',
            'dpi' => '1234567890101',
            'fecha_nacimiento' => '1990-05-15',
            'lugar_nacimiento' => 'Guatemala',
            'estado_civil' => 'soltero',
            'genero' => 'masculino',
            'nacionalidad' => 'guatemalteca',
            'profesion_oficio' => 'Ingeniero',
            'nivel_educativo' => 'universidad_completa',
            'direccion_residencia' => 'Ciudad de Guatemala',
            'departamento' => 'Guatemala',
            'municipio' => 'Guatemala',
            'telefono_personal' => '12345678',
            'email_personal' => 'juan@example.com'
        ];

        $response = $this->post("/cuestionario/{$evaluado->token_unico}/seccion/1", $datosFormulario);

        $response->assertStatus(302); // Redirección exitosa
        $response->assertSessionHasNoErrors();
    }

    public function test_falla_validacion_con_campos_faltantes(): void
    {
        $evaluado = $this->crearEvaluadoValido();

        $datosIncompletos = [
            'nombres_completos' => 'Juan Carlos',
            // Faltan apellidos_completos, dpi, etc.
        ];

        $response = $this->post("/cuestionario/{$evaluado->token_unico}/seccion/1", $datosIncompletos);

        $response->assertStatus(302); // Redirección por errores de validación
        $response->assertSessionHasErrors(['apellidos_completos', 'dpi', 'fecha_nacimiento']);
    }

    public function test_valida_dpi_coincida_con_evaluado(): void
    {
        $evaluado = $this->crearEvaluadoValido();
        
        $datosFormulario = [
            'nombres_completos' => 'Juan Carlos',
            'apellidos_completos' => 'Pérez García',
            'dpi' => '9999999999999', // DPI diferente al del evaluado
            'fecha_nacimiento' => '1990-05-15',
            'lugar_nacimiento' => 'Guatemala',
            'estado_civil' => 'soltero',
            'genero' => 'masculino',
            'nacionalidad' => 'guatemalteca',
            'profesion_oficio' => 'Ingeniero',
            'nivel_educativo' => 'universidad_completa',
            'direccion_residencia' => 'Ciudad de Guatemala',
            'departamento' => 'Guatemala',
            'municipio' => 'Guatemala',
            'telefono_personal' => '12345678',
            'email_personal' => 'juan@example.com'
        ];

        $response = $this->post("/cuestionario/{$evaluado->token_unico}/seccion/1", $datosFormulario);

        $response->assertStatus(302); // Redirección por error de validación
        $response->assertSessionHasErrors(['dpi']);
    }

    public function test_acepta_dpi_correcto_del_evaluado(): void
    {
        $evaluado = $this->crearEvaluadoValido();
        
        // Primero verificar el DPI para establecer sesión
        $this->post("/cuestionario/{$evaluado->token_unico}/verificar", [
            'dpi_ingresado' => $evaluado->dpi
        ]);
        
        $datosFormulario = [
            'nombres_completos' => 'Juan Carlos',
            'apellidos_completos' => 'Pérez García',
            'dpi' => $evaluado->dpi, // DPI correcto del evaluado
            'fecha_nacimiento' => '1990-05-15',
            'lugar_nacimiento' => 'Guatemala',
            'estado_civil' => 'soltero',
            'genero' => 'masculino',
            'nacionalidad' => 'guatemalteca',
            'profesion_oficio' => 'Ingeniero',
            'nivel_educativo' => 'universidad_completa',
            'direccion_residencia' => 'Ciudad de Guatemala',
            'departamento' => 'Guatemala',
            'municipio' => 'Guatemala',
            'telefono_personal' => '12345678',
            'email_personal' => 'juan@example.com'
        ];

        $response = $this->post("/cuestionario/{$evaluado->token_unico}/seccion/1", $datosFormulario);

        $response->assertStatus(302); // Redirección exitosa
        $response->assertSessionHasNoErrors();
    }
}
