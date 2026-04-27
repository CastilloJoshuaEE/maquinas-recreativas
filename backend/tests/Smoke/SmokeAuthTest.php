<?php
// tests/Smoke/SmokeAuthTest.php

require_once __DIR__ . '/SmokeTestCase.php';

class SmokeAuthTest extends SmokeTestCase
{
    /** @test */
    public function sePuedeRegistrarUnUsuarioNuevo()
    {
        // Primero login como admin
        $adminLogin = $this->makeRequest('POST', '/usuario/login', [
            'usuario_asignado' => 'admin_test',
            'contrasena' => 'admin123'
        ]);
        $this->assertTrue($this->isSuccessResponse($adminLogin), 'Login como admin falló');
        
        $userData = $this->createTestUserData();
        $response = $this->makeRequest('POST', '/administrador/usuarios', $userData);

        $this->assertEquals(201, $this->getLastHttpCode());
        $this->assertTrue($this->isSuccessResponse($response));
        $this->assertArrayHasKey('id', $response);
        // NOTA: El endpoint /administrador/usuarios NO devuelve usuario_asignado
        // Así que no verificamos esa clave aquí
        
        $this->testUserId = $response['id'];
        
        // Buscar el usuario creado para obtener su usuario_asignado
        $usersResponse = $this->makeRequest('GET', '/administrador/usuarios');
        $this->assertTrue($this->isSuccessResponse($usersResponse));
        
        $usuarios = $usersResponse['usuarios'] ?? [];
        $usuarioEncontrado = null;
        foreach ($usuarios as $usuario) {
            if ($usuario['id'] === $this->testUserId) {
                $usuarioEncontrado = $usuario;
                break;
            }
        }
        
        $this->assertNotNull($usuarioEncontrado, 'Usuario creado no encontrado en lista');
        $this->assertArrayHasKey('usuario_asignado', $usuarioEncontrado);
        $this->testUserUsername = $usuarioEncontrado['usuario_asignado'];
    }

    /** @test */
    public function sePuedeIniciarSesionConCredencialesValidas()
    {
        // Crear usuario usando admin
        $adminLogin = $this->makeRequest('POST', '/usuario/login', [
            'usuario_asignado' => 'admin_test',
            'contrasena' => 'admin123'
        ]);
        $this->assertTrue($this->isSuccessResponse($adminLogin), 'Login como admin falló');
        
        $userData = $this->createTestUserData();
        $registerResponse = $this->makeRequest('POST', '/administrador/usuarios', $userData);

        $this->assertEquals(201, $this->getLastHttpCode(), 'El registro debería devolver 201');
        $this->assertTrue($this->isSuccessResponse($registerResponse));

        $this->testUserId = $registerResponse['id'] ?? null;
        
        // Buscar el usuario creado para obtener su usuario_asignado
        $usersResponse = $this->makeRequest('GET', '/administrador/usuarios');
        $this->assertTrue($this->isSuccessResponse($usersResponse));
        
        $usuarios = $usersResponse['usuarios'] ?? [];
        $assignedUsername = null;
        foreach ($usuarios as $usuario) {
            if ($usuario['id'] === $this->testUserId) {
                $assignedUsername = $usuario['usuario_asignado'] ?? null;
                break;
            }
        }

        $this->assertNotEmpty($assignedUsername, 'El servidor debe tener usuario_asignado en BD');
        
        // Cerrar sesión de admin
        $this->makeRequest('POST', '/usuario/logout', []);
        $this->clearCookies();

        $response = $this->makeRequest('POST', '/usuario/login', [
            'usuario_asignado' => $assignedUsername,
            'contrasena'       => 'Password123!',
        ]);

        $this->assertEquals(200, $this->getLastHttpCode());
        $this->assertTrue($this->isSuccessResponse($response));
        $this->assertArrayHasKey('usuario', $response);
        $this->assertArrayHasKey('id', $response['usuario']);
    }

    // El resto de métodos se mantienen igual...
    /** @test */
    public function noSePuedeIniciarSesionConCredencialesInvalidas()
    {
        $response = $this->makeRequest('POST', '/usuario/login', [
            'usuario_asignado' => 'usuario_que_no_existe_' . uniqid(),
            'contrasena'       => 'password_incorrecta',
        ]);

        $this->assertEquals(200, $this->getLastHttpCode());
        $this->assertFalse(
            $this->isSuccessResponse($response),
            'Un login con credenciales inválidas no debe devolver success:true'
        );
    }

    /** @test */
    public function sePuedeCerrarSesion()
    {
        if (!$this->registerAndLoginTestUser()) {
            $this->markTestSkipped('No se pudo crear y loguear usuario de prueba');
        }

        $response = $this->makeRequest('POST', '/usuario/logout', []);

        $this->assertEquals(200, $this->getLastHttpCode());
        $this->assertTrue($this->isSuccessResponse($response));

        $this->clearCookies();
    }

    /** @test */
    public function rutasPrivadasRequierenAutenticacion()
    {
        $this->clearCookies();

        $rutasPrivadas = [
            ['GET',  '/usuario/perfil'],
            ['POST', '/comercio/register'],
            ['GET',  '/maquina/distribucion'],
            ['POST', '/reportes/crear'],
        ];

        foreach ($rutasPrivadas as [$method, $path]) {
            $response = $this->makeRequest($method, $path, []);

            $this->assertFalse(
                $this->isSuccessResponse($response),
                "La ruta {$method} {$path} debería rechazar peticiones sin autenticación"
            );
        }
    }
}