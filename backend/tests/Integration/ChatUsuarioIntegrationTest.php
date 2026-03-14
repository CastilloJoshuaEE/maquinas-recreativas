<?php
use PHPUnit\Framework\TestCase;

class ChatUsuarioIntegrationTest extends TestCase {
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
        
        $conn->query("SET FOREIGN_KEY_CHECKS = 0");
        // Limpiar tablas relevantes
        $conn->query("DELETE FROM comentario");
        $conn->query("DELETE FROM notificaciones");
        $conn->query("DELETE FROM reporte");
        $conn->query("DELETE FROM usuario");
        $conn->query("SET FOREIGN_KEY_CHECKS = 1");
        // Crear usuarios de prueba si no existen
        $this->usuario1Id = $this->usuarioModel->registrarUsuario(
            'Usuario1', 
            'Prueba', 
            '3333333333', 
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
            'Logistica'
        );
    }
    /**
     * CPI-003: Comunicación entre usuarios vía comentarios en reporte
     * Comunicación entre usuarios vía comentarios en reporte
     */
    public function testFlujoChatUsuarios() {
        $conn = self::$testDb->getConnection();
// 1. Crear reporte (inicia conversación)
        $reporteId = $this->reporteModel->crearReporte(
            $this->usuario1Id,
            $this->usuario2Id,
            'Problema con máquina #123'
        );
        
        $this->assertIsString($reporteId);
        $this->assertNotEmpty($reporteId);
// 2. Crear notificación
        $notificacionCreada = $this->notificacionModel->crearNotificacionReporte(
            $reporteId,
            $this->usuario2Id,
            'Nuevo reporte creado'
        );
        $this->assertTrue($notificacionCreada);
// 3. Crear comentarios
        $this->crearComentarioTest($reporteId, $this->usuario1Id, 'Hola, tengo un problema');
        $this->crearComentarioTest($reporteId, $this->usuario2Id, 'Cuéntame más sobre el problema');
        $this->crearComentarioTest($reporteId, $this->usuario1Id, 'La máquina no enciende');
// 4. Obtener chat completo
        $reportes = $this->reporteModel->obtenerChat($this->usuario1Id, $this->usuario2Id);
        // Verificar resultados
        $this->assertCount(1, $reportes);// Debería haber 1 reporte
        // Verificar comentarios
        $comentarios = $conn->query("SELECT * FROM comentario WHERE ID_Reporte = '$reporteId'");
        $this->assertEquals(3, $comentarios->num_rows);// Debería haber 3 comentarios
    }
    /**
     * Helper para crear comentarios de prueba
     */
    private function crearComentarioTest($reporteId, $usuarioId, $mensaje) {
        $conn = self::$testDb->getConnection();
        $comentarioId = self::$testDb->generateUUID();
        $sql = "INSERT INTO comentario (ID_Comentario, ID_Reporte, ID_Usuario_Emisor, comentario, fecha_hora) 
                VALUES (?, ?, ?, ?, NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssss", $comentarioId, $reporteId, $usuarioId, $mensaje);
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
?>