<?php
/**
 * application/commands/maquina/FinalizarMantenimientoCommand.php
 *
 * Comando para finalizar el mantenimiento de una máquina.
 *
 * @package maquinas_recreativas\Application\Commands\Maquina
 */

namespace maquinas_recreativas\Application\Commands\Maquina;

/**
 * Class FinalizarMantenimiento
 */
final class FinalizarMantenimientoCommand
{
    private string $idMaquina;
    private string $idRemitente;
    private bool $exito;
    private string $mensaje;

    public function __construct(string $idMaquina, string $idRemitente, bool $exito, string $mensaje)
    {
        $this->idMaquina = $idMaquina;
        $this->idRemitente = $idRemitente;
        $this->exito = $exito;
        $this->mensaje = $mensaje;
    }

    public function idMaquina(): string { return $this->idMaquina; }
    public function idRemitente(): string { return $this->idRemitente; }
    public function exito(): bool { return $this->exito; }
    public function mensaje(): string { return $this->mensaje; }
}