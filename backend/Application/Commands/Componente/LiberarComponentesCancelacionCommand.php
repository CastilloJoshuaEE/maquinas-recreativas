<?php
/**
 * application/commands/componente/LiberarComponentesCancelacionCommand.php
 */

namespace maquinas_recreativas\Application\Commands\Componente;

use maquinas_recreativas\Application\Commands\Command;

final class LiberarComponentesCancelacionCommand implements Command
{
    private ?string $idPlaca;
    private ?string $idCarcasa;
    private string $idUsuario;
    
    public function __construct(?string $idPlaca, ?string $idCarcasa, string $idUsuario)
    {
        $this->idPlaca = $idPlaca;
        $this->idCarcasa = $idCarcasa;
        $this->idUsuario = $idUsuario;
    }
    
    public function idPlaca(): ?string { return $this->idPlaca; }
    public function idCarcasa(): ?string { return $this->idCarcasa; }
    public function idUsuario(): string { return $this->idUsuario; }    
}