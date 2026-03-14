<?php
use PHPUnit\Framework\TestCase;

class NotificacionIntegrationTest extends TestCase {
    private $notificacionModel;
    private $reporteModel;
    private $usuarioModel;

    private $emisorId;
    private $destinatarioId;

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
        $this->notificacionModel = new NotificacionModel();
        $this->reporteModel = new ReporteModel();
        $this->usuarioModel = new UsuarioModel();

        $this->injectTestDb($this->notificacionModel, 'db');
        $this->injectTestDb($this->reporteModel, 'db');
        $this->injectTestDb($this->usuarioModel, 'db');
        
        $this->crearUsuariosPrueba();
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
     * Crea usuarios de prueba en la base de datos
     */
    private function crearUsuariosPrueba() {
        $conn = self::$testDb->getConnection();
        
        $conn->query("SET FOREIGN_KEY_CHECKS = 0");
        $conn->query("DELETE FROM notificaciones");
        $conn->query("DELETE FROM reporte");
        $conn->query("DELETE FROM usuario");
        $conn->query("SET FOREIGN_KEY_CHECKS = 1");
        
        // Guardar los IDs para usarlos después
        $this->emisorId = $this->usuarioModel->registrarUsuario(
            'Emisor',
            'Prueba',
            '1111111111',
            'emisor@test.com',
            'emisor',
            '12345678',
            'Administrador'
        );
        
        $this->destinatarioId = $this->usuarioModel->registrarUsuario(
            'Destinatario',
            'Prueba',
            '2222222222',
            'destinatario@test.com',
            'destinatario',
            '12345678',
            'Tecnico',
            'Ensamblador'
        );
    }
    /**
     * CPI-004: Notificaciones por Usuario
     * Recuperar notificaciones por ID de usuario
     */
    public function testObtenerNotificacionesUsuario() {
        $conn = self::$testDb->getConnection();
        
        $reporteId = $this->reporteModel->crearReporte(
            $this->emisorId,
            $this->destinatarioId,
            'Problema con máquina #123'
        );
        
        $this->assertIsString($reporteId);
        $this->assertNotEmpty($reporteId);
        
        $this->notificacionModel->crearNotificacionReporte($reporteId, $this->destinatarioId, 'Nuevo reporte creado');
        
        $notificaciones = $this->notificacionModel->obtenerNotificacionesPorUsuario($this->destinatarioId);
        // Verificar resultados
        $this->assertNotEmpty($notificaciones);
        $this->assertCount(1, $notificaciones);
        $this->assertEquals('Nuevo reporte creado', $notificaciones[0]['mensaje']);
    }
    /**
     * CPI-103: Marcar Notificación como Leída
     */
    public function testMarcarNotificacionComoLeida() {
        $conn = self::$testDb->getConnection();

        $reporteId = $this->reporteModel->crearReporte(
            $this->emisorId,
            $this->destinatarioId,
            'Problema con máquina #123'
        );
        
        $this->notificacionModel->crearNotificacionReporte($reporteId, $this->destinatarioId, 'Nuevo reporte creado');

        $notificacion = $conn->query("SELECT ID_Notificaciones FROM notificaciones WHERE ID_Usuario = '{$this->destinatarioId}'")->fetch_assoc();
        $notificacionId = $notificacion['ID_Notificaciones'];
        // Marcar como leída

        $resultado = $this->notificacionModel->marcarComoLeidaNotificacion($notificacionId, $this->destinatarioId);
        
        $this->assertTrue($resultado);
        // Verificar en la base de datos
        $notificacion = $conn->query("SELECT leida FROM notificaciones WHERE ID_Notificaciones = '$notificacionId'")->fetch_assoc();
        $this->assertEquals(1, $notificacion['leida']);
    }
    /**
     * CPI-104: Obtener Cantidad de Notificaciones No Leídas
     */
    public function testObtenerCantidadNoLeidas() {
        $conn = self::$testDb->getConnection();
        // Crear 3 notificaciones (2 no leídas, 1 leída)
        $this->crearNotificacionTest($this->destinatarioId, false);
        $this->crearNotificacionTest($this->destinatarioId, false);
        $this->crearNotificacionTest($this->destinatarioId, true);

        $cantidad = $this->notificacionModel->obtenerCantidadNoLeidas($this->destinatarioId);
        
        $this->assertEquals(2, $cantidad);
    }
    /**
     * Helper para crear notificaciones de prueba
     */
    private function crearNotificacionTest($userId, $leida = false) {
        $conn = self::$testDb->getConnection();

        $reporteId = self::$testDb->generateUUID();
        $notificacionId = self::$testDb->generateUUID();
        
        $sqlReporte = "INSERT INTO reporte (ID_Reporte, ID_Usuario_Emisor, ID_Usuario_Destinatario, descripcion, estado, fecha_hora) 
                       VALUES ('$reporteId', '$this->emisorId', '$userId', 'Notificación prueba', 'Pendiente', NOW())";
        
        if (!$conn->query($sqlReporte)) {
            throw new Exception("Error al crear reporte de prueba: " . $conn->error);
        }

        $sqlNotificacion = "INSERT INTO notificaciones (ID_Notificaciones, ID_Reporte, ID_Usuario, mensaje, fecha_hora, leida) 
                            VALUES ('$notificacionId', '$reporteId', '$userId', 'Mensaje de prueba', NOW(), " . ($leida ? '1' : '0') . ")";

        if (!$conn->query($sqlNotificacion)) {
            throw new Exception("Error al crear notificación de prueba: " . $conn->error);
        }
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