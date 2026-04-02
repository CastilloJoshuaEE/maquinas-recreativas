<?php
// tests/Performance/breakpoint-analyzer.php
// Script para analizar los resultados y determinar el punto de quiebre

class BreakpointAnalyzer {
    private $resultsFile;
    private $results;
    private $breakpoints = [];
    
    public function __construct($resultsFile) {
        $this->resultsFile = $resultsFile;
        $this->loadResults();
    }
    
    private function loadResults() {
        if (!file_exists($this->resultsFile)) {
            die("Error: Archivo de resultados no encontrado: {$this->resultsFile}\n");
        }
        
        $content = file_get_contents($this->resultsFile);
        $lines = explode("\n", $content);
        $this->results = [];
        
        foreach ($lines as $line) {
            if (!empty($line)) {
                $this->results[] = json_decode($line, true);
            }
        }
    }
    
    public function analyze() {
        echo " Analizando punto de quiebre del sistema...\n";
        echo "============================================\n\n";
        
        $metrics = $this->calculateMetrics();
        
        echo "RESUMEN DE MÉTRICAS:\n";
        echo "-------------------\n";
        foreach ($metrics['summary'] as $key => $value) {
            echo sprintf("%-25s: %s\n", $key, $value);
        }
        
        echo "\n\nANÁLISIS POR CARGA:\n";
        echo "------------------\n";
        echo str_pad("Usuarios", 15) . 
             str_pad("Requests", 15) . 
             str_pad("Errores", 15) . 
             str_pad("Tiempo(p95)", 15) . 
             str_pad("Estado", 15) . "\n";
        echo str_repeat("-", 75) . "\n";
        
        foreach ($metrics['by_load'] as $load) {
            $estado = $load['error_rate'] < 5 ? " OK" : 
                     ($load['error_rate'] < 10 ? " Lento" : " Falla");
            
            echo str_pad($load['users'] . " usuarios", 15) .
                 str_pad($load['requests'], 15) .
                 str_pad($load['errors'] . " (" . $load['error_rate'] . "%)", 15) .
                 str_pad(round($load['p95']) . "ms", 15) .
                 str_pad($estado, 15) . "\n";
        }
        
        // Determinar punto de quiebre
        $breakpoint = $this->findBreakpoint($metrics);
        
        echo "\n\n PUNTO DE QUIEBRE IDENTIFICADO:\n";
        echo "================================\n";
        
        if ($breakpoint) {
            echo "El sistema comienza a fallar a partir de {$breakpoint['users']} usuarios\n";
            echo "• Tasa de error: {$breakpoint['error_rate']}%\n";
            echo "• Tiempo de respuesta (p95): " . round($breakpoint['p95']) . "ms\n";
            echo "• Causa probable: {$breakpoint['cause']}\n";
            
            echo "\n RECOMENDACIONES:\n";
            echo $this->getRecommendations($breakpoint);
        } else {
            echo " No se detectó punto de quiebre en el rango probado\n";
            echo "El sistema soportó hasta " . max(array_column($metrics['by_load'], 'users')) . " usuarios\n";
        }
    }
    
    private function calculateMetrics() {
        $metrics = [
            'summary' => [],
            'by_load' => []
        ];
        
        $loadLevels = [
            50 => [],
            100 => [],
            200 => [],
            300 => [],
            500 => []
        ];
        
        $totalRequests = 0;
        $totalErrors = 0;
        $totalDuration = 0;
        
        foreach ($this->results as $result) {
            if (!isset($result['metric']) || !isset($result['data'])) {
                continue;
            }
            
            if ($result['metric'] === 'http_reqs') {
                $totalRequests += $result['data']['count'] ?? 0;
            }
            
            if ($result['metric'] === 'http_req_failed') {
                $totalErrors += $result['data']['count'] ?? 0;
            }
            
            if ($result['metric'] === 'http_req_duration') {
                $totalDuration += ($result['data']['avg'] ?? 0) * ($result['data']['count'] ?? 0);
            }
        }
        
        $metrics['summary'] = [
            'Total Requests' => number_format($totalRequests),
            'Total Errores' => number_format($totalErrors),
            'Tasa Error Global' => round(($totalErrors / max($totalRequests, 1)) * 100, 2) . '%',
            'Duración Promedio' => round($totalDuration / max($totalRequests, 1), 2) . 'ms'
        ];
        
        return $metrics;
    }
    
    private function findBreakpoint($metrics) {
        // Implementar lógica para encontrar punto de quiebre
        // Esto es un ejemplo simplificado
        
        $thresholds = [
            'error_rate' => 10, // 10% de errores
            'response_time' => 1000 // 1 segundo
        ];
        
        foreach ($metrics['by_load'] as $load) {
            if ($load['error_rate'] > $thresholds['error_rate'] || 
                $load['p95'] > $thresholds['response_time']) {
                
                $cause = [];
                if ($load['error_rate'] > $thresholds['error_rate']) {
                    $cause[] = "alta tasa de errores";
                }
                if ($load['p95'] > $thresholds['response_time']) {
                    $cause[] = "tiempos de respuesta lentos";
                }
                
                return [
                    'users' => $load['users'],
                    'error_rate' => $load['error_rate'],
                    'p95' => $load['p95'],
                    'cause' => implode(' y ', $cause)
                ];
            }
        }
        
        return null;
    }
    
    private function getRecommendations($breakpoint) {
        $recommendations = [];
        
        if ($breakpoint['error_rate'] > 10) {
            $recommendations[] = "• Optimizar consultas a base de datos";
            $recommendations[] = "• Implementar caché para consultas frecuentes";
            $recommendations[] = "• Revisar índices en tablas más utilizadas";
        }
        
        if ($breakpoint['p95'] > 1000) {
            $recommendations[] = "• Escalar servidor (más CPU/RAM)";
            $recommendations[] = "• Implementar balanceador de carga";
            $recommendations[] = "• Optimizar código de los endpoints más lentos";
        }
        
        if ($breakpoint['users'] < 100) {
            $recommendations[] = "• Revisar configuración del servidor web";
            $recommendations[] = "• Aumentar límites de conexiones simultáneas";
            $recommendations[] = "• Considerar usar un servidor más potente";
        }
        
        return implode("\n", $recommendations);
    }
}

// Uso del script
if ($argc < 2) {
    echo "Uso: php breakpoint-analyzer.php <archivo_resultados.json>\n";
    exit(1);
}

$analyzer = new BreakpointAnalyzer($argv[1]);
$analyzer->analyze();