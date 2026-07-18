<?php
/**
 * application/commands/componente/UsarComponenteHandler.php
 *
 * Manejador del comando UsarComponenteCommand.
 *
 * @package maquinas_recreativas\Application\Commands\Componente
 */

namespace maquinas_recreativas\Application\Commands\Componente;

use maquinas_recreativas\Application\Commands\Command;
use maquinas_recreativas\Application\Commands\CommandHandler;
use maquinas_recreativas\Domain\Componente\ComponenteRepository;
use maquinas_recreativas\Domain\Maquina\MaquinaRepository;
use maquinas_recreativas\Domain\Usuario\UsuarioRepository;
use maquinas_recreativas\Domain\Montaje\Montaje;
use maquinas_recreativas\Domain\Montaje\MontajeRepository;
use maquinas_recreativas\Domain\Shared\ValueObjects\Uuid;
use maquinas_recreativas\Domain\Shared\Exceptions\DomainException;

/**
 * Class UsarComponenteHandler
 */
final class UsarComponenteHandler implements CommandHandler
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

    public function handle(Command $command): void
{
    if (!$command instanceof UsarComponenteCommand) {
        throw new DomainException('Comando inválido');
    }

    error_log("=== UsarComponenteHandler::handle ===");
    error_log("Componente ID: " . $command->idComponente());
    error_log("Usuario ID: " . $command->idUsuario());
    error_log("Máquina ID: " . ($command->idMaquina() ?? 'null'));

    $idComponente = new Uuid($command->idComponente());
    $idUsuario = new Uuid($command->idUsuario());

    $componente = $this->componenteRepository->findById($idComponente);
    if (!$componente) {
        error_log("ERROR: Componente no encontrado: " . $command->idComponente());
        throw new DomainException('Componente no encontrado');
    }

    $usuario = $this->usuarioRepository->findById($idUsuario);
    if (!$usuario) {
        error_log("ERROR: Usuario no encontrado: " . $command->idUsuario());
        throw new DomainException('Usuario no encontrado');
    }

    // Verificar si el componente ya está en uso
    if (!$componente->estaDisponible()) {
        error_log("ERROR: Componente ya está en uso");
        throw new DomainException('El componente ya está siendo utilizado');
    }

    $idMaquina = $command->idMaquina() ? new Uuid($command->idMaquina()) : null;
    if ($idMaquina) {
        $maquina = $this->maquinaRepository->findById($idMaquina);
        if (!$maquina) {
            error_log("ERROR: Máquina no encontrada: " . $command->idMaquina());
            throw new DomainException('Máquina no encontrada');
        }
    }

    $componente->asignarAUso($idUsuario, $idMaquina);
    $this->componenteRepository->save($componente);
    
    error_log("Componente asignado correctamente");
}
}