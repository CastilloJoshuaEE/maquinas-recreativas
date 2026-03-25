<?php
/**
 * application/commands/maquina/RegistrarMaquinaHandler.php
 *
 * Manejador del comando RegistrarMaquina.
 *
 * @package maquinas_recreativas\Application\Commands\Maquina
 */

namespace maquinas_recreativas\Application\Commands\Maquina;

use maquinas_recreativas\Domain\Maquina\MaquinaRecreativa;
use maquinas_recreativas\Domain\Maquina\MaquinaRepository;
use maquinas_recreativas\Domain\Usuario\UsuarioRepository;
use maquinas_recreativas\Domain\Comercio\ComercioRepository;
use maquinas_recreativas\Domain\Componente\ComponenteRepository;
use maquinas_recreativas\Domain\Shared\ValueObjects\Uuid;
use maquinas_recreativas\Domain\Shared\Exceptions\DomainException;

/**
 * Class RegistrarMaquinaHandler
 */
final class RegistrarMaquinaHandler
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

    public function handle(RegistrarMaquina $command): string
    {
        $idComercio = new Uuid($command->idComercio());
        $comercio = $this->comercioRepository->findById($idComercio);
        if (!$comercio) {
            throw new DomainException('Comercio no encontrado');
        }

        $ensambladores = $this->usuarioRepository->findTecnicosByEspecialidad('Ensamblador');
        $comprobadores = $this->usuarioRepository->findTecnicosByEspecialidad('Comprobador');

        if (empty($ensambladores) || empty($comprobadores)) {
            throw new DomainException('No hay técnicos disponibles para asignar');
        }

        $idEnsamblador = $ensambladores[0]->id();
        $idComprobador = $comprobadores[0]->id();

        $maquina = MaquinaRecreativa::crear(
            $command->nombre(),
            $command->tipo(),
            $idComercio,
            $idEnsamblador,
            $idComprobador
        );

        $this->maquinaRepository->save($maquina);

        $idPlaca = new Uuid($command->idPlaca());
        $placa = $this->componenteRepository->findById($idPlaca);
        if ($placa) {
            $placa->asignarAUso($idEnsamblador, $maquina->id());
            $this->componenteRepository->save($placa);
            $this->registrarMontaje($maquina->id(), $idEnsamblador, $idPlaca, 'Placa base generada automáticamente');
        }

        $idCarcasa = new Uuid($command->idCarcasa());
        $carcasa = $this->componenteRepository->findById($idCarcasa);
        if ($carcasa) {
            $carcasa->asignarAUso($idEnsamblador, $maquina->id());
            $this->componenteRepository->save($carcasa);
            $this->registrarMontaje($maquina->id(), $idEnsamblador, $idCarcasa, 'Carcasa asignada');
        }

        $this->usuarioRepository->incrementarActividadesTecnico($idEnsamblador);
        $this->usuarioRepository->incrementarActividadesTecnico($idComprobador);

        return $maquina->id()->value();
    }

    private function registrarMontaje(Uuid $idMaquina, Uuid $idTecnico, string $idComponente, string $detalle): void
    {
        $conn = (new \maquinas_recreativas\Infrastructure\Database\Database())->getConnection();
        $sql = "INSERT INTO montaje (ID_Montaje, fecha, ID_Maquina, ID_Componente, ID_Tecnico, detalle) 
                VALUES (UUID(), NOW(), :idMaquina, :idComponente, :idTecnico, :detalle)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':idMaquina' => $idMaquina->value(),
            ':idComponente' => $idComponente,
            ':idTecnico' => $idTecnico->value(),
            ':detalle' => $detalle
        ]);
    }
}