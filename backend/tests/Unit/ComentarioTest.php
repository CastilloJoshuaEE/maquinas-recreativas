<?php
use PHPUnit\Framework\TestCase;

class ComentarioTest extends TestCase {
    private $model; // Instancia del modelo que se va a probar.
    private $reporteModel;
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
        $this->model = new ComentarioModel();
        $this->reporteModel = new ReporteModel();

        $reflection = new ReflectionClass($this->model);
        $property = $reflection->getProperty('db');
        $property->setAccessible(true);
        $property->setValue($this->model, self::$testDb);

        $reflection = new ReflectionClass($this->reporteModel);
        $property = $reflection->getProperty('db');
        $property->setAccessible(true);
        $property->setValue($this->reporteModel, self::$testDb);
        // Crear datos de prueba
        $this->crearDatosPrueba();
    }

    private function crearDatosPrueba() {
        $conn = self::$testDb->getConnection();
        
        $conn->query("SET FOREIGN_KEY_CHECKS = 0");
        // Limpiar datos previos (usuarios y reportes)
        $conn->query("DELETE FROM comentario");
        $conn->query("DELETE FROM reporte");
        $conn->query("DELETE FROM Tecnico");
        $conn->query("DELETE FROM usuario");
        $conn->query("SET FOREIGN_KEY_CHECKS = 1");
        
        $emisorId = self::$testDb->generateUUID();
        $destinatarioId = self::$testDb->generateUUID();
        // Crear usuarios de prueba con CI único
        $conn->query("INSERT INTO usuario (ID_Usuario, nombre, apellido, ci, email, usuario_asignado, contrasena, tipo, estado, fecha_registro) 
                     VALUES ('$emisorId', 'Emisor', 'Prueba', '0001110001', 'emisor@test.com', 'emisor', '12345678', 'Administrador', 'Activo', NOW())");
        
        $conn->query("INSERT INTO usuario (ID_Usuario, nombre, apellido, ci, email, usuario_asignado, contrasena, tipo, estado, fecha_registro) 
                     VALUES ('$destinatarioId', 'Destinatario', 'Prueba', '2222222222', 'destinatario@test.com', 'destinatario', '12345678', 'Tecnico', 'Activo', NOW())");
        // Crear registro en tabla Tecnico
        $conn->query("INSERT INTO Tecnico (ID_Tecnico, Especialidad) VALUES ('$destinatarioId', 'Ensamblador')");
        // Crear reporte de prueba
        $reporteId = self::$testDb->generateUUID();
        $conn->query("INSERT INTO reporte (ID_Reporte, ID_Usuario_Emisor, ID_Usuario_Destinatario, descripcion, estado, fecha_hora) 
                      VALUES ('$reporteId', '$emisorId', '$destinatarioId', 'Problema con máquina #123', 'Pendiente', NOW())");
    }
    /**
     * CP-011
     * Prueba la creación exitosa de un comentario.
     */
    public function testCrearComentario() {
        $conn = self::$testDb->getConnection();
        // Obtener IDs de prueba
        $reporte = $conn->query("SELECT ID_Reporte FROM reporte LIMIT 1")->fetch_assoc();
        $emisor = $conn->query("SELECT ID_Usuario FROM usuario WHERE ci = '0001110001'")->fetch_assoc();
        // Crear comentario
        $comentarioId = $this->model->crearComentario(
            $reporte['ID_Reporte'],
            $emisor['ID_Usuario'],
            'Este es un comentario de prueba'
        );
        // Verificar que se creó correctamente
        $this->assertIsString($comentarioId);
        $this->assertNotEmpty($comentarioId);
        // Verificar que existe en la tabla comentario
        $result = $conn->query("SELECT * FROM comentario WHERE ID_Comentario = '$comentarioId'");
        $this->assertEquals(1, $result->num_rows);
    }
    /**
     * CP-012
     * Prueba obtener comentarios por reporte.
     */
    public function testObtenerComentariosPorReporte() {
        $conn = self::$testDb->getConnection();
        // Obtener IDs de prueba
        $reporte = $conn->query("SELECT ID_Reporte FROM reporte LIMIT 1")->fetch_assoc();
        $emisor = $conn->query("SELECT ID_Usuario FROM usuario WHERE ci = '0001110001'")->fetch_assoc();
        // Crear comentarios de prueba
        $this->model->crearComentario($reporte['ID_Reporte'], $emisor['ID_Usuario'], 'Comentario 1');
        $this->model->crearComentario($reporte['ID_Reporte'], $emisor['ID_Usuario'], 'Comentario 2');
        // Obtener comentarios
        $comentarios = $this->model->obtenerComentariosPorReporte($reporte['ID_Reporte'], $emisor['ID_Usuario']);

        $this->assertIsArray($comentarios);
        $this->assertCount(2, $comentarios);
    }
    /**
     * CP-013
     * Prueba obtener comentarios por chat.
     */
    public function testObtenerComentariosPorChat() {
        $conn = self::$testDb->getConnection();
        // Obtener IDs de prueba
        $emisor = $conn->query("SELECT ID_Usuario FROM usuario WHERE ci = '0001110001'")->fetch_assoc();
        $destinatario = $conn->query("SELECT ID_Usuario FROM usuario WHERE ci = '2222222222'")->fetch_assoc();
        // Crear reporte y comentarios de prueba
        $reporteId = $this->reporteModel->crearReporte($emisor['ID_Usuario'], $destinatario['ID_Usuario'], 'Reporte para chat');
        
        $this->model->crearComentario($reporteId, $emisor['ID_Usuario'], 'Hola');
        $this->model->crearComentario($reporteId, $destinatario['ID_Usuario'], 'Hola de vuelta');
        // Obtener chat
        $comentarios = $this->model->obtenerComentariosPorChat($emisor['ID_Usuario'], $destinatario['ID_Usuario']);

        $this->assertIsArray($comentarios);
        $this->assertCount(2, $comentarios);
    }
    /**
     * CP-009
     * Manejo de comentario por usuario no autorizado
     * Prueba que no se pueden obtener comentarios de un reporte sin acceso.
     */
    public function testObtenerComentariosSinAcceso() {
        $conn = self::$testDb->getConnection();
        // Obtener IDs de prueba
        $reporte = $conn->query("SELECT ID_Reporte FROM reporte LIMIT 1")->fetch_assoc();
        // Crear usuario sin acceso
        $noAutorizadoId = self::$testDb->generateUUID();
        $conn->query("INSERT INTO usuario (ID_Usuario, nombre, apellido, ci, email, usuario_asignado, contrasena, tipo, estado, fecha_registro) 
                     VALUES ('$noAutorizadoId', 'NoAutorizado', 'Prueba', '3333333333', 'noauth@test.com', 'noauth', '12345678', 'Contabilidad', 'Activo', NOW())");
        // Intentar obtener comentarios
        $comentarios = $this->model->obtenerComentariosPorReporte($reporte['ID_Reporte'], $noAutorizadoId);

        $this->assertIsArray($comentarios);
        $this->assertEmpty($comentarios);
    }
}
?>