<?php
/**
 * application/commands/recaudacion/RegistrarRecaudacionHandler.php
 *
 * Manejador del comando RegistrarRecaudacion.
 *
 * @package Reconocimiento\Application\Commands\Recaudacion
 */

namespace Reconocimiento\Application\Commands\Recaudacion;

use Reconocimiento\Domain\Recaudacion\Recaudacion;
use Reconocimiento\Domain\Recaudacion\RecaudacionRepository;
use Reconocimiento\Domain\Maquina\MaquinaRepository;
use Reconocimiento\Domain\Usuario\UsuarioRepository;
use Reconocimiento\Domain\Shared\ValueObjects\Uuid;
use Reconocimiento\Domain\Shared\Exceptions\DomainException;

/**
 * Class RegistrarRecaudacionHandler
 */
final class RegistrarRecaudacionHandler
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

    public function handle(RegistrarRecaudacion $command): string
    {
        $idMaquina = new Uuid($command->idMaquina());
        $idUsuario = new Uuid($command->idUsuario());

        $maquina = $this->maquinaRepository->findById($idMaquina);
        if (!$maquina) {
            throw new DomainException('Máquina no encontrada');
        }

        if (!$maquina->estado()->equals('Operativa') || !$maquina->etapa()->equals('Recaudacion')) {
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