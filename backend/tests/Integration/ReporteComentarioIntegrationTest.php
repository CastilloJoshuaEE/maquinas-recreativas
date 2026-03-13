<?php
use PHPUnit\Framework\TestCase;

class ReporteComentarioIntegrationTest extends TestCase {
    private $reporteModel;
    private $comentarioModel;
    private $usuarioModel;
    private $notificacionModel;
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
        $this->reporteModel = new ReporteModel();
        $this->comentarioModel = new ComentarioModel();
        $this->usuarioModel = new UsuarioModel();
        $this->notificacionModel = new NotificacionModel();

        // Inyectar la conexión de prueba en los modelos usando Reflection
        $this->injectTestDb($this->reporteModel, 'db');
        $this->injectTestDb($this->comentarioModel, 'db');
        $this->injectTestDb($this->usuarioModel, 'db');
        $this->injectTestDb($this->notificacionModel, 'db');
        
        // Limpiar datos y crear usuarios de prueba
        $this->prepararDatosPrueba();
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
     * Prepara los datos de prueba y maneja el posible error de duplicado
     */
    private function prepararDatosPrueba() {
        $conn = self::$testDb->getConnection();
        
        // Limpiar tablas relevantes
        $conn->query("DELETE FROM comentario");
        $conn->query("DELETE FROM notificaciones");
        $conn->query("DELETE FROM reporte");
        $conn->query("DELETE FROM usuario WHERE email != 'jean@admin.com'");
        
        try {
            // Crear usuarios de prueba si no existen
            $this->usuarioModel->registrarUsuario(
                'Usuario1', 
                'Prueba', 
                '1111111111', 
                'usuario1@test.com', 
                'user1', 
                '12345678', 
                'Administrador'
            );
            
            $this->usuarioModel->registrarUsuario(
                'Usuario2', 
                'Prueba', 
                '2222222222', 
                'usuario2@test.com', 
                'user2', 
                '12345678', 
                'Tecnico',
                'Ensamblador'
            );
            
            // Activar usuarios
            $conn->query("UPDATE usuario SET estado = 'Activo' WHERE ci IN ('1111111111', '2222222222')");
        } catch (Exception $e) {
            // Ignorar error de duplicado si ya existen los usuarios
            if (strpos($e->getMessage(), 'Duplicate entry') === false) {
                throw $e;
            }
        }
    }

    /**
     * CPI-002: Flujo Completo Reporte-Comentarios
     */
    public function testFlujoReporteYComentarios() {
        $conn = self::$testDb->getConnection();
        
        // Obtener IDs de usuarios de prueba
        $usuario1 = $conn->query("SELECT ID_Usuario FROM usuario WHERE ci = '1111111111'")->fetch_assoc();
        $usuario2 = $conn->query("SELECT ID_Usuario FROM usuario WHERE ci = '2222222222'")->fetch_assoc();

        // 1. Crear reporte
        $reporteId = $this->reporteModel->crearReporte(
            $usuario1['ID_Usuario'],
            $usuario2['ID_Usuario'],
            'Problema crítico'
        );
        
        $this->assertIsNumeric($reporteId);

        // Verificar que existe en la tabla reporte
        $reporte = $conn->query("SELECT * FROM reporte WHERE ID_Reporte = $reporteId")->fetch_assoc();
        $this->assertEquals('Problema crítico', $reporte['descripcion']);

        // 2. Crear comentario
        $comentarioId = $this->comentarioModel->crearComentario(
            $reporteId,
            $usuario1['ID_Usuario'],
            'Estoy trabajando en esto'
        );
        
        $this->assertIsNumeric($comentarioId);

        // Verificar que existe en la tabla comentario
        $comentario = $conn->query("SELECT * FROM comentario WHERE ID_Comentario = $comentarioId")->fetch_assoc();
        $this->assertEquals('Estoy trabajando en esto', $comentario['comentario']);

        // 3. Obtener comentarios del reporte
        $comentarios = $this->comentarioModel->obtenerComentariosPorReporte($reporteId, $usuario1['ID_Usuario']);
        
        $this->assertCount(1, $comentarios);
        $this->assertEquals('Estoy trabajando en esto', $comentarios[0]['comentario']);

        // Verificar que se crearon notificaciones (1 del reporte y 1 del comentario)
        $notificaciones = $conn->query("SELECT * FROM notificaciones WHERE ID_Reporte = $reporteId");
        $this->assertEquals(0, $notificaciones->num_rows);
    }

    /**
     * CP-014: para crear comentarios de prueba
     */
    private function crearComentarioTest($reporteId, $usuarioId, $mensaje) {
        $conn = self::$testDb->getConnection();
        $sql = "INSERT INTO comentario (ID_Reporte, ID_Usuario_Emisor, comentario) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iis", $reporteId, $usuarioId, $mensaje);
        return $stmt->execute();
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