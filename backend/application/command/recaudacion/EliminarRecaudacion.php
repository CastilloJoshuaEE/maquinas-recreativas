<?php
/**
 * application/commands/recaudacion/EliminarRecaudacion.php
 *
 * Comando para eliminar una recaudación.
 *
 * @package Reconocimiento\Application\Commands\Recaudacion
 */

namespace Reconocimiento\Application\Commands\Recaudacion;

/**
 * Class EliminarRecaudacion
 */
final class EliminarRecaudacion
{
    private string $idRecaudacion;

    public function __construct(string $idRecaudacion)
    {
        $this->idRecaudacion = $idRecaudacion;
    }

    public function idRecaudacion(): string { return $this->idRecaudacion; }
}