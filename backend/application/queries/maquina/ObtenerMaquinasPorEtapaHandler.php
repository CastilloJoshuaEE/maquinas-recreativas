<?php
/**
 * application/queries/maquina/ObtenerMaquinasPorEtapaHandler.php
 */

namespace maquinas_recreativas\Application\Queries\Maquina;

use maquinas_recreativas\Domain\Maquina\MaquinaRepository;
use maquinas_recreativas\Domain\Maquina\EtapaMaquina;
use maquinas_recreativas\Domain\Shared\Exceptions\DomainException;

final class ObtenerMaquinasPorEtapaHandler
{
    private MaquinaRepository $maquinaRepository;

    public function __construct(MaquinaRepository $maquinaRepository)
    {
        $this->maquinaRepository = $maquinaRepository;
    }

    public function handle(ObtenerMaquinasPorEtapaQuery $query): array
    {
        $etapa = EtapaMaquina::fromString($query->getEtapa());
        $maquinas = $this->maquinaRepository->findByEtapa($etapa);

        return array_map(function ($maquina) {
            // CORREGIDO: usar toArray() en lugar de fechaRegistro()
            $data = $maquina->toArray();
            return [
                'id' => $maquina->id()->value(),
                'nombre' => $maquina->nombre(),
                'tipo' => $maquina->tipo(),
                'estado' => $maquina->estado()->value(),
                'etapa' => $maquina->etapa()->value(),
                'fecha_registro' => $data['Fecha_Registro'] ?? date('Y-m-d'),
                'id_comercio' => $maquina->idComercio()->value()
            ];
        }, $maquinas);
    }
}