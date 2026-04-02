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
        
        // Usar timestamp para asegurar unicidad
        $timestamp = time();
        
        $this->logisticaUser = [
            'nombre' => 'Logistica',
            'apellido' => 'Prueba',
            'ci' => '12345678' . rand(10, 99),
            'email' => 'logistica_' . $timestamp . '_' . uniqid() . '@test.com',
            'usuario_asignado' => 'log_' . substr(uniqid(), -8),
            'contrasena' => 'Password123!',
            'tipo' => 'Logistica'
        ];
        
        $this->ensambladorUser = [
            'nombre' => 'Ensamblador',
            'apellido' => 'Tecnico',
            'ci' => '87654321' . rand(10, 99),
            'email' => 'ensamblador_' . $timestamp . '_' . uniqid() . '@test.com',
            'usuario_asignado' => 'ens_' . substr(uniqid(), -8),
            'contrasena' => 'Password123!',
            'tipo' => 'Tecnico',
            'especialidad' => 'Ensamblador'
        ];
        
        $this->comprobadorUser = [
            'nombre' => 'Comprobador',
            'apellido' => 'Tecnico',
            'ci' => '11223344' . rand(10, 99),
            'email' => 'comprobador_' . $timestamp . '_' . uniqid() . '@test.com',
            'usuario_asignado' => 'comp_' . substr(uniqid(), -8),
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
            'nombre' => 'Máquina Arcade ' . $timestamp,
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
        $this->pasoAprobarMaquina();
        
        echo "\n✅ FLUJO COMPLETO EXITOSO\n";
    }
    
    private function pasoRegistrarUsuarios() {
        echo "📝 Paso 1: Registrando usuarios...\n";
        
        // Registrar logística
        $responseLog = $this->request('POST', '/usuario/register', $this->logisticaUser);
        $this->assertResponseSuccess('Error al registrar logística');
        $this->logisticaId = $responseLog['userId'] ?? null;
        $this->assertNotNull($this->logisticaId, 'No se recibió ID de logística');
        echo "   ✅ Logística registrado: {$this->logisticaUser['usuario_asignado']} (ID: {$this->logisticaId})\n";
        
        sleep(1);
        
        // Registrar ensamblador
        $responseEns = $this->request('POST', '/usuario/register', $this->ensambladorUser);
        $this->assertResponseSuccess('Error al registrar ensamblador');
        $this->ensambladorId = $responseEns['userId'] ?? null;
        $this->assertNotNull($this->ensambladorId, 'No se recibió ID de ensamblador');
        echo "   ✅ Ensamblador registrado: {$this->ensambladorUser['usuario_asignado']} (ID: {$this->ensambladorId})\n";
        
        sleep(1);
        
        // Registrar comprobador
        $responseComp = $this->request('POST', '/usuario/register', $this->comprobadorUser);
        $this->assertResponseSuccess('Error al registrar comprobador');
        $this->comprobadorId = $responseComp['userId'] ?? null;
        $this->assertNotNull($this->comprobadorId, 'No se recibió ID de comprobador');
        echo "   ✅ Comprobador registrado: {$this->comprobadorUser['usuario_asignado']} (ID: {$this->comprobadorId})\n";
    }
    
    private function pasoLoginLogistica() {
        echo "🔐 Paso 2: Iniciando sesión como logística...\n";
        
        $response = $this->request('POST', '/usuario/login', [
            'usuario_asignado' => $this->logisticaUser['usuario_asignado'],
            'contrasena' => $this->logisticaUser['contrasena']
        ]);
        
        $this->assertResponseSuccess('Error al iniciar sesión como logística');
        echo "   ✅ Login exitoso como: {$this->logisticaUser['usuario_asignado']}\n";
    }
    
    private function pasoRegistrarComercio() {
        echo "🏪 Paso 3: Registrando comercio...\n";
        
        $response = $this->request('POST', '/comercio/register', $this->comercioData);
        $this->assertResponseSuccess('Error al registrar comercio');
        
        // Obtener el ID del comercio desde la respuesta
        $this->comercioId = $response['idComercio'] ?? null;
        $this->assertNotNull($this->comercioId, 'No se recibió ID del comercio');
        
        echo "   ✅ Comercio registrado: {$this->comercioData['nombre']} (ID: {$this->comercioId})\n";
    }
    
    private function pasoGenerarPlaca() {
        echo "🔧 Paso 4: Generando placa...\n";
        
        $response = $this->request('POST', '/maquina/generar-placa', []);
        
        $this->assertResponseSuccess('Error al generar placa');
        $this->assertArrayHasKey('placa', $response, 'No se recibió número de placa');
        $this->placaId = $response['idComponente'] ?? null;
        $this->assertNotNull($this->placaId, 'No se recibió ID de placa');
        
        echo "   ✅ Placa generada: {$response['placa']} (ID: {$this->placaId})\n";
    }
    
    private function pasoRegistrarMaquina() {
        echo "🎮 Paso 5: Registrando máquina...\n";
        
        $maquinaData = [
            'nombre' => $this->maquinaData['nombre'],
            'tipo' => $this->maquinaData['tipo'],
            'idComercio' => $this->comercioId,
            'idPlaca' => $this->placaId,
            'idCarcasa' => $this->placaId  // Usar misma placa como carcasa
        ];
        
        $response = $this->request('POST', '/maquina/register', $maquinaData);
        
        $this->assertResponseSuccess('Error al registrar máquina');
        $this->assertArrayHasKey('idMaquina', $response, 'No se recibió ID de máquina');
        
        $this->maquinaId = $response['idMaquina'];
        echo "   ✅ Máquina registrada: {$this->maquinaData['nombre']} (ID: {$this->maquinaId})\n";
    }
    
    private function pasoLogout() {
        echo "🚪 Paso 6: Cerrando sesión...\n";
        
        $this->request('POST', '/usuario/logout', []);
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
        echo "   ✅ Login exitoso como: {$this->ensambladorUser['usuario_asignado']}\n";
    }
    
    private function pasoVerMaquinasEnsamblador() {
        echo "📋 Paso 8: Verificando máquinas asignadas...\n";
        
        $response = $this->request('GET', "/maquina/ensamblador/{$this->ensambladorId}");
        
        $this->assertResponseSuccess('Error al obtener máquinas del ensamblador');
        $this->assertArrayHasKey('maquinas', $response, 'Respuesta no contiene máquinas');
        
        $maquinaEncontrada = false;
        foreach ($response['maquinas'] as $maquina) {
            if ($maquina['id'] === $this->maquinaId) {
                $maquinaEncontrada = true;
                $this->assertEquals('Ensamblandose', $maquina['estado'], 
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
            'mensaje' => 'Máquina lista para comprobación'
        ]);
        
        $this->assertResponseSuccess('Error al enviar a comprobación');
        echo "   ✅ Máquina enviada a comprobación\n";
        
        // Verificar cambio de estado
        sleep(1);
        $maquinas = $this->request('GET', "/maquina/estado/Comprobandose");
        $this->assertResponseSuccess('Error al obtener máquinas por estado');
        
        $encontrada = false;
        foreach ($maquinas['maquinas'] as $maquina) {
            if ($maquina['id'] === $this->maquinaId) {
                $encontrada = true;
                break;
            }
        }
        
        $this->assertTrue($encontrada, 'La máquina no cambió a estado Comprobandose');
        echo "   ✅ Estado actualizado: Comprobandose\n";
    }
    
    private function pasoLoginComprobador() {
        echo "🔐 Paso 10: Iniciando sesión como comprobador...\n";
        
        $response = $this->request('POST', '/usuario/login', [
            'usuario_asignado' => $this->comprobadorUser['usuario_asignado'],
            'contrasena' => $this->comprobadorUser['contrasena']
        ]);
        
        $this->assertResponseSuccess('Error al iniciar sesión como comprobador');
        echo "   ✅ Login exitoso como: {$this->comprobadorUser['usuario_asignado']}\n";
    }
    
    private function pasoVerMaquinasComprobador() {
        echo "🔍 Paso 11: Verificando máquinas para comprobar...\n";
        
        $response = $this->request('GET', "/maquina/comprobador/{$this->comprobadorId}");
        
        $this->assertResponseSuccess('Error al obtener máquinas del comprobador');
        $this->assertArrayHasKey('maquinas', $response, 'Respuesta no contiene máquinas');
        
        $maquinaEncontrada = false;
        foreach ($response['maquinas'] as $maquina) {
            if ($maquina['id'] === $this->maquinaId) {
                $maquinaEncontrada = true;
                $this->assertEquals('Comprobandose', $maquina['estado'], 
                    'La máquina debería estar en estado Comprobandose');
                break;
            }
        }
        
        $this->assertTrue($maquinaEncontrada, 'No se encontró la máquina para comprobar');
        echo "   ✅ Máquina verificada - Estado: Comprobandose\n";
    }
    
    private function pasoAprobarMaquina() {
        echo "✅ Paso 12: Aprobando máquina y enviando a distribución...\n";
        
        // Primero aprobar y enviar a distribución
        $response = $this->request('POST', '/maquina/mandar-distribucion', [
            'idMaquina' => $this->maquinaId,
            'mensaje' => 'Máquina aprobada, enviar a distribución'
        ]);
        
        $this->assertResponseSuccess('Error al aprobar máquina');
        
        // Poner operativa
        sleep(1);
        $response = $this->request('POST', '/maquina/poner-operativa', [
            'idMaquina' => $this->maquinaId
        ]);
        
        $this->assertResponseSuccess('Error al poner máquina operativa');
        
        // Verificar estado final
        sleep(1);
        $maquinas = $this->request('GET', "/maquina/estado/Operativa");
        $this->assertResponseSuccess('Error al obtener máquinas operativas');
        
        $encontrada = false;
        foreach ($maquinas['maquinas'] as $maquina) {
            if ($maquina['id'] === $this->maquinaId) {
                $encontrada = true;
                $this->assertEquals('Recaudacion', $maquina['etapa'], 
                    'La etapa debería ser Recaudacion');
                break;
            }
        }
        
        $this->assertTrue($encontrada, 'La máquina no está en estado Operativa');
        echo "   ✅ Máquina aprobada y operativa - Etapa: Recaudacion\n";
    }
}