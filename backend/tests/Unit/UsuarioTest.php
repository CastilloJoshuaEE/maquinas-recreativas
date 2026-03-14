<?php
use PHPUnit\Framework\TestCase;

class UsuarioTest extends TestCase {
    private $usuarioModel;
    private $administradorModel; 
    private $maquinaModel;
    private $comercioModel;
    private static $testDb;

    /**
     * Método ejecutado UNA SOLA VEZ antes de todas las pruebas.
     */
    public static function setUpBeforeClass(): void {
        self::$testDb = new TestDatabase();
    }

    /**
     * Método ejecutado ANTES DE CADA prueba.
     */
    protected function setUp(): void {
        // Inicializar modelos
        $this->usuarioModel = new UsuarioModel();
        $this->administradorModel = new AdministradorModel();
        $this->maquinaModel = new MaquinaModel();
        $this->comercioModel = new ComercioModel();

        // Inyectar la conexión de prueba en los modelos usando Reflection
        $this->injectTestDb($this->usuarioModel, 'db');
        $this->injectTestDb($this->administradorModel, 'db');
        $this->injectTestDb($this->maquinaModel, 'db');
        $this->injectTestDb($this->comercioModel, 'db');
          // Limpiar datos antes de cada prueba
        $conn = self::$testDb->getConnection();
        $conn->query("DELETE FROM usuario WHERE email = 'jean@admin.com'");
        
    }

    /**
     * Helper para inyectar la conexión de prueba en los modelos
     */
    private function injectTestDb($object, $propertyName) {
        $reflection = new ReflectionClass($object);
        $property = $reflection->getProperty($propertyName);
        $property->setAccessible(true);
        $property->setValue($object, self::$testDb);
    }
    /**
     * CP-006
     * Prueba el registro fallido con contraseña corta.
     */
    public function testRegistroContrasenaCorta() {
        $this->expectException(ValidacionDatosException::class);
        $this->expectExceptionCode(1200);

        $this->usuarioModel->registrarUsuario(
            'Edú',
            'Barberan',
            '0016789914',
            'esbsabando@gmail.com',
            'esbbarberan',
            '1234',
            'Contabilidad'
        );
    }
    /**
     * CP-008
     * Prueba que no se puede eliminar un usuario con dependencias, 
     * Debe fallar exitosamente cuando se encuentra un usuario con dependencias muy fuertes como lo son los tecnicos
     * Debe esperarse un: OK (3 tests, 6 assertions)
     */
     public function testEliminarUsuarioConDependencias() {
        $conn = self::$testDb->getConnection();
        
        // Crear comercio
        $idComercio = $this->comercioModel->registrarComercio(
            "Comercio Test",
            "Minorista",
            "Dirección",
            "0999999999"
        );

        $this->assertIsString($idComercio);
        $this->assertNotEmpty($idComercio);

        // Crear usuario técnico ensamblador
        $idUsuarioEnsamblador = $this->administradorModel->registrarUsuarioAdmin([
            'nombre' => 'Tecnico',
            'apellido' => 'Cast',
            'ci' => '1234567899',
            'email' => 'tecnico@gmail.com',
            'usuario_asignado' => 'tecnico05',
            'contrasena' => '12345678',
            'tipo' => 'Tecnico',
            'estado' => 'Activo',
            'especialidad' => 'Ensamblador'
        ]);

        // Crear usuario técnico comprobador
        $idUsuarioComprobador = $this->administradorModel->registrarUsuarioAdmin([
            'nombre' => 'Comprobador',
            'apellido' => 'Kaka',
            'ci' => '0234567999',
            'email' => 'tecnico2222@hotmail.com',
            'usuario_asignado' => 'tecnico007',
            'contrasena' => '12345678',
            'tipo' => 'Tecnico',
            'estado' => 'Activo',
            'especialidad' => 'Comprobador'
        ]);

        // Registrar máquina
        $idMaquina = $this->maquinaModel->registrarMaquina(
            'Máquina de prueba',
            'Tipo prueba',
            $idUsuarioEnsamblador,
            $idUsuarioComprobador,
            $idComercio
        );

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('No se puede eliminar el usuario porque tiene máquinas asignadas');
        
        $this->administradorModel->eliminarUsuario($idUsuarioEnsamblador);
    }
    /**
     * CP-007
     * Búsqueda de usuario inexistente
     * 
     * Prueba obtener un usuario inexistente.
     */    
    public function testObtenerUsuarioInexistente() {
        $idInexistente = '00000000-0000-0000-0000-000000000000';
        $usuario = $this->usuarioModel->obtenerUsuarioPorId($idInexistente);
        
        $this->assertFalse($usuario);
    }

    /**
     * Método ejecutado UNA SOLA VEZ después de todas las pruebas para limpiar la BD de prueba.
     */
    /*
    public static function tearDownAfterClass(): void {
        self::$testDb->cleanUp();
    }
        */
}