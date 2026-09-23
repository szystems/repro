<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Hasta cinco preguntas de preempleo propias de la empresa, y un segundo juego por puesto. */
class EmpresaPreguntasPreempleo extends Model
{
    protected $table = 'empresa_preguntas_preempleo';

    protected $fillable = [
        'empresa_id',
        'p1', 'p2', 'p3', 'p4', 'p5',
        'puesto_nombre',
        'puesto_p1', 'puesto_p2', 'puesto_p3', 'puesto_p4', 'puesto_p5',
    ];

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    /**
     * @param  array<int|string, mixed>  $principal
     * @param  array<int|string, mixed>  $puesto
     */
    public static function guardarDesdeRequest(Empresa $empresa, array $principal, ?string $puestoNombre, array $puesto): void
    {
        $principales = self::cinco($principal);
        $dePuesto = self::cinco($puesto);
        $nombre = trim((string) $puestoNombre);

        $sinPrincipal = self::vacias($principales);
        $sinPuesto = $nombre === '' && self::vacias($dePuesto);

        if ($sinPrincipal && $sinPuesto) {
            static::where('empresa_id', $empresa->id)->delete();

            return;
        }

        static::updateOrCreate(
            ['empresa_id' => $empresa->id],
            [
                'p1' => $principales[0],
                'p2' => $principales[1],
                'p3' => $principales[2],
                'p4' => $principales[3],
                'p5' => $principales[4],
                'puesto_nombre' => $nombre !== '' ? mb_substr($nombre, 0, 100) : null,
                'puesto_p1' => $dePuesto[0],
                'puesto_p2' => $dePuesto[1],
                'puesto_p3' => $dePuesto[2],
                'puesto_p4' => $dePuesto[3],
                'puesto_p5' => $dePuesto[4],
            ]
        );
    }

    /** @return list<string> */
    public function espaciosPrincipales(): array
    {
        return $this->espacios(['p1', 'p2', 'p3', 'p4', 'p5']);
    }

    /** @return list<string> */
    public function espaciosPuesto(): array
    {
        return $this->espacios(['puesto_p1', 'puesto_p2', 'puesto_p3', 'puesto_p4', 'puesto_p5']);
    }

    /** @return list<string> */
    public function preguntasPrincipales(): array
    {
        return array_values(array_filter($this->espaciosPrincipales(), fn (string $texto) => $texto !== ''));
    }

    /** @return list<string> */
    public function preguntasPuesto(): array
    {
        if ($this->nombrePuestoVisible() === null) {
            return [];
        }

        return array_values(array_filter($this->espaciosPuesto(), fn (string $texto) => $texto !== ''));
    }

    public function nombrePuestoVisible(): ?string
    {
        $nombre = trim((string) $this->puesto_nombre);
        $hayPregunta = array_filter($this->espaciosPuesto(), fn (string $texto) => $texto !== '') !== [];

        if ($nombre === '' || ! $hayPregunta) {
            return null;
        }

        return $nombre;
    }

    /**
     * @param  array<int|string, mixed>  $valores
     * @return array{0: ?string, 1: ?string, 2: ?string, 3: ?string, 4: ?string}
     */
    private static function cinco(array $valores): array
    {
        $salida = [];
        for ($i = 0; $i < 5; $i++) {
            $texto = trim((string) ($valores[$i] ?? ''));
            $salida[] = $texto === '' ? null : mb_substr($texto, 0, 500);
        }

        return $salida;
    }

    /** @param  list<?string>  $valores */
    private static function vacias(array $valores): bool
    {
        foreach ($valores as $valor) {
            if ($valor !== null && $valor !== '') {
                return false;
            }
        }

        return true;
    }

    /**
     * @param  list<string>  $columnas
     * @return list<string>
     */
    private function espacios(array $columnas): array
    {
        $salida = [];
        foreach ($columnas as $columna) {
            $salida[] = trim((string) ($this->{$columna} ?? ''));
        }

        return $salida;
    }
}
