<?php
/**
 * domain/distribucion/DistribucionRepository.php
 *
 * Interfaz para el repositorio de distribución.
 *
 * @package Reconocimiento\Domain\Distribucion
 */

namespace Reconocimiento\Domain\Distribucion;

use Reconocimiento\Domain\Shared\ValueObjects\Uuid;
use Reconocimiento\Domain\Maquina\MaquinaRecreativa;

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