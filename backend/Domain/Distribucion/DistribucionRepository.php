<?php
/**
 * domain/distribucion/DistribucionRepository.php
 *
 * Interfaz para el repositorio de distribución.
 *
 * @package maquinas_recreativas\Domain\Distribucion
 */

namespace maquinas_recreativas\Domain\Distribucion;

use maquinas_recreativas\Domain\Shared\ValueObjects\Uuid;
use maquinas_recreativas\Domain\Maquina\MaquinaRecreativa;

/**
 * Interface DistribucionRepository
 */
interface DistribucionRepository
{
    public function save(InformeDistribucion $informe): void;
    public function findById(Uuid $id): ?InformeDistribucion;
    public function findByMaquina(Uuid $idMaquina): ?InformeDistribucion;
    public function findAll(array $filters = [], int $limit = 100, int $offset = 0): array;
    public function updateEstado(Uuid $idMaquina, string $estado): bool;
}