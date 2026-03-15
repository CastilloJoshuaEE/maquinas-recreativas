<?php
use PHPUnit\Framework\TestCase;

class UsuarioTest extends TestCase {
    private $usuarioModel;
    private $administradorModel; 
    private $maquinaModel;
    private $comercioModel;
    private static $testDb;

    public static function setUpBeforeClass(): void {
        self::$testDb = new TestDatabase();
    }

    protected function setUp(): void {
        $this->usuarioModel = new UsuarioModel();
        $this->administradorModel = new AdministradorModel();
        $this->maquinaModel = new MaquinaModel();
        $this->comercioModel = new ComercioModel();

        $this->injectTestDb($this->usuarioModel, 'db');
        $this->injectTestDb($this->administradorModel, 'db');
        $this->injectTestDb($this->maquinaModel, 'db');
        $this->injectTestDb($this->comercioModel, 'db');
        
        $conn = self::$testDb->getConnection();
        $conn->query("DELETE FROM usuario WHERE email = 'jean@admin.com'");
    }

    private function injectTestDb($object, $propertyName) {
        $reflection = new ReflectionClass($object);
        $property = $reflection->getProperty($propertyName);
        $property->setAccessible(true);
        $property->setValue($object, self::$testDb);
    }
    
    /**
     * CP-006 - Prueba el registro fallido con contraseña corta.
     */
    public function testRegistroContrasenaCorta() {
        $resultado = $this->usuarioModel->registrarUsuario(
            'Edú',
            'Barberan',
            '0016789914',
            'esbsabando@gmail.com',
            'esbbarberan',
            '1234', // Contraseña corta
            'Contabilidad'
        );
        
        $this->assertIsArray($resultado);
        $this->assertFalse($resultado['success']);
        $this->assertEquals('La contraseña debe tener al menos 6 caracteres', $resultado['message']);
    }
    
    /**
     * CP-008 - Prueba que no se puede eliminar un usuario con dependencias
     */
    public function testEliminarUsuarioConDependencias() {
        $conn = self::$testDb->getConnection();
        
        // Crear comercio
        $comercioId = $this->comercioModel->registrarComercio(
            "Comercio Test",
            "Minorista",
            "Dirección",
            "0999999999"
        );

        $this->assertTrue($comercioId);

        // Obtener el ID del comercio
        $comercio = $conn->query("SELECT ID_Comercio FROM Comercio WHERE Nombre = 'Comercio Test'")->fetch_assoc();
        $idComercio = $comercio['ID_Comercio'];

        // Crear usuario técnico ensamblador
        $resultadoEnsamblador = $this->administradorModel->registrarUsuarioAdmin([
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
        $resultadoComprobador = $this->administradorModel->registrarUsuarioAdmin([
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

        $this->assertIsString($resultadoEnsamblador);
        $this->assertIsString($resultadoComprobador);
        $this->assertNotEmpty($resultadoEnsamblador);
        $this->assertNotEmpty($resultadoComprobador);

        // Registrar máquina
        $idMaquina = $this->maquinaModel->registrarMaquina(
            'Máquina de prueba',
            'Tipo prueba',
            $resultadoEnsamblador,
            $resultadoComprobador,
            $idComercio
        );

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('No se puede eliminar el usuario porque tiene máquinas asignadas');
        
        $this->administradorModel->eliminarUsuario($resultadoEnsamblador);
    }
    
    /**
     * CP-007 - Búsqueda de usuario inexistente
     */
    public function testObtenerUsuarioInexistente() {
        $idInexistente = '00000000-0000-0000-0000-000000000000';
        $usuario = $this->usuarioModel->obtenerUsuarioPorId($idInexistente);
        
        $this->assertFalse($usuario);
    }
}
?>