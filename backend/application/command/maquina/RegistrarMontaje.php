<?php
/**
 * application/commands/maquina/RegistrarMontaje.php
 *
 * Comando para registrar el montaje de un componente.
 *
 * @package maquinas_recreativas\Application\Commands\Maquina
 */

namespace maquinas_recreativas\Application\Commands\Maquina;

/**
 * Class RegistrarMontaje
 */
final class RegistrarMontaje
{
    private string $idMaquina;
    private string $idComponente;
    private string $idTecnico;
    private ?string $detalle;
    public function __construct(
        string $idMaquina,
        string $idComponente,
        string $idTecnico,
        ?string $detalle=null
    ){
        $this->idMaquina = $idMaquina;
        $this->idComponente = $idComponente;
        $this->idTecnico = $idTecnico;
        $this->detalle = $detalle;
    }
    public function idMaquina(): string { return $this->idMaquina; }
    public function idComponente(): string { return $this->idComponente; }
    public function idTecnico(): string { return $this->idTecnico; }
    public function detalle(): ?string { return $this->detalle; }    
}