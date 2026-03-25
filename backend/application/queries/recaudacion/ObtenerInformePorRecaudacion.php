<?php
/**
 * application/queries/recaudacion/ObtenerInformePorRecaudacion.php
 *
 * Query para obtener informe por recaudación.
 *
 * @package maquinas_recreativas\Application\Queries\Recaudacion
 */

namespace maquinas_recreativas\Application\Queries\Recaudacion;

/**
 * Class ObtenerInformePorRecaudacionQuery
 */
final class ObtenerInformePorRecaudacionQuery
{
    private string $idRecaudacion;

    public function __construct(string $idRecaudacion)
    {
        $this->idRecaudacion = $idRecaudacion;
    }

    public function getIdRecaudacion(): string { return $this->idRecaudacion; }
}