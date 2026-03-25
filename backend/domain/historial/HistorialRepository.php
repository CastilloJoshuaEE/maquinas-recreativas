<?php
/**
 * domain/historial/HistorialRepository.php
 *
 * Interfaz para el repositorio de historial.
 *
 * @package maquinas_recreativas\Domain\Historial
 */

namespace maquinas_recreativas\Domain\Historial;

use maquinas_recreativas\Domain\Shared\ValueObjects\Uuid;
use maquinas_recreativas\Domain\Maquina\MaquinaRecreativa;
use maquinas_recreativas\Domain\Usuario\Usuario;

/**
 * Interface HistorialRepository
 */
interface HistorialRepository
{
    public function save(HistorialMaquina $historial): void;
    public function saveActividad(HistorialActividad $actividad): void;

    public function findByMaquina(Uuid $idMaquina, int $limit = 50, int $offset = 0): array;
    public function findByUsuario(Uuid $idUsuario, int $limit = 50, int $offset = 0): array;
    public function findByAccion(string $accion, int $limit = 100, int $offset = 0): array;

    public function findGeneral(
        ?Uuid $idMaquina = null,
        ?Uuid $idUsuario = null,
        ?string $tipoUsuario = null,
        ?string $accion = null,
        ?string $fechaInicio = null,
        ?string $fechaFin = null,
        int $limit = 100,
        int $offset = 0
    ): array;

    public function countByFilters(
        ?Uuid $idMaquina = null,
        ?Uuid $idUsuario = null,
        ?string $tipoUsuario = null,
        ?string $accion = null,
        ?string $fechaInicio = null,
        ?string $fechaFin = null
    ): int;

    public function findActividadesByUsuario(Uuid $idUsuario, int $limit = 50): array;
    public function getResumenReciente(int $limite = 20): array;
}