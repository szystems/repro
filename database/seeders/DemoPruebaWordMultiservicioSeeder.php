<?php

namespace Database\Seeders;

use App\Models\Empresa;
use App\Models\EvaluadoOrden;
use App\Models\Orden;
use App\Models\User;
use Database\Seeders\Support\DemoWordCuestionarioBuilder;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Orden demo con evaluados precargados para probar export Word (todos los servicios + variantes).
 *
 * Ejecutar: php artisan db:seed --class=DemoPruebaWordMultiservicioSeeder
 */
class DemoPruebaWordMultiservicioSeeder extends Seeder
{
    public const OBSERVACIONES_ORDEN = '[DEMO WORD] Orden de prueba — exportación Word multiservicio';

    /** Código fijo: observaciones_internas está cifrada y no sirve para firstOrCreate. */
    public const CODIGO_ORDEN = 'ORD-DEMO-WORD-2026';

    /** @var list<array<string, mixed>> */
    private const EVALUADOS = [
        [
            'token' => 'worddemo2026poligrafopreempl0',
            'dpi' => '2405617300601',
            'tipo_servicio' => 'poligrafo',
            'tipo_formulario' => 'preempleo',
            'nombre' => 'Roberto',
            'apellidos' => 'Polígrafo Preempleo',
            'puesto_evaluar' => 'Gerente de Operaciones',
            'email' => 'roberto.poligrafo.pre@repro.local',
            'foto_rgb' => [0, 102, 204],
            'motivo' => 'Contratación nueva — puesto gerencial con manejo de personal y efectivo.',
        ],
        [
            'token' => 'worddemo2026poligrafoperiod0',
            'dpi' => '2405617300602',
            'tipo_servicio' => 'poligrafo',
            'tipo_formulario' => 'periodica',
            'nombre' => 'Sandra',
            'apellidos' => 'Polígrafo Periódica',
            'puesto_evaluar' => 'Encargada de Sucursal',
            'email' => 'sandra.poligrafo.period@repro.local',
            'foto_rgb' => [153, 51, 153],
            'motivo' => 'Evaluación periódica anual por política de la empresa contratante.',
        ],
        [
            'token' => 'worddemo2026poligrafoespecif0',
            'dpi' => '2405617300603',
            'tipo_servicio' => 'poligrafo',
            'tipo_formulario' => 'especifica',
            'nombre' => 'Edgar',
            'apellidos' => 'Polígrafo Específica',
            'puesto_evaluar' => 'Jefe de Bodega',
            'email' => 'edgar.poligrafo.esp@repro.local',
            'foto_rgb' => [204, 102, 0],
            'motivo' => 'Investigación específica por faltante de inventario reportado el 15/05/2026.',
        ],
        [
            'token' => 'worddemo2026vsapreempleo000',
            'dpi' => '2405617300604',
            'tipo_servicio' => 'vsa',
            'tipo_formulario' => 'preempleo',
            'nombre' => 'Valentina',
            'apellidos' => 'VSA Preempleo',
            'puesto_evaluar' => 'Ejecutiva de Ventas',
            'email' => 'valentina.vsa.pre@repro.local',
            'foto_rgb' => [0, 153, 76],
            'motivo' => 'Proceso de selección VSA para puesto comercial con acceso a información confidencial.',
        ],
        [
            'token' => 'worddemo2026socioeconomico0',
            'dpi' => '2405617300605',
            'tipo_servicio' => 'socioeconomico',
            'tipo_formulario' => 'preempleo',
            'nombre' => 'Samuel',
            'apellidos' => 'Socioeconómico Demo',
            'puesto_evaluar' => 'Coordinador Administrativo',
            'email' => 'samuel.socio.pre@repro.local',
            'foto_rgb' => [220, 20, 60],
            'motivo' => 'Estudio socioeconómico integral para contratación en área administrativa.',
        ],
    ];

    public function run(): void
    {
        $this->call(DepartamentosMunicipiosSeeder::class);

        $admin = $this->ensureUsuariosLogin();

        if ($admin === null) {
            $this->command?->error('No se pudo preparar un usuario admin. Ejecute: php artisan db:seed');

            return;
        }

        $empresa = Empresa::firstOrCreate(
            ['nit' => 'WORD-DEMO-2026'],
            [
                'nombre' => 'Empresa Demo — Prueba Word REPRO',
                'descripcion' => 'Registro temporal para probar exportación Word multiservicio',
                'direccion' => 'Zona 4, Ciudad de Guatemala',
                'telefono' => '2333-8888',
                'email' => 'demo-word@repro.local',
            ]
        );

        $orden = Orden::firstOrCreate(
            ['codigo_orden' => self::CODIGO_ORDEN],
            [
                'empresa_id' => $empresa->id,
                'creado_por' => $admin->id,
                'estado' => 'en_proceso',
                'fecha_solicitud' => now()->subDays(3),
                'fecha_limite' => now()->addDays(30),
                'prioridad' => 'alta',
                'tipo_creador' => 'repro',
                'instrucciones_generales' => 'Orden demo para validar plantillas Word por servicio y tipo de formulario.',
                'observaciones_internas' => self::OBSERVACIONES_ORDEN,
            ]
        );

        Orden::query()
            ->where('empresa_id', $empresa->id)
            ->where('id', '!=', $orden->id)
            ->whereDoesntHave('evaluados')
            ->delete();

        $baseUrl = rtrim(config('app.url', 'http://localhost:8000'), '/');
        if (! str_contains($baseUrl, ':8000') && app()->environment('local')) {
            $baseUrl = 'http://localhost:8000';
        }

        $this->command?->newLine();
        $this->command?->info('══════════════════════════════════════════════════════════');
        $this->command?->info('  DEMO WORD — Orden multiservicio lista');
        $this->command?->info('══════════════════════════════════════════════════════════');
        $this->command?->newLine();
        $this->command?->line("  Orden ID:     {$orden->id}");
        $this->command?->line('  Código:       ' . self::CODIGO_ORDEN . ' (fijo — reutilizable al re-ejecutar el seeder)');
        $this->command?->line("  Empresa:      {$empresa->nombre}");
        $this->command?->line("  Admin orden:  {$baseUrl}/ordenes/{$orden->id}");
        $this->command?->line('  Nota:         El ID numérico puede variar; use siempre este enlace tras re-seed.');
        $this->command?->newLine();

        foreach (self::EVALUADOS as $config) {
            $evaluado = EvaluadoOrden::updateOrCreate(
                ['token_unico' => $config['token']],
                [
                    'orden_id' => $orden->id,
                    'nombre' => $config['nombre'],
                    'apellidos' => $config['apellidos'],
                    'email' => $config['email'],
                    'telefono' => '55550600',
                    'dpi' => $config['dpi'],
                    'tipo_documento' => 'dpi',
                    'tipo_servicio' => $config['tipo_servicio'],
                    'tipo_formulario' => $config['tipo_formulario'],
                    'puesto_evaluar' => $config['puesto_evaluar'],
                    'sede_region_empresa' => 'Agencia Demo Zona 5, Quetzaltenango',
                    'direccion' => '3ra calle 4-56 zona 3, Quetzaltenango',
                    'token_expira_at' => now()->addDays(30),
                    'cuestionario_completado' => true,
                    'completado_at' => now()->subDay(),
                    'estado_evaluacion' => 'en_revision',
                    'estado_formulario' => 'formulario_completado_y_recibido',
                    'estado_programacion' => 'proceso_realizado',
                    'fecha_realizada' => now()->subDay(),
                    'resultado' => 'aprobado',
                    'poligrafista_id' => $admin->id,
                    'motivo_hecho_evaluacion' => $config['motivo'],
                    'notas_poligrafo' => 'Notas del evaluador REPRO (demo Word): candidato colaborador durante todo el proceso.',
                    'texto_informe_preliminar' => 'Informe preliminar demo: no se detectaron inconsistencias relevantes en la información declarada.',
                    'observaciones' => 'Evaluado demo generado automáticamente para prueba de plantillas Word.',
                ]
            );

            DemoWordCuestionarioBuilder::poblar($evaluado, $config['foto_rgb']);

            $servicio = strtoupper($config['tipo_servicio']);
            $formulario = ucfirst($config['tipo_formulario']);
            $this->command?->line("  • {$evaluado->nombre} {$evaluado->apellidos}");
            $this->command?->line("    Servicio: {$servicio} · Formulario: {$formulario} · DPI: {$config['dpi']}");
            $this->command?->line("    Word: abrir orden → «Descargar informe Word» (evaluado ID {$evaluado->id})");
            $this->command?->newLine();
        }

        $this->command?->line('  Pasos para probar:');
        $this->command?->line("  1. Login: {$baseUrl}/login");
        $this->command?->line('     admin@repro.com / admin1234');
        $this->command?->line('     (alternativa admin completo: szystems@hotmail.com / SPP7007aaa@@@)');
        $this->command?->line("  2. Abrir: {$baseUrl}/ordenes/{$orden->id}");
        $this->command?->line('  3. Descargar Word de cada evaluado y verificar tablas + foto');
        $this->command?->info('══════════════════════════════════════════════════════════');
        $this->command?->newLine();
    }

    /** Crea usuarios de login demo si la BD solo tiene datos de tests PHPUnit. */
    private function ensureUsuariosLogin(): ?User
    {
        if (User::query()->where('email', 'admin@repro.com')->exists()) {
            return User::query()->where('email', 'admin@repro.com')->first();
        }

        $this->command?->warn('No existe admin@repro.com — creando usuarios de login demo…');
        $this->call(RolesAndPermissionsSeeder::class);

        User::updateOrCreate(
            ['email' => 'szystems@hotmail.com'],
            [
                'name' => 'Otto Szarata',
                'password' => Hash::make('SPP7007aaa@@@'),
                'role_as' => 3,
                'principal' => 1,
                'estado' => 1,
                'fecha_nacimiento' => '1985-05-15',
                'telefono' => '12345678',
                'celular' => '87654321',
                'direccion' => 'Ciudad de Guatemala',
                'cargo' => 'Administrador del Sistema',
            ]
        );

        $admin = User::updateOrCreate(
            ['email' => 'admin@repro.com'],
            [
                'name' => 'Admin Repro',
                'password' => Hash::make('admin1234'),
                'role_as' => 3,
                'principal' => 0,
                'estado' => 1,
                'fecha_nacimiento' => '1990-01-01',
                'telefono' => '22334455',
                'celular' => '55443322',
                'direccion' => 'Guatemala',
                'cargo' => 'Administrador de Oficina',
            ]
        );

        $this->call(RoleSeeder::class);

        return $admin;
    }
}
