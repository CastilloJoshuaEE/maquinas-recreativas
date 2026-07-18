<?php
/**
 * application/queries/reporte/ObtenerChatQuery.php
 *
 * Query para obtener chat entre dos usuarios.
 *
 * @package maquinas_recreativas\Application\Queries\Reporte
 */

namespace maquinas_recreativas\Application\Queries\Reporte;

/**
 * Class ObtenerChatQuery
 */
final class ObtenerChatQuery
{
    private string $emisorId;
    private string $destinatarioId;

    public function __construct(string $emisorId, string $destinatarioId)
    {
        $this->emisorId = $emisorId;
        $this->destinatarioId = $destinatarioId;
    }

    public function getEmisorId(): string { return $this->emisorId; }
    public function getDestinatarioId(): string { return $this->destinatarioId; }
}