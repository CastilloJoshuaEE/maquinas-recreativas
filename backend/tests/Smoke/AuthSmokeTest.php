<?php
// tests/Smoke/AuthSmokeTest.php

require_once __DIR__ . '/SmokeTestCase.php';

class AuthSmokeTest extends SmokeTestCase
{
    /**
     * @test
     */
    public function sePuedeRegistrarUnUsuarioNuevo()
    {
        $userData = $this->createTestUser();

        $response = $this->makeRequest('POST', '/usuario/register', $userData);

        $this->assertEquals(201, $this->getLastHttpCode());
        $this->assertTrue($this->isSuccessResponse($response));
        $this->assertArrayHasKey('userId', $response);
        $this->assertArrayHasKey('usuario_asignado', $response);

        $this->testUserId = $response['userId'];
    }

    /**
     * @test
     */
    public function sePuedeIniciarSesionConCredencialesValidas()
    {
        // Registrar
        $userData         = $this->createTestUser();
        $registerResponse = $this->makeRequest('POST', '/usuario/register', $userData);

        $this->assertEquals(201, $this->getLastHttpCode(),
            'El registro debería devolver 201');
        $this->assertTrue($this->isSuccessResponse($registerResponse));

        $this->testUserId = $registerResponse['userId'] ?? null;

        // El servidor asigna su propio usuario_asignado (lo genera desde nombre+apellido).
        // Hay que leerlo de la RESPUESTA, no usar el que enviamos.
        $assignedUsername = $registerResponse['usuario_asignado'];
        $this->assertNotEmpty($assignedUsername, 'El servidor debe devolver usuario_asignado');

        // Login con las credenciales reales asignadas por el servidor
        $response = $this->makeRequest('POST', '/usuario/login', [
            'usuario_asignado' => $assignedUsername,
            'contrasena'       => $userData['contrasena'],
        ]);

        $this->assertEquals(200, $this->getLastHttpCode());
        $this->assertTrue($this->isSuccessResponse($response));
        $this->assertArrayHasKey('usuario', $response);
        $this->assertArrayHasKey('id', $response['usuario']);
    }

    /**
     * @test
     *
     * El servidor devuelve HTTP 200 con success:false para credenciales inválidas
     * (comportamiento verificado en SmokeUsuarioTest::elLoginRespondeConCredencialesInvalidas).
     */
    public function noSePuedeIniciarSesionConCredencialesInvalidas()
    {
        $response = $this->makeRequest('POST', '/usuario/login', [
            'usuario_asignado' => 'usuario_que_no_existe_' . uniqid(),
            'contrasena'       => 'password_incorrecta',
        ]);

        // El servidor responde 200 con success:false — nunca lanza 401 en login fallido
        $this->assertEquals(200, $this->getLastHttpCode());
        $this->assertFalse($this->isSuccessResponse($response));
        $this->assertArrayHasKey('message', $response);
    }

    /**
     * @test
     */
    public function sePuedeCerrarSesion()
    {
        $this->loginAsTestUser();

        $response = $this->makeRequest('POST', '/usuario/logout', []);

        $this->assertEquals(200, $this->getLastHttpCode());
        $this->assertTrue($this->isSuccessResponse($response));

        $this->clearCookies();
    }

    /**
     * @test
     *
     * Verifica que las rutas privadas rechazan peticiones sin sesión.
     * El servidor responde con success:false (no con HTTP 401).
     */
    public function rutasPrivadasRequierenAutenticacion()
    {
        $this->clearCookies(); // Garantizar que no hay sesión activa

        $rutasPrivadas = [
            ['GET',  '/usuario/perfil'],
            ['POST', '/comercio/register'],
            ['GET',  '/maquina/distribucion'],
            ['POST', '/reportes/crear'],
        ];

        foreach ($rutasPrivadas as [$method, $path]) {
            $response = $this->makeRequest($method, $path, []);

            // El servidor usa success:false en vez de HTTP 401 como señal de no autenticado
            $this->assertFalse(
                $this->isSuccessResponse($response),
                "La ruta {$method} {$path} debería rechazar peticiones sin autenticación"
            );
        }
    }

    /**
     * @test
     */
    public function sePuedeRecuperarNombreDeUsuarioPorEmail()
    {
        $userData = $this->createTestUser();
        $this->makeRequest('POST', '/usuario/register', $userData);

        $response = $this->makeRequest('POST', '/usuario/recuperar-usuario', [
            'email'         => $userData['email'],
            'nuevo_usuario' => 'nuevo_nombre_' . uniqid(),
        ]);

        $this->assertEquals(200, $this->getLastHttpCode());
        $this->assertTrue($this->isSuccessResponse($response));
    }
}