<?php

namespace Database\Seeders;

use App\Models\Config;
use Illuminate\Database\Seeder;

class ConfigsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Config::create([
            'email' => 'ottoszarata@szystems.com',
            'time_zone' => 'America/Guatemala',
            'currency' => 'GTQ Q',
            'currency_iso' => 'Q',
        ]);
    }
}
