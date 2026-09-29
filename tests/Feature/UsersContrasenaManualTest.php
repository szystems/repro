<?php

namespace Tests\Feature;

use App\Mail\UserMail;
use App\Mail\UserResetPasswordMail;
use App\Models\Empresa;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\Feature\Concerns\CreatesRolesAndPermissions;
use Tests\TestCase;

class UsersContrasenaManualTest extends TestCase
{
    use RefreshDatabase, CreatesRolesAndPermissions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpRolesAndPermissions();
    }

    private function admin(): User
    {
        $admin = User::factory()->create(['role_as' => 3, 'estado' => 1]);
        $admin->roles()->attach(Role::where('name', 'admin')->first());

        return $admin;
    }

    public function test_alta_con_contrasena_escrita_queda_usable_y_se_muestra_una_vez(): void
    {
        Mail::fake();
        $admin = $this->admin();
        $empresa = Empresa::factory()->create(['estado' => 1]);
        $rol = Role::where('name', 'empresa')->firstOrFail();

        $response = $this->actingAs($admin)->post(route('users.store'), [
            'name' => 'Reclutador Sin Correo',
            'email' => 'reclutador-sin-correo@example.com',
            'fecha_nacimiento' => '1992-04-04',
            'role_id' => $rol->id,
            'empresa_id' => $empresa->id,
            'cargo' => 'Reclutador',
            'asignar_password' => '1',
            'password' => 'ClaveManual88',
            'password_confirmation' => 'ClaveManual88',
        ]);

        $response->assertRedirect('users');
        $response->assertSessionHas('clave_asignada', 'ClaveManual88');

        $usuario = User::where('email', 'reclutador-sin-correo@example.com')->firstOrFail();
        $this->assertTrue(Hash::check('ClaveManual88', $usuario->password));

        Mail::assertQueued(UserMail::class, function (UserMail $mail): bool {
            return $mail->password === 'ClaveManual88';
        });

        $this->get(route('users.index'))
            ->assertOk()
            ->assertSee('id="clave-asignada"', false)
            ->assertSee('ClaveManual88', false);
    }

    public function test_alta_sin_marcar_genera_clave_y_no_usa_un_autocompletado(): void
    {
        Mail::fake();
        $admin = $this->admin();
        $empresa = Empresa::factory()->create(['estado' => 1]);
        $rol = Role::where('name', 'empresa')->firstOrFail();

        $response = $this->actingAs($admin)->post(route('users.store'), [
            'name' => 'Reclutador Generado',
            'email' => 'reclutador-generado@example.com',
            'fecha_nacimiento' => '1991-02-02',
            'role_id' => $rol->id,
            'empresa_id' => $empresa->id,
            'password' => 'Autocompletada88',
            'password_confirmation' => 'Autocompletada88',
        ]);

        $response->assertRedirect('users');
        $clave = session('clave_asignada');
        $this->assertIsString($clave);
        $this->assertMatchesRegularExpression('/^Repro\d{4}$/', $clave);

        $usuario = User::where('email', 'reclutador-generado@example.com')->firstOrFail();
        $this->assertTrue(Hash::check($clave, $usuario->password));
        $this->assertFalse(Hash::check('Autocompletada88', $usuario->password));
    }

    public function test_editar_asigna_la_contrasena_escrita_y_la_muestra(): void
    {
        Mail::fake();
        $admin = $this->admin();
        $empresa = Empresa::factory()->create(['estado' => 1]);
        $reclutador = User::factory()->create([
            'role_as' => 1,
            'empresa_id' => $empresa->id,
            'estado' => 1,
            'fecha_nacimiento' => '1990-01-01',
        ]);
        $reclutador->roles()->attach(Role::where('name', 'empresa')->first());

        $response = $this->actingAs($admin)->put(route('users.update', $reclutador->id), [
            'name' => $reclutador->name,
            'email' => $reclutador->email,
            'fecha_nacimiento' => '1990-01-01',
            'role_id' => Role::where('name', 'empresa')->first()->id,
            'empresa_id' => $empresa->id,
            'asignar_password' => '1',
            'password' => 'EntregaWhats88',
            'password_confirmation' => 'EntregaWhats88',
        ]);

        $response->assertRedirect('show-user/'.$reclutador->id);
        $response->assertSessionHas('clave_asignada', 'EntregaWhats88');
        $this->assertTrue(Hash::check('EntregaWhats88', $reclutador->fresh()->password));
        Mail::assertQueued(UserResetPasswordMail::class);
    }

    public function test_editar_sin_marcar_no_cambia_la_contrasena(): void
    {
        Mail::fake();
        $admin = $this->admin();
        $empresa = Empresa::factory()->create(['estado' => 1]);
        $reclutador = User::factory()->create([
            'role_as' => 1,
            'empresa_id' => $empresa->id,
            'estado' => 1,
            'fecha_nacimiento' => '1990-01-01',
        ]);
        $hash = $reclutador->password;

        $this->actingAs($admin)->put(route('users.update', $reclutador->id), [
            'name' => $reclutador->name,
            'email' => $reclutador->email,
            'fecha_nacimiento' => '1990-01-01',
            'role_id' => Role::where('name', 'empresa')->first()->id,
            'empresa_id' => $empresa->id,
            'password' => 'NoDebeAplicarse88',
            'password_confirmation' => 'NoDebeAplicarse88',
        ])->assertRedirect();

        $this->assertSame($hash, $reclutador->fresh()->password);
        Mail::assertNothingQueued();
    }

    public function test_un_admin_no_se_cambia_la_clave_al_editar_su_propio_perfil(): void
    {
        $admin = $this->admin();
        $hash = $admin->password;

        $this->actingAs($admin)->put(route('users.update', $admin->id), [
            'name' => $admin->name,
            'email' => $admin->email,
            'fecha_nacimiento' => '1985-05-05',
            'role_id' => Role::where('name', 'admin')->first()->id,
            'asignar_password' => '1',
            'password' => 'NoSobreMi88',
            'password_confirmation' => 'NoSobreMi88',
        ])->assertRedirect();

        $this->assertSame($hash, $admin->fresh()->password);
        $this->assertFalse(session()->has('clave_asignada'));
    }
}
