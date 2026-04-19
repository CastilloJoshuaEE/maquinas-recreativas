<?php
/**
 * domain/recaudacion/RecaudacionRepository.php
 *
 * Interfaz para el repositorio de recaudaciones.
 *
 * @package maquinas_recreativas\Domain\Recaudacion
 */

namespace maquinas_recreativas\Domain\Recaudacion;

use maquinas_recreativas\Domain\Shared\ValueObjects\Uuid;
use maquinas_recreativas\Domain\Maquina\MaquinaRecreativa;
use maquinas_recreativas\Domain\Comercio\Comercio;

/**
 * Interface RecaudacionRepository
 */
interface RecaudacionRepository
{
    public function save(Recaudacion $recaudacion): void;
    public function saveInforme(InformeRecaudacion $informe): void;
    public function saveDetalle(DetalleInforme $detalle): void;

    public function findById(Uuid $id): ?Recaudacion;
    public function findInformeByRecaudacion(Uuid $idRecaudacion): ?InformeRecaudacion;
    public function findDetallesByInforme(Uuid $idInforme): array;

    public function findAll(array $filters = [], int $limit = 100, int $offset = 0): array;
    public function findResumenByTipoComercio(?int $limit = null): array;
    public function findMaquinasRecaudacion(): array;
    public function findMaquinasOperativasPorComercio(Comercio $comercio): array;
    public function findNombreMaquinaById(Uuid $idMaquina): ?string;
    public function delete(Uuid $id): bool;
}