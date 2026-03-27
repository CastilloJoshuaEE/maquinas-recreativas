<?php
/**
 * application/commands/recaudacion/RegistrarRecaudacionCommand.php
 *
 * Comando para registrar una recaudación.
 *
 * @package maquinas_recreativas\Application\Commands\Recaudacion
 */

namespace maquinas_recreativas\Application\Commands\Recaudacion;

/**
 * Class RegistrarRecaudacion
 */
final class RegistrarRecaudacionCommand
{
    private string $idMaquina;
    private string $idUsuario;
    private string $tipoComercio;
    private float $montoTotal;
    private float $porcentajeComercio;
    private string $detalle;
    public function __construct(string $idMaquina, string $idUsuario, string $tipoComercio, float $montoTotal, float $porcentajeComercio, string $detalle=''){
        $this->idMaquina = $idMaquina;
        $this->idUsuario = $idUsuario;
        $this->tipoComercio = $tipoComercio;
        $this->montoTotal = $montoTotal;
        $this->porcentajeComercio = $porcentajeComercio;
        $this->detalle = $detalle;
        

    }
    public function idMaquina(): string { return $this->idMaquina; }
    public function idUsuario(): string { return $this->idUsuario; }
    public function tipoComercio(): string { return $this->tipoComercio; }
    public function montoTotal(): float { return $this->montoTotal; }
    public function porcentajeComercio(): float { return $this->porcentajeComercio; }
    public function detalle(): string { return $this->detalle; }    
}