<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Empresa;
use App\Models\Orden;
use App\Models\Role;

class OrdenesControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Crear roles necesarios
        $adminRole = Role::create(['name' => 'admin', 'display_name' => 'Administrador']);
        $empresaRole = Role::create(['name' => 'empresa', 'display_name' => 'Empresa']);
        $reproRole = Role::create(['name' => 'repro', 'display_name' => 'Polígrafo']);
    }

    public function test_admin_puede_acceder_a_listado_de_ordenes()
    {
        // Crear usuario admin
        $admin = User::factory()->create();
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
        $userEmpresa = User::factory()->create(['empresa_id' => $empresa1->id]);
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
        $admin = User::factory()->create();
        $admin->roles()->attach(Role::where('name', 'admin')->first());
        
        $empresa = Empresa::factory()->create();

        // Datos de la orden
        $ordenData = [
            'empresa_id' => $empresa->id,
            'observaciones' => 'Test de creación de orden',
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
        $admin = User::factory()->create();
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
        $admin = User::factory()->create();
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
            'tipo_servicio' => 'vsa',
            'tipo_formulario' => 'periodica',
            'observaciones' => 'Orden actualizada',
            'evaluados' => [
                [
                    'nombre' => 'Juan Pérez Actualizado',
                    'apellidos' => 'García López',
                    'dpi' => '2345678901234',
                    'email' => 'juan.actualizado@example.com',
                    'telefono' => '55441122'
                ]
            ]
        ];

        $response = $this->actingAs($admin)
                         ->put(route('ordenes.update', $orden), $datosActualizados);

        $response->assertRedirect(route('ordenes.show', $orden));
        
        // Verificar actualización
        $orden->refresh();
        $this->assertEquals('vsa', $orden->tipo_servicio);
        $this->assertEquals('periodica', $orden->tipo_formulario);
        $this->assertEquals('Orden actualizada', $orden->observaciones);
    }
}
