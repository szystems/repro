<?php

namespace Tests\Unit;

use App\Models\Empresa;
use App\Models\EvaluadoOrden;
use App\Models\Orden;
use App\Support\InformeWordNombresArchivo;
use App\Support\TablaDinamica;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SprintHAdelantosTest extends TestCase
{
    use RefreshDatabase;

    public function test_nombre_archivo_word_usa_nombre_y_empresa(): void
    {
        $empresa = Empresa::factory()->create(['nombre' => 'Banco Industrial']);
        $orden = Orden::factory()->create(['empresa_id' => $empresa->id]);
        $evaluado = EvaluadoOrden::factory()->create([
            'orden_id' => $orden->id,
            'nombre' => 'Juan Carlos',
            'apellidos' => 'Pérez López',
        ]);

        $nombre = InformeWordNombresArchivo::generar($evaluado, $orden);

        $this->assertSame('Juan_Carlos_Pérez_López_Banco_Industrial.docx', $nombre);
    }

    public function test_opciones_edad_hijos_incluye_menor_de_un_ano(): void
    {
        $opciones = TablaDinamica::opcionesEdadHijos();

        $this->assertArrayHasKey('menor_1', $opciones);
        $this->assertSame('Menor de 1 año', $opciones['menor_1']);
        $this->assertSame('Menor de 1 año', TablaDinamica::etiquetaEdadHijo('menor_1'));
    }
}
