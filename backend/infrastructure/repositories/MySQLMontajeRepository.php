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
                VALUES (?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            'ssssss',
            $data['ID_Montaje'],
            $data['ID_Maquina'],
            $data['ID_Componente'],
            $data['ID_Tecnico'],
            $data['detalle'],
            $data['fecha']
        );
        $stmt->execute();
        $stmt->close();
    }

    public function findByMaquina(Uuid $idMaquina): array
    {
        $conn = $this->db->getConnection();
        $sql = "SELECT * FROM montaje WHERE ID_Maquina = ? ORDER BY fecha DESC";
        $stmt = $conn->prepare($sql);
        $idValue = $idMaquina->value();
        $stmt->bind_param('s', $idValue);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $montajes = [];
        while ($row = $result->fetch_assoc()) {
            $montajes[] = Montaje::fromArray($row);
        }
        $stmt->close();
        
        return $montajes;
    }

    public function findByComponente(Uuid $idComponente): array
    {
        $conn = $this->db->getConnection();
        $sql = "SELECT * FROM montaje WHERE ID_Componente = ? ORDER BY fecha DESC";
        $stmt = $conn->prepare($sql);
        $idValue = $idComponente->value();
        $stmt->bind_param('s', $idValue);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $montajes = [];
        while ($row = $result->fetch_assoc()) {
            $montajes[] = Montaje::fromArray($row);
        }
        $stmt->close();
        
        return $montajes;
    }

    public function findByTecnico(Uuid $idTecnico): array
    {
        $conn = $this->db->getConnection();
        $sql = "SELECT * FROM montaje WHERE ID_Tecnico = ? ORDER BY fecha DESC";
        $stmt = $conn->prepare($sql);
        $idValue = $idTecnico->value();
        $stmt->bind_param('s', $idValue);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $montajes = [];
        while ($row = $result->fetch_assoc()) {
            $montajes[] = Montaje::fromArray($row);
        }
        $stmt->close();
        
        return $montajes;
    }
}