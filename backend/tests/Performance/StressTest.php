<?php
// tests/Performance/StressTest.php
// Versión adaptada al estilo de las pruebas funcionales

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
    
    private $comercioId;
    private $maquinaId;
    private $placaId;
    
    // Configuración de fases de carga
    private $loadPhases = [
        ['users' => 10,  'duration' => 20, 'description' => '✅ Carga muy ligera'],
        ['users' => 25, 'duration' => 20, 'description' => '✅ Carga ligera'],
        ['users' => 50,  'duration' => 20, 'description' => '⚠️ Carga media'],
        ['users' => 75, 'duration' => 20, 'description' => '⚠️ Carga alta'],
        ['users' => 100, 'duration' => 20, 'description' => '❌ Carga crítica'],
    ];
    
    // Almacenar resultados por fase
    private $phaseResults = [];
    
    public function __construct() {
        parent::__construct();
    }
    
    /**
     * Inicializar la prueba
     */
    public function init() {
        echo "\n";
        echo "╔════════════════════════════════════════════════════════════╗\n";
        echo "║        PRUEBAS DE ESTRÉS - SISTEMA maquinas_recreativas               ║\n";
        echo "╚════════════════════════════════════════════════════════════╝\n\n";
    }
    
    /**
     * PRUEBA PRINCIPAL: Ejecutar todas las fases de estrés
     */
    public function testEjecutarEstres() {
        echo "🚀 INICIANDO PRUEBAS DE ESTRÉS\n";
        echo "================================\n\n";
        
        // PASO 1: Crear todos los usuarios necesarios
        $this->pasoCrearUsuarios();
        
        // PASO 2: Setup inicial (crear datos base)
        $setupOk = $this->faseSetupInicial();
        if (!$setupOk) {
            echo "❌ Error en setup inicial. Abortando pruebas.\n";
            return;
        }
        
        echo "\n✅ SETUP COMPLETADO. Iniciando fases de carga...\n";
        
        // Ejecutar cada fase de carga
        foreach ($this->loadPhases as $index => $phase) {
            $continuar = $this->ejecutarFaseCarga($index + 1, $phase);
            if (!$continuar) {
                break;
            }
        }
        
        // Mostrar resumen final
        $this->mostrarResumen();
    }
    
    /**
     * PASO 1: Crear todos los usuarios necesarios
     */
    private function pasoCrearUsuarios() {
        echo "📝 PASO 1: Creando usuarios de prueba...\n";
        echo str_repeat("-", 40) . "\n";
        
        // Limpiar cookies antes de empezar
        $this->clearCookies();
        
        // 1.1 Crear Técnico Ensamblador
        echo "   1.1 Creando técnico ensamblador...\n";
        $this->ensambladorUser = [
            'nombre' => 'Ensamblador',
            'apellido' => 'Stress',
            'ci' => '10000001' . rand(10, 99),
            'email' => 'ensamblador_stress_' . uniqid() . '@test.com',
            'usuario_asignado' => 'ens_' . substr(uniqid(), -8),
            'contrasena' => 'password123',
            'tipo' => 'Tecnico',
            'especialidad' => 'Ensamblador'
        ];
        
        $respEns = $this->request('POST', '/usuario/register', $this->ensambladorUser);
        $this->assertResponseSuccess('Error al crear ensamblador');
        $this->ensambladorId = $respEns['userId'] ?? null;
        echo "      ✅ Ensamblador ID: {$this->ensambladorId}\n";
        
        // 1.2 Crear Técnico Comprobador
        echo "   1.2 Creando técnico comprobador...\n";
        $this->comprobadorUser = [
            'nombre' => 'Comprobador',
            'apellido' => 'Stress',
            'ci' => '20000002' . rand(10, 99),
            'email' => 'comprobador_stress_' . uniqid() . '@test.com',
            'usuario_asignado' => 'comp_' . substr(uniqid(), -8),
            'contrasena' => 'password123',
            'tipo' => 'Tecnico',
            'especialidad' => 'Comprobador'
        ];
        
        $respComp = $this->request('POST', '/usuario/register', $this->comprobadorUser);
        $this->assertResponseSuccess('Error al crear comprobador');
        $this->comprobadorId = $respComp['userId'] ?? null;
        echo "      ✅ Comprobador ID: {$this->comprobadorId}\n";
        
        // 1.3 Crear Técnico Mantenimiento
        echo "   1.3 Creando técnico mantenimiento...\n";
        $this->mantenimientoUser = [
            'nombre' => 'Mantenimiento',
            'apellido' => 'Stress',
            'ci' => '30000003' . rand(10, 99),
            'email' => 'mantenimiento_stress_' . uniqid() . '@test.com',
            'usuario_asignado' => 'mant_' . substr(uniqid(), -8),
            'contrasena' => 'password123',
            'tipo' => 'Tecnico',
            'especialidad' => 'Mantenimiento'
        ];
        
        $respMant = $this->request('POST', '/usuario/register', $this->mantenimientoUser);
        $this->assertResponseSuccess('Error al crear mantenimiento');
        $this->mantenimientoId = $respMant['userId'] ?? null;
        echo "      ✅ Mantenimiento ID: {$this->mantenimientoId}\n";
        
        // 1.4 Crear Logística
        echo "   1.4 Creando usuario logística...\n";
        $this->logisticaUser = [
            'nombre' => 'Logistica',
            'apellido' => 'Stress',
            'ci' => '40000004' . rand(10, 99),
            'email' => 'logistica_stress_' . uniqid() . '@test.com',
            'usuario_asignado' => 'log_' . substr(uniqid(), -8),
            'contrasena' => 'password123',
            'tipo' => 'Logistica'
        ];
        
        $respLog = $this->request('POST', '/usuario/register', $this->logisticaUser);
        $this->assertResponseSuccess('Error al crear logística');
        $this->logisticaId = $respLog['userId'] ?? null;
        echo "      ✅ Logística ID: {$this->logisticaId}\n";
        
        // 1.5 Crear Contabilidad
        echo "   1.5 Creando usuario contabilidad...\n";
        $this->contabilidadUser = [
            'nombre' => 'Contabilidad',
            'apellido' => 'Stress',
            'ci' => '50000005' . rand(10, 99),
            'email' => 'contabilidad_stress_' . uniqid() . '@test.com',
            'usuario_asignado' => 'cont_' . substr(uniqid(), -8),
            'contrasena' => 'password123',
            'tipo' => 'Contabilidad'
        ];
        
        $respCont = $this->request('POST', '/usuario/register', $this->contabilidadUser);
        $this->assertResponseSuccess('Error al crear contabilidad');
        $this->contabilidadId = $respCont['userId'] ?? null;
        echo "      ✅ Contabilidad ID: {$this->contabilidadId}\n";
        
        // 1.6 Crear Administrador
        echo "   1.6 Creando usuario administrador...\n";
        $this->adminUser = [
            'nombre' => 'Admin',
            'apellido' => 'Stress',
            'ci' => '60000006' . rand(10, 99),
            'email' => 'admin_stress_' . uniqid() . '@test.com',
            'usuario_asignado' => 'admin_' . substr(uniqid(), -8),
            'contrasena' => 'password123',
            'tipo' => 'Administrador'
        ];
        
        $respAdmin = $this->request('POST', '/usuario/register', $this->adminUser);
        $this->assertResponseSuccess('Error al crear admin');
        $this->adminId = $respAdmin['userId'] ?? null;
        echo "      ✅ Admin ID: {$this->adminId}\n";
        
        echo "\n   ✅ Todos los usuarios creados exitosamente\n\n";
    }
    
    /**
     * Fase 0: Crear datos base para las pruebas
     */
    private function faseSetupInicial() {
        echo "\n📦 PASO 2: Preparación de datos base\n";
        echo str_repeat("-", 40) . "\n";
        
        // 2.1 Login como logística
        echo "   2.1 Iniciando sesión como logística...\n";
        $this->clearCookies();
        $loginResp = $this->request('POST', '/usuario/login', [
            'usuario_asignado' => $this->logisticaUser['usuario_asignado'],
            'contrasena' => $this->logisticaUser['contrasena']
        ]);
        
        if (!$this->assertResponseSuccess('Error en login logística')) {
            echo "   ❌ No se pudo iniciar sesión como logística\n";
            return false;
        }
        echo "      ✅ Login exitoso\n";
        
        // 2.2 Crear comercio
        echo "   2.2 Creando comercio...\n";
        $comercioData = [
            'nombre' => 'Comercio Stress ' . uniqid(),
            'tipo' => 'Minorista',
            'direccion' => 'Av. Pruebas 123',
            'telefono' => '0999' . rand(100000, 999999)
        ];
        
        $comercioResp = $this->request('POST', '/comercio/register', $comercioData);
        if (!$this->assertResponseSuccess('Error al crear comercio')) {
            echo "   ❌ No se pudo crear comercio\n";
            return false;
        }
        echo "      ✅ Comercio creado\n";
        
        // 2.3 Obtener ID del comercio
        echo "   2.3 Obteniendo ID del comercio...\n";
        sleep(1); // Pequeña pausa para que se registre
        $comercios = $this->request('GET', '/comercio/all');
        if (isset($comercios['comercios']) && is_array($comercios['comercios'])) {
            foreach ($comercios['comercios'] as $c) {
                if ($c['Nombre'] === $comercioData['nombre']) {
                    $this->comercioId = $c['ID_Comercio'];
                    echo "      ✅ Comercio encontrado ID: {$this->comercioId}\n";
                    break;
                }
            }
        }
        
        if (!$this->comercioId) {
            echo "   ❌ No se pudo obtener ID del comercio\n";
            return false;
        }
        
        // 2.4 Generar placa
        echo "   2.4 Generando placa...\n";
        $placaResp = $this->request('POST', '/maquina/generar-placa', [
            'ID_Usuario' => $this->logisticaId
        ]);
        
        if (!$this->assertResponseSuccess('Error al generar placa')) {
            echo "   ❌ No se pudo generar placa\n";
            return false;
        }
        
        if (isset($placaResp['id_componente'])) {
            $this->placaId = $placaResp['id_componente'];
            echo "      ✅ Placa generada ID: {$this->placaId}\n";
        }
        
        if (!$this->placaId) {
            echo "   ❌ No se pudo obtener ID de placa\n";
            return false;
        }
        
        // 2.5 Crear máquina (AHORA DEBERÍA FUNCIONAR porque tenemos técnicos)
        echo "   2.5 Creando máquina...\n";
        $maquinaData = [
            'nombre' => 'Máquina Stress ' . uniqid(),
            'tipo' => 'Arcade',
            'idComercio' => $this->comercioId,
            'idUsuarioLogistica' => $this->logisticaId,
            'idPlaca' => $this->placaId,
            'idCarcasa' => $this->placaId
        ];
        
        $maquinaResp = $this->request('POST', '/maquina/register', $maquinaData);
        
        // Verificar la respuesta
        if (isset($maquinaResp['success']) && $maquinaResp['success'] === true) {
            if (isset($maquinaResp['idMaquina'])) {
                $this->maquinaId = $maquinaResp['idMaquina'];
                echo "      ✅ Máquina creada ID: {$this->maquinaId}\n";
            } else {
                echo "      ⚠️ Respuesta exitosa pero sin ID: " . json_encode($maquinaResp) . "\n";
                // Intentar con un ID fijo para pruebas
                $this->maquinaId = '00000000-0000-0000-0000-000000000001';
            }
        } else {
            echo "      ❌ Error al crear máquina: " . json_encode($maquinaResp) . "\n";
            return false;
        }
        
        // 2.6 Logout
        echo "   2.6 Cerrando sesión...\n";
        $this->request('POST', '/usuario/logout', []);
        $this->clearCookies();
        
        echo "\n   ✅ Datos base listos\n";
        echo "      • Comercio ID: {$this->comercioId}\n";
        echo "      • Máquina ID: {$this->maquinaId}\n";
        echo "      • Placa ID: {$this->placaId}\n\n";
        
        return true;
    }
    
    /**
     * Ejecutar una fase específica de carga
     */
    private function ejecutarFaseCarga($faseNum, $phase) {
        echo "\n📊 FASE {$faseNum}: {$phase['users']} usuarios - {$phase['description']}\n";
        echo str_repeat("=", 50) . "\n";
        
        $startTime = microtime(true);
        $requests = 0;
        $errors = 0;
        $responseTimes = [];
        
        // Login con cada tipo de usuario
        echo "   Obteniendo tokens de autenticación...\n";
        $tokens = $this->loginTodosLosUsuarios();
        
        if (empty($tokens)) {
            echo "   ❌ No se pudo obtener tokens de autenticación\n";
            return false;
        }
        
        echo "   Iniciando simulación de carga por {$phase['duration']} segundos...\n";
        
        // Ejecutar requests durante la duración de la fase
        $endTime = $startTime + $phase['duration'];
        $iteracion = 0;
        
        while (microtime(true) < $endTime) {
            $iteracion++;
            
            // Seleccionar un usuario aleatorio
            $userTypes = array_keys($tokens);
            if (empty($userTypes)) continue;
            
            $userType = $userTypes[array_rand($userTypes)];
            $user = $tokens[$userType];
            
            // Realizar request según tipo de usuario
            $requestStart = microtime(true);
            $success = $this->ejecutarRequestUsuario($userType, $user);
            $requestTime = (microtime(true) - $requestStart) * 1000; // en ms
            
            $requests++;
            $responseTimes[] = $requestTime;
            
            if (!$success) {
                $errors++;
            }
            
            // Pequeña pausa entre requests para no saturar
            usleep(50000); // 0.05 segundos
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
        echo "\n   📈 RESULTADOS FASE {$faseNum}:\n";
        echo "      • Requests: {$requests}\n";
        echo "      • Errores: {$errors} (" . round($errorRate, 2) . "%)\n";
        echo "      • RPS: " . round($rps, 2) . " req/s\n";
        echo "      • Tiempo respuesta (p95): " . round($p95, 2) . "ms\n";
        echo "      • Tiempo respuesta (p99): " . round($p99, 2) . "ms\n";
        
        // Determinar estado
        if ($errorRate > 10 || $p95 > 2000) {
            echo "   ❌ SISTEMA COLAPSADO\n";
            if ($faseNum < count($this->loadPhases)) {
                echo "   ⚠️  Deteniendo pruebas - Punto de quiebre alcanzado\n";
                return false;
            }
        } elseif ($errorRate > 5 || $p95 > 1000) {
            echo "   ⚠️  SISTEMA DEGRADADO\n";
        } else {
            echo "   ✅ SISTEMA ESTABLE\n";
        }
        
        return true;
    }
    
    /**
     * Login con todos los tipos de usuario
     */
    private function loginTodosLosUsuarios() {
        $tokens = [];
        
        $users = [
            'admin' => ['creds' => $this->adminUser, 'id' => $this->adminId],
            'ensamblador' => ['creds' => $this->ensambladorUser, 'id' => $this->ensambladorId],
            'comprobador' => ['creds' => $this->comprobadorUser, 'id' => $this->comprobadorId],
            'mantenimiento' => ['creds' => $this->mantenimientoUser, 'id' => $this->mantenimientoId],
            'logistica' => ['creds' => $this->logisticaUser, 'id' => $this->logisticaId],
            'contabilidad' => ['creds' => $this->contabilidadUser, 'id' => $this->contabilidadId]
        ];
        
        foreach ($users as $type => $user) {
            if (!$user['id']) {
                echo "      ⚠️  Usuario {$type} no tiene ID, saltando...\n";
                continue;
            }
            
            $this->clearCookies(); // Limpiar cookies para cada login
            
            $response = $this->request('POST', '/usuario/login', [
                'usuario_asignado' => $user['creds']['usuario_asignado'],
                'contrasena' => $user['creds']['contrasena']
            ]);
            
            if ($this->lastHttpCode === 200 && isset($response['success']) && $response['success']) {
                $tokens[$type] = [
                    'id' => $user['id'],
                    'cookies' => $this->cookies
                ];
                echo "      ✅ Login {$type} exitoso\n";
            } else {
                echo "      ❌ Login {$type} falló (HTTP {$this->lastHttpCode})\n";
            }
        }
        
        return $tokens;
    }
    
    /**
     * Ejecutar un request específico según tipo de usuario
     */
    private function ejecutarRequestUsuario($userType, $user) {
        // Restaurar cookies del usuario
        $this->cookies = $user['cookies'];
        
        $rand = mt_rand(1, 100);
        $success = true;
        
        try {
            switch($userType) {
                case 'admin':
                    if ($rand <= 30) {
                        $this->request('GET', '/administrador/usuarios');
                    } elseif ($rand <= 60) {
                        $this->request('GET', "/administrador/usuarios/{$user['id']}");
                    } else {
                        $this->request('GET', '/historial-actividades?usuarioId=' . $user['id']);
                    }
                    break;
                    
                case 'ensamblador':
                    if ($rand <= 40) {
                        $this->request('GET', "/maquina/ensamblador/{$user['id']}");
                    } elseif ($rand <= 70) {
                        $this->request('GET', '/componentes/disponibles');
                    } else {
                        $this->request('GET', "/componentes/en-uso/{$user['id']}");
                    }
                    break;
                    
                case 'comprobador':
                    if ($rand <= 40) {
                        $this->request('GET', "/maquina/comprobador/{$user['id']}");
                    } elseif ($rand <= 70) {
                        $this->request('GET', '/componentes/disponibles');
                    } else {
                        $this->request('GET', "/componentes/en-uso/{$user['id']}");
                    }
                    break;
                    
                case 'mantenimiento':
                    if ($rand <= 40) {
                        $this->request('GET', "/maquina/mantenimiento/{$user['id']}");
                    } elseif ($rand <= 70) {
                        $this->request('GET', '/componentes/disponibles');
                    } else {
                        $this->request('GET', "/componentes/en-uso/{$user['id']}");
                    }
                    break;
                    
                case 'logistica':
                    if ($rand <= 25) {
                        $this->request('GET', '/maquina/distribucion');
                    } elseif ($rand <= 50) {
                        $this->request('GET', '/distribucion/informes');
                    } elseif ($rand <= 75) {
                        $this->request('GET', '/comercio/all');
                    } else {
                        $this->request('GET', "/usuario/profile/{$user['id']}");
                    }
                    break;
                    
                case 'contabilidad':
                    if ($rand <= 20) {
                        $this->request('GET', '/contabilidad/recaudaciones');
                    } elseif ($rand <= 40) {
                        $this->request('GET', '/contabilidad/resumen-recaudaciones?limit=10');
                    } elseif ($rand <= 60) {
                        $this->request('GET', '/contabilidad/maquinas-recaudacion');
                    } elseif ($rand <= 80) {
                        $this->request('GET', "/usuario/profile/{$user['id']}");
                    } else {
                        $this->request('GET', '/contabilidad/maquinas-operativas-por-comercio?ID_Comercio=' . $this->comercioId);
                    }
                    break;
            }
        } catch (Exception $e) {
            $success = false;
        }
        
        // Verificar si hubo error HTTP
        if ($this->lastHttpCode >= 400 && $this->lastHttpCode != 404) {
            $success = false;
        }
        
        return $success;
    }
    
    /**
     * Mostrar resumen final de todas las fases
     */
    private function mostrarResumen() {
        echo "\n\n";
        echo "📊 RESUMEN FINAL DE PRUEBAS DE ESTRÉS\n";
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
            $estado = $result['error_rate'] < 5 ? "✅ OK" : 
                     ($result['error_rate'] < 10 ? "⚠️ Lento" : "❌ Falla");
            
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
        echo "🔍 ANÁLISIS DEL PUNTO DE QUIEBRE\n";
        echo "--------------------------------\n";
        
        if ($breakpoint) {
            echo "❌ El sistema COMIENZA A FALLAR a partir de {$breakpoint} usuarios\n";
            
            echo "\n💡 RECOMENDACIONES:\n";
            if ($breakpoint <= 25) {
                echo "   • Revisar configuración del servidor web\n";
                echo "   • Aumentar límites de conexiones simultáneas\n";
                echo "   • Optimizar consultas a base de datos\n";
            } elseif ($breakpoint <= 50) {
                echo "   • Implementar caché para consultas frecuentes\n";
                echo "   • Optimizar índices en tablas más utilizadas\n";
                echo "   • Considerar aumentar recursos del servidor\n";
            } else {
                echo "   • Implementar balanceador de carga\n";
                echo "   • Usar caché distribuido (Redis/Memcached)\n";
                echo "   • Escalar horizontalmente\n";
            }
        } else {
            echo "✅ El sistema soportó TODAS las cargas de prueba\n";
            echo "   El punto de quiebre está por encima de " . end($this->phaseResults)['users'] . " usuarios\n";
        }
        
        // Guardar resultados en archivo
        $this->guardarResultados();
    }
    
    /**
     * Guardar resultados en archivo JSON
     */
    private function guardarResultados() {
        $filename = 'stress_test_results_' . date('Y-m-d_H-i-s') . '.json';
        $data = [
            'timestamp' => date('Y-m-d H:i:s'),
            'total_phases' => count($this->phaseResults),
            'results' => $this->phaseResults,
            'breakpoint' => $this->findBreakpoint()
        ];
        
        file_put_contents($filename, json_encode($data, JSON_PRETTY_PRINT));
        echo "\n📁 Resultados guardados en: {$filename}\n";
    }
    
    /**
     * Encontrar el punto de quiebre
     */
    private function findBreakpoint() {
        foreach ($this->phaseResults as $result) {
            if ($result['error_rate'] >= 10 || $result['p95'] > 2000) {
                return $result['users'];
            }
        }
        return null;
    }
}