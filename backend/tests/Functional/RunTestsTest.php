<?php
// tests/Functional/RunTestsTest.php

use PHPUnit\Framework\TestCase;

class RunTestsTest extends TestCase
{
    public function testRunFunctionalScript()
    {
        $output = [];
        $returnCode = 0;
        
        // Ejecutar el script run_tests.php
        exec("php " . __DIR__ . "/run_tests.php", $output, $returnCode);
        
        // Mostrar la salida para depuración
        echo "\n" . implode("\n", $output) . "\n";
        
        // Verificar que el script no haya fallado
        $this->assertEquals(0, $returnCode, "Las pruebas funcionales fallaron");
    }
}