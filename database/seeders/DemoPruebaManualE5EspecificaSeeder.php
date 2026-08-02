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
 * Orden + evaluado de prueba para E5 Específica (base periódica + caso/hecho).
 *
 * Ejecutar: php artisan db:seed --class=DemoPruebaManualE5EspecificaSeeder --force
 */
class DemoPruebaManualE5EspecificaSeeder extends Seeder
{
    public const TOKEN = 'e5demo2026especificatokenrepr0';

    public const DPI = '2405617300405';

    public const EMPRESA_USER_EMAIL = 'demo-empresa-e5e@repro.local';

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
            ['nit' => '66666666-6'],
            [
                'nombre' => 'Empresa Demo — Prueba Manual E5 (Específica)',
                'descripcion' => 'Registro temporal para probar formulario Específica',
                'direccion' => 'Zona 9, Ciudad de Guatemala',
                'telefono' => '2222-0006',
                'email' => 'demo-e5e@repro.local',
            ]
        );

        $empresaUser = User::updateOrCreate(
            ['email' => self::EMPRESA_USER_EMAIL],
            [
                'name' => 'Usuario Demo Empresa E5 Específica',
                'password' => Hash::make(self::EMPRESA_USER_PASSWORD),
                'role_as' => 1,
                'empresa_id' => $empresa->id,
                'principal' => 1,
                'estado' => 1,
                'telefono' => '5555-0006',
                'cargo' => 'Contacto demo E5 Específica',
            ]
        );

        $empresaRole = Role::where('name', 'empresa')->first();
        if ($empresaRole) {
            $empresaUser->roles()->syncWithoutDetaching([$empresaRole->id]);
        }

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
                'observaciones_internas' => '[DEMO E5] Orden específica — prueba manual 5 secciones',
            ]);
        } else {
            $orden->update([
                'empresa_id' => $empresa->id,
                'estado' => 'en_proceso',
                'observaciones_internas' => '[DEMO E5] Orden específica — prueba manual 5 secciones',
            ]);
        }

        $evaluado = EvaluadoOrden::updateOrCreate(
            ['token_unico' => self::TOKEN],
            [
                'orden_id' => $orden->id,
                'nombre' => 'Ana',
                'apellidos' => 'Demo Específica',
                'email' => 'ana.demo.e5e@repro.local',
                'telefono' => '5555-8901',
                'dpi' => self::DPI,
                'tipo_documento' => 'dpi',
                'tipo_servicio' => 'poligrafo',
                'tipo_formulario' => 'especifica',
                'puesto_evaluar' => 'Cajero — demo específica',
                'motivo_hecho_evaluacion' => 'Investigación por faltante en caja — demo manual REPRO.',
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
        $this->command?->info('  DEMO E5 — Específica listo para prueba manual');
        $this->command?->info('══════════════════════════════════════════════════════════');
        $this->command?->line('  DPI:   '.self::DPI);
        $this->command?->line('  Token: '.self::TOKEN);
        $this->command?->line("  URL:   {$urlInicio}");
        $this->command?->line('  Checklist: académica solo último grado · pregunta 1 caso/hecho amplia · solo DPI');
        $this->command?->line('  Admin: admin@repro.com / admin1234');
        $this->command?->info('══════════════════════════════════════════════════════════');
    }
}
