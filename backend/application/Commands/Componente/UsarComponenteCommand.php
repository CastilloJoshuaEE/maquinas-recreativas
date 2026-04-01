<?php
/**
 * application/commands/componente/UsarComponenteCommand.php
 */

namespace maquinas_recreativas\Application\Commands\Componente;

use maquinas_recreativas\Application\Commands\Command;

final class UsarComponenteCommand implements Command
{
    private string $idComponente;
    private string $idUsuario;
    private ?string $idMaquina;
    
    public function __construct(string $idComponente, string $idUsuario, ?string $idMaquina = null)
    {
        $this->idComponente = $idComponente;
        $this->idUsuario = $idUsuario;
        $this->idMaquina = $idMaquina;
    }
    
    public function idComponente(): string { return $this->idComponente; }
    public function idUsuario(): string { return $this->idUsuario; }
    public function idMaquina(): ?string { return $this->idMaquina; }    
}