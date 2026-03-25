<?php
/**
 * application/queries/maquina/ObtenerMaquinasPorEstadoHandler.php
 *
 * Manejador del query ObtenerMaquinasPorEstado.
 *
 * @package maquinas_recreativas\Application\Queries\Maquina
 */

namespace maquinas_recreativas\Application\Queries\Maquina;

use maquinas_recreativas\Domain\Maquina\MaquinaRepository;
use maquinas_recreativas\Domain\Maquina\EstadoMaquina;
use maquinas_recreativas\Domain\Shared\Exceptions\DomainException;

/**
 * Class ObtenerMaquinasPorEstadoHandler
 */
final class ObtenerMaquinasPorEstadoHandler
{
    private MaquinaRepository $maquinaRepository;

    public function __construct(MaquinaRepository $maquinaRepository)
    {
        $this->maquinaRepository = $maquinaRepository;
    }

    /**
     * Maneja el query de obtener máquinas por estado.
     *
     * @param ObtenerMaquinasPorEstadoQuery $query
     * @return array
     * @throws DomainException
     */
    public function handle(ObtenerMaquinasPorEstadoQuery $query): array
    {
        $estado = EstadoMaquina::fromString($query->getEstado());

        $maquinas = $this->maquinaRepository->findByEstado($estado);

        return array_map(function ($maquina) {
            return [
                'id' => $maquina->id()->value(),
                'nombre' => $maquina->nombre(),
                'tipo' => $maquina->tipo(),
                'estado' => $maquina->estado()->value(),
                'etapa' => $maquina->etapa()->value(),
                'fecha_registro' => $maquina->fechaRegistro()->format('Y-m-d H:i:s'),
                'id_comercio' => $maquina->idComercio()->value()
            ];
        }, $maquinas);
    }
}