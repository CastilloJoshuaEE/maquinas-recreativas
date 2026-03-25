<?php
/**
 * domain/notificacion/NotificacionRepository.php
 *
 * Interfaz para el repositorio de notificaciones.
 *
 * @package maquinas_recreativas\Domain\Notificacion
 */

namespace maquinas_recreativas\Domain\Notificacion;

use maquinas_recreativas\Domain\Shared\ValueObjects\Uuid;

/**
 * Interface NotificacionRepository
 */
interface NotificacionRepository
{
    public function saveMaquina(NotificacionMaquina $notificacion): void;
    public function saveReporte(NotificacionReporte $notificacion): void;

    public function findMaquinaById(Uuid $id): ?NotificacionMaquina;
    public function findReporteById(Uuid $id): ?NotificacionReporte;

    public function findMaquinasByDestinatario(Uuid $idDestinatario): array;
    public function findReportesByUsuario(Uuid $idUsuario): array;

    public function findNoLeidasMaquina(Uuid $idDestinatario): int;
    public function findNoLeidasReporte(Uuid $idUsuario): int;

    public function marcarLeidaMaquina(Uuid $id): bool;
    public function marcarLeidaReporte(Uuid $id, Uuid $idUsuario): bool;
    public function marcarTodasLeidasReporte(Uuid $idUsuario): bool;
}