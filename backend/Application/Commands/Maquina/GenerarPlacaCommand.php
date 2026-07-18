<?php
/**
 * application/commands/maquina/GenerarPlacaCommand.php
 *
 * Comando para generar una placa (componente logístico).
 *
 * @package maquinas_recreativas\Application\Commands\Maquina
 */

namespace maquinas_recreativas\Application\Commands\Maquina;
use maquinas_recreativas\Application\Commands\Command;

/**
 * Class GenerarPlaca
 */
final class GenerarPlacaCommand implements Command
{
    private string $idTecnico;
    public function __construct(string $idTecnico){
        $this->idTecnico = $idTecnico;
    }
    public function idTecnico():string {return $this->idTecnico;}
}