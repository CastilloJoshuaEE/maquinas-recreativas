<?php
/**
 * application/queries/historial/ObtenerHistorialPorMaquinaQuery.php
 *
 * Query para obtener historial de una máquina específica.
 *
 * @package maquinas_recreativas\Application\Queries\Historial
 */

namespace maquinas_recreativas\Application\Queries\Historial;

/**
 * Class ObtenerHistorialPorMaquinaQuery
 */
final class ObtenerHistorialPorMaquinaQuery
{
    private string $idMaquina;
    private int $pagina;
    private int $porPagina;

    public function __construct(string $idMaquina, int $pagina = 1, int $porPagina = 50)
    {
        $this->idMaquina = $idMaquina;
        $this->pagina = $pagina;
        $this->porPagina = $porPagina;
    }

    public function getIdMaquina(): string { return $this->idMaquina; }
    public function getPagina(): int { return $this->pagina; }
    public function getPorPagina(): int { return $this->porPagina; }
}