<?php
/**
 * application/commands/maquina/GenerarPlacaCommand.php
 *
 * Comando para generar una placa (componente logístico).
 *
 * @package maquinas_recreativas\Application\Commands\Maquina
 */

namespace maquinas_recreativas\Application\Commands\Maquina;

/**
 * Class GenerarPlaca
 */
final class GenerarPlacaCommand
{
    private string $idTecnico;
    public function __construct(string $idTecnico){
        $this->idTecnico = $idTecnico;
    }
    public function idTecnico():string {return $this->idTecnico;}
}