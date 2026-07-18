<?php
/**
 * domain/comentario/ComentarioRepository.php
 *
 * Interfaz para el repositorio de comentarios.
 *
 * @package maquinas_recreativas\Domain\Comentario
 */

namespace maquinas_recreativas\Domain\Comentario;

use maquinas_recreativas\Domain\Shared\ValueObjects\Uuid;

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
public function delete(Uuid $idComentario): bool;   
    }