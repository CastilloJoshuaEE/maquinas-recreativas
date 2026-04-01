<?php
namespace maquinas_recreativas\Infrastructure\Repositories;

use maquinas_recreativas\Domain\Recaudacion\Recaudacion;
use maquinas_recreativas\Domain\Recaudacion\InformeRecaudacion;
use maquinas_recreativas\Domain\Recaudacion\DetalleInforme;
use maquinas_recreativas\Domain\Recaudacion\RecaudacionRepository;
use maquinas_recreativas\Domain\Shared\ValueObjects\Uuid;
use maquinas_recreativas\Domain\Maquina\MaquinaRecreativa;
use maquinas_recreativas\Domain\Comercio\Comercio;
use maquinas_recreativas\Infrastructure\Database\Database;

class MySQLRecaudacionRepository implements RecaudacionRepository
{
    private Database $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    public function findById(Uuid $id): ?Recaudacion
    {
        $conn = $this->db->getConnection();
        $sql = "SELECT * FROM recaudaciones WHERE ID_Recaudacion = ?";
        $stmt = $conn->prepare($sql);
        $idValue = $id->value();
        $stmt->bind_param('s', $idValue);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        $stmt->close();

        return $data ? Recaudacion::fromArray($data) : null;
    }

    public function findInformeByRecaudacion(Uuid $idRecaudacion): ?InformeRecaudacion
    {
        $conn = $this->db->getConnection();
        $sql = "SELECT * FROM informes_recaudacion WHERE ID_Recaudacion = ?";
        $stmt = $conn->prepare($sql);
        $idValue = $idRecaudacion->value();
        $stmt->bind_param('s', $idValue);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        $stmt->close();

        return $data ? InformeRecaudacion::fromArray($data) : null;
    }

    public function findDetallesByInforme(Uuid $idInforme): array
    {
        $conn = $this->db->getConnection();
        $sql = "SELECT c.* 
                FROM informe_detalle id
                JOIN componente c ON id.ID_Componente = c.ID_Componente
                WHERE id.ID_Informe = ?";
        $stmt = $conn->prepare($sql);
        $idValue = $idInforme->value();
        $stmt->bind_param('s', $idValue);
        $stmt->execute();
        $result = $stmt->get_result();

        $detalles = [];
        while ($row = $result->fetch_assoc()) {
            $detalles[] = $row;
        }
        $stmt->close();

        return $detalles;
    }

    public function findMaquinasRecaudacion(): array
    {
        $conn = $this->db->getConnection();
        $sql = "SELECT m.*,
                        c.Nombre as NombreComercio,
                        c.Direccion as DireccionComercio,
                        c.Telefono as TelefonoComercio,
                        c.Tipo as TipoComercio
                FROM MaquinaRecreativa m
                LEFT JOIN Comercio c ON m.ID_Comercio = c.ID_Comercio
                WHERE m.Etapa = 'Recaudacion' AND m.Estado = 'Operativa'
                ORDER BY m.Fecha_Registro DESC";

        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();

        $maquinas = [];
        while ($row = $result->fetch_assoc()) {
            $maquinas[] = $row;
        }
        $stmt->close();

        return $maquinas;
    }

    public function save(Recaudacion $recaudacion): void
    {
        $conn = $this->db->getConnection();
        $data = $recaudacion->toArray();

        $sql = "INSERT INTO recaudaciones (
                    ID_Recaudacion, Tipo_Comercio, ID_Maquina, ID_Usuario,
                    Monto_Total, Monto_Empresa, Monto_Comercio, fecha,
                    detalle, Porcentaje_Comercio
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                    Tipo_Comercio = VALUES(Tipo_Comercio),
                    Monto_Total = VALUES(Monto_Total),
                    Monto_Empresa = VALUES(Monto_Empresa),
                    Monto_Comercio = VALUES(Monto_Comercio),
                    detalle = VALUES(detalle),
                    Porcentaje_Comercio = VALUES(Porcentaje_Comercio)";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            'ssssdddsds',
            $data['ID_Recaudacion'],
            $data['Tipo_Comercio'],
            $data['ID_Maquina'],
            $data['ID_Usuario'],
            $data['Monto_Total'],
            $data['Monto_Empresa'],
            $data['Monto_Comercio'],
            $data['fecha'],
            $data['detalle'],
            $data['Porcentaje_Comercio']
        );
        $stmt->execute();
        $stmt->close();
    }

    public function saveInforme(InformeRecaudacion $informe): void
    {
        $conn = $this->db->getConnection();
        $data = $informe->toArray();

        $sql = "INSERT INTO informes_recaudacion (
                    ID_Informe, ID_Recaudacion, CI_Usuario, Nombre_Maquina,
                    ID_Comercio, Nombre_Comercio, Direccion_Comercio, Telefono_Comercio,
                    Pago_Ensamblador, Pago_Comprobador, Pago_Mantenimiento,
                    empresa_nombre, empresa_descripcion
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                    CI_Usuario = VALUES(CI_Usuario),
                    Nombre_Maquina = VALUES(Nombre_Maquina),
                    Nombre_Comercio = VALUES(Nombre_Comercio),
                    Direccion_Comercio = VALUES(Direccion_Comercio),
                    Telefono_Comercio = VALUES(Telefono_Comercio),
                    Pago_Ensamblador = VALUES(Pago_Ensamblador),
                    Pago_Comprobador = VALUES(Pago_Comprobador),
                    Pago_Mantenimiento = VALUES(Pago_Mantenimiento)";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            'ssssssssddsss',
            $data['ID_Informe'],
            $data['ID_Recaudacion'],
            $data['CI_Usuario'],
            $data['Nombre_Maquina'],
            $data['ID_Comercio'],
            $data['Nombre_Comercio'],
            $data['Direccion_Comercio'],
            $data['Telefono_Comercio'],
            $data['Pago_Ensamblador'],
            $data['Pago_Comprobador'],
            $data['Pago_Mantenimiento'],
            $data['empresa_nombre'],
            $data['empresa_descripcion']
        );
        $stmt->execute();
        $stmt->close();
    }

    public function saveDetalle(DetalleInforme $detalle): void
    {
        $conn = $this->db->getConnection();
        $data = $detalle->toArray();

        $sql = "INSERT INTO informe_detalle (ID_Informe_Detalle, ID_Informe, ID_Componente) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('sss', $data['ID_Informe_Detalle'], $data['ID_Informe'], $data['ID_Componente']);
        $stmt->execute();
        $stmt->close();
    }

    public function delete(Uuid $id): bool
    {
        $conn = $this->db->getConnection();

        try {
            $conn->begin_transaction();

            $sqlDetalles = "DELETE d FROM informe_detalle d 
                            INNER JOIN informes_recaudacion i ON d.ID_Informe = i.ID_Informe
                            WHERE i.ID_Recaudacion = ?";
            $stmtDetalles = $conn->prepare($sqlDetalles);
            $idValue = $id->value();
            $stmtDetalles->bind_param('s', $idValue);
            $stmtDetalles->execute();
            $stmtDetalles->close();

            $sqlInforme = "DELETE FROM informes_recaudacion WHERE ID_Recaudacion = ?";
            $stmtInforme = $conn->prepare($sqlInforme);
            $stmtInforme->bind_param('s', $idValue);
            $stmtInforme->execute();
            $stmtInforme->close();

            $sql = "DELETE FROM recaudaciones WHERE ID_Recaudacion = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('s', $idValue);
            $result = $stmt->execute();
            $stmt->close();

            $conn->commit();
            return $result;
        } catch (\Exception $e) {
            $conn->rollback();
            error_log("Error al eliminar recaudación: " . $e->getMessage());
            return false;
        }
    }

    // Métodos adicionales necesarios
    public function findAll(array $filters = [], int $limit = 100, int $offset = 0): array
    {
        // Implementar usando MySQLi
        $conn = $this->db->getConnection();
        $sql = "SELECT r.*, c.Nombre as Nombre_Comercio, m.Nombre_Maquina,
                       u.nombre as nombre_usuario, u.apellido as apellido_usuario
                FROM recaudaciones r
                INNER JOIN MaquinaRecreativa m ON r.ID_Maquina = m.ID_Maquina
                INNER JOIN Comercio c ON m.ID_Comercio = c.ID_Comercio
                INNER JOIN usuario u ON r.ID_Usuario = u.ID_Usuario
                WHERE 1=1";
        
        $params = [];
        $types = "";
        
        if (!empty($filters['fecha_inicio'])) {
            $sql .= " AND DATE(r.fecha) >= ?";
            $params[] = $filters['fecha_inicio'];
            $types .= "s";
        }
        
        if (!empty($filters['fecha_fin'])) {
            $sql .= " AND DATE(r.fecha) <= ?";
            $params[] = $filters['fecha_fin'];
            $types .= "s";
        }
        
        if (!empty($filters['ID_Maquina'])) {
            $sql .= " AND r.ID_Maquina = ?";
            $params[] = $filters['ID_Maquina'];
            $types .= "s";
        }
        
        if (!empty($filters['Tipo_Comercio'])) {
            $sql .= " AND r.Tipo_Comercio = ?";
            $params[] = $filters['Tipo_Comercio'];
            $types .= "s";
        }
        
        $sql .= " ORDER BY r.fecha DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        $types .= "ii";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $recaudaciones = [];
        while ($row = $result->fetch_assoc()) {
            $recaudaciones[] = $row;
        }
        $stmt->close();
        
        return $recaudaciones;
    }

    public function findResumenByTipoComercio(?int $limit = null): array
    {
        $conn = $this->db->getConnection();
        $sql = "SELECT 
                    Tipo_Comercio,
                    COUNT(*) as TotalRecaudaciones,
                    SUM(Monto_Total) as TotalRecaudado,
                    SUM(Monto_Empresa) as TotalEmpresa,
                    SUM(Monto_Comercio) as TotalComercio
                FROM recaudaciones
                GROUP BY Tipo_Comercio
                ORDER BY TotalRecaudado DESC";
        
        if ($limit !== null) {
            $sql .= " LIMIT ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('i', $limit);
        } else {
            $stmt = $conn->prepare($sql);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        $resumen = [];
        while ($row = $result->fetch_assoc()) {
            $resumen[] = $row;
        }
        $stmt->close();
        
        if (empty($resumen)) {
            $resumen[] = [
                'Tipo_Comercio' => 'Sin datos',
                'TotalRecaudaciones' => 0,
                'TotalRecaudado' => 0,
                'TotalEmpresa' => 0,
                'TotalComercio' => 0
            ];
        }
        
        return $resumen;
    }
        /**
     * {@inheritdoc}
     */
    public function findMaquinasOperativasPorComercio(Comercio $comercio): array
    {
        $conn = $this->db->getConnection();
        $sql = "SELECT m.* FROM MaquinaRecreativa m
                WHERE m.ID_Comercio = ? 
                AND m.Estado = 'Operativa' 
                AND m.Etapa = 'Recaudacion'
                ORDER BY m.Nombre_Maquina ASC";

        $stmt = $conn->prepare($sql);
        $comercioId = $comercio->getId();
        $stmt->bind_param('s', $comercioId);
        $stmt->execute();
        $result = $stmt->get_result();

        $maquinas = [];
        while ($row = $result->fetch_assoc()) {
            $maquinas[] = $row;
        }
        $stmt->close();

        return $maquinas;
    }
}