<?php

use PHPUnit\Framework\TestCase;

class FunctionalRunnerTest extends TestCase {

    public function testRunFunctionalScript() {
        $output = [];
        $returnCode = 0;

        exec("php " . __DIR__ . "/run_tests.php", $output, $returnCode);

        echo "\n" . implode("\n", $output) . "\n";

        $this->assertEquals(0, $returnCode, "El script funcional falló");
    }
}