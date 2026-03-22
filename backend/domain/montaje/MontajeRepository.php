<?php
/**
 * domain/montaje/MontajeRepository.php
 *
 * Interfaz para el repositorio de montajes.
 *
 * @package Reconocimiento\Domain\Montaje
 */

namespace Reconocimiento\Domain\Montaje;

use Reconocimiento\Domain\Shared\ValueObjects\Uuid;

/**
 * Interface MontajeRepository
 */
interface MontajeRepository
{
    public function save(Montaje $montaje): void;
    public function findByMaquina(Uuid $idMaquina): array;
    public function findByComponente(Uuid $idComponente): array;
    public function findByTecnico(Uuid $idTecnico): array;
}