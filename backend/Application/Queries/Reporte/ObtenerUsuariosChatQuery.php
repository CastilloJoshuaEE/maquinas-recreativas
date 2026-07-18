<?php
/**
 * application/queries/reporte/ObtenerUsuariosChatQuery.php
 *
 * Query para obtener usuarios con los que un usuario ha chateado.
 *
 * @package maquinas_recreativas\Application\Queries\Reporte
 */

namespace maquinas_recreativas\Application\Queries\Reporte;

/**
 * Class ObtenerUsuariosChatQuery
 */
final class ObtenerUsuariosChatQuery
{
    private string $userId;

    public function __construct(string $userId)
    {
        $this->userId = $userId;
    }

    public function getUserId(): string { return $this->userId; }
}