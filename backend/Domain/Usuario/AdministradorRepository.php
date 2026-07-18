<?php
/**
 * domain/usuario/AdministradorRepository.php
 *
 * Interfaz para operaciones administrativas sobre usuarios.
 *
 * @package maquinas_recreativas\Domain\Usuario
 */

namespace maquinas_recreativas\Domain\Usuario;

use maquinas_recreativas\Domain\Shared\ValueObjects\Uuid;

/**
 * Interface AdministradorRepository
 *
 * Define operaciones específicas para administradores del sistema.
 */
interface AdministradorRepository
{
    /**
     * Obtiene todos los usuarios aplicando filtros opcionales.
     *
     * @param array $filters Filtros disponibles: 'tipo', 'estado', 'ci', 'limit', 'offset'
     * @return array Lista de arrays con datos de usuarios.
     */
    public function findAllWithFilters(array $filters = []): array;

    /**
     * Obtiene estadísticas generales de usuarios.
     *
     * @return array{total: int, por_tipo: array, por_estado: array}
     */
    public function getEstadisticas(): array;

    /**
     * Actualiza el estado de un usuario.
     *
     * @param Uuid $usuarioId
     * @param string $nuevoEstado
     * @return bool
     */
    public function updateEstado(Uuid $usuarioId, string $nuevoEstado): bool;

    /**
     * Busca usuarios por término en nombre o apellido.
     *
     * @param string $termino
     * @param int $limit
     * @return array
     */
    public function buscarPorNombre(string $termino, int $limit = 10): array;
}