<?php
/**
 * application/commands/componente/UsarComponente.php
 *
 * Comando para usar/asignar un componente.
 *
 * @package Reconocimiento\Application\Commands\Componente
 */

namespace Reconocimiento\Application\Commands\Componente;

/**
 * Class UsarComponente
 */
final class UsarComponente
{
    private string $idComponente;
    private string $idUsuario;
    private ?string $idMaquina;
    public function __construct(
        string $idComponente,
        string $idUsuario,
        ?string $idMaquina =null
    ){
        $this->idComponente = $idComponente;
        $this->idUsuario = $idUsuario;
        $this->idMaquina = $idMaquina;
    }
    public function idComponente(): string { return $this->idComponente; }
    public function idUsuario(): string { return $this->idUsuario; }
    public function idMaquina(): ?string { return $this->idMaquina; }    
}