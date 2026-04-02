<?php
// tests/Functional/ReporteFlowsTest.php

require_once __DIR__ . '/HttpTestCase.php';

class ReporteFlowsTest extends HttpTestCase {
    private $usuario1;
    private $usuario2;
    private $usuario1Id;
    private $usuario2Id;
    private $reporteId;
    
    public function __construct() {
        parent::__construct();
        
        $timestamp = time();
        
        $this->usuario1 = [
            'nombre' => 'Emisor',
            'apellido' => 'Reportes',
            'ci' => '11111111' . rand(10, 99),
            'email' => 'emisor_' . $timestamp . '_' . uniqid() . '@test.com',
            'usuario_asignado' => 'em_' . substr(uniqid(), -8),
            'contrasena' => 'Password123!',
            'tipo' => 'Tecnico',
            'especialidad' => 'Ensamblador'
        ];
        
        $this->usuario2 = [
            'nombre' => 'Destinatario',
            'apellido' => 'Reportes',
            'ci' => '22222222' . rand(10, 99),
            'email' => 'destinatario_' . $timestamp . '_' . uniqid() . '@test.com',
            'usuario_asignado' => 'dest_' . substr(uniqid(), -8),
            'contrasena' => 'Password123!',
            'tipo' => 'Logistica'
        ];
    }
    
    public function testFlujoCompletoReportes() {
        echo "\nINICIANDO FLUJO COMPLETO DE REPORTES\n";
        echo "=======================================\n\n";
        
        $this->pasoCrearUsuarios();
        $this->pasoLoginEmisor();
        $this->pasoCrearReporte();
        $this->pasoEnviarComentario();
        
        echo "\nFLUJO COMPLETO DE REPORTES EXITOSO\n";
    }
    
    private function pasoCrearUsuarios() {
        echo "Creando usuarios...\n";
        
        $resp1 = $this->request('POST', '/usuario/register', $this->usuario1);
        if ($this->assertResponseSuccess('Error al crear usuario 1')) {
            $this->usuario1Id = $resp1['userId'] ?? null;
            $this->assertNotNull($this->usuario1Id, 'No se recibio ID usuario 1');
            echo "   Usuario 1 creado: {$this->usuario1['usuario_asignado']} (ID: {$this->usuario1Id})\n";
        }
        
        sleep(2);
        
        $resp2 = $this->request('POST', '/usuario/register', $this->usuario2);
        if ($this->assertResponseSuccess('Error al crear usuario 2')) {
            $this->usuario2Id = $resp2['userId'] ?? null;
            $this->assertNotNull($this->usuario2Id, 'No se recibio ID usuario 2');
            echo "   Usuario 2 creado: {$this->usuario2['usuario_asignado']} (ID: {$this->usuario2Id})\n";
        }
    }
    
    private function pasoLoginEmisor() {
        echo "Iniciando sesion como emisor...\n";
        
        $response = $this->request('POST', '/usuario/login', [
            'usuario_asignado' => $this->usuario1['usuario_asignado'],
            'contrasena' => $this->usuario1['contrasena']
        ]);
        
        if ($this->assertResponseSuccess('Error al iniciar sesion')) {
            echo "   Login exitoso\n";
        }
    }
    
    private function pasoCrearReporte() {
        echo "Creando reporte...\n";
        
        $response = $this->request('POST', '/reportes/crear', [
            'descripcion' => 'Reporte de prueba - ' . time()
        ]);
        
        if ($this->assertResponseSuccess('Error al crear reporte')) {
            $this->reporteId = $response['id'] ?? null;
            $this->assertNotNull($this->reporteId, 'No se recibio ID de reporte');
            echo "   Reporte creado ID: {$this->reporteId}\n";
        }
    }
    
    private function pasoEnviarComentario() {
        echo "Enviando comentario...\n";
        
        $response = $this->request('POST', '/comentarios', [
            'idReporte' => $this->reporteId,
            'comentario' => 'Comentario de prueba'
        ]);
        
        if ($this->assertResponseSuccess('Error al enviar comentario')) {
            echo "   Comentario enviado\n";
        }
        
        sleep(1);
        
        $comentarios = $this->request('GET', "/comentarios/reporte/{$this->reporteId}");
        if ($this->assertResponseSuccess('Error al obtener comentarios')) {
            $this->assertNotEmpty($comentarios['comentarios'] ?? [], 'No se encontraron comentarios');
            echo "   Comentario verificado\n";
        }
    }
}