<?php
/**
 * infrastructure/repositories/MySQLRecaudacionRepository.php
 *
 * Implementación MySQL del repositorio de recaudaciones.
 *
 * @package maquinas_recreativas\Infrastructure\Repositories
 */

namespace maquinas_recreativas\Infrastructure\Repositories;

use maquinas_recreativas\Domain\Recaudacion\Recaudacion;
use maquinas_recreativas\Domain\Recaudacion\InformeRecaudacion;
use maquinas_recreativas\Domain\Recaudacion\DetalleInforme;
use maquinas_recreativas\Domain\Recaudacion\RecaudacionRepository;
use maquinas_recreativas\Domain\Shared\ValueObjects\Uuid;
use maquinas_recreativas\Domain\Maquina\MaquinaRecreativa;
use maquinas_recreativas\Domain\Comercio\Comercio;
use maquinas_recreativas\Infrastructure\Database\Database;
use PDO;

/**
 * Class MySQLRecaudacionRepository
 */
class MySQLRecaudacionRepository implements RecaudacionRepository
{
    private Database $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    public function save(Recaudacion $recaudacion): void
    {
        $conn = $this->db->getConnection();
        $data = $recaudacion->toArray();

        $sql = "INSERT INTO recaudaciones (
                    ID_Recaudacion, Tipo_Comercio, ID_Maquina, ID_Usuario,
                    Monto_Total, Monto_Empresa, Monto_Comercio, fecha,
                    detalle, Porcentaje_Comercio
                ) VALUES (
                    :id, :tipoComercio, :idMaquina, :idUsuario,
                    :montoTotal, :montoEmpresa, :montoComercio, :fecha,
                    :detalle, :porcentaje
                ) ON DUPLICATE KEY UPDATE
                    Tipo_Comercio = VALUES(Tipo_Comercio),
                    Monto_Total = VALUES(Monto_Total),
                    Monto_Empresa = VALUES(Monto_Empresa),
                    Monto_Comercio = VALUES(Monto_Comercio),
                    detalle = VALUES(detalle),
                    Porcentaje_Comercio = VALUES(Porcentaje_Comercio)";

        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':id' => $data['ID_Recaudacion'],
            ':tipoComercio' => $data['Tipo_Comercio'],
            ':idMaquina' => $data['ID_Maquina'],
            ':idUsuario' => $data['ID_Usuario'],
            ':montoTotal' => $data['Monto_Total'],
            ':montoEmpresa' => $data['Monto_Empresa'],
            ':montoComercio' => $data['Monto_Comercio'],
            ':fecha' => $data['fecha'],
            ':detalle' => $data['detalle'],
            ':porcentaje' => $data['Porcentaje_Comercio']
        ]);
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
                ) VALUES (
                    :id, :idRecaudacion, :ciUsuario, :nombreMaquina,
                    :idComercio, :nombreComercio, :direccionComercio, :telefonoComercio,
                    :pagoEnsamblador, :pagoComprobador, :pagoMantenimiento,
                    :empresaNombre, :empresaDescripcion
                ) ON DUPLICATE KEY UPDATE
                    CI_Usuario = VALUES(CI_Usuario),
                    Nombre_Maquina = VALUES(Nombre_Maquina),
                    Nombre_Comercio = VALUES(Nombre_Comercio),
                    Direccion_Comercio = VALUES(Direccion_Comercio),
                    Telefono_Comercio = VALUES(Telefono_Comercio),
                    Pago_Ensamblador = VALUES(Pago_Ensamblador),
                    Pago_Comprobador = VALUES(Pago_Comprobador),
                    Pago_Mantenimiento = VALUES(Pago_Mantenimiento)";

        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':id' => $data['ID_Informe'],
            ':idRecaudacion' => $data['ID_Recaudacion'],
            ':ciUsuario' => $data['CI_Usuario'],
            ':nombreMaquina' => $data['Nombre_Maquina'],
            ':idComercio' => $data['ID_Comercio'],
            ':nombreComercio' => $data['Nombre_Comercio'],
            ':direccionComercio' => $data['Direccion_Comercio'],
            ':telefonoComercio' => $data['Telefono_Comercio'],
            ':pagoEnsamblador' => $data['Pago_Ensamblador'],
            ':pagoComprobador' => $data['Pago_Comprobador'],
            ':pagoMantenimiento' => $data['Pago_Mantenimiento'],
            ':empresaNombre' => $data['empresa_nombre'],
            ':empresaDescripcion' => $data['empresa_descripcion']
        ]);
    }

    public function saveDetalle(DetalleInforme $detalle): void
    {
        $conn = $this->db->getConnection();
        $data = $detalle->toArray();

        $sql = "INSERT INTO informe_detalle (ID_Informe_Detalle, ID_Informe, ID_Componente) 
                VALUES (:id, :idInforme, :idComponente)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':id' => $data['ID_Informe_Detalle'],
            ':idInforme' => $data['ID_Informe'],
            ':idComponente' => $data['ID_Componente']
        ]);
    }

    public function findById(Uuid $id): ?Recaudacion
    {
        $conn = $this->db->getConnection();
        $sql = "SELECT * FROM recaudaciones WHERE ID_Recaudacion = :id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':id' => $id->value()]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        return $data ? Recaudacion::fromArray($data) : null;
    }

    public function findInformeByRecaudacion(Uuid $idRecaudacion): ?InformeRecaudacion
    {
        $conn = $this->db->getConnection();
        $sql = "SELECT * FROM informes_recaudacion WHERE ID_Recaudacion = :idRecaudacion";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':idRecaudacion' => $idRecaudacion->value()]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        return $data ? InformeRecaudacion::fromArray($data) : null;
    }

    public function findDetallesByInforme(Uuid $idInforme): array
    {
        $conn = $this->db->getConnection();
        $sql = "SELECT c.* 
                FROM informe_detalle id
                JOIN componente c ON id.ID_Componente = c.ID_Componente
                WHERE id.ID_Informe = :idInforme";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':idInforme' => $idInforme->value()]);

        $detalles = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $detalles[] = $row;
        }
        return $detalles;
    }

    public function findAll(array $filters = [], int $limit = 100, int $offset = 0): array
    {
        $conn = $this->db->getConnection();
        $sql = "SELECT 
                    r.*,
                    c.Nombre as Nombre_Comercio,
                    m.Nombre_Maquina,
                    u.nombre as nombre_usuario,
                    u.apellido as apellido_usuario,
                    (SELECT ID_Informe FROM informes_recaudacion WHERE ID_Recaudacion = r.ID_Recaudacion LIMIT 1) as ID_Informe
                FROM recaudaciones r
                INNER JOIN MaquinaRecreativa m ON r.ID_Maquina = m.ID_Maquina
                INNER JOIN Comercio c ON m.ID_Comercio = c.ID_Comercio
                INNER JOIN usuario u ON r.ID_Usuario = u.ID_Usuario
                WHERE 1=1";

        $params = [];

        if (!empty($filters['fecha_inicio'])) {
            $sql .= " AND DATE(r.fecha) >= :fechaInicio";
            $params[':fechaInicio'] = $filters['fecha_inicio'];
        }

        if (!empty($filters['fecha_fin'])) {
            $sql .= " AND DATE(r.fecha) <= :fechaFin";
            $params[':fechaFin'] = $filters['fecha_fin'];
        }

        if (!empty($filters['ID_Maquina'])) {
            $sql .= " AND r.ID_Maquina = :idMaquina";
            $params[':idMaquina'] = $filters['ID_Maquina'];
        }

        if (!empty($filters['Tipo_Comercio'])) {
            $sql .= " AND r.Tipo_Comercio = :tipoComercio";
            $params[':tipoComercio'] = $filters['Tipo_Comercio'];
        }

        $sql .= " ORDER BY r.fecha DESC LIMIT :limit OFFSET :offset";
        $params[':limit'] = $limit;
        $params[':offset'] = $offset;

        $stmt = $conn->prepare($sql);
        foreach ($params as $key => $value) {
            if (is_int($value)) {
                $stmt->bindValue($key, $value, PDO::PARAM_INT);
            } else {
                $stmt->bindValue($key, $value);
            }
        }
        $stmt->execute();

        $recaudaciones = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $recaudaciones[] = $row;
        }
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
            $sql .= " LIMIT :limit";
            $stmt = $conn->prepare($sql);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        } else {
            $stmt = $conn->prepare($sql);
        }

        $stmt->execute();

        $resumen = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $resumen[] = $row;
        }

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

        $maquinas = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $maquinas[] = $row;
        }
        return $maquinas;
    }

    public function findMaquinasOperativasPorComercio(Comercio $comercio): array
    {
        $conn = $this->db->getConnection();
        $sql = "SELECT m.* FROM MaquinaRecreativa m
                WHERE m.ID_Comercio = :idComercio 
                AND m.Estado = 'Operativa' 
                AND m.Etapa = 'Recaudacion'
                ORDER BY m.Nombre_Maquina ASC";

        $stmt = $conn->prepare($sql);
        $stmt->execute([':idComercio' => $comercio->id()->value()]);

        $maquinas = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $maquinas[] = $row;
        }
        return $maquinas;
    }

    public function delete(Uuid $id): bool
    {
        $conn = $this->db->getConnection();

        try {
            $conn->beginTransaction();

            // Eliminar detalles del informe
            $sqlDetalles = "DELETE d FROM informe_detalle d 
                            INNER JOIN informes_recaudacion i ON d.ID_Informe = i.ID_Informe
                            WHERE i.ID_Recaudacion = :id";
            $stmtDetalles = $conn->prepare($sqlDetalles);
            $stmtDetalles->execute([':id' => $id->value()]);

            // Eliminar informe principal
            $sqlInforme = "DELETE FROM informes_recaudacion WHERE ID_Recaudacion = :id";
            $stmtInforme = $conn->prepare($sqlInforme);
            $stmtInforme->execute([':id' => $id->value()]);

            // Eliminar recaudación
            $sql = "DELETE FROM recaudaciones WHERE ID_Recaudacion = :id";
            $stmt = $conn->prepare($sql);
            $result = $stmt->execute([':id' => $id->value()]);

            $conn->commit();
            return $result;
        } catch (\Exception $e) {
            $conn->rollBack();
            error_log("Error al eliminar recaudación: " . $e->getMessage());
            return false;
        }
    }
}