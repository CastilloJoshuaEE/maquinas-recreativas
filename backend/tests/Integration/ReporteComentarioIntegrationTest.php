<?php
use PHPUnit\Framework\TestCase;

class ReporteComentarioIntegrationTest extends TestCase {
    private $reporteModel;
    private $comentarioModel;
    private $usuarioModel;
    private $notificacionModel;
    private static $testDb;
    private $usuario1Id;
    private $usuario2Id;
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
        $this->reporteModel = new ReporteModel();
        $this->comentarioModel = new ComentarioModel();
        $this->usuarioModel = new UsuarioModel();
        $this->notificacionModel = new NotificacionModel();

        $this->injectTestDb($this->reporteModel, 'db');
        $this->injectTestDb($this->comentarioModel, 'db');
        $this->injectTestDb($this->usuarioModel, 'db');
        $this->injectTestDb($this->notificacionModel, 'db');
        
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
        
        $conn->query("SET FOREIGN_KEY_CHECKS = 0");
        $conn->query("DELETE FROM comentario");
        $conn->query("DELETE FROM notificaciones");
        $conn->query("DELETE FROM reporte");
        $conn->query("DELETE FROM usuario");
        $conn->query("SET FOREIGN_KEY_CHECKS = 1");
        
        $this->usuario1Id = $this->usuarioModel->registrarUsuario(
            'Usuario1', 
            'Prueba', 
            '1111111111', 
            'usuario1@test.com', 
            'user1', 
            '12345678', 
            'Administrador'
        );
        
        $this->usuario2Id = $this->usuarioModel->registrarUsuario(
            'Usuario2', 
            'Prueba', 
            '2222222222', 
            'usuario2@test.com', 
            'user2', 
            '12345678', 
            'Tecnico',
            'Ensamblador'
        );
    }
    /**
     * CPI-002: Flujo Completo Reporte-Comentarios
     */
    public function testFlujoReporteYComentarios() {
        $conn = self::$testDb->getConnection();

        $reporteId = $this->reporteModel->crearReporte(
            $this->usuario1Id,
            $this->usuario2Id,
            'Problema crítico'
        );
        
        $this->assertIsString($reporteId);
        $this->assertNotEmpty($reporteId);

        $reporte = $conn->query("SELECT * FROM reporte WHERE ID_Reporte = '$reporteId'")->fetch_assoc();
        $this->assertEquals('Problema crítico', $reporte['descripcion']);

        $comentarioId = $this->comentarioModel->crearComentario(
            $reporteId,
            $this->usuario1Id,
            'Estoy trabajando en esto'
        );
        
        $this->assertIsString($comentarioId);
        $this->assertNotEmpty($comentarioId);

        $comentario = $conn->query("SELECT * FROM comentario WHERE ID_Comentario = '$comentarioId'")->fetch_assoc();
        $this->assertEquals('Estoy trabajando en esto', $comentario['comentario']);
// 3. Obtener comentarios del reporte
        $comentarios = $this->comentarioModel->obtenerComentariosPorReporte($reporteId, $this->usuario1Id);
        
        $this->assertCount(1, $comentarios);
        $this->assertEquals('Estoy trabajando en esto', $comentarios[0]['comentario']);
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
?>