<?php
/**
 * application/queries/recaudacion/ObtenerRecaudaciones.php
 *
 * Query para obtener recaudaciones con filtros.
 *
 * @package maquinas_recreativas\Application\Queries\Recaudacion
 */

namespace maquinas_recreativas\Application\Queries\Recaudacion;

/**
 * Class ObtenerRecaudacionesQuery
 */
final class ObtenerRecaudacionesQuery
{
    private ?string $fechaInicio;
    private ?string $fechaFin;
    private ?string $idMaquina;
    private ?string $tipoComercio;
    private int $limit;
    private int $offset;

    public function __construct(
        ?string $fechaInicio = null,
        ?string $fechaFin = null,
        ?string $idMaquina = null,
        ?string $tipoComercio = null,
        int $limit = 100,
        int $offset = 0
    ) {
        $this->fechaInicio = $fechaInicio;
        $this->fechaFin = $fechaFin;
        $this->idMaquina = $idMaquina;
        $this->tipoComercio = $tipoComercio;
        $this->limit = $limit;
        $this->offset = $offset;
    }

    public function getFechaInicio(): ?string { return $this->fechaInicio; }
    public function getFechaFin(): ?string { return $this->fechaFin; }
    public function getIdMaquina(): ?string { return $this->idMaquina; }
    public function getTipoComercio(): ?string { return $this->tipoComercio; }
    public function getLimit(): int { return $this->limit; }
    public function getOffset(): int { return $this->offset; }
}