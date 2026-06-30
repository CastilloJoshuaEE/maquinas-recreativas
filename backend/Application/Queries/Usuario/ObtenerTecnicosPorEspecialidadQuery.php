<?php
/**
 * application/queries/usuario/ObtenerTecnicosPorEspecialidad.php
 *
 * Query para obtener técnicos por especialidad.
 *
 * @package maquinas_recreativas\Application\Queries\Usuario
 */

namespace maquinas_recreativas\Application\Queries\Usuario;

/**
 * Class ObtenerTecnicosPorEspecialidadQuery
 */
final class ObtenerTecnicosPorEspecialidadQuery
{
    private string $especialidad;

    public function __construct(string $especialidad)
    {
        $this->especialidad = $especialidad;
    }

    public function getEspecialidad(): string { return $this->especialidad; }
}