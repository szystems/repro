<?php

namespace Tests\Feature;

use App\Models\Empresa;
use App\Models\EmpresaPreguntasPreempleo;
use App\Models\EvaluadoOrden;
use App\Models\EvaluadorNota;
use App\Models\Orden;
use App\Models\Role;
use App\Models\User;
use App\Support\InformeWordPreguntasPoligraficas;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\Feature\Concerns\CreatesRolesAndPermissions;
use Tests\TestCase;

class EmpresaPreguntasPreempleoTest extends TestCase
{
    use RefreshDatabase, CreatesRolesAndPermissions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpRolesAndPermissions();
        Mail::fake();
        Notification::fake();
    }

    public function test_la_ficha_guarda_cinco_preguntas_y_un_segundo_juego(): void
    {
        $admin = $this->admin();
        $empresa = Empresa::factory()->create();

        $this->actingAs($admin)
            ->get(route('empresas.edit', $empresa->id))
            ->assertOk()
            ->assertSee('Preguntas de preempleo');

        $this->actingAs($admin)
            ->put(route('empresas.update', $empresa->id), $this->datosEmpresa($empresa, [
                'preguntas_preempleo' => [
                    '¿Pregunta propia uno?',
                    '¿Pregunta propia dos?',
                    '',
                    '',
                    '',
                ],
                'preguntas_puesto_nombre' => 'Cajero',
                'preguntas_preempleo_puesto' => ['¿Pregunta de cajero?', '', '', '', ''],
            ]))
            ->assertRedirect('show-empresa/'.$empresa->id);

        $registro = EmpresaPreguntasPreempleo::where('empresa_id', $empresa->id)->first();
        $this->assertNotNull($registro);
        $this->assertSame(['¿Pregunta propia uno?', '¿Pregunta propia dos?'], $registro->preguntasPrincipales());
        $this->assertSame('Cajero', $registro->nombrePuestoVisible());
        $this->assertSame(['¿Pregunta de cajero?'], $registro->preguntasPuesto());

        $this->actingAs($admin)
            ->get(route('empresas.edit', $empresa->id))
            ->assertSee('¿Pregunta propia uno?')
            ->assertSee('Cajero');
    }

    public function test_orden_nueva_de_preempleo_copia_las_preguntas_y_no_las_reescribe(): void
    {
        $admin = $this->admin();
        $empresa = Empresa::factory()->create();
        EmpresaPreguntasPreempleo::guardarDesdeRequest(
            $empresa,
            ['¿Pregunta propia uno?', '¿Pregunta propia dos?', '', '', ''],
            null,
            []
        );

        $this->actingAs($admin)->post(route('ordenes.store'), [
            'empresa_id' => $empresa->id,
            'prioridad' => 'normal',
            'evaluados' => [[
                'nombre' => 'Ana',
                'apellidos' => 'Prueba',
                'dpi' => '1234567890123',
                'email' => 'ana.preguntas@test.com',
                'tipo_servicio' => 'poligrafo',
                'tipo_formulario' => 'preempleo',
            ]],
        ])->assertRedirect();

        $evaluado = EvaluadoOrden::where('dpi', '1234567890123')->first();
        $this->assertNotNull($evaluado);
        $filas = InformeWordPreguntasPoligraficas::filas($evaluado->id, $evaluado);
        $this->assertSame('¿Pregunta propia uno?', $filas[0]['pregunta']);
        $this->assertSame('¿Pregunta propia dos?', $filas[1]['pregunta']);
        $this->assertCount(2, $filas);

        EmpresaPreguntasPreempleo::guardarDesdeRequest(
            $empresa,
            ['¿Texto cambiado despues?', '', '', '', ''],
            null,
            []
        );

        $evaluado->refresh();
        $filasDespues = InformeWordPreguntasPoligraficas::filas($evaluado->id, $evaluado);
        $this->assertSame('¿Pregunta propia uno?', $filasDespues[0]['pregunta']);
    }

    public function test_sin_lista_de_empresa_siguen_las_cinco_generales(): void
    {
        $admin = $this->admin();
        $empresa = Empresa::factory()->create();

        $this->actingAs($admin)->post(route('ordenes.store'), [
            'empresa_id' => $empresa->id,
            'prioridad' => 'normal',
            'evaluados' => [[
                'nombre' => 'Luis',
                'apellidos' => 'General',
                'dpi' => '2234567890123',
                'email' => 'luis.general@test.com',
                'tipo_servicio' => 'poligrafo',
                'tipo_formulario' => 'preempleo',
            ]],
        ])->assertRedirect();

        $evaluado = EvaluadoOrden::where('dpi', '2234567890123')->first();
        $filas = InformeWordPreguntasPoligraficas::filas($evaluado->id, $evaluado);
        $this->assertSame(
            InformeWordPreguntasPoligraficas::FILAS_PLANTILLA[0]['pregunta'],
            $filas[0]['pregunta']
        );
        $this->assertDatabaseMissing('evaluador_notas', [
            'evaluado_orden_id' => $evaluado->id,
            'seccion' => InformeWordPreguntasPoligraficas::SECCION_NOTA,
        ]);
    }

    public function test_un_evaluado_viejo_no_toma_la_lista_nueva_de_la_empresa(): void
    {
        $empresa = Empresa::factory()->create();
        $orden = Orden::factory()->create(['empresa_id' => $empresa->id]);
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'tipo_servicio' => 'poligrafo',
            'tipo_formulario' => 'preempleo',
        ]);

        EmpresaPreguntasPreempleo::guardarDesdeRequest(
            $empresa,
            ['¿No debe aparecer en el viejo?', '', '', '', ''],
            null,
            []
        );

        $filas = InformeWordPreguntasPoligraficas::filas($evaluado->id, $evaluado);
        $this->assertSame(
            InformeWordPreguntasPoligraficas::FILAS_PLANTILLA[0]['pregunta'],
            $filas[0]['pregunta']
        );
    }

    public function test_periodica_especifica_y_socio_no_copian_las_preguntas(): void
    {
        $admin = $this->admin();
        $empresa = Empresa::factory()->create();
        EmpresaPreguntasPreempleo::guardarDesdeRequest(
            $empresa,
            ['¿Solo preempleo?', '', '', '', ''],
            null,
            []
        );

        foreach ([
            ['dpi' => '3234567890123', 'tipo_servicio' => 'poligrafo', 'tipo_formulario' => 'periodica'],
            ['dpi' => '4234567890123', 'tipo_servicio' => 'vsa', 'tipo_formulario' => 'especifica'],
            ['dpi' => '5234567890123', 'tipo_servicio' => 'socioeconomico', 'tipo_formulario' => 'preempleo'],
        ] as $caso) {
            $this->actingAs($admin)->post(route('ordenes.store'), [
                'empresa_id' => $empresa->id,
                'prioridad' => 'normal',
                'evaluados' => [[
                    'nombre' => 'Caso',
                    'apellidos' => 'Blanco',
                    'dpi' => $caso['dpi'],
                    'email' => $caso['dpi'].'@test.com',
                    'tipo_servicio' => $caso['tipo_servicio'],
                    'tipo_formulario' => $caso['tipo_formulario'],
                ]],
            ])->assertRedirect();

            $evaluado = EvaluadoOrden::where('dpi', $caso['dpi'])->first();
            $this->assertNotNull($evaluado, $caso['dpi']);
            $this->assertDatabaseMissing('evaluador_notas', [
                'evaluado_orden_id' => $evaluado->id,
                'seccion' => InformeWordPreguntasPoligraficas::SECCION_NOTA,
            ]);
        }
    }

    public function test_vsa_preempleo_y_el_juego_del_puesto_tambien_se_copian(): void
    {
        $admin = $this->admin();
        $empresa = Empresa::factory()->create();
        EmpresaPreguntasPreempleo::guardarDesdeRequest(
            $empresa,
            ['¿De la empresa?', '', '', '', ''],
            'Cajero',
            ['¿De cajero?', '', '', '', '']
        );

        $this->actingAs($admin)->post(route('ordenes.store'), [
            'empresa_id' => $empresa->id,
            'prioridad' => 'normal',
            'evaluados' => [
                [
                    'nombre' => 'Vsa',
                    'apellidos' => 'Empresa',
                    'dpi' => '6234567890123',
                    'email' => 'vsa.empresa@test.com',
                    'tipo_servicio' => 'vsa',
                    'tipo_formulario' => 'preempleo',
                    'preguntas_juego' => 'principal',
                ],
                [
                    'nombre' => 'Poli',
                    'apellidos' => 'Puesto',
                    'dpi' => '7234567890123',
                    'email' => 'poli.puesto@test.com',
                    'tipo_servicio' => 'poligrafo',
                    'tipo_formulario' => 'preempleo',
                    'preguntas_juego' => 'puesto',
                ],
            ],
        ])->assertRedirect();

        $vsa = EvaluadoOrden::where('dpi', '6234567890123')->first();
        $puesto = EvaluadoOrden::where('dpi', '7234567890123')->first();
        $this->assertSame('¿De la empresa?', InformeWordPreguntasPoligraficas::filas($vsa->id, $vsa)[0]['pregunta']);
        $this->assertSame('¿De cajero?', InformeWordPreguntasPoligraficas::filas($puesto->id, $puesto)[0]['pregunta']);
        $this->assertSame(2, EvaluadorNota::where('seccion', InformeWordPreguntasPoligraficas::SECCION_NOTA)->count());
    }

    public function test_requerimientos_especiales_solo_los_ve_repro(): void
    {
        $admin = $this->admin();
        $empresa = Empresa::factory()->create();
        $secreto = 'Pedir constancia de los ultimos dos empleos';

        $this->actingAs($admin)
            ->put(route('empresas.update', $empresa->id), $this->datosEmpresa($empresa, [
                'requerimientos_repro' => $secreto,
                'preguntas_preempleo' => ['', '', '', '', ''],
            ]))
            ->assertRedirect('show-empresa/'.$empresa->id);

        $empresa->refresh();
        $this->assertSame($secreto, $empresa->requerimientos_repro);

        $this->actingAs($admin)
            ->get(route('empresas.show', $empresa->id))
            ->assertOk()
            ->assertSee($secreto);

        $orden = Orden::factory()->create([
            'empresa_id' => $empresa->id,
            'creado_por' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->get(route('ordenes.show', $orden))
            ->assertOk()
            ->assertSee($secreto);

        $this->actingAs($admin)
            ->get(route('ordenes.create'))
            ->assertOk()
            ->assertSee($secreto, false);

        $cliente = User::factory()->create([
            'role_as' => 1,
            'estado' => 1,
            'principal' => 1,
            'empresa_id' => $empresa->id,
        ]);
        $cliente->roles()->attach(Role::where('name', 'empresa')->first());

        $this->actingAs($cliente)
            ->get(route('empresa.ordenes.show', $orden))
            ->assertOk()
            ->assertDontSee($secreto);

        $this->actingAs($cliente)
            ->get(route('empresa.mi-empresa'))
            ->assertOk()
            ->assertDontSee($secreto);
    }

    public function test_periodica_copia_las_preguntas_de_la_empresa_y_especifica_queda_en_blanco(): void
    {
        $admin = $this->admin();
        $empresa = Empresa::factory()->create();

        $this->actingAs($admin)
            ->put(route('empresas.update', $empresa->id), $this->datosEmpresa($empresa, [
                'preguntas_preempleo' => ['¿Solo preempleo?', '', '', '', ''],
                'preguntas_periodica' => ['¿Pregunta fija de periódica?', '', '', '', ''],
            ]))
            ->assertRedirect('show-empresa/'.$empresa->id);

        $registro = EmpresaPreguntasPreempleo::where('empresa_id', $empresa->id)->first();
        $this->assertSame(['¿Pregunta fija de periódica?'], $registro->preguntasPeriodica());

        $this->actingAs($admin)->post(route('ordenes.store'), [
            'empresa_id' => $empresa->id,
            'prioridad' => 'normal',
            'evaluados' => [
                [
                    'nombre' => 'Peri',
                    'apellidos' => 'Poli',
                    'dpi' => '8234567890123',
                    'email' => 'peri.poli@test.com',
                    'tipo_servicio' => 'poligrafo',
                    'tipo_formulario' => 'periodica',
                ],
                [
                    'nombre' => 'Peri',
                    'apellidos' => 'Vsa',
                    'dpi' => '9234567890123',
                    'email' => 'peri.vsa@test.com',
                    'tipo_servicio' => 'vsa',
                    'tipo_formulario' => 'periodica',
                ],
                [
                    'nombre' => 'Espe',
                    'apellidos' => 'Blanco',
                    'dpi' => '1334567890123',
                    'email' => 'espe.blanco@test.com',
                    'tipo_servicio' => 'poligrafo',
                    'tipo_formulario' => 'especifica',
                ],
            ],
        ])->assertRedirect();

        $poli = EvaluadoOrden::where('dpi', '8234567890123')->first();
        $vsa = EvaluadoOrden::where('dpi', '9234567890123')->first();
        $espe = EvaluadoOrden::where('dpi', '1334567890123')->first();

        $this->assertSame('¿Pregunta fija de periódica?', InformeWordPreguntasPoligraficas::filas($poli->id, $poli)[0]['pregunta']);
        $this->assertSame('¿Pregunta fija de periódica?', InformeWordPreguntasPoligraficas::filas($vsa->id, $vsa)[0]['pregunta']);
        $this->assertNotSame('¿Solo preempleo?', InformeWordPreguntasPoligraficas::filas($poli->id, $poli)[0]['pregunta']);
        $this->assertDatabaseMissing('evaluador_notas', [
            'evaluado_orden_id' => $espe->id,
            'seccion' => InformeWordPreguntasPoligraficas::SECCION_NOTA,
        ]);
        $this->assertSame('', InformeWordPreguntasPoligraficas::filas($espe->id, $espe)[0]['pregunta']);

        EmpresaPreguntasPreempleo::guardarDesdeRequest(
            $empresa,
            ['¿Solo preempleo?', '', '', '', ''],
            null,
            [],
            null,
            ['¿Texto cambiado despues?', '', '', '', '']
        );

        $poli->refresh();
        $this->assertSame('¿Pregunta fija de periódica?', InformeWordPreguntasPoligraficas::filas($poli->id, $poli)[0]['pregunta']);
    }

    public function test_periodica_sin_preguntas_de_la_empresa_queda_en_blanco(): void
    {
        $admin = $this->admin();
        $empresa = Empresa::factory()->create();
        EmpresaPreguntasPreempleo::guardarDesdeRequest(
            $empresa,
            ['¿Solo preempleo?', '', '', '', ''],
            null,
            [],
            null,
            ['', '', '', '', '']
        );

        $this->actingAs($admin)->post(route('ordenes.store'), [
            'empresa_id' => $empresa->id,
            'prioridad' => 'normal',
            'evaluados' => [[
                'nombre' => 'Peri',
                'apellidos' => 'Vacio',
                'dpi' => '1434567890123',
                'email' => 'peri.vacio@test.com',
                'tipo_servicio' => 'poligrafo',
                'tipo_formulario' => 'periodica',
            ]],
        ])->assertRedirect();

        $evaluado = EvaluadoOrden::where('dpi', '1434567890123')->first();
        $this->assertDatabaseMissing('evaluador_notas', [
            'evaluado_orden_id' => $evaluado->id,
            'seccion' => InformeWordPreguntasPoligraficas::SECCION_NOTA,
        ]);
        $this->assertSame('', InformeWordPreguntasPoligraficas::filas($evaluado->id, $evaluado)[0]['pregunta']);
    }

    public function test_el_primer_juego_se_puede_nombrar_y_si_no_dice_preguntas_generales(): void
    {
        $admin = $this->admin();
        $empresa = Empresa::factory()->create(['nombre' => 'Ventas Unidas Hombres']);

        $this->actingAs($admin)
            ->put(route('empresas.update', $empresa->id), $this->datosEmpresa($empresa, [
                'preguntas_principal_nombre' => 'Administrativos',
                'preguntas_preempleo' => ['¿Pregunta general de la empresa?', '', '', '', ''],
                'preguntas_puesto_nombre' => 'Cajero',
                'preguntas_preempleo_puesto' => ['¿Pregunta de cajero?', '', '', '', ''],
            ]))
            ->assertRedirect('show-empresa/'.$empresa->id);

        $registro = EmpresaPreguntasPreempleo::where('empresa_id', $empresa->id)->first();
        $this->assertSame('Administrativos', $registro->nombrePrincipalVisible());

        $this->actingAs($admin)
            ->get(route('ordenes.create'))
            ->assertOk()
            ->assertSee('Administrativos', false)
            ->assertSee('Cajero', false);

        $this->actingAs($admin)
            ->put(route('empresas.update', $empresa->id), $this->datosEmpresa($empresa, [
                'preguntas_principal_nombre' => '',
                'preguntas_preempleo' => ['¿Pregunta general de la empresa?', '', '', '', ''],
                'preguntas_puesto_nombre' => 'Cajero',
                'preguntas_preempleo_puesto' => ['¿Pregunta de cajero?', '', '', '', ''],
            ]))
            ->assertRedirect('show-empresa/'.$empresa->id);

        $this->assertSame(
            EmpresaPreguntasPreempleo::ETIQUETA_PRINCIPAL,
            $registro->fresh()->nombrePrincipalVisible()
        );

        $this->actingAs($admin)
            ->get(route('ordenes.create'))
            ->assertOk()
            ->assertSee('Preguntas generales', false);
    }

    public function test_el_nombre_del_primer_juego_exige_una_pregunta(): void
    {
        $admin = $this->admin();
        $empresa = Empresa::factory()->create();

        $this->actingAs($admin)
            ->from(route('empresas.edit', $empresa->id))
            ->put(route('empresas.update', $empresa->id), $this->datosEmpresa($empresa, [
                'preguntas_principal_nombre' => 'Administrativos',
                'preguntas_preempleo' => ['', '', '', '', ''],
                'preguntas_puesto_nombre' => '',
                'preguntas_preempleo_puesto' => ['', '', '', '', ''],
            ]))
            ->assertRedirect(route('empresas.edit', $empresa->id))
            ->assertSessionHasErrors('preguntas_principal_nombre');
    }

    public function test_el_segundo_juego_exige_nombre_y_una_pregunta(): void
    {
        $admin = $this->admin();
        $empresa = Empresa::factory()->create();

        $this->actingAs($admin)
            ->from(route('empresas.edit', $empresa->id))
            ->put(route('empresas.update', $empresa->id), $this->datosEmpresa($empresa, [
                'preguntas_preempleo' => ['', '', '', '', ''],
                'preguntas_puesto_nombre' => 'Cajero',
                'preguntas_preempleo_puesto' => ['', '', '', '', ''],
            ]))
            ->assertRedirect(route('empresas.edit', $empresa->id))
            ->assertSessionHasErrors('preguntas_puesto_nombre');

        $this->assertDatabaseCount('empresa_preguntas_preempleo', 0);
    }

    private function admin(): User
    {
        $admin = User::factory()->create(['role_as' => 3, 'estado' => 1]);
        $admin->roles()->attach(Role::where('name', 'admin')->first());

        return $admin;
    }

    /** @param  array<string, mixed>  $extra */
    private function datosEmpresa(Empresa $empresa, array $extra): array
    {
        return array_merge([
            'nombre' => $empresa->nombre,
            'nit' => $empresa->nit,
            'direccion' => $empresa->direccion,
            'telefono' => $empresa->telefono,
            'email' => $empresa->email,
            'sitio_web' => $empresa->sitio_web,
            'descripcion' => $empresa->descripcion,
            'contacto_nombre' => $empresa->contacto_nombre,
            'contacto_cargo' => $empresa->contacto_cargo,
            'contacto_telefono' => $empresa->contacto_telefono,
            'contacto_email' => $empresa->contacto_email,
            'notas' => $empresa->notas,
            'estado' => 1,
        ], $extra);
    }
}
