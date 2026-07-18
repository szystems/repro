<?php

namespace Database\Seeders;

use App\Models\Empresa;
use App\Models\EvaluadoOrden;
use App\Models\Orden;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Orden + evaluado de prueba para E5 Periódica (5 secciones).
 *
 * Ejecutar: php artisan db:seed --class=DemoPruebaManualE5PeriodicaSeeder --force
 */
class DemoPruebaManualE5PeriodicaSeeder extends Seeder
{
    public const TOKEN = 'e5demo2026periodicatokenrepr0';

    public const DPI = '2405617300305';

    public const EMPRESA_USER_EMAIL = 'demo-empresa-e5@repro.local';

    public const EMPRESA_USER_PASSWORD = 'empresa1234';

    public function run(): void
    {
        $this->call(DepartamentosMunicipiosSeeder::class);

        if (! Role::where('name', 'empresa')->exists()) {
            $this->call(RolesAndPermissionsSeeder::class);
            $this->call(RoleSeeder::class);
        }

        $admin = User::query()->where('role_as', '>=', 2)->where('estado', 1)->first()
            ?? User::query()->first();

        if (! $admin) {
            $this->command?->error('No hay usuarios en la BD. Ejecute primero: php artisan db:seed');

            return;
        }

        $empresa = Empresa::firstOrCreate(
            ['nit' => '77777777-7'],
            [
                'nombre' => 'Empresa Demo — Prueba Manual E5 (Periódica)',
                'descripcion' => 'Registro temporal para probar formulario Periódica',
                'direccion' => 'Zona 10, Ciudad de Guatemala',
                'telefono' => '2222-0005',
                'email' => 'demo-e5@repro.local',
            ]
        );

        $empresaUser = User::updateOrCreate(
            ['email' => self::EMPRESA_USER_EMAIL],
            [
                'name' => 'Usuario Demo Empresa E5',
                'password' => Hash::make(self::EMPRESA_USER_PASSWORD),
                'role_as' => 1,
                'empresa_id' => $empresa->id,
                'principal' => 1,
                'estado' => 1,
                'telefono' => '5555-0005',
                'cargo' => 'Contacto demo E5',
            ]
        );

        $empresaRole = Role::where('name', 'empresa')->first();
        if ($empresaRole) {
            $empresaUser->roles()->syncWithoutDetaching([$empresaRole->id]);
        }

        // observaciones_internas está cifrada: no usar firstOrCreate por ese campo
        // (crearía una orden nueva en cada seed y “movería” el evaluado).
        $evaluadoExistente = EvaluadoOrden::query()->where('token_unico', self::TOKEN)->first();
        $orden = $evaluadoExistente?->orden;

        if (! $orden) {
            $orden = Orden::create([
                'empresa_id' => $empresa->id,
                'creado_por' => $admin->id,
                'estado' => 'en_proceso',
                'fecha_solicitud' => now(),
                'fecha_limite' => now()->addDays(30),
                'prioridad' => 'normal',
                'tipo_creador' => 'repro',
                'resultados_visibles_empresa' => false,
                'observaciones_internas' => '[DEMO E5] Orden periódica — prueba manual 5 secciones',
            ]);
        } else {
            $orden->update([
                'empresa_id' => $empresa->id,
                'estado' => 'en_proceso',
                'observaciones_internas' => '[DEMO E5] Orden periódica — prueba manual 5 secciones',
            ]);
        }

        $evaluado = EvaluadoOrden::updateOrCreate(
            ['token_unico' => self::TOKEN],
            [
                'orden_id' => $orden->id,
                'nombre' => 'Carlos',
                'apellidos' => 'Demo Periódica',
                'email' => 'carlos.demo.e5@repro.local',
                'telefono' => '5555-7890',
                'dpi' => self::DPI,
                'tipo_documento' => 'dpi',
                'tipo_servicio' => 'poligrafo',
                'tipo_formulario' => 'periodica',
                'puesto_evaluar' => 'Supervisor — demo periódica',
                'token_expira_at' => now()->addDays(30),
                'cuestionario_completado' => false,
                'estado_evaluacion' => 'pendiente_de_evaluacion',
                'estado_formulario' => 'pendiente_de_llenar',
                'estado_programacion' => 'contactando',
            ]
        );

        if ($evaluado->cuestionario) {
            $evaluado->cuestionario->respuestas()->delete();
            $evaluado->cuestionario->delete();
        }

        $baseUrl = rtrim(config('app.url', 'http://localhost:8000'), '/');
        if (! str_contains($baseUrl, ':8000') && app()->environment('local')) {
            $baseUrl = 'http://localhost:8000';
        }

        $urlInicio = "{$baseUrl}/cuestionario/".self::TOKEN;

        $this->command?->newLine();
        $this->command?->info('══════════════════════════════════════════════════════════');
        $this->command?->info('  DEMO E5 — Periódica listo para prueba manual');
        $this->command?->info('══════════════════════════════════════════════════════════');
        $this->command?->line('  DPI:   '.self::DPI);
        $this->command?->line('  Token: '.self::TOKEN);
        $this->command?->line("  URL:   {$urlInicio}");
        $this->command?->line('  Admin: admin@repro.com / admin1234');
        $this->command?->info('══════════════════════════════════════════════════════════');
    }
}
