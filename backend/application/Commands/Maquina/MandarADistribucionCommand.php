<?php
/**
 * application/commands/maquina/MandarADistribucionCommand.php
 *
 * Comando para enviar una máquina a distribución.
 *
 * @package maquinas_recreativas\Application\Commands\Maquina
 */

namespace maquinas_recreativas\Application\Commands\Maquina;

/**
 * Class MandarADistribucion
 */
final class MandarADistribucionCommand
{
    private string $idMaquina;
    private string $idRemitente;
    private string $mensaje;

    public function __construct(string $idMaquina, string $idRemitente, string $mensaje)
    {
        $this->idMaquina = $idMaquina;
        $this->idRemitente = $idRemitente;
        $this->mensaje = $mensaje;
    }

    public function idMaquina(): string { return $this->idMaquina; }
    public function idRemitente(): string { return $this->idRemitente; }
    public function mensaje(): string { return $this->mensaje; }
}