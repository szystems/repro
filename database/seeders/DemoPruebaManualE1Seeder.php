<?php

namespace Database\Seeders;

use App\Models\Empresa;
use App\Models\EvaluadoOrden;
use App\Models\Orden;
use App\Models\User;
use Database\Seeders\DepartamentosMunicipiosSeeder;
use Illuminate\Database\Seeder;

/**
 * Crea un evaluado de prueba para verificación manual de E1 (catálogo GT, foto, sección 1).
 *
 * Ejecutar: php artisan db:seed --class=DemoPruebaManualE1Seeder
 */
class DemoPruebaManualE1Seeder extends Seeder
{
    /** Token fijo — URL predecible para pruebas locales */
    public const TOKEN = 'e1demo2026pruebamanualtokenrepr0';

    /** DPI para verificación de identidad en el formulario */
    public const DPI = '2405617300105';

    public function run(): void
    {
        $this->call(DepartamentosMunicipiosSeeder::class);

        $admin = User::query()->where('role_as', '>=', 2)->where('estado', 1)->first()
            ?? User::query()->first();

        if (! $admin) {
            $this->command?->error('No hay usuarios en la BD. Ejecute primero: php artisan db:seed');

            return;
        }

        $empresa = Empresa::firstOrCreate(
            ['nit' => '99999999-9'],
            [
                'nombre' => 'Empresa Demo — Prueba Manual E1',
                'descripcion' => 'Registro temporal para probar formulario (motor E1)',
                'direccion' => 'Zona 10, Ciudad de Guatemala',
                'telefono' => '2222-0000',
                'email' => 'demo-e1@repro.local',
            ]
        );

        $orden = Orden::firstOrCreate(
            [
                'empresa_id' => $empresa->id,
                'observaciones_internas' => '[DEMO E1] Orden de prueba manual — puede eliminarse',
            ],
            [
                'creado_por' => $admin->id,
                'estado' => 'en_proceso',
                'fecha_solicitud' => now(),
                'fecha_limite' => now()->addDays(30),
                'prioridad' => 'normal',
                'tipo_creador' => 'repro',
            ]
        );

        $evaluado = EvaluadoOrden::updateOrCreate(
            ['token_unico' => self::TOKEN],
            [
                'orden_id' => $orden->id,
                'nombre' => 'Carlos',
                'apellidos' => 'Demo Prueba',
                'email' => 'carlos.demo.e1@repro.local',
                'telefono' => '5555-1234',
                'dpi' => self::DPI,
                'tipo_documento' => 'dpi',
                'tipo_servicio' => 'poligrafo',
                'tipo_formulario' => 'preempleo',
                'puesto_evaluar' => 'Analista de prueba',
                'token_expira_at' => now()->addDays(30),
                'cuestionario_completado' => false,
                'completado_at' => null,
                'estado_evaluacion' => 'pendiente_de_evaluacion',
                'estado_formulario' => 'pendiente_de_llenar',
                'estado_programacion' => 'contactando',
            ]
        );

        // Reiniciar cuestionario previo si existía (flujo limpio desde cero)
        if ($evaluado->cuestionario) {
            $evaluado->cuestionario->respuestas()->delete();
            $evaluado->cuestionario->delete();
        }

        $baseUrl = rtrim(config('app.url', 'http://localhost:8000'), '/');
        if (! str_contains($baseUrl, ':8000') && app()->environment('local')) {
            $baseUrl = 'http://localhost:8000';
        }
        $urlInicio = "{$baseUrl}/cuestionario/" . self::TOKEN;
        $urlSeccion1 = "{$urlInicio}/seccion/1";

        $this->command?->newLine();
        $this->command?->info('══════════════════════════════════════════════════════════');
        $this->command?->info('  DEMO E1 — Registro listo para prueba manual');
        $this->command?->info('══════════════════════════════════════════════════════════');
        $this->command?->newLine();
        $this->command?->line("  Evaluado:  {$evaluado->nombre} {$evaluado->apellidos}");
        $this->command?->line('  DPI:       ' . self::DPI);
        $this->command?->line('  Token:     ' . self::TOKEN);
        $this->command?->line("  Orden ID:  {$orden->id} · Evaluado ID: {$evaluado->id}");
        $this->command?->newLine();
        $this->command?->line("  1. Abrir:     {$urlInicio}");
        $this->command?->line("  2. Verificar DPI: " . self::DPI);
        $this->command?->line('  3. Aceptar términos → Sección 1');
        $this->command?->newLine();
        $this->command?->line('  Checklist E1 en sección 1:');
        $this->command?->line('  • Foto: Tomar foto / Subir archivo + vista previa');
        $this->command?->line('  • Depto/Municipio residencia: selects dependientes');
        $this->command?->line('  • Opción "Otro (extranjero)" → campos de texto');
        $this->command?->line('  • Guardar y Continuar → debe avanzar a sección 2');
        $this->command?->newLine();
        $this->command?->line("  Acceso directo sección 1 (tras verificar DPI): {$urlSeccion1}");
        $this->command?->info('══════════════════════════════════════════════════════════');
        $this->command?->newLine();
    }
}
