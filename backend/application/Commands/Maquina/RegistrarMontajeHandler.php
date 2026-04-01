<?php
namespace maquinas_recreativas\Application\Commands\Maquina;

use maquinas_recreativas\Application\Commands\Command;
use maquinas_recreativas\Application\Commands\CommandHandler;
use maquinas_recreativas\Domain\Maquina\MaquinaRepository;
use maquinas_recreativas\Domain\Componente\ComponenteRepository;
use maquinas_recreativas\Domain\Montaje\Montaje;
use maquinas_recreativas\Domain\Montaje\MontajeRepository;
use maquinas_recreativas\Domain\Historial\HistorialMaquina;
use maquinas_recreativas\Domain\Historial\HistorialRepository;
use maquinas_recreativas\Domain\Usuario\UsuarioRepository;
use maquinas_recreativas\Domain\Shared\ValueObjects\Uuid;
use maquinas_recreativas\Domain\Shared\Exceptions\DomainException;

final class RegistrarMontajeHandler implements CommandHandler
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

    public function handle(Command $command): void
    {
        if (!$command instanceof RegistrarMontajeCommand) {
            throw new DomainException('Comando inválido');
        }

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

        $historial = HistorialMaquina::registrar(
            $idMaquina,
            $idTecnico,
            $tecnico->getTipo()->value(),
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