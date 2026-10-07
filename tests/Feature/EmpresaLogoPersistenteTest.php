<?php

namespace Tests\Feature;

use App\Models\Empresa;
use App\Models\Role;
use App\Models\User;
use App\Support\PerfilImagenSupport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\Feature\Concerns\CreatesRolesAndPermissions;
use Tests\Support\FakeImage;
use Tests\TestCase;

class EmpresaLogoPersistenteTest extends TestCase
{
    use RefreshDatabase, CreatesRolesAndPermissions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpRolesAndPermissions();
        $this->directorioPublicoReal();
    }

    public function test_repro_guarda_el_logo_y_sigue_visible_si_se_pierde_la_carpeta_publica(): void
    {
        $admin = $this->admin();
        $empresa = Empresa::factory()->create(['logo' => null]);

        $this->actingAs($admin)
            ->put(route('empresas.update', $empresa->id), $this->datos($empresa, [
                'logo' => FakeImage::jpeg('logo-nuevo.jpg'),
            ]))
            ->assertRedirect('show-empresa/'.$empresa->id)
            ->assertSessionHas('status');

        $empresa->refresh();
        $this->assertNotEmpty($empresa->logo);

        $publico = public_path('assets/imgs/empresas/'.$empresa->logo);
        $persistente = PerfilImagenSupport::rutaPersistente('empresas', $empresa->logo);
        $this->assertFileExists($publico);
        $this->assertFileExists($persistente);
        $this->assertNotSame(realpath($publico), realpath($persistente));

        File::delete($publico);
        $this->assertFileDoesNotExist($publico);
        $this->assertFileExists($persistente);

        PerfilImagenSupport::reponerDesdeVolumen('empresas', $empresa->logo);

        $this->assertFileExists(public_path('assets/imgs/empresas/'.$empresa->logo));
        $this->actingAs($admin)
            ->get(route('empresas.show', $empresa->id))
            ->assertOk()
            ->assertSee('assets/imgs/empresas/'.$empresa->logo, false);
    }

    public function test_un_archivo_invalido_no_borra_el_logo_anterior(): void
    {
        $admin = $this->admin();
        $empresa = Empresa::factory()->create(['logo' => 'logo-viejo.png']);
        File::ensureDirectoryExists(public_path('assets/imgs/empresas'));
        File::put(public_path('assets/imgs/empresas/logo-viejo.png'), 'png');

        $this->actingAs($admin)
            ->put(route('empresas.update', $empresa->id), $this->datos($empresa, [
                'logo' => \Illuminate\Http\UploadedFile::fake()->create('logo.svg', 10, 'image/svg+xml'),
            ]))
            ->assertSessionHasErrors('logo');

        $empresa->refresh();
        $this->assertSame('logo-viejo.png', $empresa->logo);
        $this->assertFileExists(public_path('assets/imgs/empresas/logo-viejo.png'));
    }

    public function test_reemplazar_el_logo_borra_el_archivo_anterior_despues_de_guardar(): void
    {
        $admin = $this->admin();
        $empresa = Empresa::factory()->create(['logo' => 'logo-viejo.png']);
        foreach ([
            public_path('assets/imgs/empresas/logo-viejo.png'),
            PerfilImagenSupport::rutaPersistente('empresas', 'logo-viejo.png'),
        ] as $ruta) {
            File::ensureDirectoryExists(dirname($ruta));
            File::put($ruta, 'png');
        }

        $this->actingAs($admin)
            ->put(route('empresas.update', $empresa->id), $this->datos($empresa, [
                'logo' => FakeImage::jpeg('logo-nuevo.jpg'),
            ]))
            ->assertRedirect('show-empresa/'.$empresa->id);

        $empresa->refresh();
        $this->assertNotSame('logo-viejo.png', $empresa->logo);
        $this->assertFileDoesNotExist(public_path('assets/imgs/empresas/logo-viejo.png'));
        $this->assertFileDoesNotExist(PerfilImagenSupport::rutaPersistente('empresas', 'logo-viejo.png'));
        $this->assertFileExists(PerfilImagenSupport::rutaPersistente('empresas', $empresa->logo));
    }

    private function directorioPublicoReal(): void
    {
        $publico = public_path('assets/imgs');
        if (is_link($publico)) {
            unlink($publico);
        }
        if (! is_dir($publico)) {
            mkdir($publico, 0775, true);
        }
    }

    private function admin(): User
    {
        $admin = User::factory()->create(['role_as' => 3, 'estado' => 1]);
        $admin->roles()->attach(Role::where('name', 'admin')->first());

        return $admin;
    }

    /** @param  array<string, mixed>  $extra */
    private function datos(Empresa $empresa, array $extra = []): array
    {
        return array_merge([
            'nombre' => $empresa->nombre,
            'nit' => '1234567-8',
            'telefono' => '22223333',
            'email' => 'empresa@cliente.test',
            'sitio_web' => 'https://example.com',
            'estado' => 1,
        ], $extra);
    }
}
