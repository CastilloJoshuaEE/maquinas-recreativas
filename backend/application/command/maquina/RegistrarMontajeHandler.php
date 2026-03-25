<?php
/**
 * application/commands/maquina/RegistrarMontajeHandler.php
 *
 * Manejador del comando RegistrarMontaje.
 *
 * @package Reconocimiento\Application\Commands\Maquina
 */

namespace Reconocimiento\Application\Commands\Maquina;

use Reconocimiento\Domain\Maquina\MaquinaRepository;
use Reconocimiento\Domain\Componente\ComponenteRepository;
use Reconocimiento\Domain\Montaje\Montaje;
use Reconocimiento\Domain\Montaje\MontajeRepository;
use Reconocimiento\Domain\Historial\HistorialMaquina;
use Reconocimiento\Domain\Historial\HistorialRepository;
use Reconocimiento\Domain\Usuario\UsuarioRepository;
use Reconocimiento\Domain\Shared\ValueObjects\Uuid;
use Reconocimiento\Domain\Shared\Exceptions\DomainException;

/**
 * Class RegistrarMontajeHandler
 */
final class RegistrarMontajeHandler
{
    private MaquinaRepository $maquinaRepository;
    private ComponenteRepository $componenteRepository;
    private MontajeRepository $montajeRepository;
    private HistorialRepository $historialRepository;
    private UsuarioRepository $usuarioRepository;

    public function __construct(
        MaquinaRepository $maquinaRepository,
        ComponenteRepository $componenteRepository,
        MontajeRepository $montajeRepository,
        HistorialRepository $historialRepository,
        UsuarioRepository $usuarioRepository
    ) {
        $this->maquinaRepository = $maquinaRepository;
        $this->componenteRepository = $componenteRepository;
        $this->montajeRepository = $montajeRepository;
        $this->historialRepository = $historialRepository;
        $this->usuarioRepository = $usuarioRepository;
    }

    public function handle(RegistrarMontaje $command): void
    {
        $idMaquina = new Uuid($command->idMaquina());
        $idComponente = new Uuid($command->idComponente());
        $idTecnico = new Uuid($command->idTecnico());

        $maquina = $this->maquinaRepository->findById($idMaquina);
        if (!$maquina) {
            throw new DomainException('Máquina no encontrada');
        }

        $componente = $this->componenteRepository->findById($idComponente);
        if (!$componente) {
            throw new DomainException('Componente no encontrado');
        }

        $tecnico = $this->usuarioRepository->findById($idTecnico);
        if (!$tecnico || !$tecnico->esTecnico()) {
            throw new DomainException('Técnico no válido');
        }

        $montaje = Montaje::registrar(
            $idMaquina,
            $idComponente,
            $idTecnico,
            $command->detalle() ?? ''
        );

        $this->montajeRepository->save($montaje);

        // Registrar en historial
        $historial = HistorialMaquina::registrar(
            $idMaquina,
            $idTecnico,
            $tecnico->tipo()->value(),
            'Montaje de componente',
            "Componente montado: {$componente->nombre()}. {$command->detalle()}",
            null,
            null,
            null,
            null,
            $_SERVER['REMOTE_ADDR'] ?? null,
            ['componente' => $componente->nombre(), 'detalle' => $command->detalle()]
        );
        $this->historialRepository->save($historial);
    }
}