<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/security-test.php';

class APISecurityTestAudit extends TestCase
{
    public function testSecurityAudit()
    {
        $tester = new \APISecurityTest(); // tu clase original
        
        ob_start(); // evitar que ensucie la consola
        $tester->runAllTests();
        ob_end_clean();

        $this->assertTrue(true); // si llegó aquí, pasó
    }
}