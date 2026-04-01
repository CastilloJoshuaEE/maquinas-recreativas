<?php
/**
 * application/commands/recaudacion/RegistrarRecaudacionHandler.php
 *
 * Manejador del comando RegistrarRecaudacion.
 *
 * @package maquinas_recreativas\Application\Commands\Recaudacion
 */

namespace maquinas_recreativas\Application\Commands\Recaudacion;

use maquinas_recreativas\Application\Commands\Command;
use maquinas_recreativas\Application\Commands\CommandHandler;
use maquinas_recreativas\Domain\Recaudacion\Recaudacion;
use maquinas_recreativas\Domain\Recaudacion\RecaudacionRepository;
use maquinas_recreativas\Domain\Maquina\MaquinaRepository;
use maquinas_recreativas\Domain\Usuario\UsuarioRepository;
use maquinas_recreativas\Domain\Maquina\EstadoMaquina;
use maquinas_recreativas\Domain\Maquina\EtapaMaquina;
use maquinas_recreativas\Domain\Shared\ValueObjects\Uuid;
use maquinas_recreativas\Domain\Shared\Exceptions\DomainException;

final class RegistrarRecaudacionHandler implements CommandHandler
{
    private RecaudacionRepository $recaudacionRepository;
    private MaquinaRepository $maquinaRepository;
    private UsuarioRepository $usuarioRepository;

    public function __construct(
        RecaudacionRepository $recaudacionRepository,
        MaquinaRepository $maquinaRepository,
        UsuarioRepository $usuarioRepository
    ) {
        $this->recaudacionRepository = $recaudacionRepository;
        $this->maquinaRepository = $maquinaRepository;
        $this->usuarioRepository = $usuarioRepository;
    }

    public function handle(Command $command): string
    {
        if (!$command instanceof RegistrarRecaudacionCommand) {
            throw new DomainException('Comando inválido');
        }

        $idMaquina = new Uuid($command->idMaquina());
        $idUsuario = new Uuid($command->idUsuario());

        $maquina = $this->maquinaRepository->findById($idMaquina);
        if (!$maquina) {
            throw new DomainException('Máquina no encontrada');
        }

        if (!$maquina->estado()->equals(EstadoMaquina::OPERATIVA()) || !$maquina->etapa()->equals(EtapaMaquina::RECAUDACION())) {
            throw new DomainException('La máquina no está disponible para recaudación');
        }

        $usuario = $this->usuarioRepository->findById($idUsuario);
        if (!$usuario) {
            throw new DomainException('Usuario no válido');
        }

        $recaudacion = Recaudacion::crear(
            $idMaquina,
            $idUsuario,
            $command->tipoComercio(),
            $command->montoTotal(),
            $command->porcentajeComercio(),
            $command->detalle()
        );

        $this->recaudacionRepository->save($recaudacion);

        return $recaudacion->id()->value();
    }
}