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

    /***********************/
    // PRUEBAS UNITARIAS
    /**********************/

    // PRUEBAS PARA EL MÉTODO registrarUsuario()

    /**
     * CP-001
     * Prueba el registro exitoso de un usuario de tipo Administrador.
     * Verifica que: Se crea correctamente en la tabla usuario.
     */
    public function testRegistrarUsuarioAdministrador() {
        // 1. Ejecutar el método a probar con datos de prueba
        $userId = $this->model->registrarUsuario(
            'Jean',            // nombre
            'Castro',          // apellido
            '0909090909',      // ci
            'jean@test.com',   // email
            'jean',            // usuario_asignado
            '12345678',        // contrasena
            'Administrador'    // tipo
        );
        
        // 2. Verificar que devuelve un ID válido:
        $this->assertIsInt($userId);
        
        // Obtener conexión para verificar en la BD:
        $conn = self::$testDb->getConnection();
        
        // 3. Verificar que existe en la tabla usuario:
        $result = $conn->query("SELECT * FROM usuario WHERE ID_Usuario = $userId");
        $this->assertEquals(1, $result->num_rows, 'El usuario no se creó en la tabla usuario');
    }
    
    /**
     * CP-002
     * Prueba el registro exitoso de un usuario de tipo Logistica.
     * Verifica que:
     * 1. Se crea correctamente en la tabla usuario.
     * 2. Se registra en la tabla Logistica.
     */
    public function testRegistrarUsuarioLogistica() {
        // 1. Ejecutar el método a probar con datos de prueba:
        $userId = $this->model->registrarUsuario(
            'Edú',          // nombre
            'Sabando',      // apellido
            '1316789914',   // ci
            'esb@test.com', // email
            'esb',          // usuario_asignado
            '12345678',     // contrasena
            'Logistica'     // tipo
        );
        
        // 2. Verificar que devuelve un ID válido:
        $this->assertIsInt($userId);
        
        // Obtener conexión para verificar en la BD:
        $conn = self::$testDb->getConnection();
        
        // 3. Verificar que existe en la tabla usuario:
        $result = $conn->query("SELECT * FROM usuario WHERE ID_Usuario = $userId");
        $this->assertEquals(1, $result->num_rows, 'El usuario no se creó en la tabla usuario');
        
        // 4. Verificar que existe en la tabla Logistica:
        $result = $conn->query("SELECT * FROM Logistica WHERE ID_Logistica = $userId");
        $this->assertEquals(1, $result->num_rows, 'El usuario no se registró en Logistica');
    }
    
    /**
     * CP-003
     * Prueba el registro exitoso de un usuario de tipo Tecnico.
     * Verifica que:
     * 1. Se crea correctamente en la tabla usuario.
     * 2. Se registra en la tabla Tecnico con la especialidad correcta.
     */
    public function testRegistrarUsuarioTecnicoEnsamblador() {
        // 1. Ejecutar el método a probar con datos de prueba
        $userId = $this->model->registrarUsuario(
            'Joshúa',          // nombre
            'Castillo',        // apellido
            '0808080808',      // ci
            'joshua@test.com', // email
            'joshua',          // usuario_asignado
            '12345678',        // contrasena
            'Tecnico',         // tipo
            'Ensamblador'      // Especialidad
        );
        
        // 2. Verificar que devuelve un ID válido:
        $this->assertIsInt($userId);
        
        // Obtener conexión para verificar en la BD:
        $conn = self::$testDb->getConnection();
        
        // 3. Verificar que existe en la tabla usuario:
        $result = $conn->query("SELECT * FROM usuario WHERE ID_Usuario = $userId");
        $this->assertEquals(1, $result->num_rows, 'El usuario no se creó en la tabla usuario');
        
        // 4. Verificar que existe en la tabla Tecnico con la especialidad correcta:
        $result = $conn->query("SELECT * FROM Tecnico WHERE ID_Tecnico = $userId");
        $this->assertEquals(1, $result->num_rows, 'El usuario no se registró en Tecnico');
        
        // 5. Verificar la especialidad:
        $row = $result->fetch_assoc();
        $this->assertEquals('Ensamblador', $row['Especialidad'], 'La especialidad no coincide');
    }

    /**
     * CP-004
     * Prueba el registro exitoso de un usuario de tipo Contabilidad.
     * Verifica que: Se crea correctamente en la tabla usuario.
     */
    public function testRegistrarUsuarioContabilidad() {
        // 1. Ejecutar el método a probar con datos de prueba
        $userId = $this->model->registrarUsuario(
            'Joel',            // nombre
            'Gabino',          // apellido
            '0707070707',      // ci
            'joel@test.com',   // email
            'joel',            // usuario_asignado
            '12345678',        // contrasena
            'Contabilidad'     // tipo
        );
        
        // 2. Verificar que devuelve un ID válido:
        $this->assertIsInt($userId);
        
        // Obtener conexión para verificar en la BD:
        $conn = self::$testDb->getConnection();
        
        // 3. Verificar que existe en la tabla usuario:
        $result = $conn->query("SELECT * FROM usuario WHERE ID_Usuario = $userId");
        $this->assertEquals(1, $result->num_rows, 'El usuario no se creó en la tabla usuario');
    }

    /**
     * CP-005
     * Prueba el registro fallido de un usuario de tipo Administrador.
     * Verifica que: userId devuelve false.
     */
    public function testRegistrarUsuarioAdministradorFallido() {
        // 1. Ejecutar el método a probar con datos existente:
        $userId = $this->model->registrarUsuario(
            'Jean',            // nombre
            'Castro',          // apellido
            '0909090909',      // ci
            'jean@test.com',   // email
            'jean',            // usuario_asignado
            '12345678',        // contrasena
            'Administrador'    // tipo
        );
        
        // 2. Verificar que devuelve un false:
        $this->assertFalse($userId);
    }
    
    // Método ejecutado UNA SOLA VEZ después de todas las pruebas para limpiar la BD de prueba:
    public static function tearDownAfterClass(): void {
        // Eliminar la base de datos de prueba:
        self::$testDb->cleanUp();
    }
}