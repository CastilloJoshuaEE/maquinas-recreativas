<?php
/**
 * Domain/Comercio/ComercioRepository.php
 */

namespace maquinas_recreativas\Domain\Comercio;

use maquinas_recreativas\Domain\Shared\Exceptions\DomainException;

interface ComercioRepository
{
    public function guardar(Comercio $comercio): void;
    public function buscarPorId(string $id): ?Comercio;
    public function buscarPorNombre(string $nombre): ?Comercio;
    public function obtenerTodos(array $criterios = []): array;
    public function eliminar(string $id): void;
    public function existePorNombre(string $nombre, ?string $excluirId = null): bool;
    public function tieneMaquinas(string $id): bool;
    public function contar(array $criterios = []): int;
}