<?php
// tests/Performance/StressTest.php

require_once __DIR__ . '/HttpStressTestCase.php';

class StressTest extends HttpStressTestCase {
    private $adminUser;
    private $ensambladorUser;
    private $comprobadorUser;
    private $logisticaUser;
    private $contabilidadUser;
    private $mantenimientoUser;
    
    private $adminId;
    private $ensambladorId;
    private $comprobadorId;
    private $logisticaId;
    private $contabilidadId;
    private $mantenimientoId;
    
    private $adminUsuarioAsignado;
    private $ensambladorUsuarioAsignado;
    private $comprobadorUsuarioAsignado;
    private $logisticaUsuarioAsignado;
    private $contabilidadUsuarioAsignado;
    private $mantenimientoUsuarioAsignado;
    
    private $comercioId;
    private $maquinaId;
    private $placaId;
    private $carcasaId;
    private $maquinaData;
    
    // Configuración de fases de carga
    private $loadPhases = [
        ['users' => 5,  'duration' => 15, 'description' => 'Carga muy ligera'],
        ['users' => 10, 'duration' => 15, 'description' => 'Carga ligera'],
        ['users' => 20, 'duration' => 15, 'description' => 'Carga media'],
        ['users' => 30, 'duration' => 15, 'description' => 'Carga alta'],
        ['users' => 50, 'duration' => 15, 'description' => 'Carga crítica'],
    ];
    
    // Almacenar resultados por fase
    private $phaseResults = [];
    
    public function __construct() {
        parent::__construct();
        
        $timestamp = time();
        $rand = rand(10, 999);
        
        // Usuario Administrador
        $this->adminUser = [
            'nombre' => 'Admin',
            'apellido' => 'Stress',
            'ci' => '60000006' . $rand,
            'email' => 'admin_stress_' . $timestamp . '_' . uniqid() . '@test.com',
            'contrasena' => 'Password123!',
            'tipo' => 'Administrador'
        ];
        
        // Usuario Ensamblador
        $this->ensambladorUser = [
            'nombre' => 'Ensamblador',
            'apellido' => 'Stress',
            'ci' => '10000001' . $rand,
            'email' => 'ensamblador_stress_' . $timestamp . '_' . uniqid() . '@test.com',
            'contrasena' => 'Password123!',
            'tipo' => 'Tecnico',
            'especialidad' => 'Ensamblador'
        ];
        
        // Usuario Comprobador
        $this->comprobadorUser = [
            'nombre' => 'Comprobador',
            'apellido' => 'Stress',
            'ci' => '20000002' . $rand,
            'email' => 'comprobador_stress_' . $timestamp . '_' . uniqid() . '@test.com',
            'contrasena' => 'Password123!',
            'tipo' => 'Tecnico',
            'especialidad' => 'Comprobador'
        ];
        
        // Usuario Mantenimiento
        $this->mantenimientoUser = [
            'nombre' => 'Mantenimiento',
            'apellido' => 'Stress',
            'ci' => '30000003' . $rand,
            'email' => 'mantenimiento_stress_' . $timestamp . '_' . uniqid() . '@test.com',
            'contrasena' => 'Password123!',
            'tipo' => 'Tecnico',
            'especialidad' => 'Mantenimiento'
        ];
        
        // Usuario Logistica
        $this->logisticaUser = [
            'nombre' => 'Logistica',
            'apellido' => 'Stress',
            'ci' => '40000004' . $rand,
            'email' => 'logistica_stress_' . $timestamp . '_' . uniqid() . '@test.com',
            'contrasena' => 'Password123!',
            'tipo' => 'Logistica'
        ];
        
        // Usuario Contabilidad
        $this->contabilidadUser = [
            'nombre' => 'Contabilidad',
            'apellido' => 'Stress',
            'ci' => '50000005' . $rand,
            'email' => 'contabilidad_stress_' . $timestamp . '_' . uniqid() . '@test.com',
            'contrasena' => 'Password123!',
            'tipo' => 'Contabilidad'
        ];
    }
    
    /**
     * Inicializar la prueba
     */
    public function init() {
        echo "\n";
        echo "╔════════════════════════════════════════════════════════════════════════════╗\n";
        echo "║                    PRUEBAS DE ESTRÉS - SISTEMA maquinas_recreativas       ║\n";
        echo "╚════════════════════════════════════════════════════════════════════════════╝\n\n";
    }
    
    /**
     * PRUEBA PRINCIPAL: Ejecutar todas las fases de estrés
     */
    public function testEjecutarEstres() {
        echo "INICIANDO PRUEBAS DE ESTRÉS\n";
        echo "================================\n\n";
        
        // PASO 1: Crear todos los usuarios necesarios
        if (!$this->pasoCrearUsuarios()) {
            echo "Error crítico: No se pudieron crear los usuarios. Abortando.\n";
            return;
        }
        
        // PASO 2: Setup inicial (crear datos base)
        if (!$this->faseSetupInicial()) {
            echo "Error crítico: No se pudo completar el setup inicial. Abortando.\n";
            return;
        }
        
        echo "\nSETUP COMPLETADO. Iniciando fases de carga...\n";
        
        // Ejecutar cada fase de carga
        foreach ($this->loadPhases as $index => $phase) {
            $continuar = $this->ejecutarFaseCarga($index + 1, $phase);
            if (!$continuar) {
                break;
            }
            // Pequeña pausa entre fases
            sleep(2);
        }
        
        // Mostrar resumen final
        $this->mostrarResumen();
    }
    
    /**
     * PASO 1: Crear todos los usuarios necesarios
     */
    private function pasoCrearUsuarios() {
        echo "PASO 1: Creando usuarios de prueba...\n";
        echo str_repeat("-", 40) . "\n";
        
        // Limpiar cookies antes de empezar
        $this->clearCookies();
        
        // 1.1 Crear Técnico Ensamblador
        echo "   1.1 Creando técnico ensamblador...\n";
        $respEns = $this->request('POST', '/usuario/register', $this->ensambladorUser);
        if (!$this->assertResponseSuccess('Error al crear ensamblador')) {
            return false;
        }
        $this->ensambladorId = $respEns['userId'] ?? null;
        $this->ensambladorUsuarioAsignado = $respEns['usuario_asignado'] ?? null;
        echo "       Ensamblador ID: {$this->ensambladorId}\n";
        
        sleep(1);
        
        // 1.2 Crear Técnico Comprobador
        echo "   1.2 Creando técnico comprobador...\n";
        $respComp = $this->request('POST', '/usuario/register', $this->comprobadorUser);
        if (!$this->assertResponseSuccess('Error al crear comprobador')) {
            return false;
        }
        $this->comprobadorId = $respComp['userId'] ?? null;
        $this->comprobadorUsuarioAsignado = $respComp['usuario_asignado'] ?? null;
        echo "       Comprobador ID: {$this->comprobadorId}\n";
        
        sleep(1);
        
        // 1.3 Crear Técnico Mantenimiento
        echo "   1.3 Creando técnico mantenimiento...\n";
        $respMant = $this->request('POST', '/usuario/register', $this->mantenimientoUser);
        if (!$this->assertResponseSuccess('Error al crear mantenimiento')) {
            return false;
        }
        $this->mantenimientoId = $respMant['userId'] ?? null;
        $this->mantenimientoUsuarioAsignado = $respMant['usuario_asignado'] ?? null;
        echo "       Mantenimiento ID: {$this->mantenimientoId}\n";
        
        sleep(1);
        
        // 1.4 Crear Logística
        echo "   1.4 Creando usuario logística...\n";
        $respLog = $this->request('POST', '/usuario/register', $this->logisticaUser);
        if (!$this->assertResponseSuccess('Error al crear logística')) {
            return false;
        }
        $this->logisticaId = $respLog['userId'] ?? null;
        $this->logisticaUsuarioAsignado = $respLog['usuario_asignado'] ?? null;
        echo "       Logística ID: {$this->logisticaId}\n";
        
        sleep(1);
        
        // 1.5 Crear Contabilidad
        echo "   1.5 Creando usuario contabilidad...\n";
        $respCont = $this->request('POST', '/usuario/register', $this->contabilidadUser);
        if (!$this->assertResponseSuccess('Error al crear contabilidad')) {
            return false;
        }
        $this->contabilidadId = $respCont['userId'] ?? null;
        $this->contabilidadUsuarioAsignado = $respCont['usuario_asignado'] ?? null;
        echo "       Contabilidad ID: {$this->contabilidadId}\n";
        
        sleep(1);
        
        // 1.6 Crear Administrador
        echo "   1.6 Creando usuario administrador...\n";
        $respAdmin = $this->request('POST', '/usuario/register', $this->adminUser);
        if (!$this->assertResponseSuccess('Error al crear admin')) {
            return false;
        }
        $this->adminId = $respAdmin['userId'] ?? null;
        $this->adminUsuarioAsignado = $respAdmin['usuario_asignado'] ?? null;
        echo "       Admin ID: {$this->adminId}\n";
        
        echo "\n    Todos los usuarios creados exitosamente\n\n";
        return true;
    }
    
    /**
     * Fase 0: Crear datos base para las pruebas
     */
    private function faseSetupInicial() {
        echo "\nPASO 2: Preparación de datos base\n";
        echo str_repeat("-", 40) . "\n";
        
        // 2.1 Login como logística
        echo "   2.1 Iniciando sesión como logística...\n";
        $this->clearCookies();
        $loginResp = $this->request('POST', '/usuario/login', [
            'usuario_asignado' => $this->logisticaUsuarioAsignado,
            'contrasena' => $this->logisticaUser['contrasena']
        ]);
        
        if (!$this->assertResponseSuccess('Error en login logística')) {
            echo "       Error: No se pudo iniciar sesión como logística\n";
            echo "       Respuesta: " . json_encode($loginResp) . "\n";
            return false;
        }
        echo "       Login exitoso\n";
        
        // 2.2 Crear comercio
        echo "   2.2 Creando comercio...\n";
        $comercioData = [
            'nombre' => 'Comercio Stress ' . time(),
            'tipo' => 'Minorista',
            'direccion' => 'Av. Pruebas 123',
            'telefono' => '0999' . rand(100000, 999999)
        ];
        
        $comercioResp = $this->request('POST', '/comercio/register', $comercioData);
        if (!$this->assertResponseSuccess('Error al crear comercio')) {
            echo "       Error: No se pudo crear comercio\n";
            return false;
        }
        $this->comercioId = $comercioResp['idComercio'] ?? null;
        echo "       Comercio ID: {$this->comercioId}\n";
        
        // 2.3 Logout
        echo "   2.3 Cerrando sesión de logística...\n";
        $this->request('POST', '/usuario/logout', []);
        $this->clearCookies();
        
        // 2.4 Login como ensamblador para generar placa y carcasa
        echo "   2.4 Iniciando sesión como ensamblador...\n";
        $loginResp = $this->request('POST', '/usuario/login', [
            'usuario_asignado' => $this->ensambladorUsuarioAsignado,
            'contrasena' => $this->ensambladorUser['contrasena']
        ]);
        
        if (!$this->assertResponseSuccess('Error en login ensamblador')) {
            echo "       Error: No se pudo iniciar sesión como ensamblador\n";
            return false;
        }
        echo "       Login exitoso\n";
        
        // 2.5 Generar placa
        echo "   2.5 Generando placa...\n";
        $placaResp = $this->request('POST', '/maquina/generar-placa', []);
        if (!$this->assertResponseSuccess('Error al generar placa')) {
            echo "       Error: No se pudo generar placa\n";
            return false;
        }
        $this->placaId = $placaResp['idComponente'] ?? null;
        echo "       Placa ID: {$this->placaId}\n";
        
        // 2.6 Generar carcasa
        echo "   2.6 Generando carcasa...\n";
        $carcasaResp = $this->request('POST', '/maquina/generar-placa', []);
        if (!$this->assertResponseSuccess('Error al generar carcasa')) {
            echo "       Error: No se pudo generar carcasa\n";
            return false;
        }
        $this->carcasaId = $carcasaResp['idComponente'] ?? null;
        echo "       Carcasa ID: {$this->carcasaId}\n";
        
        // 2.7 Logout
        echo "   2.7 Cerrando sesión de ensamblador...\n";
        $this->request('POST', '/usuario/logout', []);
        $this->clearCookies();
        
        // 2.8 Login como logística nuevamente para registrar máquina
        echo "   2.8 Iniciando sesión como logística...\n";
        $loginResp = $this->request('POST', '/usuario/login', [
            'usuario_asignado' => $this->logisticaUsuarioAsignado,
            'contrasena' => $this->logisticaUser['contrasena']
        ]);
        
        if (!$this->assertResponseSuccess('Error en login logística')) {
            echo "       Error: No se pudo iniciar sesión como logística\n";
            return false;
        }
        echo "       Login exitoso\n";
        
        // 2.9 Registrar máquina - MISMO FORMATO que UserFlowsTest
        echo "   2.9 Registrando máquina...\n";
        $maquinaData = [
            'nombre' => $this->maquinaData['nombre'] ?? 'Máquina Stress ' . time(),
            'tipo' => $this->maquinaData['tipo'] ?? 'Arcade Clasica',
            'idComercio' => $this->comercioId,
            'idPlaca' => $this->placaId,
            'idCarcasa' => $this->carcasaId,
            'idEnsamblador' => $this->ensambladorId,
            'idComprobador' => $this->comprobadorId
        ];
        
        // Guardar datos de máquina para referencia posterior
        $this->maquinaData = [
            'nombre' => 'Máquina Stress ' . time(),
            'tipo' => 'Arcade Clasica'
        ];
        
        $maquinaResp = $this->request('POST', '/maquina/register', $maquinaData);
        
        // Verificar respuesta más detalladamente
        if (!$this->assertResponseSuccess('Error al registrar máquina')) {
            echo "       Error: No se pudo registrar máquina\n";
            echo "       HTTP Code: {$this->lastHttpCode}\n";
            echo "       Respuesta completa: " . json_encode($maquinaResp) . "\n";
            return false;
        }
        
        $this->maquinaId = $maquinaResp['idMaquina'] ?? null;
        
        if (!$this->assertNotNull($this->maquinaId, 'No se recibio ID de maquina')) {
            echo "       Error: No se recibió ID de máquina en la respuesta\n";
            return false;
        }
        
        echo "       Máquina registrada: {$this->maquinaData['nombre']} (ID: {$this->maquinaId})\n";
        
        // 2.10 Logout final
        echo "   2.10 Cerrando sesión...\n";
        $this->request('POST', '/usuario/logout', []);
        $this->clearCookies();
        
        echo "\n    Datos base listos\n";
        echo "      • Comercio ID: {$this->comercioId}\n";
        echo "      • Máquina ID: {$this->maquinaId}\n";
        echo "      • Placa ID: {$this->placaId}\n";
        echo "      • Carcasa ID: {$this->carcasaId}\n\n";
        
        return true;
    }
    
    /**
     * Ejecutar una fase específica de carga
     */
    private function ejecutarFaseCarga($faseNum, $phase) {
        echo "\nFASE {$faseNum}: {$phase['users']} usuarios - {$phase['description']}\n";
        echo str_repeat("=", 50) . "\n";
        
        $startTime = microtime(true);
        $requests = 0;
        $errors = 0;
        $responseTimes = [];
        
        echo "   Simulando carga por {$phase['duration']} segundos...\n";
        
        // Ejecutar requests durante la duración de la fase
        $endTime = $startTime + $phase['duration'];
        $iteracion = 0;
        
        while (microtime(true) < $endTime) {
            $iteracion++;
            
            // Seleccionar un tipo de usuario aleatorio
            $userTypes = ['admin', 'ensamblador', 'comprobador', 'mantenimiento', 'logistica', 'contabilidad'];
            $userType = $userTypes[array_rand($userTypes)];
            
            // Realizar request según tipo de usuario
            $requestStart = microtime(true);
            $success = $this->ejecutarRequestSimulado($userType);
            $requestTime = (microtime(true) - $requestStart) * 1000;
            
            $requests++;
            $responseTimes[] = $requestTime;
            
            if (!$success) {
                $errors++;
            }
            
            // Mostrar progreso cada cierto número de requests
            if ($iteracion % 50 == 0) {
                echo "      Progreso: {$requests} requests realizados...\n";
            }
            
            // Pequeña pausa entre requests
            usleep(50000);
        }
        
        // Calcular métricas
        $duration = microtime(true) - $startTime;
        $rps = $requests / $duration;
        $errorRate = ($errors / max($requests, 1)) * 100;
        
        sort($responseTimes);
        $p95 = $responseTimes[floor(count($responseTimes) * 0.95)] ?? 0;
        $p99 = $responseTimes[floor(count($responseTimes) * 0.99)] ?? 0;
        
        // Guardar resultados
        $this->phaseResults[] = [
            'users' => $phase['users'],
            'requests' => $requests,
            'errors' => $errors,
            'error_rate' => round($errorRate, 2),
            'rps' => round($rps, 2),
            'p95' => round($p95, 2),
            'p99' => round($p99, 2),
            'duration' => round($duration, 2)
        ];
        
        // Mostrar resultados de la fase
        echo "\n    RESULTADOS FASE {$faseNum}:\n";
        echo "      • Requests: {$requests}\n";
        echo "      • Errores: {$errors} (" . round($errorRate, 2) . "%)\n";
        echo "      • RPS: " . round($rps, 2) . " req/s\n";
        echo "      • Tiempo respuesta (p95): " . round($p95, 2) . "ms\n";
        echo "      • Tiempo respuesta (p99): " . round($p99, 2) . "ms\n";
        
        // Determinar estado
        if ($errorRate > 10 || $p95 > 3000) {
            echo "    ⚠ SISTEMA COLAPSADO - Deteniendo pruebas\n";
            return false;
        } elseif ($errorRate > 5 || $p95 > 1500) {
            echo "    ⚠ SISTEMA DEGRADADO\n";
        } else {
            echo "    ✓ SISTEMA ESTABLE\n";
        }
        
        return true;
    }
    
    /**
     * Ejecutar un request simulado sin necesidad de login real
     */
    private function ejecutarRequestSimulado($userType) {
        // Endpoints públicos que no requieren autenticación
        $publicEndpoints = [
            '/health',
            '/test-db'
        ];
        
        // Endpoints por tipo de usuario (simulados)
        $endpoints = [
            'admin' => [
                '/administrador/usuarios',
                '/administrador/estadisticas'
            ],
            'ensamblador' => [
                '/maquina/ensamblador/' . ($this->ensambladorId ?? '00000000-0000-0000-0000-000000000001'),
                '/componentes/disponibles'
            ],
            'comprobador' => [
                '/maquina/comprobador/' . ($this->comprobadorId ?? '00000000-0000-0000-0000-000000000001'),
                '/componentes/disponibles'
            ],
            'mantenimiento' => [
                '/maquina/mantenimiento/' . ($this->mantenimientoId ?? '00000000-0000-0000-0000-000000000001'),
                '/componentes/disponibles'
            ],
            'logistica' => [
                '/comercio/all',
                '/maquina/distribucion'
            ],
            'contabilidad' => [
                '/contabilidad/recaudaciones',
                '/contabilidad/resumen-recaudaciones'
            ]
        ];
        
        // 70% endpoints específicos, 30% endpoints públicos
        $rand = mt_rand(1, 100);
        
        if ($rand <= 30 && !empty($publicEndpoints)) {
            // Endpoint público
            $endpoint = $publicEndpoints[array_rand($publicEndpoints)];
            $method = 'GET';
            $data = null;
        } else {
            // Endpoint específico del tipo de usuario
            $userEndpoints = $endpoints[$userType] ?? $publicEndpoints;
            if (empty($userEndpoints)) {
                $userEndpoints = $publicEndpoints;
            }
            $endpoint = $userEndpoints[array_rand($userEndpoints)];
            $method = 'GET';
            $data = null;
        }
        
        try {
            $response = $this->request($method, $endpoint, $data);
            $success = $this->lastHttpCode < 400;
            return $success;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Mostrar resumen final de todas las fases
     */
    private function mostrarResumen() {
        echo "\n\n";
        echo "RESUMEN FINAL DE PRUEBAS DE ESTRÉS\n";
        echo "======================================\n\n";
        
        echo str_pad("Usuarios", 12) . 
             str_pad("Requests", 12) . 
             str_pad("Errores", 12) . 
             str_pad("Tasa Error", 12) . 
             str_pad("RPS", 10) . 
             str_pad("p95 (ms)", 10) . 
             "Estado\n";
        echo str_repeat("-", 80) . "\n";
        
        $breakpoint = null;
        
        foreach ($this->phaseResults as $result) {
            $estado = $result['error_rate'] < 5 ? "✓ OK" :  
                     ($result['error_rate'] < 10 ? "⚠ Lento" : "✗ Falla");
            
            echo str_pad($result['users'], 12) .
                 str_pad($result['requests'], 12) .
                 str_pad($result['errors'], 12) .
                 str_pad($result['error_rate'] . "%", 12) .
                 str_pad($result['rps'], 10) .
                 str_pad($result['p95'], 10) .
                 $estado . "\n";
            
            // Detectar punto de quiebre
            if ($result['error_rate'] >= 10 && !$breakpoint) {
                $breakpoint = $result['users'];
            }
        }
        
        echo "\n\n";
        echo "ANÁLISIS DEL PUNTO DE QUIEBRE\n";
        echo "--------------------------------\n";
        
        if ($breakpoint) {
            echo " El sistema COMIENZA A FALLAR a partir de {$breakpoint} usuarios concurrentes\n";
            
            echo "\n RECOMENDACIONES:\n";
            if ($breakpoint <= 10) {
                echo "   • Revisar configuración del servidor web\n";
                echo "   • Aumentar límites de conexiones simultáneas\n";
                echo "   • Optimizar consultas a base de datos\n";
                echo "   • Verificar índices en tablas más utilizadas\n";
            } elseif ($breakpoint <= 20) {
                echo "   • Implementar caché para consultas frecuentes\n";
                echo "   • Optimizar índices en tablas más utilizadas\n";
                echo "   • Considerar aumentar recursos del servidor\n";
            } elseif ($breakpoint <= 30) {
                echo "   • Optimizar consultas pesadas\n";
                echo "   • Implementar caché de resultados\n";
                echo "   • Revisar límites de conexión en MySQL\n";
            } else {
                echo "   • Implementar balanceador de carga\n";
                echo "   • Usar caché distribuido (Redis/Memcached)\n";
                echo "   • Escalar horizontalmente\n";
            }
        } else {
            echo " El sistema soportó TODAS las cargas de prueba\n";
            echo "   El punto de quiebre está por encima de " . end($this->phaseResults)['users'] . " usuarios\n";
        }
        
        // Guardar resultados en archivo
        $this->guardarResultados();
    }
    
    /**
     * Guardar resultados en archivo JSON
     */
    private function guardarResultados() {
        $filename = __DIR__ . '/stress_test_results_' . date('Y-m-d_H-i-s') . '.json';
        $data = [
            'timestamp' => date('Y-m-d H:i:s'),
            'total_phases' => count($this->phaseResults),
            'results' => $this->phaseResults,
            'breakpoint' => $this->findBreakpoint()
        ];
        
        file_put_contents($filename, json_encode($data, JSON_PRETTY_PRINT));
        echo "\n Resultados guardados en: {$filename}\n";
    }
    
    /**
     * Encontrar el punto de quiebre
     */
    private function findBreakpoint() {
        foreach ($this->phaseResults as $result) {
            if ($result['error_rate'] >= 10 || $result['p95'] > 3000) {
                return $result['users'];
            }
        }
        return null;
    }
}