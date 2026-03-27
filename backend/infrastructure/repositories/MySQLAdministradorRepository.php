<?php
/**
 * infrastructure/repositories/MySQLAdministradorRepository.php
 *
 * Implementación MySQL del repositorio para operaciones administrativas sobre usuarios.
 * Extiende el repositorio base de usuarios para aprovechar su funcionalidad.
 *
 * @package maquinas_recreativas\Infrastructure\Repositories
 */

namespace maquinas_recreativas\Infrastructure\Repositories;

use maquinas_recreativas\Domain\Usuario\Usuario;
use maquinas_recreativas\Domain\Usuario\AdministradorRepository;
use maquinas_recreativas\Domain\Shared\ValueObjects\Uuid;
use maquinas_recreativas\Infrastructure\Database\Database;
use maquinas_recreativas\Infrastructure\Security\CifradoHelper;
use PDO;
use PDOException;

/**
 * Class MySQLAdministradorRepository
 *
 * Repositorio especializado para operaciones administrativas sobre usuarios.
 * Proporciona métodos avanzados de consulta y gestión que normalmente solo
 * un administrador del sistema necesita.
 */
final class MySQLAdministradorRepository extends MySQLUsuarioRepository implements AdministradorRepository
{
    /**
     * Constructor.
     *
     * @param Database $db
     */
    public function __construct(Database $db)
    {
        parent::__construct($db);
    }

    /**
     * Obtiene todos los usuarios aplicando filtros opcionales.
     *
     * @param array $filters Filtros disponibles: 'tipo', 'estado', 'ci', 'limit', 'offset'
     * @return array Lista de arrays con datos de usuarios.
     */
    public function findAllWithFilters(array $filters = []): array
    {
        $conn = $this->db->getConnection();

        $sql = "SELECT u.*, t.Especialidad, t.Cantidad_Actividades 
                FROM usuario u 
                LEFT JOIN Tecnico t ON u.ID_Usuario = t.ID_Tecnico 
                WHERE 1=1";

        $params = [];
        $types = "";

        if (!empty($filters['tipo'])) {
            $sql .= " AND u.tipo = :tipo";
            $params[':tipo'] = $filters['tipo'];
        }

        if (!empty($filters['estado'])) {
            $sql .= " AND u.estado = :estado";
            $params[':estado'] = $filters['estado'];
        }

        if (!empty($filters['ci'])) {
            $ciEncriptada = CifradoHelper::encriptar($filters['ci']);
            $sql .= " AND u.ci = :ci";
            $params[':ci'] = $ciEncriptada;
        }

        $sql .= " ORDER BY u.nombre ASC";

        if (isset($filters['limit'])) {
            $sql .= " LIMIT :limit";
            $params[':limit'] = (int)$filters['limit'];
        }

        if (isset($filters['offset'])) {
            $sql .= " OFFSET :offset";
            $params[':offset'] = (int)$filters['offset'];
        }

        $stmt = $conn->prepare($sql);
        foreach ($params as $key => $value) {
            if (is_int($value)) {
                $stmt->bindValue($key, $value, PDO::PARAM_INT);
            } else {
                $stmt->bindValue($key, $value);
            }
        }
        $stmt->execute();

        $result = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            // Desencriptar datos sensibles
            if (!empty($row['email'])) {
                $row['email'] = CifradoHelper::desencriptar($row['email']);
            }
            if (!empty($row['ci'])) {
                $row['ci'] = CifradoHelper::desencriptar($row['ci']);
            }
            $result[] = $row;
        }

        return $result;
    }

    /**
     * Obtiene estadísticas generales de usuarios.
     *
     * @return array{total: int, por_tipo: array, por_estado: array}
     */
    public function getEstadisticas(): array
    {
        $conn = $this->db->getConnection();

        // Total de usuarios
        $stmt = $conn->query("SELECT COUNT(*) as total FROM usuario");
        $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

        // Conteo por tipo
        $stmt = $conn->query("SELECT tipo, COUNT(*) as cantidad FROM usuario GROUP BY tipo");
        $porTipo = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $porTipo[$row['tipo']] = (int)$row['cantidad'];
        }

        // Conteo por estado
        $stmt = $conn->query("SELECT estado, COUNT(*) as cantidad FROM usuario GROUP BY estado");
        $porEstado = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $porEstado[$row['estado']] = (int)$row['cantidad'];
        }

        return [
            'total' => (int)$total,
            'por_tipo' => $porTipo,
            'por_estado' => $porEstado,
        ];
    }

    /**
     * Actualiza el estado de un usuario.
     *
     * @param Uuid $usuarioId
     * @param string $nuevoEstado
     * @return bool
     */
    public function updateEstado(Uuid $usuarioId, string $nuevoEstado): bool
    {
        $conn = $this->db->getConnection();

        $sql = "UPDATE usuario SET estado = :estado WHERE ID_Usuario = :id";
        $stmt = $conn->prepare($sql);
        return $stmt->execute([
            ':estado' => $nuevoEstado,
            ':id' => $usuarioId->value(),
        ]);
    }

    /**
     * Busca usuarios por término en nombre o apellido.
     *
     * @param string $termino
     * @param int $limit
     * @return array
     */
    public function buscarPorNombre(string $termino, int $limit = 10): array
    {
        $conn = $this->db->getConnection();
        $termino = "%{$termino}%";

        $sql = "SELECT u.*, t.Especialidad, t.Cantidad_Actividades 
                FROM usuario u 
                LEFT JOIN Tecnico t ON u.ID_Usuario = t.ID_Tecnico 
                WHERE u.nombre LIKE :termino OR u.apellido LIKE :termino
                ORDER BY u.nombre ASC
                LIMIT :limit";

        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':termino', $termino);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        $result = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if (!empty($row['email'])) {
                $row['email'] = CifradoHelper::desencriptar($row['email']);
            }
            if (!empty($row['ci'])) {
                $row['ci'] = CifradoHelper::desencriptar($row['ci']);
            }
            $result[] = $row;
        }

        return $result;
    }
}