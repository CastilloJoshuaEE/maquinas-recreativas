<?php
/**
 * application/commands/maquina/DarMantenimiento.php
 *
 * Comando para solicitar mantenimiento de una máquina.
 *
 * @package Reconocimiento\Application\Commands\Maquina
 */

namespace Reconocimiento\Application\Commands\Maquina;

/**
 * Class DarMantenimiento
 */
final class DarMantenimiento
{
    private string $idMaquina;
    private string $mensaje;
    private string $idLogistica;

    public function __construct(string $idMaquina, string $mensaje, string $idLogistica)
    {
        $this->idMaquina = $idMaquina;
        $this->mensaje = $mensaje;
        $this->idLogistica = $idLogistica;
    }

    public function idMaquina(): string { return $this->idMaquina; }
    public function mensaje(): string { return $this->mensaje; }
    public function idLogistica(): string { return $this->idLogistica; }
}