<?php
/**
 * application/commands/recaudacion/ActualizarRecaudacionCommand.php
 *
 * Comando para actualizar una recaudación.
 *
 * @package maquinas_recreativas\Application\Commands\Recaudacion
 */

namespace maquinas_recreativas\Application\Commands\Recaudacion;

/**
 * Class ActualizarRecaudacion
 */
final class ActualizarRecaudacionCommand
{
    private string $idRecaudacion;
    private string $idMaquina;
    private string $tipoComercio;
    private float $montoTotal;
    private float $porcentajeComercio;
    private string $detalle;
    private string $fecha;

    public function __construct(
        string $idRecaudacion,
        string $idMaquina,
        string $tipoComercio,
        float $montoTotal,
        float $porcentajeComercio,
        string $detalle = '',
        string $fecha = ''
    ) {
        $this->idRecaudacion = $idRecaudacion;
        $this->idMaquina = $idMaquina;
        $this->tipoComercio = $tipoComercio;
        $this->montoTotal = $montoTotal;
        $this->porcentajeComercio = $porcentajeComercio;
        $this->detalle = $detalle;
        $this->fecha = $fecha;
    }

    public function idRecaudacion(): string { return $this->idRecaudacion; }
    public function idMaquina(): string { return $this->idMaquina; }
    public function tipoComercio(): string { return $this->tipoComercio; }
    public function montoTotal(): float { return $this->montoTotal; }
    public function porcentajeComercio(): float { return $this->porcentajeComercio; }
    public function detalle(): string { return $this->detalle; }
    public function fecha(): string { return $this->fecha; }
}