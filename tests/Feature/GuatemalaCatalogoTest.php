<?php

namespace Tests\Feature;

use App\Models\Departamento;
use App\Models\Municipio;
use App\Support\GuatemalaCatalogo;
use Database\Seeders\DepartamentosMunicipiosSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class GuatemalaCatalogoTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_carga_22_departamentos_y_340_municipios(): void
    {
        $this->seed(DepartamentosMunicipiosSeeder::class);

        $this->assertDatabaseCount('departamentos', 22);
        $this->assertDatabaseCount('municipios', 340);
        $this->assertDatabaseHas('departamentos', ['nombre' => 'Guatemala', 'codigo' => '01']);
        $this->assertDatabaseHas('municipios', ['nombre' => 'Mixco', 'codigo' => '0108']);
        $this->assertDatabaseHas('departamentos', ['nombre' => 'Petén', 'codigo' => '17']);
    }

    public function test_seeder_es_idempotente(): void
    {
        $this->seed(DepartamentosMunicipiosSeeder::class);
        $this->seed(DepartamentosMunicipiosSeeder::class);

        $this->assertSame(22, Departamento::count());
        $this->assertSame(340, Municipio::count());
    }

    public function test_para_select_cliente_devuelve_estructura_anidada(): void
    {
        Cache::flush();
        $this->seed(DepartamentosMunicipiosSeeder::class);

        $catalogo = GuatemalaCatalogo::paraSelectCliente();

        $this->assertCount(22, $catalogo);
        $this->assertSame('Guatemala', $catalogo[0]['nombre']);
        $this->assertNotEmpty($catalogo[0]['municipios']);
        $this->assertArrayHasKey('codigo', $catalogo[0]['municipios'][0]);
        $this->assertArrayHasKey('nombre', $catalogo[0]['municipios'][0]);
    }

    public function test_lazy_seed_si_tabla_vacia(): void
    {
        Cache::flush();

        $catalogo = GuatemalaCatalogo::paraSelectCliente();

        $this->assertSame(22, Departamento::count());
        $this->assertCount(22, $catalogo);
    }
}
