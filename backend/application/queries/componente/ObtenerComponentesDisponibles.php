<?php
/**
 * application/queries/componente/ObtenerComponentesDisponibles.php
 *
 * Query para obtener componentes disponibles (no asignados).
 *
 * @package maquinas_recreativas\Application\Queries\Componente
 */

namespace maquinas_recreativas\Application\Queries\Componente;

/**
 * Class ObtenerComponentesDisponiblesQuery
 */
final class ObtenerComponentesDisponiblesQuery
{
    private ?string $tipo;

    public function __construct(?string $tipo = null)
    {
        $this->tipo = $tipo;
    }

    public function getTipo(): ?string { return $this->tipo; }
}