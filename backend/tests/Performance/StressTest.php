<?php
// tests/Performance/StressTest.php

require_once __DIR__ . '/HttpStressTestCase.php';

class StressTest extends HttpStressTestCase {
    private $ensambladorUser;
    private $comprobadorUser;
    private $logisticaUser;
    private $contabilidadUser;
    private $mantenimientoUser;
    
    private $ensambladorId;
    private $comprobadorId;
    private $logisticaId;
    private $contabilidadId;
    private $mantenimientoId;
    
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
    
    // Almacenar sesiones por tipo de usuario
    private $userSessions = [];
    private $sessionCookieFile;
    
    private $loadPhases = [
        ['users' => 5,  'duration' => 15, 'description' => 'Carga muy ligera'],
        ['users' => 10, 'duration' => 15, 'description' => 'Carga ligera'],
        ['users' => 20, 'duration' => 15, 'description' => 'Carga media'],
        ['users' => 30, 'duration' => 15, 'description' => 'Carga alta'],
        ['users' => 50, 'duration' => 15, 'description' => 'Carga crítica'],
    ];
    
    private $phaseResults = [];
    
    public function __construct() {
        parent::__construct();
        $timestamp = time();
        $rand = rand(10, 999);
        
        $this->ensambladorUser = [
            'nombre' => 'Ensamblador',
            'apellido' => 'Stress',
            'ci' => '10000001' . $rand,
            'email' => 'ensamblador_stress_' . $timestamp . '_' . uniqid() . '@test.com',
            'contrasena' => 'Password123!',
            'tipo' => 'Tecnico',
            'especialidad' => 'Ensamblador'
        ];
        
        $this->comprobadorUser = [
            'nombre' => 'Comprobador',
            'apellido' => 'Stress',
            'ci' => '20000002' . $rand,
            'email' => 'comprobador_stress_' . $timestamp . '_' . uniqid() . '@test.com',
            'contrasena' => 'Password123!',
            'tipo' => 'Tecnico',
            'especialidad' => 'Comprobador'
        ];
        
        $this->mantenimientoUser = [
            'nombre' => 'Mantenimiento',
            'apellido' => 'Stress',
            'ci' => '30000003' . $rand,
            'email' => 'mantenimiento_stress_' . $timestamp . '_' . uniqid() . '@test.com',
            'contrasena' => 'Password123!',
            'tipo' => 'Tecnico',
            'especialidad' => 'Mantenimiento'
        ];
        
        $this->logisticaUser = [
            'nombre' => 'Logistica',
            'apellido' => 'Stress',
            'ci' => '40000004' . $rand,
            'email' => 'logistica_stress_' . $timestamp . '_' . uniqid() . '@test.com',
            'contrasena' => 'Password123!',
            'tipo' => 'Logistica'
        ];
        
        $this->contabilidadUser = [
            'nombre' => 'Contabilidad',
            'apellido' => 'Stress',
            'ci' => '50000005' . $rand,
            'email' => 'contabilidad_stress_' . $timestamp . '_' . uniqid() . '@test.com',
            'contrasena' => 'Password123!',
            'tipo' => 'Contabilidad'
        ];
        
        // Crear archivo de sesión compartido
        $this->sessionCookieFile = sys_get_temp_dir() . '/stress_session_' . uniqid() . '.txt';
    }
    
    public function init() {
        echo "\n";
        echo "╔════════════════════════════════════════════════════════════════════════════╗\n";
        echo "║                    PRUEBAS DE ESTRÉS - SISTEMA maquinas_recreativas       ║\n";
        echo "╚════════════════════════════════════════════════════════════════════════════╝\n\n";
    }
    
    public function testEjecutarEstres() {
        echo "INICIANDO PRUEBAS DE ESTRÉS\n";
        echo "================================\n\n";
        
        if (!$this->loginDefaultAdmin()) {
            echo "Error crítico: No se pudo iniciar sesión como administrador. Abortando.\n";
            return;
        }
        
        if (!$this->pasoCrearUsuarios()) {
            echo "Error crítico: No se pudieron crear los usuarios. Abortando.\n";
            return;
        }
        
        if (!$this->faseSetupInicial()) {
            echo "Error crítico: No se pudo completar el setup inicial. Abortando.\n";
            return;
        }
        
        // Autenticar todos los tipos de usuario para tener sesiones activas
        if (!$this->autenticarTodosLosUsuarios()) {
            echo "Error crítico: No se pudieron autenticar los usuarios. Abortando.\n";
            return;
        }
        
        echo "\nSETUP COMPLETADO. Iniciando fases de carga...\n";
        
        foreach ($this->loadPhases as $index => $phase) {
            $continuar = $this->ejecutarFaseCarga($index + 1, $phase);
            if (!$continuar) break;
            sleep(2);
        }
        
        $this->mostrarResumen();
    }
    
    /**
     * Autentica todos los tipos de usuario y guarda sus sesiones
     */
    private function autenticarTodosLosUsuarios(): bool {
        echo "PASO 4: Autenticando usuarios para pruebas...\n";
        echo str_repeat("-", 40) . "\n";
        
        $users = [
            'ensamblador' => [
                'usuario' => $this->ensambladorUsuarioAsignado,
                'password' => $this->ensambladorUser['contrasena']
            ],
            'comprobador' => [
                'usuario' => $this->comprobadorUsuarioAsignado,
                'password' => $this->comprobadorUser['contrasena']
            ],
            'mantenimiento' => [
                'usuario' => $this->mantenimientoUsuarioAsignado,
                'password' => $this->mantenimientoUser['contrasena']
            ],
            'logistica' => [
                'usuario' => $this->logisticaUsuarioAsignado,
                'password' => $this->logisticaUser['contrasena']
            ],
            'contabilidad' => [
                'usuario' => $this->contabilidadUsuarioAsignado,
                'password' => $this->contabilidadUser['contrasena']
            ]
        ];
        
        foreach ($users as $type => $credentials) {
            echo "   Autenticando {$type}...\n";
            
            // Usar un cliente separado para cada tipo de usuario
            $sessionFile = sys_get_temp_dir() . '/stress_session_' . $type . '_' . uniqid() . '.txt';
            
            $ch = curl_init('http://localhost:8000/api/public/usuario/login');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HEADER, true);
            curl_setopt($ch, CURLOPT_COOKIEFILE, $sessionFile);
            curl_setopt($ch, CURLOPT_COOKIEJAR, $sessionFile);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
                'usuario_asignado' => $credentials['usuario'],
                'contrasena' => $credentials['password']
            ]));
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode === 200) {
                $this->userSessions[$type] = $sessionFile;
                echo "      ✓ {$type} autenticado correctamente\n";
            } else {
                echo "      ✗ Error autenticando {$type} (HTTP {$httpCode})\n";
                return false;
            }
        }
        
        echo "   ✓ Todos los usuarios autenticados\n\n";
        return true;
    }
    
    private function loginDefaultAdmin(): bool {
        echo "PASO 1: Login como administrador del sistema...\n";
        $this->clearCookies();
        
        $response = $this->request('POST', '/usuario/login', [
            'usuario_asignado' => 'admin_test',
            'contrasena' => 'admin123'
        ]);
        
        if ($this->assertResponseSuccess('Error login admin')) {
            echo "   ✓ Login exitoso como admin_test\n";
            return true;
        }
        return false;
    }
    
    private function crearUsuarioAdmin(array $userData): ?array {
        $response = $this->request('POST', '/administrador/usuarios', $userData);
        if ($this->assertResponseSuccess('Error crear usuario')) {
            return $response;
        }
        return null;
    }
    
    private function obtenerUsuarioPorId(string $userId): ?array {
        $response = $this->request('GET', '/administrador/usuarios');
        if (!$this->assertResponseSuccess('Error obtener usuarios')) {
            return null;
        }
        
        $usuarios = $response['usuarios'] ?? [];
        foreach ($usuarios as $usuario) {
            if ($usuario['id'] === $userId) {
                return $usuario;
            }
        }
        return null;
    }
    
    private function pasoCrearUsuarios() {
        echo "\nPASO 2: Creando usuarios de prueba...\n";
        echo str_repeat("-", 40) . "\n";
        
        echo "   2.1 Creando técnico ensamblador...\n";
        $respEns = $this->crearUsuarioAdmin($this->ensambladorUser);
        if (!$respEns) return false;
        $this->ensambladorId = $respEns['id'] ?? null;
        echo "       Ensamblador ID: {$this->ensambladorId}\n";
        sleep(1);
        
        echo "   2.2 Creando técnico comprobador...\n";
        $respComp = $this->crearUsuarioAdmin($this->comprobadorUser);
        if (!$respComp) return false;
        $this->comprobadorId = $respComp['id'] ?? null;
        echo "       Comprobador ID: {$this->comprobadorId}\n";
        sleep(1);
        
        echo "   2.3 Creando técnico mantenimiento...\n";
        $respMant = $this->crearUsuarioAdmin($this->mantenimientoUser);
        if (!$respMant) return false;
        $this->mantenimientoId = $respMant['id'] ?? null;
        echo "       Mantenimiento ID: {$this->mantenimientoId}\n";
        sleep(1);
        
        echo "   2.4 Creando usuario logística...\n";
        $respLog = $this->crearUsuarioAdmin($this->logisticaUser);
        if (!$respLog) return false;
        $this->logisticaId = $respLog['id'] ?? null;
        echo "       Logística ID: {$this->logisticaId}\n";
        sleep(1);
        
        echo "   2.5 Creando usuario contabilidad...\n";
        $respCont = $this->crearUsuarioAdmin($this->contabilidadUser);
        if (!$respCont) return false;
        $this->contabilidadId = $respCont['id'] ?? null;
        echo "       Contabilidad ID: {$this->contabilidadId}\n";
        sleep(1);
        
        echo "\n   Obteniendo datos de usuarios...\n";
        
        $ensUsuario = $this->obtenerUsuarioPorId($this->ensambladorId);
        $this->ensambladorUsuarioAsignado = $ensUsuario['usuario_asignado'] ?? null;
        
        $compUsuario = $this->obtenerUsuarioPorId($this->comprobadorId);
        $this->comprobadorUsuarioAsignado = $compUsuario['usuario_asignado'] ?? null;
        
        $mantUsuario = $this->obtenerUsuarioPorId($this->mantenimientoId);
        $this->mantenimientoUsuarioAsignado = $mantUsuario['usuario_asignado'] ?? null;
        
        $logUsuario = $this->obtenerUsuarioPorId($this->logisticaId);
        $this->logisticaUsuarioAsignado = $logUsuario['usuario_asignado'] ?? null;
        
        $contUsuario = $this->obtenerUsuarioPorId($this->contabilidadId);
        $this->contabilidadUsuarioAsignado = $contUsuario['usuario_asignado'] ?? null;
        
        echo "      Ensamblador usuario: {$this->ensambladorUsuarioAsignado}\n";
        echo "      Comprobador usuario: {$this->comprobadorUsuarioAsignado}\n";
        echo "      Mantenimiento usuario: {$this->mantenimientoUsuarioAsignado}\n";
        echo "      Logística usuario: {$this->logisticaUsuarioAsignado}\n";
        echo "      Contabilidad usuario: {$this->contabilidadUsuarioAsignado}\n";
        
        echo "\n   ✓ Todos los usuarios creados exitosamente\n\n";
        return true;
    }
    
    private function faseSetupInicial() {
        echo "PASO 3: Preparación de datos base\n";
        echo str_repeat("-", 40) . "\n";
        
        $this->request('POST', '/usuario/logout', []);
        $this->clearCookies();
        
        echo "   3.1 Iniciando sesión como logística...\n";
        $loginResp = $this->request('POST', '/usuario/login', [
            'usuario_asignado' => $this->logisticaUsuarioAsignado,
            'contrasena' => $this->logisticaUser['contrasena']
        ]);
        if (!$this->assertResponseSuccess('Error en login logística')) {
            echo "       Error: No se pudo iniciar sesión como logística\n";
            return false;
        }
        echo "       Login exitoso\n";
        
        echo "   3.2 Creando comercio...\n";
        $comercioData = [
            'nombre' => 'Comercio Stress ' . time(),
            'tipo' => 'Minorista',
            'direccion' => 'Av. Pruebas 123',
            'telefono' => '0999' . rand(100000, 999999)
        ];
        $comercioResp = $this->request('POST', '/comercio/register', $comercioData);
        if (!$this->assertResponseSuccess('Error al crear comercio')) {
            return false;
        }
        $this->comercioId = $comercioResp['idComercio'] ?? null;
        echo "       Comercio ID: {$this->comercioId}\n";
        
        $this->request('POST', '/usuario/logout', []);
        $this->clearCookies();
        
        echo "   3.4 Iniciando sesión como ensamblador...\n";
        $loginResp = $this->request('POST', '/usuario/login', [
            'usuario_asignado' => $this->ensambladorUsuarioAsignado,
            'contrasena' => $this->ensambladorUser['contrasena']
        ]);
        if (!$this->assertResponseSuccess('Error en login ensamblador')) {
            return false;
        }
        echo "       Login exitoso\n";
        
        echo "   3.5 Generando placa...\n";
        $placaResp = $this->request('POST', '/maquina/generar-placa', []);
        if (!$this->assertResponseSuccess('Error al generar placa')) {
            return false;
        }
        $this->placaId = $placaResp['idComponente'] ?? null;
        echo "       Placa ID: {$this->placaId}\n";
        
        echo "   3.6 Generando carcasa...\n";
        $carcasaResp = $this->request('POST', '/maquina/generar-placa', []);
        if (!$this->assertResponseSuccess('Error al generar carcasa')) {
            return false;
        }
        $this->carcasaId = $carcasaResp['idComponente'] ?? null;
        echo "       Carcasa ID: {$this->carcasaId}\n";
        
        $this->request('POST', '/usuario/logout', []);
        $this->clearCookies();
        
        echo "   3.8 Iniciando sesión como logística...\n";
        $loginResp = $this->request('POST', '/usuario/login', [
            'usuario_asignado' => $this->logisticaUsuarioAsignado,
            'contrasena' => $this->logisticaUser['contrasena']
        ]);
        if (!$this->assertResponseSuccess('Error en login logística')) {
            return false;
        }
        echo "       Login exitoso\n";
        
        echo "   3.9 Registrando máquina...\n";
        $maquinaData = [
            'nombre' => 'Máquina Stress ' . time(),
            'tipo' => 'Arcade Clasica',
            'idComercio' => $this->comercioId,
            'idPlaca' => $this->placaId,
            'idCarcasa' => $this->carcasaId,
            'idEnsamblador' => $this->ensambladorId,
            'idComprobador' => $this->comprobadorId
        ];
        
        $this->maquinaData = [
            'nombre' => 'Máquina Stress ' . time(),
            'tipo' => 'Arcade Clasica'
        ];
        
        $maquinaResp = $this->request('POST', '/maquina/register', $maquinaData);
        if (!$this->assertResponseSuccess('Error al registrar máquina')) {
            echo "       HTTP Code: {$this->lastHttpCode}\n";
            return false;
        }
        $this->maquinaId = $maquinaResp['idMaquina'] ?? null;
        echo "       Máquina registrada: {$this->maquinaData['nombre']} (ID: {$this->maquinaId})\n";
        
        $this->request('POST', '/usuario/logout', []);
        $this->clearCookies();
        
        echo "\n   ✓ Datos base listos\n";
        echo "      • Comercio ID: {$this->comercioId}\n";
        echo "      • Máquina ID: {$this->maquinaId}\n";
        echo "      • Placa ID: {$this->placaId}\n";
        echo "      • Carcasa ID: {$this->carcasaId}\n\n";
        
        return true;
    }
    
    private function ejecutarFaseCarga($faseNum, $phase) {
        echo "\nFASE {$faseNum}: {$phase['users']} usuarios - {$phase['description']}\n";
        echo str_repeat("=", 50) . "\n";
        
        $startTime = microtime(true);
        $requests = 0;
        $errors = 0;
        $responseTimes = [];
        
        echo "   Simulando carga por {$phase['duration']} segundos...\n";
        
        $endTime = $startTime + $phase['duration'];
        $iteracion = 0;
        
        while (microtime(true) < $endTime) {
            $iteracion++;
            $userTypes = ['ensamblador', 'comprobador', 'mantenimiento', 'logistica', 'contabilidad'];
            $userType = $userTypes[array_rand($userTypes)];
            
            $requestStart = microtime(true);
            $success = $this->ejecutarRequestConSesion($userType);
            $requestTime = (microtime(true) - $requestStart) * 1000;
            
            $requests++;
            $responseTimes[] = $requestTime;
            if (!$success) $errors++;
            
            if ($iteracion % 50 == 0) {
                echo "      Progreso: {$requests} requests realizados...\n";
            }
            usleep(50000);
        }
        
        $duration = microtime(true) - $startTime;
        $rps = $requests / $duration;
        $errorRate = ($errors / max($requests, 1)) * 100;
        sort($responseTimes);
        $p95 = $responseTimes[floor(count($responseTimes) * 0.95)] ?? 0;
        $p99 = $responseTimes[floor(count($responseTimes) * 0.99)] ?? 0;
        
        $this->phaseResults[] = [
            'users' => $phase['users'],
            'requests' => $requests,
            'errors' => $errors,
            'error_rate' => round($errorRate, 2),
            'rps' => round($rps, 2),
            'p95' => round($p95, 2),
            'p99' => round($p99, 2),
        ];
        
        echo "\n    RESULTADOS FASE {$faseNum}:\n";
        echo "      • Requests: {$requests}\n";
        echo "      • Errores: {$errors} (" . round($errorRate, 2) . "%)\n";
        echo "      • RPS: " . round($rps, 2) . " req/s\n";
        echo "      • Tiempo respuesta (p95): " . round($p95, 2) . "ms\n";
        
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
     * Ejecuta una request usando la sesión del tipo de usuario especificado
     */
    private function ejecutarRequestConSesion($userType) {
        $publicEndpoints = ['/health', '/test-db'];
        
        $endpoints = [
            'ensamblador' => ['/componentes/disponibles'],
            'comprobador' => ['/componentes/disponibles'],
            'mantenimiento' => ['/componentes/disponibles'],
            'logistica' => ['/comercio/all'],
            'contabilidad' => ['/contabilidad/recaudaciones']
        ];
        
        $rand = mt_rand(1, 100);
        if ($rand <= 30) {
            $endpoint = $publicEndpoints[array_rand($publicEndpoints)];
            // Para endpoints públicos no necesitamos sesión
            return $this->ejecutarRequestPublica($endpoint);
        } else {
            $userEndpoints = $endpoints[$userType] ?? $publicEndpoints;
            $endpoint = $userEndpoints[array_rand($userEndpoints)];
            return $this->ejecutarRequestConSesionEspecifica($userType, $endpoint);
        }
    }
    
    /**
     * Ejecuta una request pública (sin autenticación)
     */
    private function ejecutarRequestPublica($endpoint) {
        $ch = curl_init('http://localhost:8000/api/public' . $endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_HEADER, true);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        $this->lastHttpCode = $httpCode;
        return $httpCode < 400;
    }
    
    /**
     * Ejecuta una request usando la sesión guardada de un tipo de usuario
     */
    private function ejecutarRequestConSesionEspecifica($userType, $endpoint) {
        if (!isset($this->userSessions[$userType])) {
            return false;
        }
        
        $sessionFile = $this->userSessions[$userType];
        
        $ch = curl_init('http://localhost:8000/api/public' . $endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $sessionFile);
        curl_setopt($ch, CURLOPT_HEADER, true);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        $this->lastHttpCode = $httpCode;
        return $httpCode < 400;
    }
    
    private function mostrarResumen() {
        echo "\n\n";
        echo "RESUMEN FINAL DE PRUEBAS DE ESTRÉS\n";
        echo "======================================\n\n";
        
        echo str_pad("Usuarios", 12) . str_pad("Requests", 12) . str_pad("Errores", 12) . str_pad("Tasa Error", 12) . str_pad("RPS", 10) . str_pad("p95 (ms)", 10) . "Estado\n";
        echo str_repeat("-", 80) . "\n";
        
        $breakpoint = null;
        foreach ($this->phaseResults as $result) {
            $estado = $result['error_rate'] < 5 ? "✓ OK" : ($result['error_rate'] < 10 ? "⚠ Lento" : "✗ Falla");
            echo str_pad($result['users'], 12) . str_pad($result['requests'], 12) . str_pad($result['errors'], 12) . str_pad($result['error_rate'] . "%", 12) . str_pad($result['rps'], 10) . str_pad($result['p95'], 10) . $estado . "\n";
            if ($result['error_rate'] >= 10 && !$breakpoint) $breakpoint = $result['users'];
        }
        
        echo "\n\nANÁLISIS DEL PUNTO DE QUIEBRE\n";
        echo "--------------------------------\n";
        if ($breakpoint) {
            echo " El sistema COMIENZA A FALLAR a partir de {$breakpoint} usuarios concurrentes\n";
        } else {
            echo " El sistema soportó TODAS las cargas de prueba\n";
        }
        
        $this->guardarResultados();
        $this->limpiarSesiones();
    }
    
    private function limpiarSesiones() {
        foreach ($this->userSessions as $sessionFile) {
            if (file_exists($sessionFile)) {
                @unlink($sessionFile);
            }
        }
        if (file_exists($this->sessionCookieFile)) {
            @unlink($this->sessionCookieFile);
        }
    }
    
    private function guardarResultados() {
        $filename = __DIR__ . '/stress_test_results_' . date('Y-m-d_H-i-s') . '.json';
        file_put_contents($filename, json_encode([
            'timestamp' => date('Y-m-d H:i:s'),
            'results' => $this->phaseResults,
            'breakpoint' => $this->findBreakpoint()
        ], JSON_PRETTY_PRINT));
        echo "\n Resultados guardados en: {$filename}\n";
    }
    
    private function findBreakpoint() {
        foreach ($this->phaseResults as $result) {
            if ($result['error_rate'] >= 10 || $result['p95'] > 3000) return $result['users'];
        }
        return null;
    }
    
    public function __destruct() {
        $this->limpiarSesiones();
        parent::__destruct();
    }
}