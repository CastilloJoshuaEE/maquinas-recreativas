<?php
/**
 * RecreaSys - Infrastructure MySQL Repository
 *
 * Implementación con MySQL del repositorio de Usuario.
 *
 * @package RecreaSys\Infrastructure\Persistence\Repository
 * @author Tu Equipo
 * @version 1.0
 */

namespace RecreaSys\Infrastructure\Persistence\Repository;

use RecreaSys\Domain\Shared\ValueObjects\Uuid;
use RecreaSys\Domain\Usuario\Usuario;
use RecreaSys\Domain\Usuario\UsuarioRepository;
use RecreaSys\Infrastructure\Security\CifradoHelper; // Reutilizando tu helper
use mysqli;

/**
 * Class MySQLUsuarioRepository
 */
class MySQLUsuarioRepository implements UsuarioRepository
{
    private mysqli $connection;

    /**
     * MySQLUsuarioRepository constructor.
     *
     * @param mysqli $connection
     */
    public function __construct(mysqli $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @inheritDoc
     */
    public function save(Usuario $usuario): void
    {
        $sql = "INSERT INTO usuario (ID_Usuario, nombre, apellido, ci, email, usuario_asignado, contrasena, tipo, estado)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                nombre = VALUES(nombre),
                apellido = VALUES(apellido),
                ci = VALUES(ci),
                email = VALUES(email),
                contrasena = VALUES(contrasena),
                tipo = VALUES(tipo),
                estado = VALUES(estado)";

        $stmt = $this->connection->prepare($sql);
        if (!$stmt) {
            throw new \RuntimeException('Error preparando la consulta: ' . $this->connection->error);
        }

        $id = $usuario->getId()->value();
        $nombre = $usuario->getNombre();
        $apellido = $usuario->getApellido();
        $ciEncriptado = CifradoHelper::encriptar($usuario->getCi());
        $emailEncriptado = CifradoHelper::encriptar($usuario->getEmail());
        $usuarioAsignado = $usuario->getUsuarioAsignado();
        $contrasenaHash = $usuario->getContrasenaHash();
        $tipo = $usuario->getTipo();
        $estado = $usuario->getEstado();

        $stmt->bind_param(
            "sssssssss",
            $id,
            $nombre,
            $apellido,
            $ciEncriptado,
            $emailEncriptado,
            $usuarioAsignado,
            $contrasenaHash,
            $tipo,
            $estado
        );

        if (!$stmt->execute()) {
            throw new \RuntimeException('Error ejecutando la consulta: ' . $stmt->error);
        }

        // Manejar la tabla Tecnico si es necesario
        if ($usuario->getTipo() === 'Tecnico' && $usuario->getEspecialidad()) {
            $this->saveTecnico($usuario->getId(), $usuario->getEspecialidad());
        }
    }

    /**
     * @inheritDoc
     */
    public function searchById(Uuid $id): ?Usuario
    {
        $sql = "SELECT u.*, t.Especialidad
                FROM usuario u
                LEFT JOIN Tecnico t ON u.ID_Usuario = t.ID_Tecnico
                WHERE u.ID_Usuario = ?";
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("s", $idValue = $id->value());
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            return $this->mapRowToUsuario($row);
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function searchByUsuarioAsignado(string $usuarioAsignado): ?Usuario
    {
        // ... (implementación similar a searchById, pero con el filtro por usuario_asignado)
        return null; // Placeholder
    }

    /**
     * @inheritDoc
     */
    public function searchByEmail(string $email): ?Usuario
    {
        $emailEncriptado = CifradoHelper::encriptar($email);
        $sql = "SELECT u.*, t.Especialidad
                FROM usuario u
                LEFT JOIN Tecnico t ON u.ID_Usuario = t.ID_Tecnico
                WHERE u.email = ?";
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("s", $emailEncriptado);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            return $this->mapRowToUsuario($row);
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function delete(Uuid $id): void
    {
        $sql = "DELETE FROM usuario WHERE ID_Usuario = ?";
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("s", $idValue = $id->value());
        $stmt->execute();
    }

    /**
     * @inheritDoc
     */
    public function findAll(array $filtros = []): array
    {
        $sql = "SELECT u.*, t.Especialidad FROM usuario u LEFT JOIN Tecnico t ON u.ID_Usuario = t.ID_Tecnico ORDER BY u.nombre ASC";
        $result = $this->connection->query($sql);
        $usuarios = [];
        while ($row = $result->fetch_assoc()) {
            $usuarios[] = $this->mapRowToUsuario($row);
        }
        return $usuarios;
    }

    /**
     * Mapea una fila de la base de datos a una entidad Usuario.
     *
     * @param array $row
     * @return Usuario
     */
    private function mapRowToUsuario(array $row): Usuario
    {
        return new Usuario(
            new Uuid($row['ID_Usuario']),
            $row['nombre'],
            $row['apellido'],
            CifradoHelper::desencriptar($row['email']),
            CifradoHelper::desencriptar($row['ci']),
            $row['usuario_asignado'],
            $row['contrasena'],
            $row['tipo'],
            $row['estado'],
            $row['Especialidad'] ?? null
        );
    }

    /**
     * Guarda o actualiza la relación de un técnico.
     *
     * @param Uuid $id
     * @param string $especialidad
     * @return void
     */
    private function saveTecnico(Uuid $id, string $especialidad): void
    {
        $sql = "INSERT INTO Tecnico (ID_Tecnico, Especialidad) VALUES (?, ?)
                ON DUPLICATE KEY UPDATE Especialidad = VALUES(Especialidad)";
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("ss", $idValue = $id->value(), $especialidad);
        $stmt->execute();
    }
}