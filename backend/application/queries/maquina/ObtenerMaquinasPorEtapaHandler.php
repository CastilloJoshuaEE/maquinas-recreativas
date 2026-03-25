<?php
/**
 * application/queries/maquina/ObtenerMaquinasPorEtapaHandler.php
 *
 * Manejador del query ObtenerMaquinasPorEtapa.
 *
 * @package maquinas_recreativas\Application\Queries\Maquina
 */

namespace maquinas_recreativas\Application\Queries\Maquina;

use maquinas_recreativas\Domain\Maquina\MaquinaRepository;
use maquinas_recreativas\Domain\Maquina\EtapaMaquina;
use maquinas_recreativas\Domain\Shared\Exceptions\DomainException;

/**
 * Class ObtenerMaquinasPorEtapaHandler
 */
final class ObtenerMaquinasPorEtapaHandler
{
    private MaquinaRepository $maquinaRepository;

    public function __construct(MaquinaRepository $maquinaRepository)
    {
        $this->maquinaRepository = $maquinaRepository;
    }

    /**
     * Maneja el query de obtener máquinas por etapa.
     *
     * @param ObtenerMaquinasPorEtapaQuery $query
     * @return array
     * @throws DomainException
     */
    public function handle(ObtenerMaquinasPorEtapaQuery $query): array
    {
        $etapa = EtapaMaquina::fromString($query->getEtapa());

        $maquinas = $this->maquinaRepository->findByEtapa($etapa);

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