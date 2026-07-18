<?php
/**
 * application/queries/recaudacion/ObtenerInformePorRecaudacionHandler.php
 */

namespace maquinas_recreativas\Application\Queries\Recaudacion;

use maquinas_recreativas\Domain\Recaudacion\RecaudacionRepository;
use maquinas_recreativas\Domain\Shared\ValueObjects\Uuid;
use maquinas_recreativas\Domain\Shared\Exceptions\DomainException;

final class ObtenerInformePorRecaudacionHandler
{
    private RecaudacionRepository $recaudacionRepository;

    public function __construct(RecaudacionRepository $recaudacionRepository)
    {
        $this->recaudacionRepository = $recaudacionRepository;
    }

    public function handle(ObtenerInformePorRecaudacionQuery $query): array
    {
        $idRecaudacion = new Uuid($query->getIdRecaudacion());

        $recaudacion = $this->recaudacionRepository->findById($idRecaudacion);
        if (!$recaudacion) {
            throw new DomainException('Recaudación no encontrada.');
        }

        $informe = $this->recaudacionRepository->findInformeByRecaudacion($idRecaudacion);
        $componentes = $informe ? $this->recaudacionRepository->findDetallesByInforme($informe->id()) : [];

        // Obtener los técnicos de la máquina
        $tecnicos = $this->recaudacionRepository->findTecnicosByRecaudacion($idRecaudacion);

        return [
            'informe' => $informe ? [
                'id' => $informe->id()->value(),
                'id_recaudacion' => $informe->idRecaudacion()->value(),
                'ci_usuario' => $informe->ciUsuario(),
                'nombre_maquina' => $informe->nombreMaquina(),
                'id_comercio' => $informe->idComercio()->value(),
                'nombre_comercio' => $informe->nombreComercio(),
                'direccion_comercio' => $informe->direccionComercio(),
                'telefono_comercio' => $informe->telefonoComercio(),
                'pago_ensamblador' => $informe->pagoEnsamblador(),
                'pago_comprobador' => $informe->pagoComprobador(),
                'pago_mantenimiento' => $informe->pagoMantenimiento(),
                'empresa_nombre' => $informe->empresaNombre(),
                'empresa_descripcion' => $informe->empresaDescripcion()
            ] : null,
            'componentes' => $componentes,
            'tecnicos' => $tecnicos  // ← Agregar técnicos
        ];
    }
}