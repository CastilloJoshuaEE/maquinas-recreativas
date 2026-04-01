<?php
/**
 * application/commands/maquina/PonerOperativaCommand.php
 *
 * Comando para poner una máquina como operativa.
 *
 * @package maquinas_recreativas\Application\Commands\Maquina
 */

namespace maquinas_recreativas\Application\Commands\Maquina;
use maquinas_recreativas\Application\Commands\Command;

/**
 * Class PonerOperativa
 */
final class PonerOperativaCommand implements Command
{
    private string $idMaquina;

    public function __construct(string $idMaquina)
    {
        $this->idMaquina = $idMaquina;
    }

    public function idMaquina(): string { return $this->idMaquina; }
}