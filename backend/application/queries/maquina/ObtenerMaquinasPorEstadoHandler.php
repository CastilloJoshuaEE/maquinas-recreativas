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
        $data = $maquina->toArray();
        return [
            'id' => $data['ID_Maquina'],
            'nombre' => $data['Nombre_Maquina'],
            'tipo' => $data['Tipo'],
            'estado' => $data['Estado'],
            'etapa' => $data['Etapa'],
            'fecha_registro' => $data['Fecha_Registro'],
            'id_comercio' => $data['ID_Comercio']
        ];
    }, $maquinas);
}
}