<?php
/**
 * application/commands/recaudacion/GuardarInformeHandler.php
 *
 * Manejador del comando GuardarInforme.
 *
 * @package maquinas_recreativas\Application\Commands\Recaudacion
 */

namespace maquinas_recreativas\Application\Commands\Recaudacion;

use maquinas_recreativas\Domain\Recaudacion\InformeRecaudacion;
use maquinas_recreativas\Domain\Recaudacion\DetalleInforme;
use maquinas_recreativas\Domain\Recaudacion\RecaudacionRepository;
use maquinas_recreativas\Domain\Componente\ComponenteRepository;
use maquinas_recreativas\Domain\Shared\ValueObjects\Uuid;
use maquinas_recreativas\Domain\Shared\Exceptions\DomainException;

/**
 * Class GuardarInformeHandler
 */
final class GuardarInformeHandler
{
    private RecaudacionRepository $recaudacionRepository;
    private ComponenteRepository $componenteRepository;

    public function __construct(
        RecaudacionRepository $recaudacionRepository,
        ComponenteRepository $componenteRepository
    ) {
        $this->recaudacionRepository = $recaudacionRepository;
        $this->componenteRepository = $componenteRepository;
    }

    public function handle(GuardarInforme $command): string
    {
        $idRecaudacion = new Uuid($command->idRecaudacion());
        $recaudacion = $this->recaudacionRepository->findById($idRecaudacion);

        if (!$recaudacion) {
            throw new DomainException('Recaudación no encontrada');
        }

        $idComercio = new Uuid($command->idComercio());

        $informe = InformeRecaudacion::crear(
            $idRecaudacion,
            $command->ciUsuario(),
            $command->nombreMaquina(),
            $idComercio,
            $command->nombreComercio(),
            $command->direccionComercio(),
            $command->telefonoComercio()
        );

        $this->recaudacionRepository->saveInforme($informe);

        if (!empty($command->componentes())) {
            foreach ($command->componentes() as $componenteData) {
                if (!isset($componenteData['ID_Componente'])) {
                    continue;
                }

                $idComponente = new Uuid($componenteData['ID_Componente']);
                $componente = $this->componenteRepository->findById($idComponente);

                if ($componente) {
                    $detalle = DetalleInforme::crear($informe->id(), $idComponente);
                    $this->recaudacionRepository->saveDetalle($detalle);
                }
            }
        }

        return $informe->id()->value();
    }
}