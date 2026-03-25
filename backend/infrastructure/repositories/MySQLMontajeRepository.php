<?php
/**
 * infrastructure/repositories/MySQLMontajeRepository.php
 *
 * Implementación MySQL del repositorio de montajes.
 *
 * @package maquinas_recreativas\Infrastructure\Repositories
 */

namespace maquinas_recreativas\Infrastructure\Repositories;

use maquinas_recreativas\Domain\Montaje\Montaje;
use maquinas_recreativas\Domain\Montaje\MontajeRepository;
use maquinas_recreativas\Domain\Shared\ValueObjects\Uuid;
use maquinas_recreativas\Infrastructure\Database\Database;
use PDO;

/**
 * Class MySQLMontajeRepository
 */
class MySQLMontajeRepository implements MontajeRepository
{
    private Database $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    public function save(Montaje $montaje): void
    {
        $conn = $this->db->getConnection();
        $data = $montaje->toArray();

        $sql = "INSERT INTO montaje (ID_Montaje, ID_Maquina, ID_Componente, ID_Tecnico, detalle, fecha) 
                VALUES (:id, :idMaquina, :idComponente, :idTecnico, :detalle, :fecha)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':id' => $data['ID_Montaje'],
            ':idMaquina' => $data['ID_Maquina'],
            ':idComponente' => $data['ID_Componente'],
            ':idTecnico' => $data['ID_Tecnico'],
            ':detalle' => $data['detalle'],
            ':fecha' => $data['fecha']
        ]);
    }

    public function findByMaquina(Uuid $idMaquina): array
    {
        $conn = $this->db->getConnection();
        $sql = "SELECT * FROM montaje WHERE ID_Maquina = :idMaquina ORDER BY fecha DESC";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':idMaquina' => $idMaquina->value()]);

        $montajes = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $montajes[] = Montaje::fromArray($row);
        }
        return $montajes;
    }

    public function findByComponente(Uuid $idComponente): array
    {
        $conn = $this->db->getConnection();
        $sql = "SELECT * FROM montaje WHERE ID_Componente = :idComponente ORDER BY fecha DESC";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':idComponente' => $idComponente->value()]);

        $montajes = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $montajes[] = Montaje::fromArray($row);
        }
        return $montajes;
    }

    public function findByTecnico(Uuid $idTecnico): array
    {
        $conn = $this->db->getConnection();
        $sql = "SELECT * FROM montaje WHERE ID_Tecnico = :idTecnico ORDER BY fecha DESC";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':idTecnico' => $idTecnico->value()]);

        $montajes = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $montajes[] = Montaje::fromArray($row);
        }
        return $montajes;
    }
}