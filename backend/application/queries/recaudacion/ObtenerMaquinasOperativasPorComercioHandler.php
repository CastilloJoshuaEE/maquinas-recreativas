<?php
/**
 * application/queries/recaudacion/ObtenerMaquinasOperativasPorComercioHandler.php
 *
 * Manejador del query ObtenerMaquinasOperativasPorComercio.
 *
 * @package maquinas_recreativas\Application\Queries\Recaudacion
 */

namespace maquinas_recreativas\Application\Queries\Recaudacion;

use maquinas_recreativas\Domain\Recaudacion\RecaudacionRepository;
use maquinas_recreativas\Domain\Comercio\ComercioRepository;
use maquinas_recreativas\Domain\Shared\ValueObjects\Uuid;
use maquinas_recreativas\Domain\Shared\Exceptions\DomainException;

/**
 * Class ObtenerMaquinasOperativasPorComercioHandler
 */
final class ObtenerMaquinasOperativasPorComercioHandler
{
    private RecaudacionRepository $recaudacionRepository;
    private ComercioRepository $comercioRepository;

    public function __construct(
        RecaudacionRepository $recaudacionRepository,
        ComercioRepository $comercioRepository
    ) {
        $this->recaudacionRepository = $recaudacionRepository;
        $this->comercioRepository = $comercioRepository;
    }

    /**
     * Maneja el query de obtener máquinas operativas por comercio.
     *
     * @param ObtenerMaquinasOperativasPorComercioQuery $query
     * @return array
     * @throws DomainException
     */
    public function handle(ObtenerMaquinasOperativasPorComercioQuery $query): array
    {
        $idComercio = new Uuid($query->getIdComercio());

        $comercio = $this->comercioRepository->buscarPorId($idComercio);
        if (!$comercio) {
            throw new DomainException('Comercio no encontrado.');
        }

        $maquinas = $this->recaudacionRepository->findMaquinasOperativasPorComercio($comercio);

        return $maquinas;
    }
}