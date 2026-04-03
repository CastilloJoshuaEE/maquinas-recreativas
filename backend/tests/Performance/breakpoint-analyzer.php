<?php
// tests/Performance/breakpoint-analyzer.php

class BreakpointAnalyzer {
    private $resultsFile;
    private $results;
    
    public function __construct($resultsFile) {
        $this->resultsFile = $resultsFile;
        $this->loadResults();
    }
    
    private function loadResults() {
        if (!file_exists($this->resultsFile)) {
            die("Error: Archivo de resultados no encontrado: {$this->resultsFile}\n");
        }
        
        $content = file_get_contents($this->resultsFile);
        $this->results = json_decode($content, true);
        
        if (!$this->results) {
            die("Error: No se pudo parsear el archivo de resultados\n");
        }
    }
    
    public function analyze() {
        echo "\n";
        echo "============================================\n";
        echo "ANALIZANDO RESULTADOS DE PRUEBAS DE ESTRÉS\n";
        echo "============================================\n\n";
        
        if (!isset($this->results['results']) || empty($this->results['results'])) {
            echo "No hay resultados para analizar\n";
            return;
        }
        
        echo "RESUMEN POR FASE:\n";
        echo "-----------------\n";
        echo str_pad("Fase", 8) . 
             str_pad("Usuarios", 12) . 
             str_pad("Requests", 12) . 
             str_pad("Errores", 12) . 
             str_pad("Tasa Error", 12) . 
             str_pad("RPS", 10) . 
             str_pad("p95(ms)", 10) . "\n";
        echo str_repeat("-", 76) . "\n";
        
        $faseNum = 1;
        foreach ($this->results['results'] as $result) {
            $estado = $result['error_rate'] < 5 ? "✓ OK" : 
                     ($result['error_rate'] < 10 ? "⚠ Lento" : "✗ Falla");
            
            echo str_pad($faseNum++, 8) .
                 str_pad($result['users'], 12) .
                 str_pad($result['requests'], 12) .
                 str_pad($result['errors'], 12) .
                 str_pad($result['error_rate'] . "%", 12) .
                 str_pad($result['rps'], 10) .
                 str_pad($result['p95'], 10) . " " . $estado . "\n";
        }
        
        echo "\n";
        echo "PUNTO DE QUIEBRE IDENTIFICADO:\n";
        echo "-----------------------------\n";
        
        if ($this->results['breakpoint']) {
            echo "El sistema comienza a fallar a partir de {$this->results['breakpoint']} usuarios\n";
        } else {
            echo "No se detectó punto de quiebre en las fases ejecutadas\n";
        }
        
        echo "\nTimestamp: " . $this->results['timestamp'] . "\n";
        echo "Total fases: " . $this->results['total_phases'] . "\n";
    }
}

// Uso del script
if ($argc < 2) {
    echo "Uso: php breakpoint-analyzer.php <archivo_resultados.json>\n";
    echo "Ejemplo: php breakpoint-analyzer.php stress_test_results_2024-01-15_10-30-00.json\n";
    exit(1);
}

$analyzer = new BreakpointAnalyzer($argv[1]);
$analyzer->analyze();