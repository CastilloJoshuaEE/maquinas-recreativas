<?php 
use PHPUnit\Framework\TestCase;

class ReporteTest extends TestCase {
    private $model;
    private $usuarioModel;
    private static $testDb;

    // Propiedades para almacenar los IDs de los usuarios de prueba
    private $emisorId;
    private $destinatarioId;

    public static function setUpBeforeClass(): void {
        self::$testDb = new TestDatabase();
    }

    protected function setUp(): void {
        $this->model = new ReporteModel();
        $this->usuarioModel = new UsuarioModel();
        
        $reflection = new ReflectionClass($this->model);
        $property = $reflection->getProperty('db');
        $property->setAccessible(true);
        $property->setValue($this->model, self::$testDb);
        
        $reflection = new ReflectionClass($this->usuarioModel);
        $property = $reflection->getProperty('db');
        $property->setAccessible(true);
        $property->setValue($this->usuarioModel, self::$testDb);
        
        $conn = self::$testDb->getConnection();
        $conn->query("DELETE FROM usuario WHERE email = 'jean@admin.com'");
        
        $this->crearDatosPrueba();
    }

    private function crearDatosPrueba() {
        $conn = self::$testDb->getConnection();
        $conn->query("DELETE FROM comentario");
        $conn->query("DELETE FROM reporte");
        $conn->query("DELETE FROM Tecnico");
        $conn->query("DELETE FROM usuario");

        // Crear usuarios de prueba y almacenar sus IDs
        $resultadoEmisor = $this->usuarioModel->registrarUsuario(
            'Emisor', 'Prueba', '0900000001', 'emisor@test.com', 'emisor', '123456789', 'Administrador', null
        );
        
        $resultadoDestinatario = $this->usuarioModel->registrarUsuario(
            'Carlos', 'Tecnico', '0900000002', 'carlos@tecnico.com', 'carlostec', '12345678', 'Tecnico', 'Ensamblador'
        );
        
        $this->assertTrue($resultadoEmisor['success']);
        $this->assertTrue($resultadoDestinatario['success']);
        
        $this->emisorId = $resultadoEmisor['userId'];
        $this->destinatarioId = $resultadoDestinatario['userId'];
        
        // Crear un reporte de prueba
        $reporteId = self::$testDb->generateUUID();
        $conn->query("INSERT INTO reporte (ID_Reporte, ID_Usuario_Emisor, ID_Usuario_Destinatario, descripcion, estado, fecha_hora)
                      VALUES ('$reporteId', '$this->emisorId', '$this->destinatarioId', 'Pantalla no enciende', 'Pendiente', NOW())");
    }

    /**
     * CPI-001 - Prueba la creación exitosa de un reporte.
     */
    public function testCrearReporteValido() {
        // Pequeña pausa para asegurar que el nuevo reporte tenga una fecha_hora posterior
        usleep(1000000); // 1 segundo

        $reporteId = $this->model->crearReporte(
            $this->emisorId,
            $this->destinatarioId,
            'Problema con máquina #123'
        );
        
        $this->assertIsString($reporteId);
        $this->assertNotEmpty($reporteId);
        
        $conn = self::$testDb->getConnection();
        $result = $conn->query("SELECT * FROM reporte WHERE ID_Reporte = '$reporteId'");
        $this->assertEquals(1, $result->num_rows);
        $reporte = $result->fetch_assoc();
        $this->assertEquals('Problema con máquina #123', $reporte['descripcion']);
    }

    /**
     * CP-002 - Prueba obtener reportes por usuario.
     */
    public function testObtenerReportesPorUsuario() {
        // Crear reportes adicionales usando los IDs almacenados
        $this->model->crearReporte($this->emisorId, $this->destinatarioId, 'Reporte 1');
        $this->model->crearReporte($this->destinatarioId, $this->emisorId, 'Reporte 2');
        
        $reportes = $this->model->obtenerReportesPorUsuario($this->emisorId);

        $this->assertIsArray($reportes);
        $this->assertCount(3, $reportes); // 1 de crearDatosPrueba + 2 nuevos
    }
    
    /**
     * CP-003 - Prueba obtener un reporte por ID.
     */
    public function testObtenerReportePorId() {
        // Pequeña pausa para asegurar que el nuevo reporte tenga una fecha_hora posterior
        usleep(1000000); // 1 segundo

        $reporteId = $this->model->crearReporte(
            $this->emisorId,
            $this->destinatarioId,
            'Problema con máquina #123'
        );
        
        $reporte = $this->model->obtenerReportePorId($reporteId);

        $this->assertIsArray($reporte);
        $this->assertEquals($reporteId, $reporte['ID_Reporte']);
        $this->assertEquals('Problema con máquina #123', $reporte['descripcion']);
    }

    /**
     * CP-010 - Prueba actualizar estado de un reporte.
     */
    public function testActualizarEstadoReporte() {
        $reporteId = $this->model->crearReporte(
            $this->emisorId,
            $this->destinatarioId,
            'Problema con máquina #123'
        );

        $result = $this->model->actualizarEstadoReporte($reporteId, 'Resuelto');

        $this->assertTrue($result);
        
        $reporte = $this->model->obtenerReportePorId($reporteId);
        $this->assertEquals('Resuelto', $reporte['estado']);
    }
    
    /**
     * CP-005 - Prueba obtener chat entre usuarios.
     */
    public function testObtenerChat() {
        // Crear reportes adicionales usando los IDs almacenados
        $this->model->crearReporte($this->emisorId, $this->destinatarioId, 'Reporte 1');
        $this->model->crearReporte($this->destinatarioId, $this->emisorId, 'Reporte 2');

        $chat = $this->model->obtenerChat($this->emisorId, $this->destinatarioId);

        $this->assertIsArray($chat);
        $this->assertCount(3, $chat); // 1 de crearDatosPrueba + 2 nuevos
    }
}
?>