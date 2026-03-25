<?php
/**
 * application/commands/maquina/PonerOperativa.php
 *
 * Comando para poner una máquina como operativa.
 *
 * @package maquinas_recreativas\Application\Commands\Maquina
 */

namespace maquinas_recreativas\Application\Commands\Maquina;

/**
 * Class PonerOperativa
 */
final class PonerOperativa
{
    private string $idMaquina;

    public function __construct(string $idMaquina)
    {
        $this->idMaquina = $idMaquina;
    }

    public function idMaquina(): string { return $this->idMaquina; }
}