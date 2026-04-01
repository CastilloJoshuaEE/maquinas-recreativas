<?php
/**
 * application/commands/recaudacion/EliminarRecaudacionCommand.php
 *
 * Comando para eliminar una recaudación.
 *
 * @package maquinas_recreativas\Application\Commands\Recaudacion
 */

namespace maquinas_recreativas\Application\Commands\Recaudacion;
use maquinas_recreativas\Application\Commands\Command;

/**
 * Class EliminarRecaudacion
 */
final class EliminarRecaudacionCommand implements Command
{
    private string $idRecaudacion;

    public function __construct(string $idRecaudacion)
    {
        $this->idRecaudacion = $idRecaudacion;
    }

    public function idRecaudacion(): string { return $this->idRecaudacion; }
}