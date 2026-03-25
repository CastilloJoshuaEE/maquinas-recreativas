<?php
/**
 * application/commands/componente/LiberarComponente.php
 *
 * Comando para liberar un componente.
 *
 * @package Reconocimiento\Application\Commands\Componente
 */

namespace Reconocimiento\Application\Commands\Componente;

/**
 * Class LiberarComponente
 */
final class LiberarComponente
{
    private string $idComponente;
    private string $idUsuario;
    public function __construct(
        string $idComponente,
        string $idUsuario
    ){
        $this->idComponente = $idComponente;
        $this->idUsuario = $idUsuario;
    }

    public function idComponente(): string { return $this->idComponente; }
    public function idUsuario(): string { return $this->idUsuario; }    
}