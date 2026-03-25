<?php
/**
 * application/queries/maquina/ObtenerMaquinasParaDistribucionHandler.php
 *
 * Manejador del query ObtenerMaquinasParaDistribucion.
 *
 * @package maquinas_recreativas\Application\Queries\Maquina
 */

namespace maquinas_recreativas\Application\Queries\Maquina;

use maquinas_recreativas\Domain\Maquina\MaquinaRepository;

/**
 * Class ObtenerMaquinasParaDistribucionHandler
 */
final class ObtenerMaquinasParaDistribucionHandler
{
    private MaquinaRepository $maquinaRepository;

    public function __construct(MaquinaRepository $maquinaRepository)
    {
        $this->maquinaRepository = $maquinaRepository;
    }

    /**
     * Maneja el query de obtener máquinas para distribución.
     *
     * @param ObtenerMaquinasParaDistribucionQuery $query
     * @return array
     */
    public function handle(ObtenerMaquinasParaDistribucionQuery $query): array
    {
        $maquinas = $this->maquinaRepository->findParaDistribucion();

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