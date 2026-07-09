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
 * Orden + evaluado de prueba para verificación manual de E4 (formulario Socioeconómico, 6 secciones).
 *
 * Ejecutar: php artisan db:seed --class=DemoPruebaManualE4Seeder --force
 */
class DemoPruebaManualE4Seeder extends Seeder
{
    /** Token fijo — URL predecible para pruebas locales */
    public const TOKEN = 'e4demo2026pruebamanualtokenrepr0';

    /** DPI para verificación de identidad (13 dígitos numéricos, CUI Guatemala) */
    public const DPI = '2405617300205';

    public const EMPRESA_USER_EMAIL = 'demo-empresa-e4@repro.local';

    public const EMPRESA_USER_PASSWORD = 'empresa1234';

    public function run(): void
    {
        $this->call(DepartamentosMunicipiosSeeder::class);

        if (! Role::where('name', 'empresa')->exists()) {
            $this->call(RolesAndPermissionsSeeder::class);
            $this->call(RoleSeeder::class);
        }

        if (strlen(self::DPI) !== 13 || ! ctype_digit(self::DPI)) {
            $this->command?->error('DPI demo debe ser exactamente 13 dígitos numéricos.');

            return;
        }

        $admin = User::query()->where('role_as', '>=', 2)->where('estado', 1)->first()
            ?? User::query()->first();

        if (! $admin) {
            $this->command?->error('No hay usuarios en la BD. Ejecute primero: php artisan db:seed');

            return;
        }

        $empresa = Empresa::firstOrCreate(
            ['nit' => '88888888-8'],
            [
                'nombre' => 'Empresa Demo — Prueba Manual E4 (Socio)',
                'descripcion' => 'Registro temporal para probar formulario Socioeconómico',
                'direccion' => 'Zona 4, Ciudad de Guatemala',
                'telefono' => '2222-0001',
                'email' => 'demo-e4@repro.local',
            ]
        );

        $empresaUser = User::updateOrCreate(
            ['email' => self::EMPRESA_USER_EMAIL],
            [
                'name' => 'Usuario Demo Empresa E4',
                'password' => Hash::make(self::EMPRESA_USER_PASSWORD),
                'role_as' => 1,
                'empresa_id' => $empresa->id,
                'principal' => 1,
                'estado' => 1,
                'telefono' => '5555-0004',
                'cargo' => 'Contacto demo E4',
            ]
        );

        $empresaRole = Role::where('name', 'empresa')->first();
        if ($empresaRole) {
            $empresaUser->roles()->syncWithoutDetaching([$empresaRole->id]);
        }

        $orden = Orden::firstOrCreate(
            [
                'empresa_id' => $empresa->id,
                'observaciones_internas' => '[DEMO E4] Orden socioeconómica — prueba manual 6 secciones',
            ],
            [
                'creado_por' => $admin->id,
                'estado' => 'en_proceso',
                'fecha_solicitud' => now(),
                'fecha_limite' => now()->addDays(30),
                'prioridad' => 'normal',
                'tipo_creador' => 'repro',
                'resultados_visibles_empresa' => false,
            ]
        );

        $evaluado = EvaluadoOrden::updateOrCreate(
            ['token_unico' => self::TOKEN],
            [
                'orden_id' => $orden->id,
                'nombre' => 'María',
                'apellidos' => 'Demo Socio',
                'email' => 'maria.demo.e4@repro.local',
                'telefono' => '5555-5678',
                'dpi' => self::DPI,
                'tipo_documento' => 'dpi',
                'tipo_servicio' => 'socioeconomico',
                'tipo_formulario' => 'preempleo',
                'puesto_evaluar' => 'Auxiliar administrativo — demo',
                'token_expira_at' => now()->addDays(30),
                'cuestionario_completado' => false,
                'completado_at' => null,
                'cuestionario_completado_at' => null,
                'estado_evaluacion' => 'pendiente_de_evaluacion',
                'estado_formulario' => 'pendiente_de_llenar',
                'estado_programacion' => 'contactando',
            ]
        );

        if ($evaluado->cuestionario) {
            $evaluado->cuestionario->respuestas()->delete();
            $evaluado->cuestionario->delete();
        }

        $evaluado->update([
            'cuestionario_completado' => false,
            'cuestionario_completado_at' => null,
        ]);

        $baseUrl = rtrim(config('app.url', 'http://localhost:8000'), '/');
        if (! str_contains($baseUrl, ':8000') && app()->environment('local')) {
            $baseUrl = 'http://localhost:8000';
        }

        $urlInicio = "{$baseUrl}/cuestionario/" . self::TOKEN;
        $urlSeccion1 = "{$urlInicio}/seccion/1";
        $urlAdminOrden = "{$baseUrl}/ordenes/{$orden->id}";

        $this->command?->newLine();
        $this->command?->info('══════════════════════════════════════════════════════════');
        $this->command?->info('  DEMO E4 — Socioeconómico listo para prueba manual');
        $this->command?->info('══════════════════════════════════════════════════════════');
        $this->command?->newLine();
        $this->command?->line("  Evaluado:  {$evaluado->nombre} {$evaluado->apellidos}");
        $this->command?->line('  Servicio:  Socioeconómico (6 secciones + finalizar)');
        $this->command?->line('  DPI (13 dígitos): ' . self::DPI);
        $this->command?->line('  Token:     ' . self::TOKEN);
        $this->command?->line("  Orden ID:  {$orden->id} · Evaluado ID: {$evaluado->id}");
        $this->command?->line("  Empresa:   {$empresa->nombre} (NIT {$empresa->nit})");
        $this->command?->newLine();
        $this->command?->line('  ── Candidato (formulario) ──');
        $this->command?->line("  1. Abrir:  {$urlInicio}");
        $this->command?->line('  2. Verificar DPI: ' . self::DPI);
        $this->command?->line('  3. Completar secciones 1–5 (matriz) + sección 6 complementaria');
        $this->command?->line('  4. Finalizar → documentos sugeridos: constancia laboral, recibo de luz');
        $this->command?->newLine();
        $this->command?->line('  ── Admin REPRO ──');
        $this->command?->line('  admin@repro.com / admin1234');
        $this->command?->line("  Orden:     {$urlAdminOrden}");
        $this->command?->newLine();
        $this->command?->line('  ── Portal empresa (tras completar cuestionario) ──');
        $this->command?->line('  ' . self::EMPRESA_USER_EMAIL . ' / ' . self::EMPRESA_USER_PASSWORD);
        $this->command?->line('  Liberar resultados: orden → estado Entregado + Resultados visibles');
        $this->command?->newLine();
        $this->command?->line('  Checklist E4 sección 6:');
        $this->command?->line('  • Referencias fam (≥2), pers (≥2), vec (≥1)');
        $this->command?->line('  • Importar referencias laborales desde historial (sec. 3)');
        $this->command?->line('  • Totales autocalculados en bienes y presupuesto');
        $this->command?->line('  • Campos de vivienda condicionales (alquiler, zona riesgo)');
        $this->command?->newLine();
        $this->command?->line("  Acceso directo sección 1 (tras verificar DPI): {$urlSeccion1}");
        $this->command?->info('══════════════════════════════════════════════════════════');
        $this->command?->newLine();
    }
}
