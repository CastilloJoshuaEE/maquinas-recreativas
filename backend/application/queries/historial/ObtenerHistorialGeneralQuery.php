<?php
/**
 * application/queries/historial/ObtenerHistorialGeneralQuery.php
 *
 * Query para obtener historial general con filtros.
 *
 * @package maquinas_recreativas\Application\Queries\Historial
 */

namespace maquinas_recreativas\Application\Queries\Historial;

/**
 * Class ObtenerHistorialGeneralQuery
 */
final class ObtenerHistorialGeneralQuery
{
    private ?string $idMaquina;
    private ?string $idUsuario;
    private ?string $tipoUsuario;
    private ?string $accion;
    private ?string $fechaInicio;
    private ?string $fechaFin;
    private int $pagina;
    private int $porPagina;

    public function __construct(
        ?string $idMaquina = null,
        ?string $idUsuario = null,
        ?string $tipoUsuario = null,
        ?string $accion = null,
        ?string $fechaInicio = null,
        ?string $fechaFin = null,
        int $pagina = 1,
        int $porPagina = 100
    ) {
        $this->idMaquina = $idMaquina;
        $this->idUsuario = $idUsuario;
        $this->tipoUsuario = $tipoUsuario;
        $this->accion = $accion;
        $this->fechaInicio = $fechaInicio;
        $this->fechaFin = $fechaFin;
        $this->pagina = $pagina;
        $this->porPagina = $porPagina;
    }

    public function getIdMaquina(): ?string { return $this->idMaquina; }
    public function getIdUsuario(): ?string { return $this->idUsuario; }
    public function getTipoUsuario(): ?string { return $this->tipoUsuario; }
    public function getAccion(): ?string { return $this->accion; }
    public function getFechaInicio(): ?string { return $this->fechaInicio; }
    public function getFechaFin(): ?string { return $this->fechaFin; }
    public function getPagina(): int { return $this->pagina; }
    public function getPorPagina(): int { return $this->porPagina; }
}