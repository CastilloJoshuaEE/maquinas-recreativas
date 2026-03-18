<?php
// tests/scripts/debug_cookies.php

require_once __DIR__ . '/../../Functional/HttpTestCase.php';

class DebugCookiesTest extends HttpTestCase {
    public function testCookies() {
        echo "🔍 DEBUG: Verificando manejo de cookies\n";
        echo "======================================\n\n";
        
        // Registrar usuario
        $user = [
            'nombre' => 'Test',
            'apellido' => 'User',
            'ci' => '12345678',
            'email' => 'test_' . uniqid() . '@test.com',
            'usuario_asignado' => 'test_' . uniqid(),
            'contrasena' => 'password123',
            'tipo' => 'Usuario'
        ];
        
        echo "Registrando usuario...\n";
        $resp = $this->request('POST', '/usuario/register', $user);
        echo "Respuesta registro: " . json_encode($resp) . "\n";
        echo "Cookies después de registro: " . json_encode($this->cookies) . "\n\n";
        
        // Login
        echo "Iniciando sesión...\n";
        $resp = $this->request('POST', '/usuario/login', [
            'usuario_asignado' => $user['usuario_asignado'],
            'contrasena' => $user['contrasena']
        ]);
        echo "Respuesta login: " . json_encode($resp) . "\n";
        echo "Cookies después de login: " . json_encode($this->cookies) . "\n\n";
        
        // Verificar sesión
        echo "Verificando sesión...\n";
        echo "Cookies actuales: " . json_encode($this->cookies) . "\n";
        
        echo "\n✅ Debug completado\n";
    }
}

$test = new DebugCookiesTest();
$test->testCookies();