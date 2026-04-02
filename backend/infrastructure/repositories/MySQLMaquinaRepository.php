<?php
namespace maquinas_recreativas\Infrastructure\Repositories;

use maquinas_recreativas\Domain\Maquina\MaquinaRecreativa;
use maquinas_recreativas\Domain\Maquina\MaquinaRepository;
use maquinas_recreativas\Domain\Maquina\EstadoMaquina;
use maquinas_recreativas\Domain\Maquina\EtapaMaquina;
use maquinas_recreativas\Domain\Shared\ValueObjects\Uuid;
use maquinas_recreativas\Domain\Comercio\Comercio;
use maquinas_recreativas\Domain\Componente\Componente;
use maquinas_recreativas\Infrastructure\Database\Database;

class MySQLMaquinaRepository implements MaquinaRepository
{
    private Database $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    public function findById(Uuid $id): ?MaquinaRecreativa
    {
        $conn = $this->db->getConnection();
        $sql = "SELECT * FROM MaquinaRecreativa WHERE ID_Maquina = ?";
        $stmt = $conn->prepare($sql);
        $idValue = $id->value();
        $stmt->bind_param('s', $idValue);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        $stmt->close();

        return $data ? MaquinaRecreativa::fromArray($data) : null;
    }

    public function findByTecnicoEnsamblador(Uuid $idTecnico): array
    {
        $conn = $this->db->getConnection();
        $sql = "SELECT * FROM MaquinaRecreativa 
                WHERE ID_Tecnico_Ensamblador = ? 
                AND (Estado = 'Ensamblandose' OR Estado = 'Reensamblandose')
                ORDER BY Fecha_Registro DESC";
        $stmt = $conn->prepare($sql);
        $idValue = $idTecnico->value();
        $stmt->bind_param('s', $idValue);
        $stmt->execute();
        $result = $stmt->get_result();

        $maquinas = [];
        while ($row = $result->fetch_assoc()) {
            $maquinas[] = MaquinaRecreativa::fromArray($row);
        }
        $stmt->close();

        return $maquinas;
    }

    public function findByTecnicoComprobador(Uuid $idTecnico): array
{
    $conn = $this->db->getConnection();
    $sql = "SELECT * FROM MaquinaRecreativa 
            WHERE ID_Tecnico_Comprobador = ? AND Estado = 'Comprobandose'
            ORDER BY Fecha_Registro DESC";
    $stmt = $conn->prepare($sql);
    $idValue = $idTecnico->value();
    $stmt->bind_param('s', $idValue);
    $stmt->execute();
    $result = $stmt->get_result();

    error_log("findByTecnicoComprobador: buscando técnico $idValue, encontró " . $result->num_rows . " filas");

    $maquinas = [];
    while ($row = $result->fetch_assoc()) {
        $maquinas[] = MaquinaRecreativa::fromArray($row);
    }
    $stmt->close();
    return $maquinas;
}

    public function findByTecnicoMantenimiento(Uuid $idTecnico): array
    {
        $conn = $this->db->getConnection();
        $sql = "SELECT * FROM MaquinaRecreativa 
                WHERE ID_Tecnico_Mantenimiento = ? AND Estado = 'No operativa'
                ORDER BY Fecha_Registro DESC";
        $stmt = $conn->prepare($sql);
        $idValue = $idTecnico->value();
        $stmt->bind_param('s', $idValue);
        $stmt->execute();
        $result = $stmt->get_result();

        $maquinas = [];
        while ($row = $result->fetch_assoc()) {
            $maquinas[] = MaquinaRecreativa::fromArray($row);
        }
        $stmt->close();

        return $maquinas;
    }

    public function findByEstado(EstadoMaquina $estado): array
    {
        $conn = $this->db->getConnection();
        $sql = "SELECT * FROM MaquinaRecreativa WHERE Estado = ? ORDER BY Fecha_Registro DESC";
        $stmt = $conn->prepare($sql);
        $estadoValue = $estado->value();
        $stmt->bind_param('s', $estadoValue);
        $stmt->execute();
        $result = $stmt->get_result();

        $maquinas = [];
        while ($row = $result->fetch_assoc()) {
            $maquinas[] = MaquinaRecreativa::fromArray($row);
        }
        $stmt->close();

        return $maquinas;
    }

    public function findByEtapa(EtapaMaquina $etapa): array
    {
        $conn = $this->db->getConnection();
        $sql = "SELECT * FROM MaquinaRecreativa WHERE Etapa = ? ORDER BY Fecha_Registro DESC";
        $stmt = $conn->prepare($sql);
        $etapaValue = $etapa->value();
        $stmt->bind_param('s', $etapaValue);
        $stmt->execute();
        $result = $stmt->get_result();

        $maquinas = [];
        while ($row = $result->fetch_assoc()) {
            $maquinas[] = MaquinaRecreativa::fromArray($row);
        }
        $stmt->close();

        return $maquinas;
    }

    public function findParaDistribucion(): array
    {
        return $this->findByEtapaAndEstado(EtapaMaquina::DISTRIBUCION(), EstadoMaquina::DISTRIBUYENDOSE());
    }

    private function findByEtapaAndEstado(EtapaMaquina $etapa, EstadoMaquina $estado): array
    {
        $conn = $this->db->getConnection();
        $sql = "SELECT * FROM MaquinaRecreativa WHERE Etapa = ? AND Estado = ? ORDER BY Fecha_Registro DESC";
        $stmt = $conn->prepare($sql);
        $etapaValue = $etapa->value();
        $estadoValue = $estado->value();
        $stmt->bind_param('ss', $etapaValue, $estadoValue);
        $stmt->execute();
        $result = $stmt->get_result();

        $maquinas = [];
        while ($row = $result->fetch_assoc()) {
            $maquinas[] = MaquinaRecreativa::fromArray($row);
        }
        $stmt->close();

        return $maquinas;
    }

    public function findOperativasPorComercio(Comercio $comercio): array
    {
        $conn = $this->db->getConnection();
        $sql = "SELECT * FROM MaquinaRecreativa 
                WHERE ID_Comercio = ? 
                AND Estado = 'Operativa' 
                AND Etapa = 'Recaudacion'
                ORDER BY Nombre_Maquina ASC";
        $stmt = $conn->prepare($sql);
        $comercioId = $comercio->getId();
        $stmt->bind_param('s', $comercioId);
        $stmt->execute();
        $result = $stmt->get_result();

        $maquinas = [];
        while ($row = $result->fetch_assoc()) {
            $maquinas[] = MaquinaRecreativa::fromArray($row);
        }
        $stmt->close();

        return $maquinas;
    }

    public function getComponentesMontaje(MaquinaRecreativa $maquina): array
    {
        $conn = $this->db->getConnection();
        $sql = "SELECT c.* FROM montaje m
                JOIN componente c ON m.ID_Componente = c.ID_Componente
                WHERE m.ID_Maquina = ?
                ORDER BY m.fecha DESC";
        $stmt = $conn->prepare($sql);
        $maquinaId = $maquina->id()->value();
        $stmt->bind_param('s', $maquinaId);
        $stmt->execute();
        $result = $stmt->get_result();

        $componentes = [];
        while ($row = $result->fetch_assoc()) {
            $componentes[] = Componente::fromArray($row);
        }
        $stmt->close();

        return $componentes;
    }

    public function save(MaquinaRecreativa $maquina): void
    {
        $conn = $this->db->getConnection();
        $data = $maquina->toArray();

        $sql = "INSERT INTO MaquinaRecreativa (
                    ID_Maquina, Nombre_Maquina, Tipo, Fecha_Registro, Estado, Etapa,
                    ID_Comercio, ID_Tecnico_Ensamblador, ID_Tecnico_Comprobador, ID_Tecnico_Mantenimiento
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                    Nombre_Maquina = VALUES(Nombre_Maquina),
                    Tipo = VALUES(Tipo),
                    Estado = VALUES(Estado),
                    Etapa = VALUES(Etapa),
                    ID_Tecnico_Ensamblador = VALUES(ID_Tecnico_Ensamblador),
                    ID_Tecnico_Comprobador = VALUES(ID_Tecnico_Comprobador),
                    ID_Tecnico_Mantenimiento = VALUES(ID_Tecnico_Mantenimiento)";

        $stmt = $conn->prepare($sql);
        $id = $data['ID_Maquina'];
        $nombre = $data['Nombre_Maquina'];
        $tipo = $data['Tipo'];
        $fechaRegistro = $data['Fecha_Registro'];
        $estado = $data['Estado'];
        $etapa = $data['Etapa'];
        $idComercio = $data['ID_Comercio'];
        $idEnsamblador = $data['ID_Tecnico_Ensamblador'];
        $idComprobador = $data['ID_Tecnico_Comprobador'];
        $idMantenimiento = $data['ID_Tecnico_Mantenimiento'];
        
        $stmt->bind_param('ssssssssss', $id, $nombre, $tipo, $fechaRegistro, $estado, $etapa, $idComercio, $idEnsamblador, $idComprobador, $idMantenimiento);
        $stmt->execute();
        $stmt->close();
    }
}