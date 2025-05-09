<?php

use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    public function testConfigIsHandledProperly()
    {
        $config = new Config();
        $this->assertNotNull($config);
        // Agregar más aserciones según sea necesario para verificar la configuración
    }
}