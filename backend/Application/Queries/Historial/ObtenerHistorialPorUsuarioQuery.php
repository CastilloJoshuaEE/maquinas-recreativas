<?php
/**
 * application/queries/historial/ObtenerHistorialPorUsuarioQuery.php
 *
 * Query para obtener historial de un usuario específico.
 *
 * @package maquinas_recreativas\Application\Queries\Historial
 */

namespace maquinas_recreativas\Application\Queries\Historial;

/**
 * Class ObtenerHistorialPorUsuarioQuery
 */
final class ObtenerHistorialPorUsuarioQuery
{
    private string $idUsuario;
    private int $pagina;
    private int $porPagina;

    public function __construct(string $idUsuario, int $pagina = 1, int $porPagina = 50)
    {
        $this->idUsuario = $idUsuario;
        $this->pagina = $pagina;
        $this->porPagina = $porPagina;
    }

    public function getIdUsuario(): string { return $this->idUsuario; }
    public function getPagina(): int { return $this->pagina; }
    public function getPorPagina(): int { return $this->porPagina; }
}