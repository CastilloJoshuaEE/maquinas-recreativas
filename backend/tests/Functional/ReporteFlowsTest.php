<?php
// tests/Functional/ReporteFlowsTest.php

require_once __DIR__ . '/HttpTestCase.php';

class ReporteFlowsTest extends HttpTestCase {
    private $usuario1;
    private $usuario2;
    private $usuario1Id;
    private $usuario2Id;
    private $usuario1UsuarioAsignado;
    private $usuario2UsuarioAsignado;
    private $reporteId;
    
    public function __construct() {
        parent::__construct();
        $timestamp = time();
        $this->usuario1 = [
            'nombre' => 'Emisor',
            'apellido' => 'Reportes',
            'ci' => '11111111' . rand(10, 99),
            'email' => 'emisor_' . $timestamp . '_' . uniqid() . '@test.com',
            'contrasena' => 'Password123!',
            'tipo' => 'Tecnico',
            'especialidad' => 'Ensamblador',
            'estado' => 'Activo'
        ];
        $this->usuario2 = [
            'nombre' => 'Destinatario',
            'apellido' => 'Reportes',
            'ci' => '22222222' . rand(10, 99),
            'email' => 'destinatario_' . $timestamp . '_' . uniqid() . '@test.com',
            'contrasena' => 'Password123!',
            'tipo' => 'Logistica',
            'estado' => 'Activo'
        ];
    }
    
    public function testFlujoCompletoReportes() {
        echo "\nINICIANDO FLUJO COMPLETO DE REPORTES\n";
        echo "=======================================\n\n";
        echo "Paso 0: Login como administrador...\n";
        if (!$this->loginAsAdmin()) {
            $this->assertTrue(false, 'No se pudo iniciar sesión como administrador');
            return;
        }
        $this->pasoCrearUsuarios();
        $this->pasoLogout();
        $this->pasoLoginEmisor();
        $this->pasoCrearReporte();
        $this->pasoEnviarComentario();
        echo "\nFLUJO COMPLETO DE REPORTES EXITOSO\n";
    }
    
    private function pasoCrearUsuarios() {
        echo "Creando usuarios (usando administrador)...\n";
        $this->registrarUsuarioAdmin($this->usuario1);
        sleep(1);
        $this->registrarUsuarioAdmin($this->usuario2);
        
        $u1 = $this->buscarUsuarioPorEmail($this->usuario1['email']);
        if ($u1) {
            $this->usuario1Id = $u1['id'];
            $this->usuario1UsuarioAsignado = $u1['usuario_asignado'];
            echo "   Usuario 1 creado: {$this->usuario1UsuarioAsignado} (ID: {$this->usuario1Id})\n";
        }
        $u2 = $this->buscarUsuarioPorEmail($this->usuario2['email']);
        if ($u2) {
            $this->usuario2Id = $u2['id'];
            $this->usuario2UsuarioAsignado = $u2['usuario_asignado'];
            echo "   Usuario 2 creado: {$this->usuario2UsuarioAsignado} (ID: {$this->usuario2Id})\n";
        }
    }
    
    private function pasoLoginEmisor() {
        echo "Iniciando sesion como emisor...\n";
        $response = $this->request('POST', '/usuario/login', [
            'usuario_asignado' => $this->usuario1UsuarioAsignado,
            'contrasena' => $this->usuario1['contrasena']
        ]);
        $this->assertResponseSuccess('Error al iniciar sesion');
        if ($this->isSuccessResponse($response)) echo "   Login exitoso\n";
    }
    
    private function pasoLogout() {
        echo "Cerrando sesion...\n";
        $this->request('POST', '/usuario/logout', []);
        $this->clearCookies();
        echo "   Sesion cerrada\n";
    }
    
    private function pasoCrearReporte() {
        echo "Creando reporte...\n";
        $response = $this->request('POST', '/reportes/crear', [
            'descripcion' => 'Reporte de prueba - ' . time(),
            'idUsuarioDestinatario' => $this->usuario2Id
        ]);
        $this->assertResponseSuccess('Error al crear reporte');
        if ($this->isSuccessResponse($response)) {
            $this->reporteId = $response['id'] ?? null;
            echo "   Reporte creado ID: {$this->reporteId}\n";
        }
    }
    
    private function pasoEnviarComentario() {
        echo "Enviando comentario...\n";
        
        // Enviar comentario
        $response = $this->request('POST', '/comentarios', [
            'idReporte' => $this->reporteId,
            'comentario' => 'Comentario de prueba'
        ]);
        $this->assertResponseSuccess('Error al enviar comentario');
        
        sleep(1);
        
        // Obtener comentarios del reporte
        $comentariosResp = $this->request('GET', "/comentarios/reporte/{$this->reporteId}");
        $this->assertResponseSuccess('Error al obtener comentarios');
        
        // Verificar que la respuesta contiene comentarios (puede tener diferentes estructuras)
        $comentarios = [];
        
        // La respuesta podría tener la estructura {"success": true, "comentarios": [...]}
        if (isset($comentariosResp['comentarios']) && is_array($comentariosResp['comentarios'])) {
            $comentarios = $comentariosResp['comentarios'];
        } 
        // O podría ser directamente un array de comentarios
        elseif (is_array($comentariosResp) && !isset($comentariosResp['success'])) {
            $comentarios = $comentariosResp;
        }
        // O podría tener la clave "data"
        elseif (isset($comentariosResp['data']) && is_array($comentariosResp['data'])) {
            $comentarios = $comentariosResp['data'];
        }
        
        // Log para depuración
        echo "   Estructura de respuesta: " . json_encode(array_keys($comentariosResp)) . "\n";
        echo "   Total comentarios encontrados: " . count($comentarios) . "\n";
        
        // Verificar que hay al menos un comentario
        $this->assertNotEmpty($comentarios, 'No se encontraron comentarios');
        
        if (!empty($comentarios)) {
            echo "   Comentario verificado: " . substr($comentarios[0]['comentario'] ?? '', 0, 50) . "\n";
        }
    }
}