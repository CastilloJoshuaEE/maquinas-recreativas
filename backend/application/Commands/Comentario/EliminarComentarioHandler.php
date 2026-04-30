<?php
namespace maquinas_recreativas\Application\Commands\Comentario;

use maquinas_recreativas\Domain\Comentario\ComentarioRepository;
use maquinas_recreativas\Domain\Shared\ValueObjects\Uuid;
use maquinas_recreativas\Domain\Shared\Exceptions\DomainException;
use maquinas_recreativas\Application\Commands\Command;
use maquinas_recreativas\Application\Commands\CommandHandler;

final class EliminarComentarioHandler implements CommandHandler
{
    private ComentarioRepository $comentarioRepository;

    public function __construct(ComentarioRepository $comentarioRepository)
    {
        $this->comentarioRepository = $comentarioRepository;
    }

    public function handle(Command $command): bool
    {
        if (!$command instanceof EliminarComentarioCommand) {
            throw new DomainException('Comando inválido');
        }

        $idComentario = new Uuid($command->idComentario());
        $idUsuario = new Uuid($command->idUsuario());

        $comentario = $this->comentarioRepository->findById($idComentario);
        if (!$comentario) {
            throw new DomainException('Comentario no encontrado', 404);
        }

        // Verificar que el usuario sea el emisor
        if (!$comentario->esDeUsuario($idUsuario)) {
            throw new DomainException('No autorizado para eliminar este comentario', 403);
        }

        // Verificar que no hayan pasado más de 15 minutos
        if (!$comentario->puedeSerEliminado()) {
            throw new DomainException('Ya no puedes eliminar este comentario. Solo tienes 15 minutos después de enviarlo.', 403);
        }

        // Eliminar el comentario (soft delete o hard delete)
        $this->comentarioRepository->delete($idComentario);

        return true;
    }
}