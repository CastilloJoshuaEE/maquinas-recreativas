<?php
// tests/Functional/UserFlowsTest.php

require_once __DIR__ . '/HttpTestCase.php';

class UserFlowsTest extends HttpTestCase {
    private $logisticaUser;
    private $ensambladorUser;
    private $comprobadorUser;
    private $comercioData;
    private $maquinaData;
    
    private $logisticaId;
    private $ensambladorId;
    private $comprobadorId;
    private $comercioId;
    private $placaId;
    private $carcasaId;
    private $maquinaId;
    
    private $logisticaUsuarioAsignado;
    private $ensambladorUsuarioAsignado;
    private $comprobadorUsuarioAsignado;
    
    public function __construct() {
        parent::__construct();
        
        $timestamp = time();
        
        $this->logisticaUser = [
            'nombre' => 'Logistica',
            'apellido' => 'Prueba',
            'ci' => '12345678' . rand(10, 99),
            'email' => 'logistica_' . $timestamp . '_' . uniqid() . '@test.com',
            'contrasena' => 'Password123!',
            'tipo' => 'Logistica'
        ];
        
        $this->ensambladorUser = [
            'nombre' => 'Ensamblador',
            'apellido' => 'Tecnico',
            'ci' => '87654321' . rand(10, 99),
            'email' => 'ensamblador_' . $timestamp . '_' . uniqid() . '@test.com',
            'contrasena' => 'Password123!',
            'tipo' => 'Tecnico',
            'especialidad' => 'Ensamblador'
        ];
        
        $this->comprobadorUser = [
            'nombre' => 'Comprobador',
            'apellido' => 'Tecnico',
            'ci' => '11223344' . rand(10, 99),
            'email' => 'comprobador_' . $timestamp . '_' . uniqid() . '@test.com',
            'contrasena' => 'Password123!',
            'tipo' => 'Tecnico',
            'especialidad' => 'Comprobador'
        ];
        
        $this->comercioData = [
            'nombre' => 'Comercio Test ' . $timestamp,
            'tipo' => 'Minorista',
            'direccion' => 'Av. Principal 123',
            'telefono' => '0999' . rand(100000, 999999)
        ];
        
        $this->maquinaData = [
            'nombre' => 'Maquina Arcade ' . $timestamp,
            'tipo' => 'Arcade Clasica'
        ];
    }
    
    public function testFlujoCompletoUsuario() {
        echo "\nINICIANDO FLUJO COMPLETO (LOGISTICA -> ENSAMBLADOR -> COMPROBADOR)\n";
        echo "================================================================\n\n";
        
        $this->pasoRegistrarUsuarios();
        
        echo "\nPARTE 1: LOGISTICA (crea comercio)\n";
        echo "-----------------------------------\n";
        $this->pasoLoginLogistica();
        $this->pasoRegistrarComercio();
        $this->pasoLogout();
        
        echo "\nPARTE 2: TECNICO ENSAMBLADOR (genera placa y carcasa)\n";
        echo "-----------------------------------------------------\n";
        $this->pasoLoginEnsamblador();
        $this->pasoGenerarPlaca();
        $this->pasoGenerarCarcasa();
        $this->pasoLogout();
        
        echo "\nPARTE 3: LOGISTICA (registra maquina)\n";
        echo "------------------------------------\n";
        $this->pasoLoginLogistica();
        $this->pasoRegistrarMaquina();
        $this->pasoLogout();
        
        echo "\nPARTE 4: TECNICO ENSAMBLADOR (monta componentes)\n";
        echo "------------------------------------------------\n";
        $this->pasoLoginEnsamblador();
        $this->pasoVerMaquinasEnsamblador();
        $this->pasoRegistrarMontaje();
        $this->pasoEnviarAComprobacion();
        $this->pasoLogout();
        
        echo "\nPARTE 5: TECNICO COMPROBADOR (verifica y aprueba)\n";
        echo "--------------------------------------------------\n";
        $this->pasoLoginComprobador();
        $this->pasoVerMaquinasComprobador();
        $this->pasoAprobarMaquina();
        
        echo "\nFLUJO COMPLETO EXITOSO\n";
    }
    
    private function pasoRegistrarUsuarios() {
        echo "Paso 1: Registrando usuarios...\n";
        
        // Registrar logistica
        $responseLog = $this->request('POST', '/usuario/register', $this->logisticaUser);
        if ($this->assertResponseSuccess('Error al registrar logistica')) {
            $this->logisticaId = $responseLog['userId'] ?? null;
            $this->logisticaUsuarioAsignado = $responseLog['usuario_asignado'] ?? null;
            $this->assertNotNull($this->logisticaId, 'No se recibio ID de logistica');
            echo "   Logistica registrado: {$this->logisticaUsuarioAsignado} (ID: {$this->logisticaId})\n";
        }
        
        sleep(1);
        
        // Registrar ensamblador
        $responseEns = $this->request('POST', '/usuario/register', $this->ensambladorUser);
        if ($this->assertResponseSuccess('Error al registrar ensamblador')) {
            $this->ensambladorId = $responseEns['userId'] ?? null;
            $this->ensambladorUsuarioAsignado = $responseEns['usuario_asignado'] ?? null;
            $this->assertNotNull($this->ensambladorId, 'No se recibio ID de ensamblador');
            echo "   Ensamblador registrado: {$this->ensambladorUsuarioAsignado} (ID: {$this->ensambladorId})\n";
        }
        
        sleep(1);
        
        // Registrar comprobador
        $responseComp = $this->request('POST', '/usuario/register', $this->comprobadorUser);
        if ($this->assertResponseSuccess('Error al registrar comprobador')) {
            $this->comprobadorId = $responseComp['userId'] ?? null;
            $this->comprobadorUsuarioAsignado = $responseComp['usuario_asignado'] ?? null;
            $this->assertNotNull($this->comprobadorId, 'No se recibio ID de comprobador');
            echo "   Comprobador registrado: {$this->comprobadorUsuarioAsignado} (ID: {$this->comprobadorId})\n";
        }
    }
    
    private function pasoLoginLogistica() {
        echo "Iniciando sesion como logistica...\n";
        
        $response = $this->request('POST', '/usuario/login', [
            'usuario_asignado' => $this->logisticaUsuarioAsignado,
            'contrasena' => $this->logisticaUser['contrasena']
        ]);
        
        if ($this->assertResponseSuccess('Error al iniciar sesion como logistica')) {
            echo "   Login exitoso como: {$this->logisticaUsuarioAsignado}\n";
        }
    }
    
    private function pasoLoginEnsamblador() {
        echo "Iniciando sesion como ensamblador...\n";
        
        $response = $this->request('POST', '/usuario/login', [
            'usuario_asignado' => $this->ensambladorUsuarioAsignado,
            'contrasena' => $this->ensambladorUser['contrasena']
        ]);
        
        if ($this->assertResponseSuccess('Error al iniciar sesion como ensamblador')) {
            echo "   Login exitoso como: {$this->ensambladorUsuarioAsignado}\n";
        }
    }
    
    private function pasoLoginComprobador() {
        echo "Iniciando sesion como comprobador...\n";
        
        $response = $this->request('POST', '/usuario/login', [
            'usuario_asignado' => $this->comprobadorUsuarioAsignado,
            'contrasena' => $this->comprobadorUser['contrasena']
        ]);
        
        if ($this->assertResponseSuccess('Error al iniciar sesion como comprobador')) {
            echo "   Login exitoso como: {$this->comprobadorUsuarioAsignado}\n";
        }
    }
    
    private function pasoLogout() {
        echo "Cerrando sesion...\n";
        $this->request('POST', '/usuario/logout', []);
        $this->clearCookies();
        echo "   Sesion cerrada\n";
    }
    
    private function pasoRegistrarComercio() {
        echo "Registrando comercio...\n";
        
        $response = $this->request('POST', '/comercio/register', $this->comercioData);
        
        if ($this->assertResponseSuccess('Error al registrar comercio')) {
            $this->comercioId = $response['idComercio'] ?? null;
            $this->assertNotNull($this->comercioId, 'No se recibio ID del comercio');
            echo "   Comercio registrado: {$this->comercioData['nombre']} (ID: {$this->comercioId})\n";
        }
    }
    
    private function pasoGenerarPlaca() {
        echo "Generando placa...\n";
        
        $response = $this->request('POST', '/maquina/generar-placa', []);
        
        if ($this->assertResponseSuccess('Error al generar placa')) {
            $this->assertArrayHasKey('placa', $response, 'No se recibio numero de placa');
            $this->placaId = $response['idComponente'] ?? null;
            $this->assertNotNull($this->placaId, 'No se recibio ID de placa');
            echo "   Placa generada: {$response['placa']} (ID: {$this->placaId})\n";
        }
    }
    
    private function pasoGenerarCarcasa() {
        echo "Generando carcasa...\n";
        
      
        $response = $this->request('POST', '/maquina/generar-placa', []);
        
        if ($this->assertResponseSuccess('Error al generar carcasa')) {
            $this->carcasaId = $response['idComponente'] ?? null;
            $this->assertNotNull($this->carcasaId, 'No se recibio ID de carcasa');
            echo "   Carcasa generada (ID: {$this->carcasaId})\n";
        }
    }
    
    private function pasoRegistrarMaquina() {
        echo "Registrando maquina con placa y carcasa...\n";
    $maquinaData = [
        'nombre' => $this->maquinaData['nombre'],
        'tipo' => $this->maquinaData['tipo'],
        'idComercio' => $this->comercioId,
        'idPlaca' => $this->placaId,
        'idCarcasa' => $this->carcasaId,
        'idEnsamblador' => $this->ensambladorId,   // ← agregar
        'idComprobador' => $this->comprobadorId    // ← agregar
    ];
    
        
        $response = $this->request('POST', '/maquina/register', $maquinaData);
        
        if ($this->assertResponseSuccess('Error al registrar maquina')) {
            $this->assertArrayHasKey('idMaquina', $response, 'No se recibio ID de maquina');
            $this->maquinaId = $response['idMaquina'];
            echo "   Maquina registrada: {$this->maquinaData['nombre']} (ID: {$this->maquinaId})\n";
        }
    }
 private function pasoVerMaquinasEnsamblador() {
    echo "Verificando maquinas asignadas al ensamblador...\n";
    
    $response = $this->request('GET', "/maquina/ensamblador/{$this->ensambladorId}");
    
    if ($this->assertResponseSuccess('Error al obtener maquinas del ensamblador')) {
        // Ahora la respuesta tiene estructura {success: true, maquinas: [...]}
        $maquinas = $response['maquinas'] ?? [];
        $this->assertArrayHasKey('maquinas', $response, 'Respuesta no contiene maquinas');
        
        $maquinaEncontrada = false;
        foreach ($maquinas as $maquina) {
            if (isset($maquina['id']) && $maquina['id'] === $this->maquinaId) {
                $maquinaEncontrada = true;
                break;
            }
        }
        
        $this->assertTrue($maquinaEncontrada, 'No se encontro la maquina asignada al ensamblador');
        echo "   Maquina verificada\n";
    }
}
private function pasoEnviarAComprobacion() {
    echo "Enviando maquina a comprobacion...\n";
    
    $response = $this->request('POST', '/maquina/mandar-comprobacion', [
        'idMaquina' => $this->maquinaId,
        'mensaje' => 'Maquina lista para comprobacion'
    ]);
    
    $this->assertResponseSuccess('Error al enviar a comprobacion');
    echo "   Maquina enviada a comprobacion\n";
    
    // Consulta directa a la base de datos de pruebas usando valores fijos
    $host = 'localhost';
    $user = 'root';
    $pass = '';
    $dbname = 'test_bd_recrea_sys';
    
    $conn = new \mysqli($host, $user, $pass, $dbname);
    if ($conn->connect_error) {
        echo "   Error de conexión a BD: " . $conn->connect_error . "\n";
    } else {
        $result = $conn->query("SELECT Estado FROM MaquinaRecreativa WHERE ID_Maquina = '$this->maquinaId'");
        if ($result && $row = $result->fetch_assoc()) {
            echo "   Estado en BD después de enviar a comprobación: " . $row['Estado'] . "\n";
        } else {
            echo "   ERROR: Máquina no encontrada en BD o error en consulta\n";
        }
        $conn->close();
    }
    
    sleep(1);
    
    $response = $this->request('GET', "/maquina/estado/Comprobandose");
    if ($this->assertResponseSuccess('Error al obtener maquinas por estado')) {
        $maquinas = $response['maquinas'] ?? [];
        $encontrada = false;
        foreach ($maquinas as $maquina) {
            if (isset($maquina['id']) && $maquina['id'] === $this->maquinaId) {
                $encontrada = true;
                break;
            }
        }
        $this->assertTrue($encontrada, 'La maquina no cambio a estado Comprobandose');
        echo "   Estado actualizado: Comprobandose\n";
    }
}
    private function pasoRegistrarMontaje() {
        echo "Registrando montaje de componentes...\n";
        
        // Primero registrar montaje de placa
        $response = $this->request('POST', '/maquina/registrar-montaje', [
            'idMaquina' => $this->maquinaId,
            'idComponente' => $this->placaId,
            'detalle' => 'Montaje de placa'
        ]);
        
        $this->assertResponseSuccess('Error al registrar montaje de placa');
        echo "   Placa montada\n";
        
        sleep(1);
        
        // Luego registrar montaje de carcasa
        $response = $this->request('POST', '/maquina/registrar-montaje', [
            'idMaquina' => $this->maquinaId,
            'idComponente' => $this->carcasaId,
            'detalle' => 'Montaje de carcasa'
        ]);
        
        $this->assertResponseSuccess('Error al registrar montaje de carcasa');
        echo "   Carcasa montada\n";
    }
    private function pasoVerMaquinasComprobador() {
    echo "Verificando maquinas para comprobar...\n";
    
    $response = $this->request('GET', "/maquina/comprobador/{$this->comprobadorId}");
    
    if ($this->assertResponseSuccess('Error al obtener maquinas del comprobador')) {
        $maquinas = $response['maquinas'] ?? [];
        $this->assertArrayHasKey('maquinas', $response, 'Respuesta no contiene maquinas');
        
        $maquinaEncontrada = false;
        foreach ($maquinas as $maquina) {
            if (isset($maquina['id']) && $maquina['id'] === $this->maquinaId) {
                $maquinaEncontrada = true;
                break;
            }
        }
        
        $this->assertTrue($maquinaEncontrada, 'No se encontro la maquina para comprobar');
        echo "   Maquina verificada\n";
    }
}

private function pasoAprobarMaquina() {
    echo "Aprobando maquina y enviando a distribucion...\n";
    
    $response = $this->request('POST', '/maquina/mandar-distribucion', [
        'idMaquina' => $this->maquinaId,
        'mensaje' => 'Maquina aprobada, enviar a distribucion'
    ]);
    
    $this->assertResponseSuccess('Error al aprobar maquina');
    
    sleep(1);
    
    $response = $this->request('POST', '/maquina/poner-operativa', [
        'idMaquina' => $this->maquinaId
    ]);
    
    $this->assertResponseSuccess('Error al poner maquina operativa');
    
    sleep(1);
    
    $response = $this->request('GET', "/maquina/estado/Operativa");
    if ($this->assertResponseSuccess('Error al obtener maquinas operativas')) {
        $maquinas = $response['maquinas'] ?? [];
        $encontrada = false;
        foreach ($maquinas as $maquina) {
            if (isset($maquina['id']) && $maquina['id'] === $this->maquinaId) {
                $encontrada = true;
                break;
            }
        }
        $this->assertTrue($encontrada, 'La maquina no esta en estado Operativa');
        echo "   Maquina aprobada y operativa\n";
    }
}
}