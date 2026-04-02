<?php
/**
 * application/queries/maquina/ObtenerMaquinasPorEstadoHandler.php
 */

namespace maquinas_recreativas\Application\Queries\Maquina;

use maquinas_recreativas\Domain\Maquina\MaquinaRepository;
use maquinas_recreativas\Domain\Maquina\EstadoMaquina;
use maquinas_recreativas\Domain\Shared\Exceptions\DomainException;

final class ObtenerMaquinasPorEstadoHandler
{
    private MaquinaRepository $maquinaRepository;

    public function __construct(MaquinaRepository $maquinaRepository)
    {
        $this->maquinaRepository = $maquinaRepository;
    }
public function handle(ObtenerMaquinasPorEstadoQuery $query): array
{
    try {
        $estado = EstadoMaquina::fromString($query->getEstado());
        $maquinas = $this->maquinaRepository->findByEstado($estado);
        
        $resultado = [];
        foreach ($maquinas as $maquina) {
            $data = $maquina->toArray();
            $resultado[] = [
                'id' => $data['ID_Maquina'],
                'nombre' => $data['Nombre_Maquina'],
                'tipo' => $data['Tipo'],
                'estado' => $data['Estado'],
                'etapa' => $data['Etapa'],
                'fecha_registro' => $data['Fecha_Registro'],
                'id_comercio' => $data['ID_Comercio']
            ];
        }
        return ['success' => true, 'maquinas' => $resultado];
    } catch (\Exception $e) {
        error_log("Error en ObtenerMaquinasPorEstadoHandler: " . $e->getMessage());
        return ['success' => false, 'maquinas' => [], 'error' => $e->getMessage()];
    }
}
}