<?php
/**
 * application/queries/maquina/ObtenerComponentesMaquinaHandler.php
 *
 * Manejador del query ObtenerComponentesMaquina.
 *
 * @package maquinas_recreativas\Application\Queries\Maquina
 */

namespace maquinas_recreativas\Application\Queries\Maquina;

use maquinas_recreativas\Domain\Maquina\MaquinaRepository;
use maquinas_recreativas\Domain\Shared\ValueObjects\Uuid;
use maquinas_recreativas\Domain\Shared\Exceptions\DomainException;

/**
 * Class ObtenerComponentesMaquinaHandler
 */
final class ObtenerComponentesMaquinaHandler
{
    private MaquinaRepository $maquinaRepository;

    public function __construct(MaquinaRepository $maquinaRepository)
    {
        $this->maquinaRepository = $maquinaRepository;
    }

    /**
     * Maneja el query de obtener componentes de una máquina.
     *
     * @param ObtenerComponentesMaquinaQuery $query
     * @return array
     * @throws DomainException
     */
    public function handle(ObtenerComponentesMaquinaQuery $query): array
    {
        $idMaquina = new Uuid($query->getIdMaquina());

        $maquina = $this->maquinaRepository->findById($idMaquina);
        if (!$maquina) {
            throw new DomainException('Máquina no encontrada.');
        }

        $componentes = $this->maquinaRepository->getComponentesMontaje($maquina);

        return array_map(function ($componente) {
            return [
                'id' => $componente->id()->value(),
                'tipo' => $componente->tipo()->value(),
                'nombre' => $componente->nombre(),
                'precio' => $componente->precio(),
                'esta_disponible' => $componente->estaDisponible(),
                'esta_asignado' => $componente->estaAsignado()
            ];
        }, $componentes);
    }
}