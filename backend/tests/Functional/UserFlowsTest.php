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
            'nombre' => 'Maquina Arcade ' . $timestamp,
            'tipo' => 'Arcade Clasica'
        ];
    }
    
    public function testFlujoCompletoUsuario() {
        echo "\nINICIANDO FLUJO COMPLETO (LOGISTICA -> ENSAMBLADOR -> COMPROBADOR)\n";
        echo "================================================================\n\n";
        
        $this->pasoRegistrarUsuarios();
        
        echo "\nPARTE 1: LOGISTICA\n";
        echo "--------------------\n";
        $this->pasoLoginLogistica();
        $this->pasoRegistrarComercio();
        $this->pasoGenerarPlaca();
        $this->pasoRegistrarMaquina();
        $this->pasoLogout();
        
        echo "\nPARTE 2: TECNICO ENSAMBLADOR\n";
        echo "------------------------------\n";
        $this->pasoLoginEnsamblador();
        $this->pasoVerMaquinasEnsamblador();
        $this->pasoEnviarAComprobacion();
        $this->pasoLogout();
        
        echo "\nPARTE 3: TECNICO COMPROBADOR\n";
        echo "------------------------------\n";
        $this->pasoLoginComprobador();
        $this->pasoVerMaquinasComprobador();
        $this->pasoAprobarMaquina();
        
        echo "\nFLUJO COMPLETO EXITOSO\n";
    }
    
    private function pasoRegistrarUsuarios() {
        echo "Paso 1: Registrando usuarios...\n";
        
        $responseLog = $this->request('POST', '/usuario/register', $this->logisticaUser);
        if ($this->assertResponseSuccess('Error al registrar logistica')) {
            $this->logisticaId = $responseLog['userId'] ?? null;
            $this->assertNotNull($this->logisticaId, 'No se recibio ID de logistica');
            echo "   Logistica registrado: {$this->logisticaUser['usuario_asignado']} (ID: {$this->logisticaId})\n";
        }
        
        sleep(1);
        
        $responseEns = $this->request('POST', '/usuario/register', $this->ensambladorUser);
        if ($this->assertResponseSuccess('Error al registrar ensamblador')) {
            $this->ensambladorId = $responseEns['userId'] ?? null;
            $this->assertNotNull($this->ensambladorId, 'No se recibio ID de ensamblador');
            echo "   Ensamblador registrado: {$this->ensambladorUser['usuario_asignado']} (ID: {$this->ensambladorId})\n";
        }
        
        sleep(1);
        
        $responseComp = $this->request('POST', '/usuario/register', $this->comprobadorUser);
        if ($this->assertResponseSuccess('Error al registrar comprobador')) {
            $this->comprobadorId = $responseComp['userId'] ?? null;
            $this->assertNotNull($this->comprobadorId, 'No se recibio ID de comprobador');
            echo "   Comprobador registrado: {$this->comprobadorUser['usuario_asignado']} (ID: {$this->comprobadorId})\n";
        }
    }
    
    private function pasoLoginLogistica() {
        echo "Paso 2: Iniciando sesion como logistica...\n";
        
        $response = $this->request('POST', '/usuario/login', [
            'usuario_asignado' => $this->logisticaUser['usuario_asignado'],
            'contrasena' => $this->logisticaUser['contrasena']
        ]);
        
        if ($this->assertResponseSuccess('Error al iniciar sesion como logistica')) {
            echo "   Login exitoso como: {$this->logisticaUser['usuario_asignado']}\n";
        }
    }
    
    private function pasoRegistrarComercio() {
        echo "Paso 3: Registrando comercio...\n";
        
        $response = $this->request('POST', '/comercio/register', $this->comercioData);
        
        if ($this->assertResponseSuccess('Error al registrar comercio')) {
            $this->comercioId = $response['idComercio'] ?? null;
            $this->assertNotNull($this->comercioId, 'No se recibio ID del comercio');
            echo "   Comercio registrado: {$this->comercioData['nombre']} (ID: {$this->comercioId})\n";
        }
    }
    
    private function pasoGenerarPlaca() {
        echo "Paso 4: Generando placa...\n";
        
        $response = $this->request('POST', '/maquina/generar-placa', []);
        
        if ($this->assertResponseSuccess('Error al generar placa')) {
            $this->assertArrayHasKey('placa', $response, 'No se recibio numero de placa');
            $this->placaId = $response['idComponente'] ?? null;
            $this->assertNotNull($this->placaId, 'No se recibio ID de placa');
            echo "   Placa generada: {$response['placa']} (ID: {$this->placaId})\n";
        }
    }
    
    private function pasoRegistrarMaquina() {
        echo "Paso 5: Registrando maquina...\n";
        
        $maquinaData = [
            'nombre' => $this->maquinaData['nombre'],
            'tipo' => $this->maquinaData['tipo'],
            'idComercio' => $this->comercioId,
            'idPlaca' => $this->placaId,
            'idCarcasa' => $this->placaId
        ];
        
        $response = $this->request('POST', '/maquina/register', $maquinaData);
        
        if ($this->assertResponseSuccess('Error al registrar maquina')) {
            $this->assertArrayHasKey('idMaquina', $response, 'No se recibio ID de maquina');
            $this->maquinaId = $response['idMaquina'];
            echo "   Maquina registrada: {$this->maquinaData['nombre']} (ID: {$this->maquinaId})\n";
        }
    }
    
    private function pasoLogout() {
        echo "Paso 6: Cerrando sesion...\n";
        $this->request('POST', '/usuario/logout', []);
        $this->clearCookies();
        echo "   Sesion cerrada\n";
    }
    
    private function pasoLoginEnsamblador() {
        echo "Paso 7: Iniciando sesion como ensamblador...\n";
        
        $response = $this->request('POST', '/usuario/login', [
            'usuario_asignado' => $this->ensambladorUser['usuario_asignado'],
            'contrasena' => $this->ensambladorUser['contrasena']
        ]);
        
        if ($this->assertResponseSuccess('Error al iniciar sesion como ensamblador')) {
            echo "   Login exitoso como: {$this->ensambladorUser['usuario_asignado']}\n";
        }
    }
    
    private function pasoVerMaquinasEnsamblador() {
        echo "Paso 8: Verificando maquinas asignadas...\n";
        
        $response = $this->request('GET', "/maquina/ensamblador/{$this->ensambladorId}");
        
        if ($this->assertResponseSuccess('Error al obtener maquinas del ensamblador')) {
            $this->assertArrayHasKey('maquinas', $response, 'Respuesta no contiene maquinas');
            
            $maquinaEncontrada = false;
            if (isset($response['maquinas']) && is_array($response['maquinas'])) {
                foreach ($response['maquinas'] as $maquina) {
                    if (isset($maquina['id']) && $maquina['id'] === $this->maquinaId) {
                        $maquinaEncontrada = true;
                        break;
                    }
                }
            }
            
            $this->assertTrue($maquinaEncontrada, 'No se encontro la maquina asignada al ensamblador');
            echo "   Maquina verificada\n";
        }
    }
    
    private function pasoEnviarAComprobacion() {
        echo "Paso 9: Enviando maquina a comprobacion...\n";
        
        $response = $this->request('POST', '/maquina/mandar-comprobacion', [
            'idMaquina' => $this->maquinaId,
            'mensaje' => 'Maquina lista para comprobacion'
        ]);
        
        $this->assertResponseSuccess('Error al enviar a comprobacion');
        echo "   Maquina enviada a comprobacion\n";
        
        sleep(1);
        
        $response = $this->request('GET', "/maquina/estado/Comprobandose");
        if ($this->assertResponseSuccess('Error al obtener maquinas por estado')) {
            $encontrada = false;
            if (isset($response['maquinas']) && is_array($response['maquinas'])) {
                foreach ($response['maquinas'] as $maquina) {
                    if (isset($maquina['id']) && $maquina['id'] === $this->maquinaId) {
                        $encontrada = true;
                        break;
                    }
                }
            }
            $this->assertTrue($encontrada, 'La maquina no cambio a estado Comprobandose');
            echo "   Estado actualizado: Comprobandose\n";
        }
    }
    
    private function pasoLoginComprobador() {
        echo "Paso 10: Iniciando sesion como comprobador...\n";
        
        $response = $this->request('POST', '/usuario/login', [
            'usuario_asignado' => $this->comprobadorUser['usuario_asignado'],
            'contrasena' => $this->comprobadorUser['contrasena']
        ]);
        
        if ($this->assertResponseSuccess('Error al iniciar sesion como comprobador')) {
            echo "   Login exitoso como: {$this->comprobadorUser['usuario_asignado']}\n";
        }
    }
    
    private function pasoVerMaquinasComprobador() {
        echo "Paso 11: Verificando maquinas para comprobar...\n";
        
        $response = $this->request('GET', "/maquina/comprobador/{$this->comprobadorId}");
        
        if ($this->assertResponseSuccess('Error al obtener maquinas del comprobador')) {
            $this->assertArrayHasKey('maquinas', $response, 'Respuesta no contiene maquinas');
            
            $maquinaEncontrada = false;
            if (isset($response['maquinas']) && is_array($response['maquinas'])) {
                foreach ($response['maquinas'] as $maquina) {
                    if (isset($maquina['id']) && $maquina['id'] === $this->maquinaId) {
                        $maquinaEncontrada = true;
                        break;
                    }
                }
            }
            
            $this->assertTrue($maquinaEncontrada, 'No se encontro la maquina para comprobar');
            echo "   Maquina verificada\n";
        }
    }
    
    private function pasoAprobarMaquina() {
        echo "Paso 12: Aprobando maquina y enviando a distribucion...\n";
        
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
            $encontrada = false;
            if (isset($response['maquinas']) && is_array($response['maquinas'])) {
                foreach ($response['maquinas'] as $maquina) {
                    if (isset($maquina['id']) && $maquina['id'] === $this->maquinaId) {
                        $encontrada = true;
                        break;
                    }
                }
            }
            $this->assertTrue($encontrada, 'La maquina no esta en estado Operativa');
            echo "   Maquina aprobada y operativa\n";
        }
    }
}