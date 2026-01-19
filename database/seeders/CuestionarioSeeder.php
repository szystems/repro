<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Empresa;
use App\Models\Orden;
use App\Models\EvaluadoOrden;
use Illuminate\Support\Facades\Hash;
use Faker\Factory as Faker;

class CuestionarioSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('es_ES');
        
        // Crear empresas de ejemplo
        $empresas = [
            [
                'nombre' => 'Construcciones Guatemala S.A.',
                'descripcion' => 'Empresa constructora especializada en proyectos residenciales y comerciales',
                'direccion' => 'Zona 10, Ciudad de Guatemala',
                'telefono' => '2333-4567',
                'email' => 'contacto@construcciones-gt.com',
                'nit' => '12345678-9',
            ],
            [
                'nombre' => 'Servicios Profesionales REPRO',
                'descripcion' => 'Consultora en recursos humanos y desarrollo organizacional',
                'direccion' => 'Zona 14, Ciudad de Guatemala',
                'telefono' => '2378-9012',
                'email' => 'info@repro.gt',
                'nit' => '98765432-1',
            ],
            [
                'nombre' => 'Manufactura Industrial del Norte',
                'descripcion' => 'Empresa manufacturera con enfoque en productos textiles',
                'direccion' => 'Zona Industrial, Guatemala',
                'telefono' => '2456-7890',
                'email' => 'ventas@manufactura-norte.com',
                'nit' => '55667788-0',
            ]
        ];
        
        foreach ($empresas as $empresaData) {
            Empresa::create($empresaData);
        }
        
        // Crear órdenes de evaluación
        $ordenes = [
            [
                'empresa_id' => 1,
                'cantidad_evals' => 5,
                'estado' => 'en_proceso',
                'creado_por' => 1,
                'fecha_solicitud' => now()->subDays(5),
                'fecha_limite' => now()->addDays(25),
                'observaciones' => 'Evaluación para puestos de ingeniería civil',
                'instrucciones_generales' => 'Evaluación completa para candidatos a ingeniero civil con experiencia en construcción residencial',
                'prioridad' => 'alta'
            ],
            [
                'empresa_id' => 1,
                'cantidad_evals' => 3,
                'estado' => 'programacion',
                'creado_por' => 1,
                'fecha_solicitud' => now()->subDays(10),
                'fecha_limite' => now()->addDays(20),
                'observaciones' => 'Proceso de selección para supervisores',
                'instrucciones_generales' => 'Proceso de selección para supervisor con experiencia en gestión de equipos',
                'prioridad' => 'normal'
            ],
            [
                'empresa_id' => 2,
                'cantidad_evals' => 2,
                'estado' => 'en_proceso',
                'creado_por' => 1,
                'fecha_solicitud' => now()->subDays(3),
                'fecha_limite' => now()->addDays(27),
                'observaciones' => 'Evaluación para consultores RRHH',
                'instrucciones_generales' => 'Evaluación para consultor especializado en desarrollo organizacional',
                'prioridad' => 'alta'
            ],
            [
                'empresa_id' => 3,
                'cantidad_evals' => 10,
                'estado' => 'requisito',
                'creado_por' => 1,
                'fecha_solicitud' => now()->subDays(1),
                'fecha_limite' => now()->addDays(29),
                'observaciones' => 'Proceso masivo operarios',
                'instrucciones_generales' => 'Proceso masivo de selección para operarios de producción textil',
                'prioridad' => 'baja'
            ],
            [
                'empresa_id' => 2,
                'cantidad_evals' => 1,
                'estado' => 'entregado',
                'creado_por' => 1,
                'fecha_solicitud' => now()->subDays(45),
                'fecha_limite' => now()->subDays(15),
                'observaciones' => 'Proceso completado',
                'instrucciones_generales' => 'Evaluación completada para analista especializado en estructuras salariales',
                'prioridad' => 'normal'
            ]
        ];
        
        foreach ($ordenes as $ordenData) {
            Orden::create($ordenData);
        }
        
        // Crear evaluados con datos realistas guatemaltecos
        $evaluados = [
            // Evaluados para primera orden (Ingeniería)
            [
                'nombre' => 'Carlos Alberto',
                'apellidos' => 'Morales Hernández',
                'dpi' => '2234567890101',
                'email' => 'carlos.morales@email.com',
                'telefono' => '4567-8901',
                'tipo_documento' => 'dpi',
                'tipo_servicio' => 'poligrafo',
                'tipo_formulario' => 'preempleo',
                'puesto_evaluar' => 'Ingeniero Civil',
                'fecha_programada' => now()->addDays(rand(1, 15)),
                'estado_evaluacion' => 'pendiente',
                'token_unico' => \Illuminate\Support\Str::random(32),
                'token_expira_at' => now()->addDays(30),
                'cuestionario_completado' => 0,
                'orden_id' => 1,
            ],
            [
                'nombre' => 'Ana Sofía',
                'apellidos' => 'García López',
                'dpi' => '1987654321098',
                'email' => 'ana.garcia@email.com',
                'telefono' => '5678-9012',
                'tipo_documento' => 'dpi',
                'tipo_servicio' => 'socioeconomico',
                'tipo_formulario' => 'preempleo',
                'puesto_evaluar' => 'Ingeniero Civil',
                'fecha_programada' => now()->addDays(rand(1, 15)),
                'estado_evaluacion' => 'en_proceso',
                'token_unico' => \Illuminate\Support\Str::random(32),
                'token_expira_at' => now()->addDays(30),
                'cuestionario_completado' => 1,
                'orden_id' => 1,
            ],
            [
                'nombre' => 'Luis Fernando',
                'apellidos' => 'Rodríguez Pérez',
                'dpi' => '3456789012345',
                'email' => 'luis.rodriguez@email.com',
                'telefono' => '6789-0123',
                'tipo_documento' => 'dpi',
                'tipo_servicio' => 'vsa',
                'tipo_formulario' => 'preempleo',
                'puesto_evaluar' => 'Ingeniero Civil',
                'fecha_programada' => now()->addDays(rand(1, 15)),
                'estado_evaluacion' => 'pendiente',
                'token_unico' => \Illuminate\Support\Str::random(32),
                'token_expira_at' => now()->addDays(30),
                'cuestionario_completado' => 0,
                'orden_id' => 1,
            ],
            
            // Evaluados para segunda orden (Supervisión)
            [
                'nombre' => 'Miguel Ángel',
                'apellidos' => 'Vásquez Santizo',
                'dpi' => '2345678901234',
                'email' => 'miguel.vasquez@email.com',
                'telefono' => '7890-1234',
                'tipo_documento' => 'dpi',
                'tipo_servicio' => 'poligrafo',
                'tipo_formulario' => 'periodica',
                'puesto_evaluar' => 'Supervisor de Obras',
                'fecha_programada' => now()->addDays(rand(1, 15)),
                'estado_evaluacion' => 'completado',
                'token_unico' => \Illuminate\Support\Str::random(32),
                'token_expira_at' => now()->addDays(30),
                'cuestionario_completado' => 1,
                'completado_at' => now()->subDays(rand(1, 5)),
                'resultado' => 'aprobado',
                'orden_id' => 2,
            ],
            [
                'nombre' => 'Rosa María',
                'apellidos' => 'Juárez Morales',
                'dpi' => '4567890123456',
                'email' => 'rosa.juarez@email.com',
                'telefono' => '8901-2345',
                'tipo_documento' => 'dpi',
                'tipo_servicio' => 'socioeconomico',
                'tipo_formulario' => 'preempleo',
                'puesto_evaluar' => 'Supervisor de Obras',
                'fecha_programada' => now()->addDays(rand(1, 15)),
                'estado_evaluacion' => 'en_proceso',
                'token_unico' => \Illuminate\Support\Str::random(32),
                'token_expira_at' => now()->addDays(30),
                'cuestionario_completado' => 0,
                'orden_id' => 2,
            ],
            
            // Evaluados para tercera orden (RRHH)
            [
                'nombre' => 'Fernando José',
                'apellidos' => 'Castillo Méndez',
                'dpi' => '5678901234567',
                'email' => 'fernando.castillo@email.com',
                'telefono' => '9012-3456',
                'tipo_documento' => 'dpi',
                'tipo_servicio' => 'vsa',
                'tipo_formulario' => 'especifica',
                'puesto_evaluar' => 'Consultor Senior en RRHH',
                'fecha_programada' => now()->addDays(rand(1, 15)),
                'estado_evaluacion' => 'completado',
                'token_unico' => \Illuminate\Support\Str::random(32),
                'token_expira_at' => now()->addDays(30),
                'cuestionario_completado' => 1,
                'completado_at' => now()->subDays(rand(1, 3)),
                'resultado' => 'aprobado',
                'orden_id' => 3,
            ],
            [
                'nombre' => 'Patricia Elena',
                'apellidos' => 'Monterroso Aguilar',
                'dpi' => '6789012345678',
                'email' => 'patricia.monterroso@email.com',
                'telefono' => '0123-4567',
                'tipo_documento' => 'dpi',
                'tipo_servicio' => 'poligrafo',
                'tipo_formulario' => 'preempleo',
                'puesto_evaluar' => 'Consultor Senior en RRHH',
                'fecha_programada' => now()->addDays(rand(1, 15)),
                'estado_evaluacion' => 'pendiente',
                'token_unico' => \Illuminate\Support\Str::random(32),
                'token_expira_at' => now()->addDays(30),
                'cuestionario_completado' => 0,
                'orden_id' => 3,
            ],
            
            // Evaluados para cuarta orden (Operarios)
            [
                'nombre' => 'Juan Carlos',
                'apellidos' => 'López Ramírez',
                'dpi' => '7890123456789',
                'email' => 'juan.lopez@email.com',
                'telefono' => '1234-5678',
                'tipo_documento' => 'dpi',
                'tipo_servicio' => 'socioeconomico',
                'tipo_formulario' => 'periodica',
                'puesto_evaluar' => 'Operario de Producción',
                'fecha_programada' => now()->addDays(rand(1, 15)),
                'estado_evaluacion' => 'en_proceso',
                'token_unico' => \Illuminate\Support\Str::random(32),
                'token_expira_at' => now()->addDays(30),
                'cuestionario_completado' => 1,
                'orden_id' => 4,
            ],
            [
                'nombre' => 'María Isabel',
                'apellidos' => 'Hernández Flores',
                'dpi' => '8901234567890',
                'email' => 'maria.hernandez@email.com',
                'telefono' => '2345-6789',
                'tipo_documento' => 'dpi',
                'tipo_servicio' => 'vsa',
                'tipo_formulario' => 'preempleo',
                'puesto_evaluar' => 'Operario de Producción',
                'fecha_programada' => now()->addDays(rand(1, 15)),
                'estado_evaluacion' => 'pendiente',
                'token_unico' => \Illuminate\Support\Str::random(32),
                'token_expira_at' => now()->addDays(30),
                'cuestionario_completado' => 0,
                'orden_id' => 4,
            ],
            
            // Evaluado para proceso completado
            [
                'nombre' => 'Roberto Antonio',
                'apellidos' => 'Sánchez Gutiérrez',
                'dpi' => '9012345678901',
                'email' => 'roberto.sanchez@email.com',
                'telefono' => '3456-7890',
                'tipo_documento' => 'dpi',
                'tipo_servicio' => 'poligrafo',
                'tipo_formulario' => 'especifica',
                'puesto_evaluar' => 'Analista de Compensaciones',
                'fecha_programada' => now()->subDays(20),
                'fecha_realizada' => now()->subDays(18),
                'estado_evaluacion' => 'completado',
                'token_unico' => \Illuminate\Support\Str::random(32),
                'token_expira_at' => now()->subDays(15),
                'cuestionario_completado' => 1,
                'completado_at' => now()->subDays(18),
                'resultado' => 'aprobado',
                'orden_id' => 5,
            ]
        ];
        
        foreach ($evaluados as $evaluadoData) {
            \App\Models\EvaluadoOrden::create($evaluadoData);
        }
        
        // Crear cuestionarios con respuestas realistas usando la estructura existente
        $this->crearCuestionariosEjemplo();
        
        $this->command->info('✅ Seeders de cuestionarios creados exitosamente');
        $this->command->info('📊 Datos creados:');
        $this->command->info('   - 3 empresas');
        $this->command->info('   - 5 órdenes de evaluación');
        $this->command->info('   - 11 evaluados');
        $this->command->info('   - Cuestionarios con respuestas de ejemplo');
    }
    
    private function crearCuestionariosEjemplo(): void
    {
        $faker = Faker::create('es_ES');
        
        // Obtener algunos evaluados para crear cuestionarios
        $evaluados = \App\Models\EvaluadoOrden::take(8)->get();
        
        foreach ($evaluados as $index => $evaluado) {
            $completado = $faker->boolean(60); // 60% de probabilidad de estar completado
            $seccionActual = $completado ? 5 : rand(1, 4);
            $progreso = $completado ? 100 : rand(20, 90);
            
            // Crear el cuestionario base
            $cuestionario = \App\Models\Cuestionario::create([
                'evaluado_orden_id' => $evaluado->id,
                'tipo_formulario' => $evaluado->tipo_formulario,
                'seccion_actual' => $seccionActual,
                'total_secciones' => 5,
                'progreso_porcentaje' => $progreso,
                'completado' => $completado ? 1 : 0,
                'bloqueado' => 0,
                'ip_completado' => $completado ? $faker->ipv4() : null,
                'completado_at' => $completado ? now()->subDays(rand(1, 10)) : null,
                'created_at' => now()->subDays(rand(1, 30)),
                'updated_at' => now()->subDays(rand(0, 5)),
            ]);
            
            // Crear algunas respuestas de ejemplo básicas
            $this->crearRespuestasBasicas($cuestionario->id, $faker, $seccionActual);
        }
    }
    
    private function crearRespuestasBasicas(int $cuestionarioId, $faker, int $seccionActual): void
    {
        // Crear respuestas básicas para mostrar en el dashboard
        $respuestasBasicas = [
            ['seccion' => 'datos_personales', 'campo' => 'estado_civil', 'valor' => $faker->randomElement(['soltero', 'casado', 'union_libre']), 'tipo_campo' => 'select'],
            ['seccion' => 'datos_personales', 'campo' => 'numero_hijos', 'valor' => rand(0, 3), 'tipo_campo' => 'number'],
            ['seccion' => 'datos_personales', 'campo' => 'licencia_conducir', 'valor' => $faker->randomElement(['si', 'no']), 'tipo_campo' => 'select'],
            ['seccion' => 'formacion_academica', 'campo' => 'nivel_educativo', 'valor' => $faker->randomElement(['secundaria', 'universitario', 'tecnico']), 'tipo_campo' => 'select'],
            ['seccion' => 'formacion_academica', 'campo' => 'carrera', 'valor' => $faker->randomElement(['Ingeniería', 'Administración', 'Contaduría']), 'tipo_campo' => 'text'],
            ['seccion' => 'experiencia_laboral', 'campo' => 'anos_experiencia', 'valor' => rand(1, 15), 'tipo_campo' => 'number'],
            ['seccion' => 'experiencia_laboral', 'campo' => 'empresa_anterior', 'valor' => $faker->company(), 'tipo_campo' => 'text'],
        ];
        
        foreach ($respuestasBasicas as $respuesta) {
            if ($respuesta['seccion'] === 'datos_personales' || rand(1, 10) <= $seccionActual * 2) {
                \App\Models\CuestionarioRespuesta::create([
                    'cuestionario_id' => $cuestionarioId,
                    'seccion' => $respuesta['seccion'],
                    'campo' => $respuesta['campo'],
                    'valor' => $respuesta['valor'],
                    'tipo_campo' => $respuesta['tipo_campo'],
                    'requerido' => 1,
                    'metadata' => null,
                ]);
            }
        }
    }

}