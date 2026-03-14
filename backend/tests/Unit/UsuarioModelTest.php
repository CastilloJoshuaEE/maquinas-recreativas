<?php
use PHPUnit\Framework\TestCase;

class UsuarioModelTest extends TestCase {
    private $model; // Instancia del modelo que se va a probar.
    private static $testDb; // Conexión a la base de datos de prueba.

    /**
     * Método ejecutado UNA SOLA VEZ antes de todas las pruebas.
     * Ideal para inicialización costosa que no cambia entre pruebas.
     */
    public static function setUpBeforeClass(): void {
        self::$testDb = new TestDatabase(); // Inicializa la base de datos de prueba.
    }

    /**
     * Método ejecutado ANTES DE CADA prueba.
     * Prepara el entorno para cada caso de prueba individual.
     */
    protected function setUp(): void {
        $this->model = new UsuarioModel();
        
        // Inyectar la conexión de prueba en el modelo
        $reflection = new ReflectionClass($this->model);
        $property = $reflection->getProperty('db');
        $property->setAccessible(true);
        $property->setValue($this->model, self::$testDb);
        
        // Limpiar datos antes de cada prueba
        $conn = self::$testDb->getConnection();
        $conn->query("DELETE FROM usuario WHERE email = 'jean@admin.com'");
    }

    /**
     * CP-001
     * Prueba el registro exitoso de un usuario de tipo Administrador.
     * Verifica que: Se crea correctamente en la tabla usuario.
     */
     public function testRegistrarUsuarioAdministrador() {
        $userId = $this->model->registrarUsuario(
            'Jean',
            'Castro',
            '0909090909',
            'jean@test.com',
            'jean',
            '12345678',
            'Administrador'
        );
        
        $this->assertIsString($userId);
        $this->assertNotEmpty($userId);
        
        $conn = self::$testDb->getConnection();
        $result = $conn->query("SELECT * FROM usuario WHERE ID_Usuario = '$userId'");
        $this->assertEquals(1, $result->num_rows);
    }
    /**
     * CP-002
     * Prueba el registro exitoso de un usuario de tipo Logistica.
     * Verifica que:
     * 1. Se crea correctamente en la tabla usuario.
     * 2. Se registra en la tabla Logistica.
     */
   public function testRegistrarUsuarioLogistica() {
        $userId = $this->model->registrarUsuario(
            'Edú',
            'Sabando',
            '1316789914',
            'esb@test.com',
            'esb',
            '12345678',
            'Logistica'
        );
        
        $this->assertIsString($userId);
        $this->assertNotEmpty($userId);
        
        $conn = self::$testDb->getConnection();
        
        $result = $conn->query("SELECT * FROM usuario WHERE ID_Usuario = '$userId'");
        $this->assertEquals(1, $result->num_rows);
        
        $result = $conn->query("SELECT * FROM Logistica WHERE ID_Logistica = '$userId'");
        $this->assertEquals(1, $result->num_rows);
    }
    
    /**
     * CP-003
     * Prueba el registro exitoso de un usuario de tipo Tecnico.
     * Verifica que:
     * 1. Se crea correctamente en la tabla usuario.
     * 2. Se registra en la tabla Tecnico con la especialidad correcta.
     */
     
    public function testRegistrarUsuarioTecnicoEnsamblador() {
        $userId = $this->model->registrarUsuario(
            'Joshúa',
            'Castillo',
            '0808080808',
            'joshua@test.com',
            'joshua',
            '12345678',
            'Tecnico',
            'Ensamblador'
        );
        
        $this->assertIsString($userId);
        $this->assertNotEmpty($userId);
        
        $conn = self::$testDb->getConnection();
        
        $result = $conn->query("SELECT * FROM usuario WHERE ID_Usuario = '$userId'");
        $this->assertEquals(1, $result->num_rows);
        
        $result = $conn->query("SELECT * FROM Tecnico WHERE ID_Tecnico = '$userId'");
        $this->assertEquals(1, $result->num_rows);
        
        $row = $result->fetch_assoc();
        $this->assertEquals('Ensamblador', $row['Especialidad']);
    }

    /**
     * CP-004
     * Prueba el registro exitoso de un usuario de tipo Contabilidad.
     * Verifica que: Se crea correctamente en la tabla usuario.
     */
        public function testRegistrarUsuarioContabilidad() {
        $userId = $this->model->registrarUsuario(
            'Joel',
            'Gabino',
            '0707070707',
            'joel@test.com',
            'joel',
            '12345678',
            'Contabilidad'
        );
        
        $this->assertIsString($userId);
        $this->assertNotEmpty($userId);
        
        $conn = self::$testDb->getConnection();
        $result = $conn->query("SELECT * FROM usuario WHERE ID_Usuario = '$userId'");
        $this->assertEquals(1, $result->num_rows);
    }

    /**
     * CP-005
     * Prueba el registro fallido de un usuario de tipo Administrador.
     * Verifica que: userId devuelve false.
     */
    public function testRegistrarUsuarioAdministradorFallido() {
        // Primero registrar un usuario
        $userId = $this->model->registrarUsuario(
            'Jean',
            'Castro',
            '0909090909',
            'jean@test.com',
            'jean',
            '12345678',
            'Administrador'
        );
        
        // Intentar registrar el mismo usuario
        $result = $this->model->registrarUsuario(
            'Jean',
            'Castro',
            '0909090909',
            'jean@test.com',
            'jean',
            '12345678',
            'Administrador'
        );
        
        $this->assertIsArray($result);
        $this->assertFalse($result['success']);
        $this->assertEquals('El correo electrónico ya está registrado', $result['message']);
    }
    
    /*
    // Método ejecutado UNA SOLA VEZ después de todas las pruebas para limpiar la BD de prueba:
    public static function tearDownAfterClass(): void {
        // Eliminar la base de datos de prueba:
        self::$testDb->cleanUp();
    }
        */
}