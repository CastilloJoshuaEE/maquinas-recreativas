<?php
/**
 * application/commands/componente/LiberarComponentesCancelacionCommand.php
 *
 * Comando para liberar componentes durante cancelación de registro.
 *
 * @package maquinas_recreativas\Application\Commands\Componente
 */

namespace maquinas_recreativas\Application\Commands\Componente;

/**
 * Class LiberarComponentesCancelacionCommand
 */
final class LiberarComponentesCancelacionCommand
{
    private ?string $idPlaca;
    private ?string $idCarcasa;
    private string $idUsuario;
    public function __construct(string $idPlaca, ?string $idCarcasa, string $idUsuario){
        $this->idPlaca = $idPlaca;
        $this->idCarcasa = $idCarcasa;
        $this->idUsuario = $idUsuario;
    }
    public function idPlaca(): ?string { return $this->idPlaca; }
    public function idCarcasa(): ?string { return $this->idCarcasa; }
    public function idUsuario(): string { return $this->idUsuario; }    
}