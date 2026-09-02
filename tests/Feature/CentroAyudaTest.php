<?php

namespace Tests\Feature;

use App\Models\Empresa;
use App\Models\User;
use App\Support\AyudaSupport;
use App\Support\EmpresaPermisosSupport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesRolesAndPermissions;
use Tests\TestCase;

class CentroAyudaTest extends TestCase
{
    use RefreshDatabase, CreatesRolesAndPermissions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpRolesAndPermissions();
    }

    public function test_ayuda_requiere_autenticacion(): void
    {
        $this->get(route('ayuda.index'))->assertRedirect(route('login'));
    }

    public function test_admin_ve_centro_ayuda_con_articulos_repro(): void
    {
        $admin = User::factory()->create(['role_as' => 3, 'estado' => 1]);

        $response = $this->actingAs($admin)->get(route('ayuda.index'));

        $response->assertOk();
        $response->assertSee('Centro de Ayuda');
        $response->assertSee('Detalle de orden: gestionar evaluados');
        $response->assertSee('Informe Word');
    }

    public function test_empresa_principal_ve_articulos_cliente(): void
    {
        $empresa = Empresa::factory()->create();
        $user = User::factory()->create([
            'role_as' => 1,
            'estado' => 1,
            'empresa_id' => $empresa->id,
            'principal' => 1,
        ]);

        $response = $this->actingAs($user)->get(route('ayuda.index'));

        $response->assertOk();
        $response->assertSee('Crear una nueva orden');
        $response->assertSee('Gestionar usuarios de mi empresa');
        $response->assertDontSee('Configuración del sistema');
    }

    public function test_trabajador_no_ve_articulos_solo_principal(): void
    {
        $empresa = Empresa::factory()->create();
        $trabajador = User::factory()->create([
            'empresa_id' => $empresa->id,
            'role_as' => 1,
            'principal' => 0,
            'permisos' => json_encode(EmpresaPermisosSupport::permisosDefaultTrabajador()),
        ]);

        $this->assertNull(AyudaSupport::articuloPorSlug($trabajador, 'usuarios-empresa'));
        $this->assertNull(AyudaSupport::articuloPorSlug($trabajador, 'crear-orden'));

        $response = $this->actingAs($trabajador)->get(route('ayuda.show', 'seguimiento-ordenes'));
        $response->assertOk();
    }

    public function test_articulo_inexistente_retorna_404(): void
    {
        $admin = User::factory()->create(['role_as' => 3, 'estado' => 1]);

        $this->actingAs($admin)->get(route('ayuda.show', 'no-existe'))->assertNotFound();
    }

    public function test_buscar_encuentra_enlaces(): void
    {
        $admin = User::factory()->create(['role_as' => 3, 'estado' => 1]);

        $response = $this->actingAs($admin)->get(route('ayuda.buscar', ['q' => 'enlace']));

        $response->assertOk();
        $response->assertSee('Enlaces del candidato');
    }

    public function test_faq_y_glosario_accesibles(): void
    {
        $admin = User::factory()->create(['role_as' => 3, 'estado' => 1]);

        $this->actingAs($admin)->get(route('ayuda.faq'))->assertOk()->assertSee('Preguntas frecuentes');
        $this->actingAs($admin)->get(route('ayuda.glosario'))->assertOk()->assertSee('Orden de evaluación');
    }

    public function test_contexto_ordenes_detalle_prioriza_sobre_enlaces(): void
    {
        $admin = User::factory()->create(['role_as' => 3, 'estado' => 1]);

        $ctx = AyudaSupport::articuloContextual($admin, 'ordenes/161');

        $this->assertNotNull($ctx);
        $this->assertSame('ordenes-detalle-evaluado', $ctx['slug']);
    }

    public function test_contexto_create_ordenes_es_crear_orden(): void
    {
        $empresa = Empresa::factory()->create();
        $user = User::factory()->create([
            'role_as' => 1,
            'estado' => 1,
            'empresa_id' => $empresa->id,
            'principal' => 1,
        ]);

        $ctx = AyudaSupport::articuloContextual($user, 'ordenes/create');

        $this->assertNotNull($ctx);
        $this->assertSame('crear-orden', $ctx['slug']);
    }

    public function test_dashboard_muestra_tarjeta_ayuda(): void
    {
        $admin = User::factory()->create(['role_as' => 3, 'estado' => 1]);

        $this->actingAs($admin)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Centro de Ayuda');
    }

    public function test_dashboard_cliente_muestra_una_sola_tarjeta_ayuda(): void
    {
        $empresa = Empresa::factory()->create();
        $user = User::factory()->create([
            'role_as' => 1,
            'estado' => 1,
            'empresa_id' => $empresa->id,
            'principal' => 1,
        ]);

        $html = $this->actingAs($user)->get(route('dashboard'))->assertOk()->getContent();

        $this->assertSame(
            1,
            substr_count($html, 'bi-book me-1'),
            'El recuadro Centro de Ayuda no debe repetirse en el panel del cliente'
        );
    }

    public function test_candidato_ayuda_publica(): void
    {
        $this->get(route('cuestionario.ayuda'))
            ->assertOk()
            ->assertSee('Ayuda — Cuestionario REPRO')
            ->assertSee('Fecha de nacimiento');
    }

    public function test_index_muestra_agrupacion_por_modulo(): void
    {
        $admin = User::factory()->create(['role_as' => 3, 'estado' => 1]);

        $this->actingAs($admin)->get(route('ayuda.index'))
            ->assertOk()
            ->assertSee('Por módulo del menú')
            ->assertSee('Órdenes');
    }

    public function test_articulo_muestra_mock_y_toc(): void
    {
        $admin = User::factory()->create(['role_as' => 3, 'estado' => 1]);

        $this->actingAs($admin)->get(route('ayuda.show', 'enlaces-candidato'))
            ->assertOk()
            ->assertSee('Vista simulada')
            ->assertSee('En este artículo')
            ->assertSee('Referencia de botones')
            ->assertSee('btn-outline-primary');
    }

    public function test_faq_incluye_enlace_a_guia(): void
    {
        $admin = User::factory()->create(['role_as' => 3, 'estado' => 1]);

        $this->actingAs($admin)->get(route('ayuda.faq'))
            ->assertOk()
            ->assertSee('Ver guía completa');
    }

    public function test_glosario_incluye_enlace_articulo(): void
    {
        $admin = User::factory()->create(['role_as' => 3, 'estado' => 1]);

        $this->actingAs($admin)->get(route('ayuda.glosario'))
            ->assertOk()
            ->assertSee('Ver en ayuda');
    }

    public function test_flujo_muestra_diagrama(): void
    {
        $admin = User::factory()->create(['role_as' => 3, 'estado' => 1]);

        $this->actingAs($admin)->get(route('ayuda.show', 'flujo-orden-completa'))
            ->assertOk()
            ->assertSee('Diagrama del flujo')
            ->assertSee('ayuda-flujo-diagrama');
    }

    public function test_guia_usuarios_roles_explica_eliminar_y_permisos(): void
    {
        $admin = User::factory()->create(['role_as' => 3, 'estado' => 1]);

        $this->actingAs($admin)->get(route('ayuda.show', 'seguridad-usuarios'))
            ->assertOk()
            ->assertSee('Eliminar un usuario')
            ->assertSee('no borra el historial')
            ->assertSee('último administrador')
            ->assertSee('Permisos individuales')
            ->assertSee('Editar órdenes');
    }

    public function test_guia_archivar_ordenes_explica_listados(): void
    {
        $admin = User::factory()->create(['role_as' => 3, 'estado' => 1]);

        $this->actingAs($admin)->get(route('ayuda.show', 'archivar-ordenes'))
            ->assertOk()
            ->assertSee('Qué deja de verse')
            ->assertSee('Historial por DPI')
            ->assertSee('Órdenes archivadas');
    }

    public function test_faq_y_busqueda_cubren_usuarios_y_archivo(): void
    {
        $admin = User::factory()->create(['role_as' => 3, 'estado' => 1]);

        $this->actingAs($admin)->get(route('ayuda.faq'))
            ->assertOk()
            ->assertSee('¿Por qué no puedo eliminar algunos usuarios?')
            ->assertSee('Archivé una orden');

        $this->actingAs($admin)->get(route('ayuda.buscar', ['q' => 'archivar']))
            ->assertOk()
            ->assertSee('Archivar órdenes');

        $this->actingAs($admin)->get(route('ayuda.glosario'))
            ->assertOk()
            ->assertSee('Eliminar usuario')
            ->assertSee('Permisos individuales');
    }

    public function test_contexto_users_y_roles_abre_guia_seguridad(): void
    {
        $admin = User::factory()->create(['role_as' => 3, 'estado' => 1]);

        $this->assertSame('seguridad-usuarios', AyudaSupport::articuloContextual($admin, 'users')['slug'] ?? null);
        $this->assertSame('seguridad-usuarios', AyudaSupport::articuloContextual($admin, 'edit-user/216')['slug'] ?? null);
        $this->assertSame('seguridad-usuarios', AyudaSupport::articuloContextual($admin, 'admin/roles')['slug'] ?? null);
        $this->assertSame('seguridad-usuarios', AyudaSupport::articuloContextual($admin, 'admin/roles/1/edit')['slug'] ?? null);
        $this->assertSame('archivar-ordenes', AyudaSupport::articuloContextual($admin, 'ordenes')['slug'] ?? null);
    }
}
