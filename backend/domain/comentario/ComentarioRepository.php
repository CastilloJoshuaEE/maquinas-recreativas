<?php
/**
 * domain/comentario/ComentarioRepository.php
 *
 * Interfaz para el repositorio de comentarios.
 *
 * @package Reconocimiento\Domain\Comentario
 */

namespace Reconocimiento\Domain\Comentario;

use Reconocimiento\Domain\Shared\ValueObjects\Uuid;

/**
 * Interface ComentarioRepository
 */
interface ComentarioRepository
{
    public function save(Comentario $comentario): void;
    public function findById(Uuid $id): ?Comentario;
    public function findByReporte(Uuid $idReporte, Uuid $idUsuario): array;
    public function findByChat(Uuid $emisorId, Uuid $destinatarioId): array;
    public function deleteByReporte(Uuid $idReporte): bool;
}