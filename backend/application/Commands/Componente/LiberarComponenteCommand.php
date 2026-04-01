<?php
/**
 * application/commands/componente/LiberarComponenteCommand.php
 */

namespace maquinas_recreativas\Application\Commands\Componente;

use maquinas_recreativas\Application\Commands\Command;

final class LiberarComponenteCommand implements Command
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