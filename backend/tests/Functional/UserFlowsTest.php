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
    private $maquinaId;
    
    public function __construct() {
        parent::__construct();
        
        $this->logisticaUser = [
            'nombre' => 'Logistica',
            'apellido' => 'Prueba',
            'ci' => '12345678' . rand(10, 99),
            'email' => 'logistica_' . uniqid() . '@test.com',
            'usuario_asignado' => 'logistica_' . uniqid(),
            'contrasena' => 'password123',
            'tipo' => 'Logistica'
        ];
        
        $this->ensambladorUser = [
            'nombre' => 'Ensamblador',
            'apellido' => 'Tecnico',
            'ci' => '87654321' . rand(10, 99),
            'email' => 'ensamblador_' . uniqid() . '@test.com',
            'usuario_asignado' => 'ensamblador_' . uniqid(),
            'contrasena' => 'password123',
            'tipo' => 'Tecnico',
            'especialidad' => 'Ensamblador'
        ];
        
        $this->comprobadorUser = [
            'nombre' => 'Comprobador',
            'apellido' => 'Tecnico',
            'ci' => '11223344' . rand(10, 99),
            'email' => 'comprobador_' . uniqid() . '@test.com',
            'usuario_asignado' => 'comprobador_' . uniqid(),
            'contrasena' => 'password123',
            'tipo' => 'Tecnico',
            'especialidad' => 'Comprobador'
        ];
        
        $this->comercioData = [
            'nombre' => 'Comercio Test ' . uniqid(),
            'tipo' => 'Minorista',
            'direccion' => 'Av. Principal 123',
            'telefono' => '0999' . rand(100000, 999999)
        ];
        
        $this->maquinaData = [
            'nombre' => 'Máquina Arcade ' . uniqid(),
            'tipo' => 'Arcade Clásica'
        ];
    }
    
    public function testFlujoCompletoUsuario() {
        echo "\n🚀 INICIANDO FLUJO COMPLETO (LOGÍSTICA → ENSAMBLADOR → COMPROBADOR)\n";
        echo "================================================================\n\n";
        
        $this->pasoRegistrarUsuarios();
        
        echo "\n📦 PARTE 1: LOGÍSTICA\n";
        echo "--------------------\n";
        $this->pasoLoginLogistica();
        $this->pasoRegistrarComercio();
        $this->pasoGenerarPlaca();
        $this->pasoRegistrarMaquina();
        $this->pasoLogout();
        
        echo "\n🔧 PARTE 2: TÉCNICO ENSAMBLADOR\n";
        echo "------------------------------\n";
        $this->pasoLoginEnsamblador();
        $this->pasoVerMaquinasEnsamblador();
        $this->pasoEnviarAComprobacion();
        $this->pasoLogout();
        
        echo "\n✅ PARTE 3: TÉCNICO COMPROBADOR\n";
        echo "------------------------------\n";
        $this->pasoLoginComprobador();
        $this->pasoVerMaquinasComprobador();
        $this->pasoPonerOperativa();
        
        echo "\n✅ FLUJO COMPLETO EXITOSO\n";
    }
    
    private function pasoRegistrarUsuarios() {
        echo "📝 Paso 1: Registrando usuarios...\n";
        
        $responseLog = $this->request('POST', '/usuario/register', $this->logisticaUser);
        $this->assertResponseSuccess('Error al registrar logística');
        $this->logisticaId = $responseLog['userId'] ?? null;
        $this->assertNotNull($this->logisticaId, 'No se recibió ID de logística');
        echo "   ✅ Logística registrado: {$this->logisticaUser['usuario_asignado']}\n";
        
        $responseEns = $this->request('POST', '/usuario/register', $this->ensambladorUser);
        $this->assertResponseSuccess('Error al registrar ensamblador');
        $this->ensambladorId = $responseEns['userId'] ?? null;
        $this->assertNotNull($this->ensambladorId, 'No se recibió ID de ensamblador');
        echo "   ✅ Ensamblador registrado: {$this->ensambladorUser['usuario_asignado']}\n";
        
        $responseComp = $this->request('POST', '/usuario/register', $this->comprobadorUser);
        $this->assertResponseSuccess('Error al registrar comprobador');
        $this->comprobadorId = $responseComp['userId'] ?? null;
        $this->assertNotNull($this->comprobadorId, 'No se recibió ID de comprobador');
        echo "   ✅ Comprobador registrado: {$this->comprobadorUser['usuario_asignado']}\n";
    }
    
    private function pasoLoginLogistica() {
        echo "🔐 Paso 2: Iniciando sesión como logística...\n";
        
        $response = $this->request('POST', '/usuario/login', [
            'usuario_asignado' => $this->logisticaUser['usuario_asignado'],
            'contrasena' => $this->logisticaUser['contrasena']
        ]);
        
        $this->assertResponseSuccess('Error al iniciar sesión como logística');
        $this->assertHttpCode(200);
        echo "   ✅ Login exitoso como: {$this->logisticaUser['usuario_asignado']}\n";
    }
    
    private function pasoRegistrarComercio() {
        echo "🏪 Paso 3: Registrando comercio...\n";
        
        $response = $this->request('POST', '/comercio/register', $this->comercioData);
        $this->assertResponseSuccess('Error al registrar comercio');
        
        // Obtener el ID del comercio (el endpoint /comercio/all devuelve los comercios)
        $comerciosResponse = $this->request('GET', '/comercio/all');
        $this->assertResponseSuccess('Error al obtener comercios');
        
        $encontrado = false;
        foreach ($comerciosResponse['comercios'] as $comercio) {
            if ($comercio['Nombre'] === $this->comercioData['nombre']) {
                $this->comercioId = $comercio['ID_Comercio'];
                $encontrado = true;
                break;
            }
        }
        
        $this->assertTrue($encontrado, 'No se encontró el comercio registrado');
        echo "   ✅ Comercio registrado: {$this->comercioData['nombre']} (ID: {$this->comercioId})\n";
    }
    
    private function pasoGenerarPlaca() {
        echo "🔧 Paso 4: Generando placa...\n";
        
        $response = $this->request('POST', '/maquina/generar-placa', [
            'ID_Usuario' => $this->logisticaId
        ]);
        
        $this->assertResponseSuccess('Error al generar placa');
        $this->assertArrayHasKey('placa', $response, 'No se recibió número de placa');
        $this->placaId = $response['id_componente'] ?? null;
        $this->assertNotNull($this->placaId, 'No se recibió ID de placa');
        
        echo "   ✅ Placa generada: {$response['placa']} (ID: {$this->placaId})\n";
    }
    
    private function pasoRegistrarMaquina() {
        echo "🎮 Paso 5: Registrando máquina...\n";
        
        $maquinaData = [
            'nombre' => $this->maquinaData['nombre'],
            'tipo' => $this->maquinaData['tipo'],
            'idComercio' => $this->comercioId,
            'idUsuarioLogistica' => $this->logisticaId,
            'idPlaca' => $this->placaId,
            'idCarcasa' => $this->placaId // Usar misma placa como carcasa
        ];
        
        $response = $this->request('POST', '/maquina/register', $maquinaData);
        
        $this->assertResponseSuccess('Error al registrar máquina');
        $this->assertArrayHasKey('idMaquina', $response, 'No se recibió ID de máquina');
        
        $this->maquinaId = $response['idMaquina'];
        // Verificar que el ID es un UUID válido (opcional)
        if (!preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $this->maquinaId)) {
            echo "      ⚠️  ID de máquina no es UUID: {$this->maquinaId}\n";
        }
        echo "   ✅ Máquina registrada: {$maquinaData['nombre']} (ID: {$this->maquinaId})\n";
    }
    
    private function pasoLogout() {
        echo "🚪 Paso 6: Cerrando sesión...\n";
        
        $response = $this->request('POST', '/usuario/logout', []);
        // No importa si falla, limpiamos cookies
        $this->cookies = [];
        echo "   ✅ Sesión cerrada\n";
    }
    
    private function pasoLoginEnsamblador() {
        echo "🔐 Paso 7: Iniciando sesión como ensamblador...\n";
        
        $response = $this->request('POST', '/usuario/login', [
            'usuario_asignado' => $this->ensambladorUser['usuario_asignado'],
            'contrasena' => $this->ensambladorUser['contrasena']
        ]);
        
        $this->assertResponseSuccess('Error al iniciar sesión como ensamblador');
        $this->assertHttpCode(200);
        echo "   ✅ Login exitoso como: {$this->ensambladorUser['usuario_asignado']}\n";
    }
    
    private function pasoVerMaquinasEnsamblador() {
        echo "📋 Paso 8: Verificando máquinas asignadas...\n";
        
        $response = $this->request('GET', "/maquina/ensamblador/{$this->ensambladorId}");
        
        $this->assertResponseSuccess('Error al obtener máquinas del ensamblador');
        $this->assertArrayHasKey('maquinas', $response, 'Respuesta no contiene máquinas');
        
        $maquinaEncontrada = false;
        foreach ($response['maquinas'] as $maquina) {
            if ($maquina['ID_Maquina'] === $this->maquinaId) {
                $maquinaEncontrada = true;
                $this->assertEquals('Ensamblandose', $maquina['Estado'], 
                    'La máquina debería estar en estado Ensamblandose');
                break;
            }
        }
        
        $this->assertTrue($maquinaEncontrada, 'No se encontró la máquina asignada al ensamblador');
        echo "   ✅ Máquina verificada - Estado: Ensamblandose\n";
    }
    
    private function pasoEnviarAComprobacion() {
        echo "📤 Paso 9: Enviando máquina a comprobación...\n";
        
        $response = $this->request('POST', '/maquina/mandar-comprobacion', [
            'idMaquina' => $this->maquinaId,
            'idRemitente' => $this->ensambladorId,
            'mensaje' => 'Máquina lista para comprobación'
        ]);
        
        $this->assertResponseSuccess('Error al enviar a comprobación');
        echo "   ✅ Máquina enviada a comprobación\n";
        
        // Verificar cambio de estado
        $maquinas = $this->request('GET', "/maquina/estado/Comprobandose");
        $this->assertResponseSuccess('Error al obtener máquinas por estado');
        
        $encontrada = false;
        foreach ($maquinas['maquinas'] as $maquina) {
            if ($maquina['ID_Maquina'] === $this->maquinaId) {
                $encontrada = true;
                break;
            }
        }
        
        $this->assertTrue($encontrada, 'La máquina no cambió a estado Comprobandose');
        echo "   ✅ Estado actualizado: Comprobandose\n";
    }
    
    private function pasoLoginComprobador() {
        echo "🔐 Paso 11: Iniciando sesión como comprobador...\n";
        
        $response = $this->request('POST', '/usuario/login', [
            'usuario_asignado' => $this->comprobadorUser['usuario_asignado'],
            'contrasena' => $this->comprobadorUser['contrasena']
        ]);
        
        $this->assertResponseSuccess('Error al iniciar sesión como comprobador');
        $this->assertHttpCode(200);
        echo "   ✅ Login exitoso como: {$this->comprobadorUser['usuario_asignado']}\n";
    }
    
    private function pasoVerMaquinasComprobador() {
        echo "🔍 Paso 12: Verificando máquinas para comprobar...\n";
        
        $response = $this->request('GET', "/maquina/comprobador/{$this->comprobadorId}");
        
        $this->assertResponseSuccess('Error al obtener máquinas del comprobador');
        $this->assertArrayHasKey('maquinas', $response, 'Respuesta no contiene máquinas');
        
        $maquinaEncontrada = false;
        foreach ($response['maquinas'] as $maquina) {
            if ($maquina['ID_Maquina'] === $this->maquinaId) {
                $maquinaEncontrada = true;
                $this->assertEquals('Comprobandose', $maquina['Estado'], 
                    'La máquina debería estar en estado Comprobandose');
                break;
            }
        }
        
        $this->assertTrue($maquinaEncontrada, 'No se encontró la máquina para comprobar');
        echo "   ✅ Máquina verificada - Estado: Comprobandose\n";
    }
    
    private function pasoPonerOperativa() {
        echo "✅ Paso 13: Aprobando máquina y poniendo operativa...\n";
        
        $response = $this->request('POST', '/maquina/poner-operativa', [
            'idMaquina' => $this->maquinaId
        ]);
        
        $this->assertResponseSuccess('Error al poner máquina operativa');
        
        // Verificar estado final
        $maquinas = $this->request('GET', "/maquina/estado/Operativa");
        $this->assertResponseSuccess('Error al obtener máquinas operativas');
        
        $encontrada = false;
        foreach ($maquinas['maquinas'] as $maquina) {
            if ($maquina['ID_Maquina'] === $this->maquinaId) {
                $encontrada = true;
                $this->assertEquals('Recaudacion', $maquina['Etapa'], 
                    'La etapa debería ser Recaudacion');
                break;
            }
        }
        
        $this->assertTrue($encontrada, 'La máquina no está en estado Operativa');
        echo "   ✅ Máquina aprobada y operativa - Etapa: Recaudacion\n";
    }
}