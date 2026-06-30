<?php
/**
 * Domain/Usuario/TecnicoRepository.php
 */

namespace maquinas_recreativas\Domain\Usuario;

use maquinas_recreativas\Domain\Shared\ValueObjects\Uuid;

interface TecnicoRepository
{
    public function findByEspecialidad(string $especialidad): array;
    public function incrementarActividades(Uuid $tecnicoId): bool;
    public function findAvailableByEspecialidad(string $especialidad): array;
}