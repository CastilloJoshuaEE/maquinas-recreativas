<?php
/**
 * application/commands/componente/AsignarCarcasaCommand.php
 *
 * Comando para asignar una carcasa a un técnico.
 *
 * @package maquinas_recreativas\Application\Commands\Componente
 */

namespace maquinas_recreativas\Application\Commands\Componente;

/**
 * Class AsignarCarcasa
 */
final class AsignarCarcasaCommand
{
    private string $idComponente;
    private string $idUsuario;
    private function __construct(string $idComponente, string $idUsuario){
        $this->idComponente = $idComponente;
        $this->idUsuario = $idUsuario;
    }

    public function idComponente(): string { return $this->idComponente; }
    public function idUsuario(): string { return $this->idUsuario; }    
}