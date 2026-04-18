<?php
/**
 * application/queries/recaudacion/ObtenerMaquinasOperativasPorComercioHandler.php
 */

namespace maquinas_recreativas\Application\Queries\Recaudacion;

use maquinas_recreativas\Domain\Recaudacion\RecaudacionRepository;
use maquinas_recreativas\Domain\Comercio\ComercioRepository;
use maquinas_recreativas\Domain\Shared\ValueObjects\Uuid;
use maquinas_recreativas\Domain\Shared\Exceptions\DomainException;

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

    public function handle(ObtenerMaquinasOperativasPorComercioQuery $query): array
    {
        $idComercio = new Uuid($query->getIdComercio());
        
        $comercio = $this->comercioRepository->buscarPorId($idComercio->value());
        if (!$comercio) {
            throw new DomainException('Comercio no encontrado');
        }
        
        $maquinas = $this->recaudacionRepository->findMaquinasOperativasPorComercio($comercio);
        
        error_log("ObtenerMaquinasOperativasPorComercio: comercio={$comercio->getNombre()}, máquinas=" . count($maquinas));
        
        //  Devolver el array directamente, no un objeto con 'maquinas'
        $resultado = [];
        foreach ($maquinas as $maquina) {
            if (is_array($maquina)) {
                $resultado[] = [
                    'ID_Maquina' => $maquina['ID_Maquina'],
                    'Nombre_Maquina' => $maquina['Nombre_Maquina'],
                    'Tipo' => $maquina['Tipo'] ?? '',
                    'NombreComercio' => $comercio->getNombre()
                ];
            } else {
                $resultado[] = [
                    'ID_Maquina' => $maquina->id()->value(),
                    'Nombre_Maquina' => $maquina->nombre(),
                    'Tipo' => $maquina->tipo(),
                    'NombreComercio' => $comercio->getNombre()
                ];
            }
        }
        
        return $resultado; //  Devolver solo el array, no un objeto envuelto
    }
}