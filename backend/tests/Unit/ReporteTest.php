<?php 
use PHPUnit\Framework\TestCase;

class ReporteTest extends TestCase {
    private $model; // Instancia del modelo que se va a probar.
    private $usuarioModel; // Instancia de UsuarioModel para datos de prueba
    private static $testDb; // Conexión a la base de datos de prueba.

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
        $this->model = new ReporteModel(); // Cambiado a ReporteModel
        $this->usuarioModel = new UsuarioModel();
        
        // Inyectar la conexión de prueba en los modelos
        $reflection = new ReflectionClass($this->model);
        $property = $reflection->getProperty('db');
        $property->setAccessible(true);
        $property->setValue($this->model, self::$testDb);
        
        $reflection = new ReflectionClass($this->usuarioModel);
        $property = $reflection->getProperty('db');
        $property->setAccessible(true);
        $property->setValue($this->usuarioModel, self::$testDb);
        
        // Limpiar datos antes de cada prueba
        $conn = self::$testDb->getConnection();
        $conn->query("DELETE FROM usuario WHERE email = 'jean@admin.com'");
        
        // Crear datos de prueba
        $this->crearDatosPrueba();
    }

    private function crearDatosPrueba() {
        $conn = self::$testDb->getConnection();

        // Limpiar datos previos
        $conn->query("DELETE FROM comentario");
        $conn->query("DELETE FROM reporte");
        $conn->query("DELETE FROM Tecnico");
        $conn->query("DELETE FROM usuario");

        // Crear usuarios de prueba con CI y email únicos
        $conn->query("INSERT INTO usuario (nombre, apellido, ci, email, usuario_asignado, contrasena, tipo, estado)
                     VALUES ('Emisor', 'Prueba', '0900000001', 'emisor@test.com', 'emisor', '123456789', 'Administrador', 'Activo')");
        $emisorId = $conn->insert_id;

        $conn->query("INSERT INTO usuario (nombre, apellido, ci, email, usuario_asignado, contrasena, tipo, estado)
                     VALUES ('Carlos', 'Tecnico', '0900000002', 'carlos@tecnico.com', 'carlostec', '12345678', 'Tecnico', 'Activo')");
        $destinatarioId = $conn->insert_id;

        // Insertar en tabla Tecnico
        $conn->query("INSERT INTO Tecnico (ID_Tecnico, Especialidad) VALUES ($destinatarioId, 'Reparación de hardware')");

        // Crear un reporte de prueba
        $conn->query("INSERT INTO reporte (ID_Usuario_Emisor, ID_Usuario_Destinatario, descripcion, estado, fecha_hora)
                      VALUES ($emisorId, $destinatarioId, 'Pantalla no enciende', 'Pendiente', NOW())");
    }
    /**
     * CPI-001
     * Prueba la creación exitosa de un reporte.
     */
    public function testCrearReporteValido() {
        $conn = self::$testDb->getConnection();
        
        // Obtener IDs de usuarios de prueba
        $emisor = $conn->query("SELECT ID_Usuario FROM usuario WHERE ci = '0900000001'")->fetch_assoc();
        $destinatario = $conn->query("SELECT ID_Usuario FROM usuario WHERE ci = '0900000002'")->fetch_assoc();

        // Crear reporte
        $reporteId = $this->model->crearReporte(
            $emisor['ID_Usuario'],
            $destinatario['ID_Usuario'],
            'Problema con máquina #123'
        );

        // Verificar que se creó correctamente
        $this->assertIsInt($reporteId);
        
        // Verificar que existe en la tabla reporte
        $result = $conn->query("SELECT * FROM reporte WHERE ID_Reporte = $reporteId");
        $this->assertEquals(1, $result->num_rows);
    }

    /**
     * CP-002
     * Prueba obtener reportes por usuario.
     */
    public function testObtenerReportesPorUsuario() {
        $conn = self::$testDb->getConnection();
        
        // Obtener IDs de usuarios de prueba
        $emisor = $conn->query("SELECT ID_Usuario FROM usuario WHERE ci = '0900000001'")->fetch_assoc();
        $destinatario = $conn->query("SELECT ID_Usuario FROM usuario WHERE ci = '0900000002'")->fetch_assoc();

        // Crear reportes de prueba
        $this->model->crearReporte($emisor['ID_Usuario'], $destinatario['ID_Usuario'], 'Reporte 1');
        $this->model->crearReporte($destinatario['ID_Usuario'], $emisor['ID_Usuario'], 'Reporte 2');

        // Obtener reportes para el emisor
        $reportes = $this->model->obtenerReportesPorUsuario($emisor['ID_Usuario']);

        $this->assertIsArray($reportes);
        $this->assertCount(3, $reportes); // 2 nuevos + 1 creado en crearDatosPrueba()
    }

    /**
     * CP-003
     * Prueba obtener un reporte por ID.
     */
    public function testObtenerReportePorId() {
        $conn = self::$testDb->getConnection();
        
        // Obtener IDs de usuarios de prueba
        $emisor = $conn->query("SELECT ID_Usuario FROM usuario WHERE ci = '0900000001'")->fetch_assoc();
        $destinatario = $conn->query("SELECT ID_Usuario FROM usuario WHERE ci = '0900000002'")->fetch_assoc();

        // Crear reporte de prueba
        $reporteId = $this->model->crearReporte(
            $emisor['ID_Usuario'],
            $destinatario['ID_Usuario'],
            'Problema con máquina #123'
        );

        // Obtener el reporte
        $reporte = $this->model->obtenerReportePorId($reporteId);

        $this->assertIsArray($reporte);
        $this->assertEquals($reporteId, $reporte['ID_Reporte']);
        $this->assertEquals('Problema con máquina #123', $reporte['descripcion']);
    }

    /**
     * CP-010
     * Notificación al cambiar estado de reporte
     * Prueba actualizar estado de un reporte.
     */
    public function testActualizarEstadoReporte() {
        $conn = self::$testDb->getConnection();
        
        // Obtener IDs de usuarios de prueba
        $emisor = $conn->query("SELECT ID_Usuario FROM usuario WHERE ci = '0900000001'")->fetch_assoc();
        $destinatario = $conn->query("SELECT ID_Usuario FROM usuario WHERE ci = '0900000002'")->fetch_assoc();

        // Crear reporte de prueba
        $reporteId = $this->model->crearReporte(
            $emisor['ID_Usuario'],
            $destinatario['ID_Usuario'],
            'Problema con máquina #123'
        );

        // Actualizar estado
        $result = $this->model->actualizarEstadoReporte($reporteId, 'Resuelto');

        $this->assertTrue($result);
        
        // Verificar que se actualizó
        $reporte = $this->model->obtenerReportePorId($reporteId);
        $this->assertEquals('Resuelto', $reporte['estado']);
    }

    /**
     * CP-005
     * Prueba obtener chat entre usuarios.
     */
    public function testObtenerChat() {
        $conn = self::$testDb->getConnection();
        
        // Obtener IDs de usuarios de prueba
        $emisor = $conn->query("SELECT ID_Usuario FROM usuario WHERE ci = '0900000001'")->fetch_assoc();
        $destinatario = $conn->query("SELECT ID_Usuario FROM usuario WHERE ci = '0900000002'")->fetch_assoc();

        // Crear reportes de prueba
        $this->model->crearReporte($emisor['ID_Usuario'], $destinatario['ID_Usuario'], 'Reporte 1');
        $this->model->crearReporte($destinatario['ID_Usuario'], $emisor['ID_Usuario'], 'Reporte 2');

        // Obtener chat
        $chat = $this->model->obtenerChat($emisor['ID_Usuario'], $destinatario['ID_Usuario']);

        $this->assertIsArray($chat);
        $this->assertCount(3, $chat); // 2 nuevos + 1 creado en crearDatosPrueba()
    }
/*
    public static function tearDownAfterClass(): void {
        self::$testDb->cleanUp();
    }
        */
}