<?php
/**
 * application/commands/componente/UsarComponenteHandler.php
 *
 * Manejador del comando UsarComponente.
 *
 * @package Reconocimiento\Application\Commands\Componente
 */

namespace Reconocimiento\Application\Commands\Componente;

use Reconocimiento\Domain\Componente\ComponenteRepository;
use Reconocimiento\Domain\Maquina\MaquinaRepository;
use Reconocimiento\Domain\Usuario\UsuarioRepository;
use Reconocimiento\Domain\Montaje\Montaje;
use Reconocimiento\Domain\Montaje\MontajeRepository;
use Reconocimiento\Domain\Shared\ValueObjects\Uuid;
use Reconocimiento\Domain\Shared\Exceptions\DomainException;

/**
 * Class UsarComponenteHandler
 */
final class UsarComponenteHandler
{
    private ComponenteRepository $componenteRepository;
    private MaquinaRepository $maquinaRepository;
    private UsuarioRepository $usuarioRepository;
    private MontajeRepository $montajeRepository;

    public function __construct(
        ComponenteRepository $componenteRepository,
        MaquinaRepository $maquinaRepository,
        UsuarioRepository $usuarioRepository,
        MontajeRepository $montajeRepository
    ) {
        $this->componenteRepository = $componenteRepository;
        $this->maquinaRepository = $maquinaRepository;
        $this->usuarioRepository = $usuarioRepository;
        $this->montajeRepository = $montajeRepository;
    }

    public function handle(UsarComponente $command): void
    {
        $idComponente = new Uuid($command->idComponente());
        $idUsuario = new Uuid($command->idUsuario());

        $componente = $this->componenteRepository->findById($idComponente);
        if (!$componente) {
            throw new DomainException('Componente no encontrado');
        }

        $usuario = $this->usuarioRepository->findById($idUsuario);
        if (!$usuario) {
            throw new DomainException('Usuario no encontrado');
        }

        $idMaquina = $command->idMaquina() ? new Uuid($command->idMaquina()) : null;
        if ($idMaquina) {
            $maquina = $this->maquinaRepository->findById($idMaquina);
            if (!$maquina) {
                throw new DomainException('Máquina no encontrada');
            }
        }

        $componente->asignarAUso($idUsuario, $idMaquina);
        $this->componenteRepository->save($componente);

        // Registrar montaje si hay máquina
        if ($idMaquina) {
            $montaje = Montaje::registrar(
                $idMaquina,
                $idComponente,
                $idUsuario,
                "Componente {$componente->nombre()} asignado"
            );
            $this->montajeRepository->save($montaje);
        }
    }
}