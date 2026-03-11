<?php
use PHPUnit\Framework\TestCase;

class NotificacionIntegrationTest extends TestCase {
    private $notificacionModel;
    private $reporteModel;
    private $usuarioModel;
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
        $this->notificacionModel = new NotificacionModel();
        $this->reporteModel = new ReporteModel();
        $this->usuarioModel = new UsuarioModel();

        // Inyectar la conexión de prueba en los modelos usando Reflection
        $this->injectTestDb($this->notificacionModel, 'db');
        $this->injectTestDb($this->reporteModel, 'db');
        $this->injectTestDb($this->usuarioModel, 'db');
        
        // Crear usuarios de prueba
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
        
        $userId1 = $this->usuarioModel->registrarUsuario(
            'Emisor',
            'Prueba',
            '1111111111',
            'emisor@test.com',
            'emisor',
            '12345678',
            'Administrador'
        );
        
        $userId2 = $this->usuarioModel->registrarUsuario(
            'Destinatario',
            'Prueba',
            '2222222222',
            'destinatario@test.com',
            'destinatario',
            '12345678',
            'Tecnico',
            'Ensamblador'
        );

        $conn->query("UPDATE usuario SET estado = 'Activo' WHERE ci = '1111111111'");
        $conn->query("UPDATE usuario SET estado = 'Activo' WHERE ci = '2222222222'");
    }

    /**
     * CPI-004: Notificaciones por Usuario
     * Recuperar notificaciones por ID de usuario
     */
    public function testObtenerNotificacionesUsuario() {
        $conn = self::$testDb->getConnection();
        
        // Obtener IDs de usuarios de prueba
        $emisor = $conn->query("SELECT ID_Usuario FROM usuario WHERE ci = '1111111111'")->fetch_assoc();
        $destinatario = $conn->query("SELECT ID_Usuario FROM usuario WHERE ci = '2222222222'")->fetch_assoc();

        // Crear reporte para generar notificación
        $reporteId = $this->reporteModel->crearReporte(
            $emisor['ID_Usuario'],
            $destinatario['ID_Usuario'],
            'Problema con máquina #123'
        );
        // Crear notificación directamente
        $this->notificacionModel->crearNotificacionReporte($reporteId, $destinatario['ID_Usuario'], 'Nuevo reporte creado');
        // Obtener notificaciones del destinatario
        $notificaciones = $this->notificacionModel->obtenerNotificacionesPorUsuario($destinatario['ID_Usuario']);
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
        
        // Obtener IDs de usuarios de prueba
        $emisor = $conn->query("SELECT ID_Usuario FROM usuario WHERE ci = '1111111111'")->fetch_assoc();
        $destinatario = $conn->query("SELECT ID_Usuario FROM usuario WHERE ci = '2222222222'")->fetch_assoc();

        // Crear reporte y notificación
        $reporteId = $this->reporteModel->crearReporte(
            $emisor['ID_Usuario'],
            $destinatario['ID_Usuario'],
            'Problema con máquina #123'
        );
        
        $this->notificacionModel->crearNotificacionReporte($reporteId, $destinatario['ID_Usuario'], 'Nuevo reporte creado');

        // Obtener ID de la notificación creada
        $notificacion = $conn->query("SELECT ID_Notificaciones FROM notificaciones WHERE ID_Usuario = {$destinatario['ID_Usuario']}")->fetch_assoc();
        $notificacionId = $notificacion['ID_Notificaciones'];

        // Marcar como leída
        $resultado = $this->notificacionModel->marcarComoLeidaNotificacion($notificacionId, $destinatario['ID_Usuario']);
        
        // Verificar que se marcó correctamente
        $this->assertTrue($resultado);
        
        // Verificar en la base de datos
        $notificacion = $conn->query("SELECT leida FROM notificaciones WHERE ID_Notificaciones = $notificacionId")->fetch_assoc();
        $this->assertEquals(1, $notificacion['leida']);
    }

    /**
     * CPI-104: Obtener Cantidad de Notificaciones No Leídas
     */
    public function testObtenerCantidadNoLeidas() {
        $conn = self::$testDb->getConnection();
        
        // Obtener IDs de usuarios de prueba
        $destinatario = $conn->query("SELECT ID_Usuario FROM usuario WHERE ci = '2222222222'")->fetch_assoc();

        // Crear 3 notificaciones (2 no leídas, 1 leída)
        $this->crearNotificacionTest($destinatario['ID_Usuario'], false);
        $this->crearNotificacionTest($destinatario['ID_Usuario'], false);
        $this->crearNotificacionTest($destinatario['ID_Usuario'], true);

        // Obtener cantidad de no leídas
        $cantidad = $this->notificacionModel->obtenerCantidadNoLeidas($destinatario['ID_Usuario']);
        
        // Verificar resultados
        $this->assertEquals(3, $cantidad);
    }

    /**
     * Helper para crear notificaciones de prueba
     */
    private function crearNotificacionTest($userId, $leida = false) {
        $conn = self::$testDb->getConnection();
        
        // Crear reporte de prueba
        $conn->query("INSERT INTO reporte (ID_Usuario_Emisor, ID_Usuario_Destinatario, descripcion, estado, fecha_hora) 
                      VALUES (1, $userId, 'Notificación prueba', 'Pendiente', NOW())");
        $reporteId = $conn->insert_id;
        
        // Crear notificación
        $conn->query("INSERT INTO notificaciones (ID_Reporte, ID_Usuario, fecha_hora, mensaje, leida) 
                      VALUES ($reporteId, $userId, NOW(), 'Mensaje de prueba', " . ($leida ? '1' : '0') . ")");
    }

    /**
     * Método ejecutado UNA SOLA VEZ después de todas las pruebas para limpiar la BD de prueba.
     */
    public static function tearDownAfterClass(): void {
        self::$testDb->cleanUp();
    }
}