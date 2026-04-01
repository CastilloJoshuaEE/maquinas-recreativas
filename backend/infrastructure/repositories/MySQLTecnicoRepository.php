<?php
declare(strict_types=1);

namespace maquinas_recreativas\Infrastructure\Repositories;

use maquinas_recreativas\Domain\Usuario\Tecnico;
use maquinas_recreativas\Domain\Usuario\TecnicoRepository;
use maquinas_recreativas\Domain\Shared\ValueObjects\Uuid;
use maquinas_recreativas\Infrastructure\Database\Database;

final class MySQLTecnicoRepository implements TecnicoRepository
{
    private Database $db;
    private MySQLUsuarioRepository $usuarioRepository;

    public function __construct(Database $db)
    {
        $this->db = $db;
        $this->usuarioRepository = new MySQLUsuarioRepository($db);
    }

    public function findByEspecialidad(string $especialidad): array
    {
        $conn = $this->db->getConnection();
        
        $sql = "SELECT u.*, t.Especialidad, t.Cantidad_Actividades 
                FROM usuario u
                INNER JOIN Tecnico t ON u.ID_Usuario = t.ID_Tecnico
                WHERE t.Especialidad = ?
                ORDER BY u.nombre ASC";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('s', $especialidad);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $tecnicos = [];
        while ($row = $result->fetch_assoc()) {
            $tecnicos[] = $this->usuarioRepository->hydrate($row);
        }
        $stmt->close();
        
        return $tecnicos;
    }

    public function incrementarActividades(Uuid $tecnicoId): bool
    {
        $conn = $this->db->getConnection();
        
        $sql = "UPDATE Tecnico 
                SET Cantidad_Actividades = Cantidad_Actividades + 1 
                WHERE ID_Tecnico = ?";
        
        $stmt = $conn->prepare($sql);
        $idValue = $tecnicoId->value();
        $stmt->bind_param('s', $idValue);
        $result = $stmt->execute();
        $stmt->close();
        
        return $result;
    }

    public function findAvailableByEspecialidad(string $especialidad): array
    {
        $conn = $this->db->getConnection();
        
        $sql = "SELECT u.*, t.Especialidad, t.Cantidad_Actividades 
                FROM usuario u
                INNER JOIN Tecnico t ON u.ID_Usuario = t.ID_Tecnico
                WHERE t.Especialidad = ? 
                  AND u.estado = 'Activo'
                ORDER BY t.Cantidad_Actividades ASC";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('s', $especialidad);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $tecnicos = [];
        while ($row = $result->fetch_assoc()) {
            $tecnicos[] = $this->usuarioRepository->hydrate($row);
        }
        $stmt->close();
        
        return $tecnicos;
    }
}