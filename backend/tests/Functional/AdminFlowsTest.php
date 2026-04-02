<?php
// tests/Functional/AdminFlowsTest.php

require_once __DIR__ . '/HttpTestCase.php';

class AdminFlowsTest extends HttpTestCase {
    private $adminUser;
    private $adminId;
    private $usuarioCreadoId;
    
    public function __construct() {
        parent::__construct();
        
        $timestamp = time();
        
        $this->adminUser = [
            'nombre' => 'Admin',
            'apellido' => 'Sistema',
            'ci' => '00000000' . rand(10, 99),
            'email' => 'admin_' . $timestamp . '_' . uniqid() . '@test.com',
            'usuario_asignado' => 'adm_' . substr(uniqid(), -8),
            'contrasena' => 'Admin123!',
            'tipo' => 'Administrador',
            'estado' => 'Activo'
        ];
    }
    
    public function testFlujoCompletoAdministrador() {
        echo "\n👑 INICIANDO FLUJO COMPLETO DE ADMINISTRADOR\n";
        echo "============================================\n\n";
        
        // Registrar admin
        $this->pasoRegistrarAdmin();
        
        // Login
        $this->pasoLoginAdmin();
        
        // Obtener todos los usuarios
        $this->pasoObtenerTodosUsuarios();
        
        // Crear usuario
        $this->pasoCrearUsuario();
        
        echo "\n✅ FLUJO COMPLETO DE ADMINISTRADOR EXITOSO\n";
    }
    
    private function pasoRegistrarAdmin() {
        echo "📝 Registrando administrador...\n";
        
        $response = $this->request('POST', '/usuario/register', $this->adminUser);
        $this->assertResponseSuccess('Error al registrar administrador');
        
        $this->adminId = $response['userId'] ?? null;
        $this->assertNotNull($this->adminId, 'No se recibió ID');
        
        echo "   ✅ Admin registrado: {$this->adminUser['usuario_asignado']} (ID: {$this->adminId})\n";
    }
    
    private function pasoLoginAdmin() {
        echo "🔐 Login como administrador...\n";
        
        $response = $this->request('POST', '/usuario/login', [
            'usuario_asignado' => $this->adminUser['usuario_asignado'],
            'contrasena' => $this->adminUser['contrasena']
        ]);
        
        $this->assertResponseSuccess('Error al iniciar sesión');
        echo "   ✅ Login exitoso\n";
    }
    
    private function pasoObtenerTodosUsuarios() {
        echo "📋 Obteniendo todos los usuarios...\n";
        
        $response = $this->request('GET', '/administrador/usuarios');
        $this->assertResponseSuccess('Error al obtener usuarios');
        
        $total = count($response['usuarios'] ?? []);
        echo "   ✅ Se obtuvieron {$total} usuarios\n";
    }
    
    private function pasoCrearUsuario() {
        echo "➕ Creando nuevo usuario...\n";
        
        $nuevoUsuario = [
            'nombre' => 'Usuario',
            'apellido' => 'Prueba',
            'ci' => '11122233' . rand(10, 99),
            'email' => 'usuario_' . uniqid() . '@test.com',
            'usuario_asignado' => 'usr_' . substr(uniqid(), -8),
            'contrasena' => 'Password123!',
            'tipo' => 'Tecnico',
            'estado' => 'Activo',
            'especialidad' => 'Mantenimiento'
        ];
        
        $response = $this->request('POST', '/administrador/usuarios', $nuevoUsuario);
        $this->assertResponseSuccess('Error al crear usuario');
        
        $this->usuarioCreadoId = $response['id'] ?? null;
        $this->assertNotNull($this->usuarioCreadoId, 'No se recibió ID');
        
        echo "   ✅ Usuario creado con ID: {$this->usuarioCreadoId}\n";
    }
}