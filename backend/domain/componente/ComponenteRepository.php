<?php
/**
 * domain/componente/ComponenteRepository.php
 *
 * Interfaz para el repositorio de componentes.
 *
 * @package Reconocimiento\Domain\Componente
 */

namespace Reconocimiento\Domain\Componente;

use Reconocimiento\Domain\Shared\ValueObjects\Uuid;
use Reconocimiento\Domain\Usuario\Usuario;
use Reconocimiento\Domain\Maquina\MaquinaRecreativa;

/**
 * Interface ComponenteRepository
 */
interface ComponenteRepository
{
    public function save(Componente $componente): void;
    public function findById(Uuid $id): ?Componente;
    public function findByTipo(?TipoComponente $tipo = null, int $limit = 10, int $offset = 0): array;
    public function findDisponibles(?TipoComponente $tipo = null): array;
    public function findEnUsoPorUsuario(Uuid $idUsuario, ?Uuid $idMaquina = null): array;
    public function countByTipo(?TipoComponente $tipo = null): int;
    public function generarNumeroPlaca(): string;
}