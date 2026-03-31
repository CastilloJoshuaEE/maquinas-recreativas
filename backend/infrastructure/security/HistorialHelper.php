<?php
/**
 * backend/infrastructure/security/HistorialHelper.php
 *
 * Helper para registrar historial de actividades.
 *
 * @package maquinas_recreativas\Infrastructure\Security
 */

namespace maquinas_recreativas\Infrastructure\Security;

use maquinas_recreativas\Infrastructure\Database\Database;
use PDO;

class HistorialHelper
{
    private static ?HistorialHelper $instance = null;
    private Database $db;

    private function __construct()
    {
        $this->db = new Database();
    }

    public static function getInstance(): HistorialHelper
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Registrar actividad genérica.
     */
    public function registrar(
        ?string $idMaquina,
        ?string $idUsuario,
        ?string $tipoUsuario,
        string $accion,
        string $descripcion = '',
        ?string $estadoAnterior = null,
        ?string $estadoNuevo = null,
        ?string $etapaAnterior = null,
        ?string $etapaNueva = null,
        ?string $ipAddress = null,
        array $detalles = []
    ): bool {
        try {
            $conn = $this->db->getConnection();
            
            $sql = "INSERT INTO historial_maquina (
                        ID_Maquina, ID_Usuario, tipo_usuario, accion, descripcion,
                        estado_anterior, estado_nuevo, etapa_anterior, etapa_nueva,
                        ip_address, detalles, fecha_hora
                    ) VALUES (
                        :idMaquina, :idUsuario, :tipoUsuario, :accion, :descripcion,
                        :estadoAnterior, :estadoNuevo, :etapaAnterior, :etapaNueva,
                        :ipAddress, :detalles, NOW()
                    )";
            
            $stmt = $conn->prepare($sql);
            return $stmt->execute([
                ':idMaquina' => $idMaquina,
                ':idUsuario' => $idUsuario,
                ':tipoUsuario' => $tipoUsuario,
                ':accion' => $accion,
                ':descripcion' => $descripcion,
                ':estadoAnterior' => $estadoAnterior,
                ':estadoNuevo' => $estadoNuevo,
                ':etapaAnterior' => $etapaAnterior,
                ':etapaNueva' => $etapaNueva,
                ':ipAddress' => $ipAddress ?? $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0',
                ':detalles' => json_encode($detalles, JSON_UNESCAPED_UNICODE)
            ]);
        } catch (\Exception $e) {
            error_log("Error registrando historial: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Registrar envío a comprobación
     */
    public function registrarEnvioComprobacion(string $idMaquina, string $idUsuario, string $mensaje): bool
    {
        $ipAddress = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        
        return $this->registrar(
            $idMaquina,
            $idUsuario,
            $this->getTipoUsuario($idUsuario),
            'Envío a comprobación',
            "Máquina enviada a comprobación. Mensaje: $mensaje",
            'Ensamblandose/Reensamblandose',
            'Comprobandose',
            'Montaje',
            'Montaje',
            $ipAddress,
            ['mensaje' => $mensaje]
        );
    }

    /**
     * Registrar envío a reensamblar
     */
    public function registrarEnvioReensamblar(string $idMaquina, string $idUsuario, string $mensaje): bool
    {
        $ipAddress = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        
        return $this->registrar(
            $idMaquina,
            $idUsuario,
            $this->getTipoUsuario($idUsuario),
            'Envío a reensamblar',
            "Máquina rechazada, enviada a reensamblar. Motivo: $mensaje",
            'Comprobandose',
            'Reensamblandose',
            'Montaje',
            'Montaje',
            $ipAddress,
            ['motivo' => $mensaje]
        );
    }

    /**
     * Registrar envío a distribución
     */
    public function registrarEnvioDistribucion(string $idMaquina, string $idUsuario, string $comercio, string $mensaje): bool
    {
        $ipAddress = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        
        return $this->registrar(
            $idMaquina,
            $idUsuario,
            $this->getTipoUsuario($idUsuario),
            'Envío a distribución',
            "Máquina aprobada y enviada a distribución. Comercio: $comercio. Mensaje: $mensaje",
            'Comprobandose',
            'Distribuyendose',
            'Montaje',
            'Distribucion',
            $ipAddress,
            ['comercio' => $comercio, 'mensaje' => $mensaje]
        );
    }

    /**
     * Registrar puesta en operativa
     */
    public function registrarPuestaOperativa(string $idMaquina, string $idUsuario): bool
    {
        $ipAddress = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        
        return $this->registrar(
            $idMaquina,
            $idUsuario,
            $this->getTipoUsuario($idUsuario),
            'Puesta en operativa',
            "Máquina marcada como operativa y en etapa de recaudación",
            'Distribuyendose',
            'Operativa',
            'Distribucion',
            'Recaudacion',
            $ipAddress,
            []
        );
    }

    /**
     * Obtener tipo de usuario por ID
     */
    private function getTipoUsuario(?string $idUsuario): string
    {
        if (!$idUsuario) {
            return 'Desconocido';
        }
        
        try {
            $conn = $this->db->getConnection();
            $sql = "SELECT tipo FROM usuario WHERE ID_Usuario = :id";
            $stmt = $conn->prepare($sql);
            $stmt->execute([':id' => $idUsuario]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $result ? $result['tipo'] : 'Desconocido';
        } catch (\Exception $e) {
            error_log("Error obteniendo tipo usuario: " . $e->getMessage());
            return 'Desconocido';
        }
    }
}