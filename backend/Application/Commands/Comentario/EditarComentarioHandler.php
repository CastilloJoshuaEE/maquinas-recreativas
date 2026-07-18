<?php
namespace maquinas_recreativas\Application\Commands\Comentario;

use maquinas_recreativas\Domain\Comentario\ComentarioRepository;
use maquinas_recreativas\Domain\Shared\ValueObjects\Uuid;
use maquinas_recreativas\Domain\Shared\Exceptions\DomainException;
use maquinas_recreativas\Application\Commands\Command;
use maquinas_recreativas\Application\Commands\CommandHandler;

final class EditarComentarioHandler implements CommandHandler
{
    private ComentarioRepository $comentarioRepository;

    public function __construct(ComentarioRepository $comentarioRepository)
    {
        $this->comentarioRepository = $comentarioRepository;
    }

    public function handle(Command $command): bool
    {
        if (!$command instanceof EditarComentarioCommand) {
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
            throw new DomainException('No autorizado para editar este comentario', 403);
        }

        // Verificar que no hayan pasado más de 15 minutos
        if (!$comentario->puedeSerEditado()) {
            throw new DomainException('Ya no puedes editar este comentario. Solo tienes 15 minutos después de enviarlo.', 403);
        }

        // Actualizar el comentario
        $comentario->editar($command->nuevoComentario());
        $this->comentarioRepository->save($comentario);

        return true;
    }
}