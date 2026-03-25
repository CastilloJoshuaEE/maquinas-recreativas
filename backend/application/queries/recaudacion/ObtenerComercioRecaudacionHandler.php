<?php
/**
 * application/queries/recaudacion/ObtenerComercioRecaudacionHandler.php
 *
 * Manejador del query ObtenerComercioRecaudacion.
 *
 * @package maquinas_recreativas\Application\Queries\Recaudacion
 */

namespace maquinas_recreativas\Application\Queries\Recaudacion;

use maquinas_recreativas\Domain\Comercio\ComercioRepository;
use maquinas_recreativas\Domain\Shared\ValueObjects\Uuid;
use maquinas_recreativas\Domain\Shared\Exceptions\DomainException;

/**
 * Class ObtenerComercioRecaudacionHandler
 */
final class ObtenerComercioRecaudacionHandler
{
    private ComercioRepository $comercioRepository;

    public function __construct(ComercioRepository $comercioRepository)
    {
        $this->comercioRepository = $comercioRepository;
    }

    /**
     * Maneja el query de obtener comercio para recaudación.
     *
     * @param ObtenerComercioRecaudacionQuery $query
     * @return array
     * @throws DomainException
     */
    public function handle(ObtenerComercioRecaudacionQuery $query): array
    {
        $idComercio = new Uuid($query->getIdComercio());

        $comercio = $this->comercioRepository->findById($idComercio);
        if (!$comercio) {
            throw new DomainException('Comercio no encontrado.');
        }

        return [
            'id' => $comercio->id()->value(),
            'nombre' => $comercio->nombre(),
            'tipo' => $comercio->tipo(),
            'direccion' => $comercio->direccion(),
            'telefono' => $comercio->telefono(),
            'cantidad_maquinas' => $comercio->cantidadMaquinas()
        ];
    }
}