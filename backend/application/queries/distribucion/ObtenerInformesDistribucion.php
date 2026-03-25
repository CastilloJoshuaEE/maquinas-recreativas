<?php
/**
 * application/queries/distribucion/ObtenerInformesDistribucion.php
 *
 * Query para obtener informes de distribución con filtros.
 *
 * @package maquinas_recreativas\Application\Queries\Distribucion
 */

namespace maquinas_recreativas\Application\Queries\Distribucion;

/**
 * Class ObtenerInformesDistribucionQuery
 */
final class ObtenerInformesDistribucionQuery
{
    private ?string $estado;
    private ?string $idComercio;
    private ?string $idMaquina;
    private ?string $fechaInicio;
    private ?string $fechaFin;
    private int $limit;
    private int $offset;

    public function __construct(
        ?string $estado = null,
        ?string $idComercio = null,
        ?string $idMaquina = null,
        ?string $fechaInicio = null,
        ?string $fechaFin = null,
        int $limit = 100,
        int $offset = 0
    ) {
        $this->estado = $estado;
        $this->idComercio = $idComercio;
        $this->idMaquina = $idMaquina;
        $this->fechaInicio = $fechaInicio;
        $this->fechaFin = $fechaFin;
        $this->limit = $limit;
        $this->offset = $offset;
    }

    public function getEstado(): ?string { return $this->estado; }
    public function getIdComercio(): ?string { return $this->idComercio; }
    public function getIdMaquina(): ?string { return $this->idMaquina; }
    public function getFechaInicio(): ?string { return $this->fechaInicio; }
    public function getFechaFin(): ?string { return $this->fechaFin; }
    public function getLimit(): int { return $this->limit; }
    public function getOffset(): int { return $this->offset; }
}