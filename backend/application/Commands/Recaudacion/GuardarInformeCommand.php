<?php
/**
 * application/commands/recaudacion/GuardarInformeCommand.php
 *
 * Comando para guardar el informe de una recaudación.
 *
 * @package maquinas_recreativas\Application\Commands\Recaudacion
 */

namespace maquinas_recreativas\Application\Commands\Recaudacion;
use maquinas_recreativas\Application\Commands\Command;

/**
 * Class GuardarInforme
 */
final class GuardarInformeCommand implements Command
{
    private string $idRecaudacion;
    private string $ciUsuario;
    private string $nombreMaquina;
    private string $idComercio;
    private string $nombreComercio;
    private string $direccionComercio;
    private string $telefonoComercio;
    private float $montoTotal;
    private ?array $componentes;

    public function __construct(
        string $idRecaudacion,
        string $ciUsuario,
        string $nombreMaquina,
        string $idComercio,
        string $nombreComercio,
        string $direccionComercio,
        string $telefonoComercio,
        float $montoTotal,
        ?array $componentes = null
    ) {
        $this->idRecaudacion = $idRecaudacion;
        $this->ciUsuario = $ciUsuario;
        $this->nombreMaquina = $nombreMaquina;
        $this->idComercio = $idComercio;
        $this->nombreComercio = $nombreComercio;
        $this->direccionComercio = $direccionComercio;
        $this->telefonoComercio = $telefonoComercio;
        $this->montoTotal = $montoTotal;
        $this->componentes = $componentes;
    }

    public function idRecaudacion(): string { return $this->idRecaudacion; }
    public function ciUsuario(): string { return $this->ciUsuario; }
    public function nombreMaquina(): string { return $this->nombreMaquina; }
    public function idComercio(): string { return $this->idComercio; }
    public function nombreComercio(): string { return $this->nombreComercio; }
    public function direccionComercio(): string { return $this->direccionComercio; }
    public function telefonoComercio(): string { return $this->telefonoComercio; }
    public function montoTotal(): float { return $this->montoTotal; }
    public function componentes(): ?array { return $this->componentes; }
}