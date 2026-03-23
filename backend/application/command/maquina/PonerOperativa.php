<?php
/**
 * application/commands/maquina/PonerOperativa.php
 *
 * Comando para poner una máquina como operativa.
 *
 * @package Reconocimiento\Application\Commands\Maquina
 */

namespace Reconocimiento\Application\Commands\Maquina;

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