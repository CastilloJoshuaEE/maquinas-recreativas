<?php
/**
 * application/commands/maquina/RegistrarMaquinaHandler.php
 */

namespace maquinas_recreativas\Application\Commands\Maquina;

use maquinas_recreativas\Application\Commands\Command;
use maquinas_recreativas\Application\Commands\CommandHandler;
use maquinas_recreativas\Domain\Maquina\MaquinaRecreativa;
use maquinas_recreativas\Domain\Maquina\MaquinaRepository;
use maquinas_recreativas\Domain\Usuario\UsuarioRepository;
use maquinas_recreativas\Domain\Comercio\ComercioRepository;
use maquinas_recreativas\Domain\Componente\ComponenteRepository;
use maquinas_recreativas\Domain\Shared\ValueObjects\Uuid;
use maquinas_recreativas\Domain\Shared\Exceptions\DomainException;

final class RegistrarMaquinaHandler implements CommandHandler
{
    private MaquinaRepository $maquinaRepository;
    private UsuarioRepository $usuarioRepository;
    private ComercioRepository $comercioRepository;
    private ComponenteRepository $componenteRepository;

    public function __construct(
        MaquinaRepository $maquinaRepository,
        UsuarioRepository $usuarioRepository,
        ComercioRepository $comercioRepository,
        ComponenteRepository $componenteRepository
    ) {
        $this->maquinaRepository = $maquinaRepository;
        $this->usuarioRepository = $usuarioRepository;
        $this->comercioRepository = $comercioRepository;
        $this->componenteRepository = $componenteRepository;
    }

    public function handle(Command $command): string
    {
        if (!$command instanceof RegistrarMaquinaCommand) {
            throw new DomainException('Comando inválido');
        }

        $idComercio = new Uuid($command->idComercio());
        $comercio = $this->comercioRepository->buscarPorId($idComercio->value());
        if (!$comercio) {
            throw new DomainException('Comercio no encontrado');
        }

        // Obtener técnicos por especialidad
        $ensambladores = $this->usuarioRepository->findTecnicosByEspecialidad('Ensamblador');
        $comprobadores = $this->usuarioRepository->findTecnicosByEspecialidad('Comprobador');

        if (empty($ensambladores)) {
            throw new DomainException('No hay técnicos ensambladores disponibles');
        }
        if (empty($comprobadores)) {
            throw new DomainException('No hay técnicos comprobadores disponibles');
        }

        // Tomar el primer técnico de cada lista
        $idEnsamblador = $ensambladores[0]->getId();
        $idComprobador = $comprobadores[0]->getId();

        error_log("Asignando técnicos - Ensamblador: {$idEnsamblador->value()}, Comprobador: {$idComprobador->value()}");

        $maquina = MaquinaRecreativa::crear(
            $command->nombre(),
            $command->tipo(),
            $idComercio,
            $idEnsamblador,
            $idComprobador
        );

        $this->maquinaRepository->save($maquina);

        return $maquina->id()->value();
    }
}