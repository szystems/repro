<?php

namespace Tests\Unit;

use App\Support\EmpresaPermisosSupport;
use App\Support\HistorialLaboralPeriodico;
use App\Support\InformeWordBloquesEvaluador;
use App\Support\SocioeconomicoComplementariaCampos;
use Tests\TestCase;

class RevisionAgosto2026SupportTest extends TestCase
{
    public function test_orden_bloques_word_coincide_con_cliente(): void
    {
        $titulos = array_column(InformeWordBloquesEvaluador::BLOQUES, 'titulo');

        $this->assertSame([
            'Aspecto laboral',
            'Información económica',
            'Salud',
            'Hábitos personales',
            'Actividades delictivas / sustancias',
            'Aspectos judiciales',
        ], $titulos);
    }

    public function test_permiso_crear_ordenes_habilita_ruta_store(): void
    {
        $this->assertTrue(
            EmpresaPermisosSupport::empresaTienePermisoSistema(
                json_encode(['crear_ordenes']),
                'ordenes.crear'
            )
        );
    }

    public function test_permiso_subir_documentos_habilita_documentos_subir(): void
    {
        $this->assertTrue(
            EmpresaPermisosSupport::empresaTienePermisoSistema(
                json_encode(['subir_documentos']),
                'documentos.subir'
            )
        );
    }

    public function test_permiso_descargar_documentos_habilita_documentos_ver(): void
    {
        $this->assertTrue(
            EmpresaPermisosSupport::empresaTienePermisoSistema(
                json_encode(['descargar_documentos']),
                'documentos.ver'
            )
        );
    }

    public function test_permiso_editar_ordenes_habilita_editar_y_eliminar(): void
    {
        $json = json_encode(['editar_ordenes']);

        $this->assertTrue(EmpresaPermisosSupport::empresaTienePermisoSistema($json, 'ordenes.editar'));
        $this->assertTrue(EmpresaPermisosSupport::empresaTienePermisoSistema($json, 'ordenes.eliminar'));
    }

    public function test_perfil_default_trabajador_sin_crear_ordenes(): void
    {
        $defaults = EmpresaPermisosSupport::permisosDefaultTrabajador();
        $json = json_encode($defaults);

        $this->assertNotContains('crear_ordenes', $defaults);
        $this->assertFalse(EmpresaPermisosSupport::empresaTienePermisoSistema($json, 'ordenes.crear'));
        $this->assertTrue(EmpresaPermisosSupport::empresaTienePermisoSistema($json, 'documentos.subir'));
        $this->assertTrue(EmpresaPermisosSupport::empresaTienePermisoSistema($json, 'ordenes.ver'));
    }

    public function test_periodico_omite_pregunta_experiencia_empleo_actual(): void
    {
        $claves = array_column(HistorialLaboralPeriodico::preguntasVisibles(), 'key');

        $this->assertNotContains('periodico_02', $claves);
        $this->assertContains('periodico_01', $claves);
        $this->assertContains('periodico_03', $claves);
    }

    public function test_socio_referencias_minimo_dos_y_vecinales_opcionales(): void
    {
        $reglas = SocioeconomicoComplementariaCampos::reglasValidacion();

        $this->assertSame('required|array|min:2', $reglas['referencias_familiares']);
        $this->assertSame('required|array|min:2', $reglas['referencias_personales']);
        $this->assertSame('nullable|array', $reglas['referencias_vecinales']);
    }
}
