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
        
        $this->usuario1 = [
            'nombre' => 'Emisor',
            'apellido' => 'Reportes',
            'ci' => '11111111' . rand(10, 99),
            'email' => 'emisor_' . uniqid() . '@test.com',
            'usuario_asignado' => 'emisor_' . uniqid(),
            'contrasena' => 'password123',
            'tipo' => 'Tecnico',
            'especialidad' => 'Ensamblador'
        ];
        
        $this->usuario2 = [
            'nombre' => 'Destinatario',
            'apellido' => 'Reportes',
            'ci' => '22222222' . rand(10, 99),
            'email' => 'destinatario_' . uniqid() . '@test.com',
            'usuario_asignado' => 'destinatario_' . uniqid(),
            'contrasena' => 'password123',
            'tipo' => 'Logistica'
        ];
    }
    
    public function testFlujoCompletoReportes() {
        echo "\n📝 INICIANDO FLUJO COMPLETO DE REPORTES\n";
        echo "=======================================\n\n";
        
        $this->pasoCrearUsuarios();
        $this->pasoLoginEmisor();
        $this->pasoCrearReporte();
        $this->pasoEnviarComentario();
        
        echo "\n✅ FLUJO COMPLETO DE REPORTES EXITOSO\n";
    }
    
    private function pasoCrearUsuarios() {
    echo "👥 Creando usuarios...\n";
    
    // Usuario 1
    $resp1 = $this->request('POST', '/usuario/register', $this->usuario1);
    $this->assertResponseSuccess('Error al crear usuario 1');
    
    // Verificar estructura de respuesta
    if (isset($resp1['success']) && $resp1['success']) {
        $this->usuario1Id = $resp1['userId'] ?? null;
    } else {
        $this->usuario1Id = null;
    }
    $this->assertNotNull($this->usuario1Id, 'No se recibió ID usuario 1');
    
    // Esperar un poco entre registros
    sleep(1);
    
    // Usuario 2
    $resp2 = $this->request('POST', '/usuario/register', $this->usuario2);
    $this->assertResponseSuccess('Error al crear usuario 2');
    
    if (isset($resp2['success']) && $resp2['success']) {
        $this->usuario2Id = $resp2['userId'] ?? null;
    } else {
        $this->usuario2Id = null;
    }
    $this->assertNotNull($this->usuario2Id, 'No se recibió ID usuario 2');
    
    echo "   ✅ Usuarios creados\n";
}
    
    private function pasoLoginEmisor() {
        echo "🔐 Iniciando sesión como emisor...\n";
        
        $response = $this->request('POST', '/usuario/login', [
            'usuario_asignado' => $this->usuario1['usuario_asignado'],
            'contrasena' => $this->usuario1['contrasena']
        ]);
        
        $this->assertResponseSuccess('Error al iniciar sesión');
        echo "   ✅ Login exitoso\n";
    }
    
    private function pasoCrearReporte() {
        echo "📋 Creando reporte...\n";
        
        $response = $this->request('POST', '/reportes/crear', [
            'ID_Usuario_Emisor' => $this->usuario1Id,
            'ID_Usuario_Destinatario' => $this->usuario2Id,
            'descripcion' => 'Reporte de prueba'
        ]);
        
        $this->assertResponseSuccess('Error al crear reporte');
        $this->reporteId = $response['reporteId'] ?? null;
        $this->assertNotNull($this->reporteId, 'No se recibió ID de reporte');
        
        echo "   ✅ Reporte creado ID: {$this->reporteId}\n";
    }
    
    private function pasoEnviarComentario() {
        echo "💬 Enviando comentario...\n";
        
        $response = $this->request('POST', '/comentarios', [
            'ID_Reporte' => $this->reporteId,
            'comentario' => 'Comentario de prueba'
        ]);
        
        $this->assertResponseSuccess('Error al enviar comentario');
        echo "   ✅ Comentario enviado\n";
    }
}