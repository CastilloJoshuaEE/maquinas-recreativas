<?php
/**
 * domain/reporte/ReporteRepository.php
 *
 * Interfaz para el repositorio de reportes.
 *
 * @package maquinas_recreativas\Domain\Reporte
 */

namespace maquinas_recreativas\Domain\Reporte;

use maquinas_recreativas\Domain\Shared\ValueObjects\Uuid;

/**
 * Interface ReporteRepository
 */
interface ReporteRepository
{
    public function save(Reporte $reporte): void;
    public function findById(Uuid $id): ?Reporte;
    public function findByUsuario(Uuid $idUsuario): array;
    public function findChat(Uuid $emisorId, Uuid $destinatarioId): array;
    public function findUsuariosChat(Uuid $idUsuario): array;
    public function updateEstado(Uuid $id, EstadoReporte $estado): bool;
}