<?php
/**
 * application/commands/componente/AsignarCarcasaCommand.php
 */

namespace maquinas_recreativas\Application\Commands\Componente;

use maquinas_recreativas\Application\Commands\Command;

final class AsignarCarcasaCommand implements Command
{
    private string $idComponente;
    private string $idUsuario;
    
    public function __construct(string $idComponente, string $idUsuario)
    {
        $this->idComponente = $idComponente;
        $this->idUsuario = $idUsuario;
    }

    public function idComponente(): string { return $this->idComponente; }
    public function idUsuario(): string { return $this->idUsuario; }    
}