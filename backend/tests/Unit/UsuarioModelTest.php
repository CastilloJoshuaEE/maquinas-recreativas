<?php
use PHPUnit\Framework\TestCase;

class UsuarioModelTest extends TestCase {
    private $model;
    private static $testDb;

    public static function setUpBeforeClass(): void {
        self::$testDb = new TestDatabase();
    }

    protected function setUp(): void {
        $this->model = new UsuarioModel();
        
        $reflection = new ReflectionClass($this->model);
        $property = $reflection->getProperty('db');
        $property->setAccessible(true);
        $property->setValue($this->model, self::$testDb);
        
        $conn = self::$testDb->getConnection();

        // Limpiar todos los usuarios de prueba antes de cada test.
        // No se puede filtrar por email (está encriptado), se filtra por usuario_asignado
        // que es texto plano y único. Se excluye el admin inicial de TestDatabase.
        $conn->query("SET FOREIGN_KEY_CHECKS = 0");
        $conn->query("DELETE FROM Logistica");
        $conn->query("DELETE FROM Tecnico");
        $conn->query("DELETE FROM usuario WHERE usuario_asignado != 'admin'");
        $conn->query("SET FOREIGN_KEY_CHECKS = 1");
    }

    /**
     * CP-001 - Prueba el registro exitoso de un usuario de tipo Administrador.
     */
    public function testRegistrarUsuarioAdministrador() {
        $resultado = $this->model->registrarUsuario(
            'Jean',
            'Castro',
            '0909090909',
            'jean@test.com',
            'jean',
            '12345678',
            'Administrador'
        );
        
        $this->assertIsArray($resultado);
        $this->assertTrue($resultado['success']);
        $this->assertIsString($resultado['userId']);
        $this->assertNotEmpty($resultado['userId']);
        
        $conn = self::$testDb->getConnection();
        $result = $conn->query("SELECT * FROM usuario WHERE ID_Usuario = '{$resultado['userId']}'");
        $this->assertEquals(1, $result->num_rows);
    }
    
    /**
     * CP-002 - Prueba el registro exitoso de un usuario de tipo Logistica.
     */
    public function testRegistrarUsuarioLogistica() {
        $resultado = $this->model->registrarUsuario(
            'Edú',
            'Sabando',
            '1316789914',
            'esb@test.com',
            'esb',
            '12345678',
            'Logistica'
        );
        
        $this->assertIsArray($resultado);
        $this->assertTrue($resultado['success']);
        $this->assertIsString($resultado['userId']);
        $this->assertNotEmpty($resultado['userId']);
        
        $conn = self::$testDb->getConnection();
        
        $result = $conn->query("SELECT * FROM usuario WHERE ID_Usuario = '{$resultado['userId']}'");
        $this->assertEquals(1, $result->num_rows);
        
        $result = $conn->query("SELECT * FROM Logistica WHERE ID_Logistica = '{$resultado['userId']}'");
        $this->assertEquals(1, $result->num_rows);
    }
    
    /**
     * CP-003 - Prueba el registro exitoso de un usuario de tipo Tecnico.
     */
    public function testRegistrarUsuarioTecnicoEnsamblador() {
        $resultado = $this->model->registrarUsuario(
            'Joshúa',
            'Castillo',
            '0808080808',
            'joshua@test.com',
            'joshua',
            '12345678',
            'Tecnico',
            'Ensamblador'
        );
        
        $this->assertIsArray($resultado);
        $this->assertTrue($resultado['success']);
        $this->assertIsString($resultado['userId']);
        $this->assertNotEmpty($resultado['userId']);
        
        $conn = self::$testDb->getConnection();
        
        $result = $conn->query("SELECT * FROM usuario WHERE ID_Usuario = '{$resultado['userId']}'");
        $this->assertEquals(1, $result->num_rows);
        
        $result = $conn->query("SELECT * FROM Tecnico WHERE ID_Tecnico = '{$resultado['userId']}'");
        $this->assertEquals(1, $result->num_rows);
        
        $row = $result->fetch_assoc();
        $this->assertEquals('Ensamblador', $row['Especialidad']);
    }

    /**
     * CP-004 - Prueba el registro exitoso de un usuario de tipo Contabilidad.
     */
    public function testRegistrarUsuarioContabilidad() {
        $resultado = $this->model->registrarUsuario(
            'Joel',
            'Gabino',
            '0707070707',
            'joel@test.com',
            'joel',
            '12345678',
            'Contabilidad'
        );
        
        $this->assertIsArray($resultado);
        $this->assertTrue($resultado['success']);
        $this->assertIsString($resultado['userId']);
        $this->assertNotEmpty($resultado['userId']);
        
        $conn = self::$testDb->getConnection();
        $result = $conn->query("SELECT * FROM usuario WHERE ID_Usuario = '{$resultado['userId']}'");
        $this->assertEquals(1, $result->num_rows);
    }

    /**
     * CP-005 - Prueba el registro fallido de un usuario duplicado.
     */
    public function testRegistrarUsuarioAdministradorFallido() {
        // Primero registrar un usuario (setUp ya limpió la BD, así que esto debe funcionar)
        $resultado = $this->model->registrarUsuario(
            'Jean',
            'Castro',
            '0909090909',
            'jean@test.com',
            'jean',
            '12345678',
            'Administrador'
        );
        
        $this->assertTrue($resultado['success']);
        
        // Intentar registrar el mismo usuario (mismo email, ci y usuario_asignado)
        $resultado2 = $this->model->registrarUsuario(
            'Jean',
            'Castro',
            '0909090909',
            'jean@test.com',
            'jean',
            '12345678',
            'Administrador'
        );
        
        $this->assertIsArray($resultado2);
        $this->assertFalse($resultado2['success']);
        $this->assertEquals('El correo electrónico ya está registrado', $resultado2['message']);
    }
}
?>