<?php
/**
 * infrastructure/repositories/MySQLMaquinaRepository.php
 *
 * Implementación MySQL del repositorio de máquinas.
 *
 * @package maquinas_recreativas\Infrastructure\Repositories
 */

namespace maquinas_recreativas\Infrastructure\Repositories;

use maquinas_recreativas\Domain\Maquina\MaquinaRecreativa;
use maquinas_recreativas\Domain\Maquina\MaquinaRepository;
use maquinas_recreativas\Domain\Maquina\EstadoMaquina;
use maquinas_recreativas\Domain\Maquina\EtapaMaquina;
use maquinas_recreativas\Domain\Shared\ValueObjects\Uuid;
use maquinas_recreativas\Domain\Usuario\Usuario;
use maquinas_recreativas\Domain\Comercio\Comercio;
use maquinas_recreativas\Domain\Componente\Componente;
use maquinas_recreativas\Infrastructure\Database\Database;
use PDO;
use RuntimeException;

/**
 * Class MySQLMaquinaRepository
 */
class MySQLMaquinaRepository implements MaquinaRepository
{
    private Database $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    /**
     * @inheritDoc
     */
    public function save(MaquinaRecreativa $maquina): void
    {
        $conn = $this->db->getConnection();
        $data = $maquina->toArray();

        $sql = "INSERT INTO MaquinaRecreativa (
                    ID_Maquina, Nombre_Maquina, Tipo, Fecha_Registro, Estado, Etapa,
                    ID_Comercio, ID_Tecnico_Ensamblador, ID_Tecnico_Comprobador, ID_Tecnico_Mantenimiento
                ) VALUES (
                    :id, :nombre, :tipo, :fechaRegistro, :estado, :etapa,
                    :idComercio, :idEnsamblador, :idComprobador, :idMantenimiento
                ) ON DUPLICATE KEY UPDATE
                    Nombre_Maquina = VALUES(Nombre_Maquina),
                    Tipo = VALUES(Tipo),
                    Estado = VALUES(Estado),
                    Etapa = VALUES(Etapa),
                    ID_Tecnico_Ensamblador = VALUES(ID_Tecnico_Ensamblador),
                    ID_Tecnico_Comprobador = VALUES(ID_Tecnico_Comprobador),
                    ID_Tecnico_Mantenimiento = VALUES(ID_Tecnico_Mantenimiento)";

        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':id' => $data['ID_Maquina'],
            ':nombre' => $data['Nombre_Maquina'],
            ':tipo' => $data['Tipo'],
            ':fechaRegistro' => $data['Fecha_Registro'],
            ':estado' => $data['Estado'],
            ':etapa' => $data['Etapa'],
            ':idComercio' => $data['ID_Comercio'],
            ':idEnsamblador' => $data['ID_Tecnico_Ensamblador'],
            ':idComprobador' => $data['ID_Tecnico_Comprobador'],
            ':idMantenimiento' => $data['ID_Tecnico_Mantenimiento']
        ]);
    }

    /**
     * @inheritDoc
     */
    public function findById(Uuid $id): ?MaquinaRecreativa
    {
        $conn = $this->db->getConnection();
        $sql = "SELECT * FROM MaquinaRecreativa WHERE ID_Maquina = :id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':id' => $id->value()]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        return $data ? MaquinaRecreativa::fromArray($data) : null;
    }

    /**
     * @inheritDoc
     */
    public function findByTecnicoEnsamblador(Uuid $idTecnico): array
    {
        $conn = $this->db->getConnection();
        $sql = "SELECT * FROM MaquinaRecreativa 
                WHERE ID_Tecnico_Ensamblador = :id 
                AND (Estado = 'Ensamblandose' OR Estado = 'Reensamblandose')
                ORDER BY Fecha_Registro DESC";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':id' => $idTecnico->value()]);

        $maquinas = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $maquinas[] = MaquinaRecreativa::fromArray($row);
        }
        return $maquinas;
    }

    /**
     * @inheritDoc
     */
    public function findByTecnicoComprobador(Uuid $idTecnico): array
    {
        $conn = $this->db->getConnection();
        $sql = "SELECT * FROM MaquinaRecreativa 
                WHERE ID_Tecnico_Comprobador = :id AND Estado = 'Comprobandose'
                ORDER BY Fecha_Registro DESC";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':id' => $idTecnico->value()]);

        $maquinas = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $maquinas[] = MaquinaRecreativa::fromArray($row);
        }
        return $maquinas;
    }

    /**
     * @inheritDoc
     */
    public function findByTecnicoMantenimiento(Uuid $idTecnico): array
    {
        $conn = $this->db->getConnection();
        $sql = "SELECT * FROM MaquinaRecreativa 
                WHERE ID_Tecnico_Mantenimiento = :id AND Estado = 'No operativa'
                ORDER BY Fecha_Registro DESC";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':id' => $idTecnico->value()]);

        $maquinas = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $maquinas[] = MaquinaRecreativa::fromArray($row);
        }
        return $maquinas;
    }

    /**
     * @inheritDoc
     */
    public function findByEstado(EstadoMaquina $estado): array
    {
        $conn = $this->db->getConnection();
        $sql = "SELECT * FROM MaquinaRecreativa WHERE Estado = :estado ORDER BY Fecha_Registro DESC";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':estado' => $estado->value()]);

        $maquinas = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $maquinas[] = MaquinaRecreativa::fromArray($row);
        }
        return $maquinas;
    }

    /**
     * @inheritDoc
     */
    public function findByEtapa(EtapaMaquina $etapa): array
    {
        $conn = $this->db->getConnection();
        $sql = "SELECT * FROM MaquinaRecreativa WHERE Etapa = :etapa ORDER BY Fecha_Registro DESC";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':etapa' => $etapa->value()]);

        $maquinas = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $maquinas[] = MaquinaRecreativa::fromArray($row);
        }
        return $maquinas;
    }

    /**
     * @inheritDoc
     */
    public function findParaDistribucion(): array
    {
        return $this->findByEtapaAndEstado(EtapaMaquina::DISTRIBUCION(), EstadoMaquina::DISTRIBUYENDOSE());
    }

    /**
     * Busca máquinas por etapa y estado combinados.
     *
     * @param EtapaMaquina $etapa
     * @param EstadoMaquina $estado
     * @return array<MaquinaRecreativa>
     */
    private function findByEtapaAndEstado(EtapaMaquina $etapa, EstadoMaquina $estado): array
    {
        $conn = $this->db->getConnection();
        $sql = "SELECT * FROM MaquinaRecreativa 
                WHERE Etapa = :etapa AND Estado = :estado 
                ORDER BY Fecha_Registro DESC";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':etapa' => $etapa->value(),
            ':estado' => $estado->value()
        ]);

        $maquinas = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $maquinas[] = MaquinaRecreativa::fromArray($row);
        }
        return $maquinas;
    }

    /**
     * @inheritDoc
     */
    public function findOperativasPorComercio(Comercio $comercio): array
    {
        $conn = $this->db->getConnection();
        $sql = "SELECT * FROM MaquinaRecreativa 
                WHERE ID_Comercio = :idComercio 
                AND Estado = 'Operativa' 
                AND Etapa = 'Recaudacion'
                ORDER BY Nombre_Maquina ASC";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':idComercio' => $comercio->id()->value()]);

        $maquinas = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $maquinas[] = MaquinaRecreativa::fromArray($row);
        }
        return $maquinas;
    }

    /**
     * @inheritDoc
     */
    public function getComponentesMontaje(MaquinaRecreativa $maquina): array
    {
        $conn = $this->db->getConnection();
        $sql = "SELECT c.* FROM montaje m
                JOIN componente c ON m.ID_Componente = c.ID_Componente
                WHERE m.ID_Maquina = :idMaquina
                ORDER BY m.fecha DESC";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':idMaquina' => $maquina->id()->value()]);

        $componentes = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $componentes[] = Componente::fromArray($row);
        }
        return $componentes;
    }
}