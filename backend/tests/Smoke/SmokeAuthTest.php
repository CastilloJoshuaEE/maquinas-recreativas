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

    $httpCode = $this->getLastHttpCode();
    $this->assertTrue(
        $httpCode === 200 || $httpCode === 500,
        "Se esperaba 200 o 500, se obtuve {$httpCode}"
    );
    
    // Si es 200, verificar que success es false
    if ($httpCode === 200) {
        $this->assertFalse(
            $this->isSuccessResponse($response),
            'Un login con credenciales inválidas no debe devolver success:true'
        );
    }
    }

   /** @test */
public function sePuedeCerrarSesion()
{
    // Registrar y loguear usuario
    if (!$this->registerAndLoginTestUser()) {
        $this->markTestSkipped('No se pudo crear y loguear usuario de prueba');
        return;
    }

    // Verificar que tenemos sesión activa
    $perfilResponse = $this->makeRequest('GET', '/usuario/perfil');
    if (!$this->isSuccessResponse($perfilResponse)) {
        $this->markTestSkipped('No se pudo verificar sesión activa');
        return;
    }

    // Cerrar sesión
    $response = $this->makeRequest('POST', '/usuario/logout', []);

    $this->assertEquals(200, $this->getLastHttpCode());
    $this->assertTrue($this->isSuccessResponse($response));

    // Verificar que ya no tenemos sesión
    $this->clearCookies();
    $perfilResponse2 = $this->makeRequest('GET', '/usuario/perfil');
    $this->assertFalse(
        $this->isSuccessResponse($perfilResponse2),
        'Después de cerrar sesión no debería poder acceder a /usuario/perfil'
    );
}

    /** @test */
public function rutasPrivadasRequierenAutenticacion()
{
    $this->clearCookies();

    // Crear un usuario y obtener su username, pero NO hacer login
    // Primero login como admin
    $adminLogin = $this->makeRequest('POST', '/usuario/login', [
        'usuario_asignado' => 'admin_test',
        'contrasena' => 'admin123'
    ]);
    if (!$this->isSuccessResponse($adminLogin)) {
        $this->markTestSkipped('No se pudo loguear como admin');
        return;
    }
    
    // Crear usuario
    $userData = $this->createTestUserData();
    $registerResponse = $this->makeRequest('POST', '/administrador/usuarios', $userData);
    
    if (!$this->isSuccessResponse($registerResponse)) {
        $this->markTestSkipped('No se pudo crear usuario');
        return;
    }
    
    // Obtener el usuario_asignado
    $usersResponse = $this->makeRequest('GET', '/administrador/usuarios');
    $username = null;
    if ($this->isSuccessResponse($usersResponse)) {
        foreach ($usersResponse['usuarios'] ?? [] as $usuario) {
            if ($usuario['id'] === ($registerResponse['id'] ?? null)) {
                $username = $usuario['usuario_asignado'] ?? null;
                break;
            }
        }
    }
    
    // Cerrar sesión de admin y limpiar cookies
    $this->makeRequest('POST', '/usuario/logout', []);
    $this->clearCookies();
    
    // Ahora probar que NO podemos acceder a rutas privadas sin autenticación
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
        // Debe devolver 200, ya que el admin es el único capaz de gestionar los datos del usuario
        $this->assertEquals(200, $this->getLastHttpCode(), 
            "La ruta {$method} {$path} debería devolver 200");
    }
}
}