<?php

declare(strict_types=1);

namespace maquinas_recreativas\Infrastructure\Repositories;

use maquinas_recreativas\Domain\Usuario\Tecnico;
use maquinas_recreativas\Domain\Usuario\TecnicoRepository;
use maquinas_recreativas\Domain\Shared\ValueObjects\Uuid;
use maquinas_recreativas\Infrastructure\Database\Database;
use PDO;

/**
 * Implementación en MySQL del repositorio de Técnicos.
 *
 * @package maquinas_recreativas\Infrastructure\Repositories
 * @version 1.0
 */
final class MySQLTecnicoRepository implements TecnicoRepository
{
    private Database $db;
    private MySQLUsuarioRepository $usuarioRepository;

    /**
     * Constructor del repositorio.
     *
     * @param Database $db
     */
    public function __construct(Database $db)
    {
        $this->db = $db;
        $this->usuarioRepository = new MySQLUsuarioRepository($db);
    }

    /**
     * @inheritDoc
     */
    public function findByEspecialidad(string $especialidad): array
    {
        $conn = $this->db->getConnection();
        
        $sql = "SELECT u.*, t.Especialidad, t.Cantidad_Actividades 
                FROM usuario u
                INNER JOIN Tecnico t ON u.ID_Usuario = t.ID_Tecnico
                WHERE t.Especialidad = :especialidad
                ORDER BY u.nombre ASC";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute(['especialidad' => $especialidad]);
        
        $tecnicos = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $tecnicos[] = $this->usuarioRepository->hydrateTecnico($row);
        }
        
        return $tecnicos;
    }

    /**
     * @inheritDoc
     */
    public function incrementarActividades(Uuid $tecnicoId): bool
    {
        $conn = $this->db->getConnection();
        
        $sql = "UPDATE Tecnico 
                SET Cantidad_Actividades = Cantidad_Actividades + 1 
                WHERE ID_Tecnico = :id";
        
        $stmt = $conn->prepare($sql);
        return $stmt->execute(['id' => $tecnicoId->value()]);
    }

    /**
     * @inheritDoc
     */
    public function findAvailableByEspecialidad(string $especialidad): array
    {
        $conn = $this->db->getConnection();
        
        $sql = "SELECT u.*, t.Especialidad, t.Cantidad_Actividades 
                FROM usuario u
                INNER JOIN Tecnico t ON u.ID_Usuario = t.ID_Tecnico
                WHERE t.Especialidad = :especialidad 
                  AND u.estado = 'Activo'
                ORDER BY t.Cantidad_Actividades ASC";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute(['especialidad' => $especialidad]);
        
        $tecnicos = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $tecnicos[] = $this->usuarioRepository->hydrateTecnico($row);
        }
        
        return $tecnicos;
    }
}