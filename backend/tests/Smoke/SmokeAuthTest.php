<?php
// tests/Smoke/SmokeAuthTest.php

require_once __DIR__ . '/SmokeTestCase.php';

class SmokeAuthTest extends SmokeTestCase
{
    /** @test */
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

    /** @test */
    public function sePuedeIniciarSesionConCredencialesValidas()
    {
        $userData         = $this->createTestUser();
        $registerResponse = $this->makeRequest('POST', '/usuario/register', $userData);

        $this->assertEquals(201, $this->getLastHttpCode(), 'El registro debería devolver 201');
        $this->assertTrue($this->isSuccessResponse($registerResponse));

        $this->testUserId     = $registerResponse['userId'] ?? null;
        $assignedUsername     = $registerResponse['usuario_asignado'];

        $this->assertNotEmpty($assignedUsername, 'El servidor debe devolver usuario_asignado');

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
     * El servidor devuelve HTTP 200 para credenciales inválidas.
     * La respuesta puede ser {"success": false, ...} o {"error": "..."} según
     * si la excepción la maneja el controlador o el handler global — ambas formas
     * indican fallo y son válidas para este smoke test.
     */
    public function noSePuedeIniciarSesionConCredencialesInvalidas()
    {
        $response = $this->makeRequest('POST', '/usuario/login', [
            'usuario_asignado' => 'usuario_que_no_existe_' . uniqid(),
            'contrasena'       => 'password_incorrecta',
        ]);

        // El servidor siempre devuelve 200 (nunca 401) para login fallido
        $this->assertEquals(200, $this->getLastHttpCode());
        // La respuesta no debe indicar éxito
        $this->assertFalse(
            $this->isSuccessResponse($response),
            'Un login con credenciales inválidas no debe devolver success:true'
        );
    }

    /** @test */
    public function sePuedeCerrarSesion()
    {
        $this->loginAsTestUser();

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

    /** @test */
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