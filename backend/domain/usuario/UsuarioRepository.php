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
 *
 * Define las operaciones de persistencia para la entidad Usuario.
 * La implementación concreta (MySQL, Mock, etc.) residirá en la capa de Infrastructure.
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
}