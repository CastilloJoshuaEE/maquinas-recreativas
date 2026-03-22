<?php
/**
 * Implementación MySQL del repositorio de comercios
 * 
 * @package Infrastructure\Repositories
 * @author Tu Nombre
 * @version 1.0.0
 */

namespace Infrastructure\Repositories;

use Domain\Comercio\Comercio;
use Domain\Comercio\ComercioRepository;
use Domain\Shared\ValueObjects\Uuid;
use Infrastructure\Database\Database;
use Domain\Shared\Exceptions\DomainException;

/**
 * @package Infrastructure\Repositories
 * 
 * Implementación concreta del repositorio de comercios
 * utilizando MySQL como almacenamiento.
 */
class MySQLComercioRepository implements ComercioRepository {
    
    /**
     * @var Database Instancia de la conexión a BD
     */
    private $db;
    
    /**
     * Constructor del repositorio
     * 
     * @param Database $database
     */
    public function __construct(Database $database) {
        $this->db = $database;
    }
    
    /**
     * {@inheritdoc}
     */
    public function save(Comercio $comercio): void {
        $conn = $this->db->getConnection();
        
        try {
            $sql = "INSERT INTO comercio (ID_Comercio, nombre, tipo, direccion, telefono, Cantidad_Maquinas, fecha_registro) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param(
                "sssssis",
                $comercio->getId(),
                $comercio->getNombre(),
                $comercio->getTipo(),
                $comercio->getDireccion(),
                $comercio->getTelefono(),
                $comercio->getCantidadMaquinas(),
                $comercio->getFechaRegistro()
            );
            
            if (!$stmt->execute()) {
                throw new DomainException(
                    'Error al guardar el comercio: ' . $stmt->error,
                    DomainException::HTTP_INTERNAL_ERROR
                );
            }
            
        } catch (\Exception $e) {
            error_log("Error en MySQLComercioRepository::save: " . $e->getMessage());
            throw new DomainException(
                'Error al guardar el comercio',
                DomainException::HTTP_INTERNAL_ERROR,
                $e
            );
        }
    }
    
    /**
     * {@inheritdoc}
     */
    public function update(Comercio $comercio): void {
        $conn = $this->db->getConnection();
        
        try {
            $sql = "UPDATE comercio SET 
                    nombre = ?,
                    tipo = ?,
                    direccion = ?,
                    telefono = ?,
                    Cantidad_Maquinas = ?
                    WHERE ID_Comercio = ?";
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param(
                "ssssis",
                $comercio->getNombre(),
                $comercio->getTipo(),
                $comercio->getDireccion(),
                $comercio->getTelefono(),
                $comercio->getCantidadMaquinas(),
                $comercio->getId()
            );
            
            if (!$stmt->execute()) {
                throw new DomainException(
                    'Error al actualizar el comercio: ' . $stmt->error,
                    DomainException::HTTP_INTERNAL_ERROR
                );
            }
            
        } catch (\Exception $e) {
            error_log("Error en MySQLComercioRepository::update: " . $e->getMessage());
            throw new DomainException(
                'Error al actualizar el comercio',
                DomainException::HTTP_INTERNAL_ERROR,
                $e
            );
        }
    }
    
    /**
     * {@inheritdoc}
     */
    public function findById(string $id): ?Comercio {
        $conn = $this->db->getConnection();
        
        try {
            $sql = "SELECT * FROM comercio WHERE ID_Comercio = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $id);
            $stmt->execute();
            
            $result = $stmt->get_result();
            
            if ($result->num_rows === 0) {
                return null;
            }
            
            $row = $result->fetch_assoc();
            
            return $this->mapToEntity($row);
            
        } catch (\Exception $e) {
            error_log("Error en MySQLComercioRepository::findById: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * {@inheritdoc}
     */
    public function findAll(array $filters = [], int $offset = 0, int $limit = 10, string $orderBy = 'nombre', string $direction = 'ASC'): array {
        $conn = $this->db->getConnection();
        
        try {
            $sql = "SELECT * FROM comercio WHERE 1=1";
            $params = [];
            $types = "";
            
            // Aplicar filtros
            if (!empty($filters['nombre'])) {
                $sql .= " AND nombre LIKE ?";
                $params[] = "%{$filters['nombre']}%";
                $types .= "s";
            }
            
            if (!empty($filters['tipo'])) {
                $sql .= " AND tipo = ?";
                $params[] = $filters['tipo'];
                $types .= "s";
            }
            
            // Ordenamiento
            $sql .= " ORDER BY {$orderBy} {$direction}";
            
            // Paginación
            $sql .= " LIMIT ? OFFSET ?";
            $params[] = $limit;
            $params[] = $offset;
            $types .= "ii";
            
            $stmt = $conn->prepare($sql);
            
            if (!empty($params)) {
                $stmt->bind_param($types, ...$params);
            }
            
            $stmt->execute();
            $result = $stmt->get_result();
            
            $comercios = [];
            while ($row = $result->fetch_assoc()) {
                $comercios[] = $this->mapToEntity($row);
            }
            
            return $comercios;
            
        } catch (\Exception $e) {
            error_log("Error en MySQLComercioRepository::findAll: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * {@inheritdoc}
     */
    public function count(array $filters = []): int {
        $conn = $this->db->getConnection();
        
        try {
            $sql = "SELECT COUNT(*) as total FROM comercio WHERE 1=1";
            $params = [];
            $types = "";
            
            if (!empty($filters['nombre'])) {
                $sql .= " AND nombre LIKE ?";
                $params[] = "%{$filters['nombre']}%";
                $types .= "s";
            }
            
            if (!empty($filters['tipo'])) {
                $sql .= " AND tipo = ?";
                $params[] = $filters['tipo'];
                $types .= "s";
            }
            
            $stmt = $conn->prepare($sql);
            
            if (!empty($params)) {
                $stmt->bind_param($types, ...$params);
            }
            
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            
            return (int)$row['total'];
            
        } catch (\Exception $e) {
            error_log("Error en MySQLComercioRepository::count: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * {@inheritdoc}
     */
    public function delete(string $id): void {
        $conn = $this->db->getConnection();
        
        try {
            // Verificar si tiene máquinas asociadas
            $checkSql = "SELECT COUNT(*) as total FROM MaquinaRecreativa WHERE ID_Comercio = ?";
            $checkStmt = $conn->prepare($checkSql);
            $checkStmt->bind_param("s", $id);
            $checkStmt->execute();
            $checkResult = $checkStmt->get_result();
            $checkRow = $checkResult->fetch_assoc();
            
            if ($checkRow['total'] > 0) {
                throw new DomainException(
                    'No se puede eliminar el comercio porque tiene máquinas asociadas',
                    DomainException::HTTP_CONFLICT
                );
            }
            
            // Eliminar comercio
            $sql = "DELETE FROM comercio WHERE ID_Comercio = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $id);
            
            if (!$stmt->execute()) {
                throw new DomainException(
                    'Error al eliminar el comercio',
                    DomainException::HTTP_INTERNAL_ERROR
                );
            }
            
        } catch (DomainException $e) {
            throw $e;
        } catch (\Exception $e) {
            error_log("Error en MySQLComercioRepository::delete: " . $e->getMessage());
            throw new DomainException(
                'Error al eliminar el comercio',
                DomainException::HTTP_INTERNAL_ERROR,
                $e
            );
        }
    }
    
    /**
     * {@inheritdoc}
     */
    public function incrementarMaquinas(string $idComercio): void {
        $conn = $this->db->getConnection();
        
        try {
            $sql = "UPDATE comercio SET Cantidad_Maquinas = Cantidad_Maquinas + 1 WHERE ID_Comercio = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $idComercio);
            
            if (!$stmt->execute()) {
                throw new DomainException(
                    'Error al incrementar contador de máquinas',
                    DomainException::HTTP_INTERNAL_ERROR
                );
            }
            
        } catch (\Exception $e) {
            error_log("Error en MySQLComercioRepository::incrementarMaquinas: " . $e->getMessage());
            throw new DomainException(
                'Error al incrementar contador de máquinas',
                DomainException::HTTP_INTERNAL_ERROR,
                $e
            );
        }
    }
    
    /**
     * {@inheritdoc}
     */
    public function decrementarMaquinas(string $idComercio): void {
        $conn = $this->db->getConnection();
        
        try {
            $sql = "UPDATE comercio SET Cantidad_Maquinas = GREATEST(0, Cantidad_Maquinas - 1) WHERE ID_Comercio = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $idComercio);
            
            if (!$stmt->execute()) {
                throw new DomainException(
                    'Error al decrementar contador de máquinas',
                    DomainException::HTTP_INTERNAL_ERROR
                );
            }
            
        } catch (\Exception $e) {
            error_log("Error en MySQLComercioRepository::decrementarMaquinas: " . $e->getMessage());
            throw new DomainException(
                'Error al decrementar contador de máquinas',
                DomainException::HTTP_INTERNAL_ERROR,
                $e
            );
        }
    }
    
    /**
     * Mapea un array de BD a una entidad Comercio
     * 
     * @param array $row Datos de la BD
     * @return Comercio Entidad mapeada
     */
    private function mapToEntity(array $row): Comercio {
        $comercio = new Comercio(
            $row['nombre'],
            $row['tipo'],
            $row['direccion'],
            $row['telefono']
        );
        
        // Usar reflexión para establecer el ID y otros campos
        $reflection = new \ReflectionClass($comercio);
        
        $idProperty = $reflection->getProperty('id');
        $idProperty->setAccessible(true);
        $idProperty->setValue($comercio, $row['ID_Comercio']);
        
        if (isset($row['Cantidad_Maquinas'])) {
            $cantidadProperty = $reflection->getProperty('cantidadMaquinas');
            $cantidadProperty->setAccessible(true);
            $cantidadProperty->setValue($comercio, (int)$row['Cantidad_Maquinas']);
        }
        
        if (isset($row['fecha_registro'])) {
            $fechaProperty = $reflection->getProperty('fechaRegistro');
            $fechaProperty->setAccessible(true);
            $fechaProperty->setValue($comercio, $row['fecha_registro']);
        }
        
        return $comercio;
    }
}