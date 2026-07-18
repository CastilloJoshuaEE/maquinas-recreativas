<?php
/**
 * application/queries/recaudacion/ObtenerRecaudacionPorIdHandler.php
 */

namespace maquinas_recreativas\Application\Queries\Recaudacion;

use maquinas_recreativas\Domain\Recaudacion\RecaudacionRepository;
use maquinas_recreativas\Domain\Maquina\MaquinaRepository;
use maquinas_recreativas\Domain\Shared\ValueObjects\Uuid;
use maquinas_recreativas\Domain\Shared\Exceptions\DomainException;

final class ObtenerRecaudacionPorIdHandler
{
    private RecaudacionRepository $recaudacionRepository;
    private MaquinaRepository $maquinaRepository;

    public function __construct(
        RecaudacionRepository $recaudacionRepository,
        MaquinaRepository $maquinaRepository
    ) {
        $this->recaudacionRepository = $recaudacionRepository;
        $this->maquinaRepository = $maquinaRepository;
    }

    public function handle(ObtenerRecaudacionPorIdQuery $query): array
    {
        $idRecaudacion = new Uuid($query->getIdRecaudacion());

        $recaudacion = $this->recaudacionRepository->findById($idRecaudacion);
        if (!$recaudacion) {
            throw new DomainException('Recaudación no encontrada.');
        }

        // Obtener el nombre de la máquina
        $nombreMaquina = $this->recaudacionRepository->findNombreMaquinaById($recaudacion->idMaquina());

        // Obtener la máquina para extraer los IDs de técnicos y comercio
        $maquina = $this->maquinaRepository->findById($recaudacion->idMaquina());
        $idTecnicoEnsamblador = null;
        $idTecnicoComprobador = null;
        $idTecnicoMantenimiento = null;
        $idComercio = null;
        
        if ($maquina) {
            // ID del comercio
            $idComercio = $maquina->idComercio() ? $maquina->idComercio()->value() : null;
            
            // IDs de técnicos
            $idTecnicoEnsamblador = $maquina->idTecnicoEnsamblador() ? $maquina->idTecnicoEnsamblador()->value() : null;
            $idTecnicoComprobador = $maquina->idTecnicoComprobador() ? $maquina->idTecnicoComprobador()->value() : null;
            $idTecnicoMantenimiento = $maquina->idTecnicoMantenimiento() ? $maquina->idTecnicoMantenimiento()->value() : null;
        }

        return [
            'recaudacion' => [
                'id' => $recaudacion->id()->value(),
                'id_maquina' => $recaudacion->idMaquina()->value(),
                'nombre_maquina' => $nombreMaquina,
                'id_comercio' => $idComercio,  // ← AÑADIR ID DEL COMERCIO
                'id_usuario' => $recaudacion->idUsuario()->value(),
                'tipo_comercio' => $recaudacion->tipoComercio(),
                'monto_total' => $recaudacion->montoTotal(),
                'monto_empresa' => $recaudacion->montoEmpresa(),
                'monto_comercio' => $recaudacion->montoComercio(),
                'porcentaje_comercio' => $recaudacion->porcentajeComercio(),
                'detalle' => $recaudacion->detalle(),
                'fecha' => $recaudacion->fecha()->format('Y-m-d H:i:s'),
                // IDs de técnicos
                'id_tecnico_ensamblador' => $idTecnicoEnsamblador,
                'id_tecnico_comprobador' => $idTecnicoComprobador,
                'id_tecnico_mantenimiento' => $idTecnicoMantenimiento,
            ]
        ];
    }
}