<?php
/**
 * backend/infrastructure/security/HistorialHelper.php
 *
 * Helper para registrar historial de actividades.
 *
 * @package maquinas_recreativas\Infrastructure\Security
 */

// backend/infrastructure/security/HistorialHelper.php

namespace maquinas_recreativas\Infrastructure\Security;

use PDO;
use maquinas_recreativas\Infrastructure\Database\Database;

class HistorialHelper
{
    private static ?HistorialHelper $instance = null;
    private PDO $conn;
    private ?Database $db = null;


    // Constructor ahora recibe PDO directamente
public function __construct(?PDO $conn = null)
{
    if ($conn === null) {
        $this->db = new Database();
        $this->conn = $this->db->getConnection();
    } else {
        $this->conn = $conn;
        // Para getTipoUsuario, también necesitamos Database
        $this->db = new Database();
        // Sobrescribir la conexión del Database con la recibida
        $reflection = new \ReflectionClass($this->db);
        $property = $reflection->getProperty('connection');
        $property->setAccessible(true);
        $property->setValue($this->db, $conn);
    }
}

    public static function getInstance(): HistorialHelper
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

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
            // Usamos $this->conn directamente, sin getConnection()
            $sql = "INSERT INTO historial_maquinas (
                        ID_Maquina, ID_Usuario, tipo_usuario, accion, descripcion,
                        estado_anterior, estado_nuevo, etapa_anterior, etapa_nueva,
                        ip_address, detalles_adicionales, fecha_hora
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
            
            $stmt = $this->conn->prepare($sql);
            if (!$stmt) {
                error_log("Error preparando consulta en HistorialHelper");
                return false;
            }
            
            $detallesJson = json_encode($detalles, JSON_UNESCAPED_UNICODE);
            $ip = $ipAddress ?? $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
            
            $result = $stmt->execute([
                $idMaquina,
                $idUsuario,
                $tipoUsuario,
                $accion,
                $descripcion,
                $estadoAnterior,
                $estadoNuevo,
                $etapaAnterior,
                $etapaNueva,
                $ip,
                $detallesJson
            ]);
            
            return $result;
        } catch (\Exception $e) {
            error_log("Error registrando historial: " . $e->getMessage());
            return false;
        }
    }

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

private function getTipoUsuario(?string $idUsuario): string
{
    if (!$idUsuario) {
        return 'Desconocido';
    }

    try {
        // Usar $this->db en lugar de crear nueva conexión
        $conn = $this->db->getConnection();
        $sql = "SELECT tipo FROM usuario WHERE ID_Usuario = ?";
        $stmt = $conn->prepare($sql);
        
        if (!$stmt) {
            error_log("Error preparando consulta para getTipoUsuario");
            return 'Desconocido';
        }
        
        $stmt->execute([$idUsuario]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($row) {
            return $row['tipo'];
        }
        
        return 'Desconocido';
    } catch (\Exception $e) {
        error_log("Error obteniendo tipo usuario: " . $e->getMessage());
        return 'Desconocido';
    }
}
}