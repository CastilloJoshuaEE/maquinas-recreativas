<?php
/**
 * maquinas_recreativas - Domain Usuario Repository Interface
 *
 * Define el contrato para los repositorios de Usuario.
 *
 * @package maquinas_recreativas\Domain\Usuario
 * @author Tu Equipo
 * @version 1.0
 */

namespace maquinas_recreativas\Domain\Usuario;

use maquinas_recreativas\Domain\Shared\ValueObjects\Uuid;

/**
 * Interface UsuarioRepository
 */
interface UsuarioRepository
{
    /**
     * Guarda una entidad Usuario (crea o actualiza).
     *
     * @param Usuario $usuario
     * @return void
     */
    public function save(Usuario $usuario): void;

    /**
     * Busca un usuario por su ID (alias de searchById para compatibilidad).
     *
     * @param Uuid $id
     * @return Usuario|null
     */
    public function findById(Uuid $id): ?Usuario;

    /**
     * Busca un usuario por su ID.
     *
     * @param Uuid $id
     * @return Usuario|null
     */
    public function searchById(Uuid $id): ?Usuario;

    /**
     * Busca un usuario por su nombre de usuario (usuario_asignado).
     *
     * @param string $usuarioAsignado
     * @return Usuario|null
     */
    public function searchByUsuarioAsignado(string $usuarioAsignado): ?Usuario;

    /**
     * Busca un usuario por su email.
     *
     * @param string $email
     * @return Usuario|null
     */
    public function searchByEmail(string $email): ?Usuario;

    /**
     * Elimina un usuario por su ID.
     *
     * @param Uuid $id
     * @return void
     */
    public function delete(Uuid $id): void;

    /**
     * Retorna todos los usuarios, opcionalmente filtrados.
     *
     * @param array $filtros
     * @return Usuario[]
     */
    public function findAll(array $filtros = []): array;
public function existsByEmail(string $emailEncriptado): bool;
    public function existsByCi(string $ciEncriptada): bool;
    public function existsByUsuarioAsignado(string $usuarioAsignado): bool;
    public function existsByUsuarioAsignadoAndNotId(string $usuarioAsignado, Uuid $id): bool;
    public function hasMachinesAssigned(Uuid $id): bool;
    public function registrarLogout(Uuid $id): void;
    public function registrarActividad(Uuid $id, string $descripcion): void;
    public function obtenerHistorialActividades(Uuid $id): array;
    public function findByTipo(string $tipo, ?Uuid $excluirId = null): array;
    public function findTecnicosByEspecialidad(string $especialidad): array;
    public function findByEmail(string $email): ?Usuario;

}