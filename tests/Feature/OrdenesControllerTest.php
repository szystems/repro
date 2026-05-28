<?php

namespace Tests\Feature;

use App\Models\Empresa;
use App\Models\EvaluadoOrden;
use App\Models\Orden;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Mail;
use Tests\Feature\Concerns\CreatesRolesAndPermissions;
use Tests\TestCase;

class OrdenesControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker, CreatesRolesAndPermissions;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->setUpRolesAndPermissions();
    }

    public function test_admin_puede_acceder_a_listado_de_ordenes()
    {
        // Crear usuario admin
        $admin = User::factory()->create(['role_as' => 3]);
        $admin->roles()->attach(Role::where('name', 'admin')->first());
        
        // Crear empresa
        $empresa = Empresa::factory()->create();
        
        // Crear orden
        $orden = Orden::factory()->create([
            'empresa_id' => $empresa->id,
            'creado_por' => $admin->id
        ]);

        // Autenticar como admin
        $response = $this->actingAs($admin)
                         ->get(route('ordenes.index'));

        $response->assertStatus(200);
        $response->assertSee($orden->codigo_orden);
    }

    public function test_usuario_empresa_solo_ve_sus_ordenes()
    {
        // Crear empresa
        $empresa1 = Empresa::factory()->create();
        $empresa2 = Empresa::factory()->create();
        
        // Crear usuario empresa
        $userEmpresa = User::factory()->create(['role_as' => 1, 'empresa_id' => $empresa1->id]);
        $userEmpresa->roles()->attach(Role::where('name', 'empresa')->first());
        
        // Crear órdenes
        $ordenPropia = Orden::factory()->create([
            'empresa_id' => $empresa1->id,
            'creado_por' => $userEmpresa->id
        ]);
        
        $ordenAjena = Orden::factory()->create([
            'empresa_id' => $empresa2->id,
            'creado_por' => $userEmpresa->id
        ]);

        // Autenticar como usuario empresa
        $response = $this->actingAs($userEmpresa)
                         ->get(route('ordenes.index'));

        $response->assertStatus(200);
        $response->assertSee($ordenPropia->codigo_orden);
        $response->assertDontSee($ordenAjena->codigo_orden);
    }

    public function test_puede_crear_nueva_orden()
    {
        // Crear admin y empresa
        $admin = User::factory()->create(['role_as' => 3]);
        $admin->roles()->attach(Role::where('name', 'admin')->first());
        
        $empresa = Empresa::factory()->create();

        // Datos de la orden
        $ordenData = [
            'empresa_id' => $empresa->id,
            'observaciones_internas' => 'Test de creación de orden',
            'prioridad' => 'normal',
            'evaluados' => [
                [
                    'nombre' => 'Juan Carlos',
                    'apellidos' => 'Pérez López', 
                    'dpi' => '1234567890123',
                    'telefono' => '23451234',
                    'email' => 'juan@test.com',
                    'tipo_servicio' => 'poligrafo',
                    'tipo_formulario' => 'preempleo'
                ]
            ]
        ];

        // Crear orden
        $response = $this->actingAs($admin)
                         ->post(route('ordenes.store'), $ordenData);

        // Verificar redirección
        $response->assertStatus(302);
        
        // Verificar que la orden fue creada
        $orden = Orden::where('empresa_id', $empresa->id)->first();
        $this->assertNotNull($orden);
        $this->assertEquals('normal', $orden->prioridad);
        
        // Verificar evaluado creado con datos granulares
        $this->assertCount(1, $orden->evaluados);
        $evaluado = $orden->evaluados->first();
        $this->assertEquals('Juan Carlos', $evaluado->nombre);
        $this->assertEquals('1234567890123', $evaluado->dpi);
        $this->assertEquals('poligrafo', $evaluado->tipo_servicio);
        $this->assertEquals('preempleo', $evaluado->tipo_formulario);
    }

    public function test_puede_ver_detalle_de_orden()
    {
        // Crear usuario admin
        $admin = User::factory()->create(['role_as' => 3]);
        $admin->roles()->attach(Role::where('name', 'admin')->first());
        
        // Crear empresa
        $empresa = Empresa::factory()->create();
        
        // Crear orden
        $orden = Orden::factory()->create([
            'empresa_id' => $empresa->id,
            'creado_por' => $admin->id
        ]);

        // Ver detalle
        $response = $this->actingAs($admin)
                         ->get(route('ordenes.show', $orden));

        $response->assertStatus(200);
        $response->assertSee($orden->codigo_orden);
        $response->assertSee($empresa->nombre);
    }

    public function test_puede_editar_orden()
    {
        // Crear usuario admin
        $admin = User::factory()->create(['role_as' => 3]);
        $admin->roles()->attach(Role::where('name', 'admin')->first());
        
        // Crear empresa
        $empresa = Empresa::factory()->create();
        
        // Crear orden
        $orden = Orden::factory()->create([
            'empresa_id' => $empresa->id,
            'creado_por' => $admin->id
        ]);

        // Acceder a edición
        $response = $this->actingAs($admin)
                         ->get(route('ordenes.edit', $orden));

        $response->assertStatus(200);
        $response->assertSee($orden->codigo_orden);

        // Actualizar orden
        $datosActualizados = [
            'empresa_id' => $empresa->id,
            'observaciones_internas' => 'Orden actualizada',
            'evaluados' => [
                [
                    'nombre' => 'Juan Pérez Actualizado',
                    'apellidos' => 'García López',
                    'dpi' => '2345678901234',
                    'email' => 'juan.actualizado@example.com',
                    'telefono' => '55441122',
                    'tipo_servicio' => 'vsa',
                    'tipo_formulario' => 'periodica'
                ]
            ]
        ];

        $response = $this->actingAs($admin)
                         ->put(route('ordenes.update', $orden), $datosActualizados);

        $response->assertRedirect(route('ordenes.show', $orden));
        
        // Verificar actualización
        $orden->refresh();
        $this->assertEquals('Orden actualizada', $orden->observaciones_internas);
        
        // Verificar evaluado actualizado
        $evaluado = $orden->evaluados()->first();
        $this->assertEquals('vsa', $evaluado->tipo_servicio);
        $this->assertEquals('periodica', $evaluado->tipo_formulario);
    }

    public function test_usuario_empresa_puede_editar_su_orden_sin_enviar_empresa_id()
    {
        $empresa = Empresa::factory()->create();
        $usuarioEmpresa = User::factory()->create([
            'role_as' => 1,
            'empresa_id' => $empresa->id,
        ]);
        $usuarioEmpresa->roles()->attach(Role::where('name', 'empresa')->first());

        $orden = Orden::factory()->create([
            'empresa_id' => $empresa->id,
            'creado_por' => $usuarioEmpresa->id,
            'estado' => 'solicitud',
        ]);

        $response = $this->actingAs($usuarioEmpresa)
            ->put(route('ordenes.update', $orden), [
                'observaciones_internas' => 'Edicion desde empresa sin empresa_id',
                'evaluados' => [
                    [
                        'nombre' => 'Cliente Empresa',
                        'apellidos' => 'Editado',
                        'dpi' => '3456789012345',
                        'email' => 'cliente.empresa@test.com',
                        'telefono' => '55112233',
                        'tipo_servicio' => 'poligrafo',
                        'tipo_formulario' => 'preempleo',
                    ],
                ],
            ]);

        $response->assertRedirect(route('empresa.ordenes.show', $orden));

        $orden->refresh();
        $this->assertEquals($empresa->id, $orden->empresa_id);
        $this->assertDatabaseHas('evaluados_orden', [
            'orden_id' => $orden->id,
            'dpi' => '3456789012345',
            'nombre' => 'Cliente Empresa',
        ]);
    }

    public function test_puede_reenviar_correo_a_evaluado()
    {
        Mail::fake();

        // Crear usuario admin
        $admin = User::factory()->create(['role_as' => 3]);
        $admin->roles()->attach(Role::where('name', 'admin')->first());
        
        // Crear empresa y orden
        $empresa = Empresa::factory()->create();
        $orden = Orden::factory()->create([
            'empresa_id' => $empresa->id,
            'creado_por' => $admin->id
        ]);
        
        // Crear evaluado con email
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'email' => 'evaluado@test.com',
            'cuestionario_completado' => false,
            'token_expira_at' => now()->addDays(30),
        ]);

        // Reenviar correo
        $response = $this->actingAs($admin)
                         ->post(route('evaluados.reenviar-correo', $evaluado));

        $response->assertRedirect();
        $response->assertSessionHas('success');
        
        // Verificar que se envió el email (usa assertQueued porque el Mailable implementa ShouldQueue)
        Mail::assertQueued(\App\Mail\EvaluadoAsignadoMail::class, function ($mail) {
            return $mail->hasTo('evaluado@test.com');
        });
    }

    public function test_no_puede_reenviar_correo_si_evaluado_no_tiene_email()
    {
        // Crear usuario admin
        $admin = User::factory()->create(['role_as' => 3]);
        $admin->roles()->attach(Role::where('name', 'admin')->first());
        
        // Crear empresa y orden
        $empresa = Empresa::factory()->create();
        $orden = Orden::factory()->create([
            'empresa_id' => $empresa->id,
            'creado_por' => $admin->id
        ]);
        
        // Crear evaluado con email vacío (no null para evitar constraint)
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'email' => '',
            'cuestionario_completado' => false,
        ]);

        // Intentar reenviar correo
        $response = $this->actingAs($admin)
                         ->post(route('evaluados.reenviar-correo', $evaluado));

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    // === R4: Campo Sede/Región del Evaluado ===

    public function test_r4_sede_region_empresa_se_guarda_al_crear_orden(): void
    {
        $admin = User::factory()->create(['role_as' => 3]);
        $admin->roles()->attach(Role::where('name', 'admin')->first());

        $empresa = Empresa::factory()->create();

        $response = $this->actingAs($admin)
            ->post(route('ordenes.store'), [
                'empresa_id' => $empresa->id,
                'evaluados' => [
                    [
                        'nombre' => 'María',
                        'apellidos' => 'López',
                        'dpi' => '9876543210123',
                        'email' => 'maria@test.com',
                        'tipo_servicio' => 'poligrafo',
                        'tipo_formulario' => 'preempleo',
                        'puesto_evaluar' => 'Analista',
                        'sede_region_empresa' => 'Regional Norte',
                    ]
                ]
            ]);

        $response->assertStatus(302);

        $evaluado = EvaluadoOrden::where('dpi', '9876543210123')->first();
        $this->assertNotNull($evaluado);
        $this->assertEquals('Regional Norte', $evaluado->sede_region_empresa);
    }

    public function test_r4_sede_region_empresa_es_nullable_al_crear(): void
    {
        $admin = User::factory()->create(['role_as' => 3]);
        $admin->roles()->attach(Role::where('name', 'admin')->first());

        $empresa = Empresa::factory()->create();

        $response = $this->actingAs($admin)
            ->post(route('ordenes.store'), [
                'empresa_id' => $empresa->id,
                'evaluados' => [
                    [
                        'nombre' => 'Carlos',
                        'apellidos' => 'Gómez',
                        'dpi' => '1111111111111',
                        'email' => 'carlos@test.com',
                        'tipo_servicio' => 'vsa',
                        'tipo_formulario' => 'periodica',
                    ]
                ]
            ]);

        $response->assertStatus(302);

        $evaluado = EvaluadoOrden::where('dpi', '1111111111111')->first();
        $this->assertNotNull($evaluado);
        $this->assertNull($evaluado->sede_region_empresa);
    }

    public function test_r4_sede_region_empresa_se_actualiza_al_editar_orden(): void
    {
        $admin = User::factory()->create(['role_as' => 3]);
        $admin->roles()->attach(Role::where('name', 'admin')->first());

        $empresa = Empresa::factory()->create();
        $orden = Orden::factory()->create([
            'empresa_id' => $empresa->id,
            'creado_por' => $admin->id,
        ]);
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'sede_region_empresa' => null,
        ]);

        $response = $this->actingAs($admin)
            ->put(route('ordenes.update', $orden), [
                'empresa_id' => $empresa->id,
                'evaluados' => [
                    [
                        'id' => $evaluado->id,
                        'nombre' => $evaluado->nombre,
                        'apellidos' => $evaluado->apellidos,
                        'dpi' => $evaluado->dpi,
                        'email' => $evaluado->email,
                        'tipo_servicio' => $evaluado->tipo_servicio,
                        'tipo_formulario' => $evaluado->tipo_formulario,
                        'sede_region_empresa' => 'Sucursal Centro',
                    ]
                ]
            ]);

        $response->assertStatus(302);

        $evaluado->refresh();
        $this->assertEquals('Sucursal Centro', $evaluado->sede_region_empresa);
    }

    public function test_actualizar_orden_agregando_nuevo_evaluado_preserva_existente_y_campos_criticos(): void
    {
        $admin = User::factory()->create(['role_as' => 3]);
        $admin->roles()->attach(Role::where('name', 'admin')->first());

        $empresa = Empresa::factory()->create();
        $orden = Orden::factory()->create([
            'empresa_id' => $empresa->id,
            'creado_por' => $admin->id,
        ]);

        $evaluadoExistente = EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'nombre' => 'Evaluado',
            'apellidos' => 'Original',
            'dpi' => '1111111111111',
            'email' => 'existente@test.com',
            'estado_evaluacion' => 'reprogramado',
            'fecha_programada' => now()->addDays(3),
            'token_unico' => 'token-existente',
            'token_expira_at' => now()->addDays(30),
        ]);

        $this->actingAs($admin)
            ->put(route('ordenes.update', $orden), [
                'empresa_id' => $empresa->id,
                'evaluados' => [
                    [
                        'id' => $evaluadoExistente->id,
                        'nombre' => $evaluadoExistente->nombre,
                        'apellidos' => $evaluadoExistente->apellidos,
                        'dpi' => $evaluadoExistente->dpi,
                        'email' => $evaluadoExistente->email,
                        'tipo_servicio' => $evaluadoExistente->tipo_servicio,
                        'tipo_formulario' => $evaluadoExistente->tipo_formulario,
                    ],
                    [
                        'nombre' => 'Evaluado',
                        'apellidos' => 'Nuevo',
                        'dpi' => '2222222222222',
                        'email' => 'nuevo@test.com',
                        'tipo_servicio' => 'poligrafo',
                        'tipo_formulario' => 'preempleo',
                    ],
                ],
            ])
            ->assertStatus(302);

        $orden->refresh();
        $this->assertCount(2, $orden->evaluados);

        $evaluadoExistente->refresh();
        $this->assertEquals('reprogramado', $evaluadoExistente->estado_evaluacion);
        $this->assertNotNull($evaluadoExistente->fecha_programada);
        $this->assertEquals('token-existente', $evaluadoExistente->token_unico);

        $this->assertDatabaseHas('evaluados_orden', [
            'orden_id' => $orden->id,
            'dpi' => '2222222222222',
        ]);
    }

    public function test_actualizar_orden_sin_id_en_existente_lo_reconoce_y_permite_agregar_otro(): void
    {
        $admin = User::factory()->create(['role_as' => 3]);
        $admin->roles()->attach(Role::where('name', 'admin')->first());

        $empresa = Empresa::factory()->create();
        $orden = Orden::factory()->create([
            'empresa_id' => $empresa->id,
            'creado_por' => $admin->id,
        ]);

        $existente = EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'nombre' => 'Evaluado',
            'apellidos' => 'Prueba',
            'dpi' => '1234567896541',
            'tipo_servicio' => 'poligrafo',
            'tipo_formulario' => 'preempleo',
            'email' => 'existente@test.com',
            'estado_evaluacion' => 'reprogramado',
            'fecha_programada' => now()->addDay(),
        ]);

        $response = $this->actingAs($admin)
            ->put(route('ordenes.update', $orden), [
                'empresa_id' => $empresa->id,
                'evaluados' => [
                    // Simula payload sin id (caso reportado en producción)
                    [
                        'nombre' => 'Evaluado',
                        'apellidos' => 'Prueba',
                        'dpi' => '1234567896541',
                        'email' => 'existente@test.com',
                        'tipo_servicio' => 'poligrafo',
                        'tipo_formulario' => 'preempleo',
                    ],
                    [
                        'nombre' => 'Maria',
                        'apellidos' => 'Rivera',
                        'dpi' => '9999999999999',
                        'email' => 'maria.rivera@test.com',
                        'tipo_servicio' => 'vsa',
                        'tipo_formulario' => 'periodica',
                    ],
                ],
            ]);

        $response->assertStatus(302);
        $response->assertSessionHas('success');

        $this->assertEquals(2, $orden->evaluados()->count());
        $this->assertEquals(1, $orden->evaluados()->where('dpi', '1234567896541')->count());

        $existente->refresh();
        $this->assertEquals('reprogramado', $existente->estado_evaluacion);
        $this->assertNotNull($existente->fecha_programada);
        $this->assertEquals('Evaluado', $existente->nombre);
        $this->assertEquals('Prueba', $existente->apellidos);

        $this->assertDatabaseHas('evaluados_orden', [
            'orden_id' => $orden->id,
            'dpi' => '9999999999999',
            'tipo_servicio' => 'vsa',
        ]);
    }

    public function test_actualizar_orden_con_mismo_dpi_servicio_sin_id_actualiza_existente_y_no_duplica(): void
    {
        $admin = User::factory()->create(['role_as' => 3]);
        $admin->roles()->attach(Role::where('name', 'admin')->first());

        $empresa = Empresa::factory()->create();
        $orden = Orden::factory()->create([
            'empresa_id' => $empresa->id,
            'creado_por' => $admin->id,
        ]);

        EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'nombre' => 'Evaluado',
            'apellidos' => 'Prueba',
            'dpi' => '1234567896541',
            'tipo_servicio' => 'poligrafo',
            'tipo_formulario' => 'preempleo',
            'email' => 'existente@test.com',
        ]);

        $response = $this->actingAs($admin)
            ->put(route('ordenes.update', $orden), [
                'empresa_id' => $empresa->id,
                'evaluados' => [
                    [
                        'nombre' => 'Persona',
                        'apellidos' => 'Distinta',
                        'dpi' => '1234567896541',
                        'email' => 'otro@test.com',
                        'tipo_servicio' => 'poligrafo',
                        'tipo_formulario' => 'preempleo',
                    ],
                ],
            ]);

        $response->assertStatus(302);
        $response->assertSessionHas('success');

        $this->assertEquals(1, $orden->evaluados()->count());

        $actualizado = $orden->evaluados()->first();
        $this->assertEquals('Persona', $actualizado->nombre);
        $this->assertEquals('Distinta', $actualizado->apellidos);
        $this->assertEquals('otro@test.com', $actualizado->email);
    }

    public function test_actualizar_orden_con_id_vacio_en_existente_actualiza_y_no_duplica(): void
    {
        $admin = User::factory()->create(['role_as' => 3]);
        $admin->roles()->attach(Role::where('name', 'admin')->first());

        $empresa = Empresa::factory()->create();
        $orden = Orden::factory()->create([
            'empresa_id' => $empresa->id,
            'creado_por' => $admin->id,
        ]);

        EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'nombre' => 'Evaluado',
            'apellidos' => 'Prueba',
            'dpi' => '1234567896541',
            'tipo_servicio' => 'poligrafo',
            'tipo_formulario' => 'preempleo',
            'email' => 'existente@test.com',
        ]);

        $response = $this->actingAs($admin)
            ->put(route('ordenes.update', $orden), [
                'empresa_id' => $empresa->id,
                'evaluados' => [
                    [
                        'id' => '',
                        'nombre' => 'Evaluado',
                        'apellidos' => 'Prueba',
                        'dpi' => '1234567896541',
                        'email' => 'existente@test.com',
                        'tipo_servicio' => 'poligrafo',
                        'tipo_formulario' => 'preempleo',
                    ],
                    [
                        'nombre' => 'Maria',
                        'apellidos' => 'Rivera',
                        'dpi' => '9999999999999',
                        'email' => 'maria.rivera@test.com',
                        'tipo_servicio' => 'vsa',
                        'tipo_formulario' => 'periodica',
                    ],
                ],
            ]);

        $response->assertStatus(302);
        $response->assertSessionHas('success');

        $this->assertEquals(2, $orden->evaluados()->count());
        $this->assertEquals(1, $orden->evaluados()->where('dpi', '1234567896541')->count());
        $this->assertEquals(1, $orden->evaluados()->where('dpi', '9999999999999')->count());
    }

    // =========================================================
    // R1 — Auto-cambio de estados por acciones
    // =========================================================

    public function test_r1_crear_evaluado_con_email_establece_link_enviado(): void
    {
        Mail::fake();

        $admin = User::factory()->create(['role_as' => 3]);
        $empresa = Empresa::factory()->create();
        $sede = \App\Models\Sede::factory()->create(['estado' => 1]);

        $response = $this->actingAs($admin)->post(route('ordenes.store'), [
            'empresa_id'   => $empresa->id,
            'sede_id'      => $sede->id,
            'tipo_servicio' => ['vsa'],
            'evaluados' => [[
                'nombre'         => 'Juan',
                'apellidos'      => 'Pérez',
                'dpi'            => '1234567890101',
                'email'          => 'juan@example.com',
                'tipo_servicio'  => 'vsa',
                'tipo_formulario'=> 'preempleo',
            ]],
        ]);

        $evaluado = EvaluadoOrden::where('email', 'juan@example.com')->first();
        $this->assertNotNull($evaluado);
        $this->assertEquals('link_enviado', $evaluado->estado_evaluacion);
    }

    public function test_r1_reenviar_correo_establece_link_enviado(): void
    {
        Mail::fake();

        $admin = User::factory()->create(['role_as' => 3]);
        $empresa = Empresa::factory()->create();
        $sede = \App\Models\Sede::factory()->create(['estado' => 1]);
        $orden = Orden::factory()->create(['empresa_id' => $empresa->id, 'sede_id' => $sede->id]);
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id'          => $orden->id,
            'email'             => 'test@example.com',
            'estado_evaluacion' => 'pendiente',
            'token_unico'       => 'test-token-abc',
            'token_expira_at'   => now()->addDays(30),
        ]);

        $this->actingAs($admin)->post(route('evaluados.reenviar-correo', $evaluado));

        $evaluado->refresh();
        $this->assertEquals('link_enviado', $evaluado->estado_evaluacion);
    }

    public function test_r1_subir_resultado_final_marca_evaluado_completado(): void
    {
        $admin = User::factory()->create(['role_as' => 3]);
        $empresa = Empresa::factory()->create();
        $sede = \App\Models\Sede::factory()->create(['estado' => 1]);
        $orden = Orden::factory()->create(['empresa_id' => $empresa->id, 'sede_id' => $sede->id, 'estado' => 'en_proceso']);
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id'          => $orden->id,
            'estado_evaluacion' => 'docs_pendientes',
        ]);

        $archivo = \Illuminate\Http\UploadedFile::fake()->create('informe.pdf', 100, 'application/pdf');

        $this->actingAs($admin)->post(route('evaluados.subir-resultado-archivo', $evaluado), [
            'tipo_resultado' => 'final',
            'archivo'        => $archivo,
        ]);

        $evaluado->refresh();
        $this->assertEquals('completado', $evaluado->estado_evaluacion);
    }
}
