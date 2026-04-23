<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

/**
 * Cifra registros históricos en campos PII (H-09).
 *
 * Detecta filas cuyo valor no empieza con el prefijo de Laravel Crypt
 * ('eyJpdiI...') y las cifra in-place.
 *
 * Uso:
 *   php artisan pii:encrypt-legacy --dry-run
 *   php artisan pii:encrypt-legacy
 */
class EncryptLegacyPII extends Command
{
    protected $signature = 'pii:encrypt-legacy {--dry-run : Solo informa sin escribir}';

    protected $description = 'Cifra registros históricos de PII (observaciones, notas_poligrafo) que aún no están cifrados.';

    /**
     * @var array<int, array{table: string, column: string}>
     */
    private array $objetivos = [
        ['table' => 'ordenes', 'column' => 'observaciones_internas'],
        ['table' => 'evaluados_orden', 'column' => 'observaciones'],
        ['table' => 'evaluados_orden', 'column' => 'notas_poligrafo'],
    ];

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $totalCifrados = 0;

        foreach ($this->objetivos as $objetivo) {
            $cifrados = $this->procesar($objetivo['table'], $objetivo['column'], $dryRun);
            $totalCifrados += $cifrados;
        }

        $modo = $dryRun ? 'DRY-RUN (sin cambios)' : 'aplicado';
        $this->info("Total filas cifradas ({$modo}): {$totalCifrados}");

        return self::SUCCESS;
    }

    private function procesar(string $tabla, string $columna, bool $dryRun): int
    {
        $cifrados = 0;
        $registros = DB::table($tabla)
            ->select('id', $columna)
            ->whereNotNull($columna)
            ->where($columna, '!=', '')
            ->get();

        foreach ($registros as $row) {
            $valor = $row->{$columna};

            // Si ya se puede descifrar, saltar
            try {
                Crypt::decryptString($valor);
                continue;
            } catch (DecryptException) {
                // No está cifrado, procedemos
            }

            if (!$dryRun) {
                DB::table($tabla)
                    ->where('id', $row->id)
                    ->update([$columna => Crypt::encryptString($valor)]);
            }
            $cifrados++;
        }

        $this->line("  {$tabla}.{$columna}: {$cifrados} filas " . ($dryRun ? 'se cifrarían' : 'cifradas'));

        return $cifrados;
    }
}
