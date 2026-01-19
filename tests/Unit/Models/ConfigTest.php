<?php

namespace Tests\Unit\Models;

use App\Models\Config;
use Tests\TestCase;

class ConfigTest extends TestCase
{
    public function testConfigModelCanBeInstantiated()
    {
        $config = new Config();
        $this->assertInstanceOf(Config::class, $config);
    }

    public function testConfigHasCorrectTable()
    {
        $config = new Config();
        $this->assertEquals('configs', $config->getTable());
    }
}