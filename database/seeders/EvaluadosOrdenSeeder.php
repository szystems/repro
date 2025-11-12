<?php

namespace Database\Seeders;

use App\Models\EvaluadoOrden;
use Illuminate\Database\Seeder;

class EvaluadosOrdenSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Crea evaluados de ejemplo vinculados a órdenes (futuras).
     * Incluye diferentes estados para demostrar el flujo completo.
     */
    public function run(): void
    {
        $this->command->info('🔄 Creando evaluados de ejemplo...');

        // Empresas guatemaltecas ficticias
        $empresas = [
            'Banco Industrial', 
            'Corporación Multi Inversiones',
            'Grupo Pantaleón',
            'Pollo Campero',
            'Cervecería Centroamericana'
        ];

        // 1. Evaluados con cuestionarios completados (10)
        $this->command->info('   📝 Creando 10 evaluados con cuestionario completado...');
        
        for ($i = 0; $i < 10; $i++) {
            EvaluadoOrden::factory()
                ->completado()
                ->notificado()
                ->create();
        }

        // 2. Evaluados con cuestionario pendiente (5)
        $this->command->info('   ⏳ Creando 5 evaluados con cuestionario pendiente...');
        
        for ($i = 0; $i < 5; $i++) {
            EvaluadoOrden::factory()
                ->notificado()
                ->create();
        }

        // 3. Evaluados en progreso (3)
        $this->command->info('   🔄 Creando 3 evaluados con cuestionario en progreso...');
        
        for ($i = 0; $i < 3; $i++) {
            EvaluadoOrden::factory()
                ->enProgreso()
                ->notificado()
                ->create();
        }

        // 4. Evaluados con token expirado (2)
        $this->command->info('   ⚠️  Creando 2 evaluados con token expirado...');
        
        for ($i = 0; $i < 2; $i++) {
            EvaluadoOrden::factory()
                ->expirado()
                ->notificado()
                ->create();
        }

        // 5. Caso especial: Mismo DPI con múltiples evaluaciones (historial)
        $this->command->info('   📚 Creando historial de evaluaciones para mismo DPI...');
        
        $dpiRecurrente = '2845671230001'; // DPI guatemalteco de ejemplo
        $nombreRecurrente = 'José Antonio Morales Pérez';
        $emailBase = 'jose.morales';

        // Primera evaluación (completada hace 6 meses)
        EvaluadoOrden::factory()
            ->completado()
            ->notificado()
            ->create([
                'nombre' => $nombreRecurrente,
                'dpi' => $dpiRecurrente,
                'email' => "{$emailBase}@bancoindustrial.com",
                'notas' => 'Primera evaluación - Banco Industrial - Pre-empleo',
                'created_at' => now()->subMonths(6),
                'updated_at' => now()->subMonths(6),
                'completado_at' => now()->subMonths(6)->addDays(2),
            ]);

        // Segunda evaluación (completada hace 2 meses)
        EvaluadoOrden::factory()
            ->completado()
            ->notificado()
            ->create([
                'nombre' => $nombreRecurrente,
                'dpi' => $dpiRecurrente,
                'email' => "{$emailBase}@cmi.com.gt",
                'notas' => 'Segunda evaluación - CMI - Periódica',
                'created_at' => now()->subMonths(2),
                'updated_at' => now()->subMonths(2),
                'completado_at' => now()->subMonths(2)->addDays(1),
            ]);

        // Tercera evaluación (pendiente actual)
        EvaluadoOrden::factory()
            ->notificado()
            ->create([
                'nombre' => $nombreRecurrente,
                'dpi' => $dpiRecurrente,
                'email' => "{$emailBase}@campero.com",
                'notas' => 'Tercera evaluación - Pollo Campero - Específica',
            ]);

        // 6. Evaluados sin notificar (2) - simulan recién creados
        $this->command->info('   📧 Creando 2 evaluados sin notificar...');
        
        EvaluadoOrden::factory()->count(2)->create([
            'notificado' => false,
            'notificado_at' => null,
        ]);

        $total = EvaluadoOrden::count();
        $completados = EvaluadoOrden::where('cuestionario_completado', true)->count();
        $pendientes = EvaluadoOrden::where('cuestionario_completado', false)->count();

        $this->command->newLine();
        $this->command->info("✅ Seeder completado!");
        $this->command->newLine();
        $this->command->table(
            ['Métrica', 'Cantidad'],
            [
                ['Total evaluados', $total],
                ['Cuestionarios completados', $completados],
                ['Cuestionarios pendientes', $pendientes],
                ['Evaluados únicos por DPI', EvaluadoOrden::distinct('dpi')->count()],
                ['Con historial múltiple', '1 (José Antonio Morales - 3 evaluaciones)'],
            ]
        );
    }
}
